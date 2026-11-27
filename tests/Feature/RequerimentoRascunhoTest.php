<?php

use App\Models\AssuntoRequerimento;
use App\Models\Empreendimento;
use App\Models\RequerimentoRascunho;
use App\Models\RequerimentoRascunhoArquivo;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a citizen can save and resume an online draft with its files', function () {
    Storage::fake('local');

    $cidadao = Usuario::create([
        'nome' => 'Maria de Souza',
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
        'role' => 'cidadao',
        'ativo' => true,
    ]);
    $outroCidadao = Usuario::create([
        'nome' => 'Joao da Silva',
        'email' => 'joao@example.com',
        'password' => Hash::make('password'),
        'role' => 'cidadao',
        'ativo' => true,
    ]);
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);
    AssuntoRequerimento::create([
        'setor_id' => $setor->id,
        'descricao' => 'Licença de Operação',
        'ativo' => true,
    ]);
    AssuntoRequerimento::create([
        'setor_id' => $setor->id,
        'descricao' => 'Autorização Ambiental',
        'ativo' => true,
    ]);
    $empreendimento = Empreendimento::create([
        'usuario_id' => $cidadao->id,
        'nome' => 'Empreendimento da Maria',
    ]);
    $empreendimento->representantes()->attach($cidadao->id, [
        'termo_aceito' => true,
        'termo_aceito_em' => now(),
        'status' => 'ativo',
    ]);

    $formularioInicial = $this->actingAs($cidadao)
        ->get(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->assertOk()
        ->assertSee(route('requerimentos.rascunhos.show', $setor->id), false)
        ->assertSee('data-step="2"', false)
        ->assertSee('<textarea name="motivo" rows="5" required', false)
        ->assertSee('data-step="3"', false)
        ->assertSee('name="aceite_declaracao"', false)
        ->assertSee('name="representante_legal_id"', false)
        ->assertSee('Maria de Souza', false)
        ->assertDontSee('value="outro"', false)
        ->assertDontSee('name="objeto_outro"', false)
        ->assertSee('Finalizar')
        ->assertDontSee('name="email"', false)
        ->assertDontSee('name="celular"', false)
        ->assertDontSee('name="rua"', false);
    $htmlFormularioInicial = $formularioInicial->getContent();
    expect(strpos($htmlFormularioInicial, 'data-step="2"'))
        ->toBeLessThan(strpos($htmlFormularioInicial, 'name="empreendimento_id"'));

    $this->actingAs($cidadao)
        ->from(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->post(route('cidadao.enviar-email'), [
            'setor_id' => $setor->id,
            'objetoDoRequerimento' => 'Licença de Operação',
        ])
        ->assertRedirect(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->assertSessionHasErrors('motivo');

    $this->actingAs($cidadao)
        ->getJson(route('requerimentos.rascunhos.show', [
            'setor' => $setor->id,
            'objeto' => 'Licença de Operação',
        ]))
        ->assertOk()
        ->assertJsonPath('rascunho', null);

    $respostaEtapaUm = $this->actingAs($cidadao)->putJson(route('requerimentos.rascunhos.salvar', $setor->id), [
        'objeto' => 'Licença de Operação',
        'campos' => ['motivo' => 'Rascunho ainda na primeira etapa'],
        'etapa' => 1,
    ]);
    $respostaEtapaUm->assertUnprocessable();
    expect(RequerimentoRascunho::count())->toBe(0);

    $this->actingAs($cidadao)->putJson(route('requerimentos.rascunhos.salvar', $setor->id), [
        'objeto' => 'Licença de Operação',
        'campos' => [
            'email' => 'maria@example.com',
            'motivo' => 'Continuar de outro dispositivo',
            'empreendimento_id' => (string) $empreendimento->id,
            'representante_legal_id' => (string) $cidadao->id,
            'bacia_hidrografica' => 'Bacia salva no rascunho',
            'objetoDoRequerimento' => 'Licença de Operação',
            'aceite_declaracao' => [],
            'campo_nao_permitido' => 'não deve ser salvo',
        ],
        'etapa' => 2,
    ])->assertOk()->assertJsonPath('salvo', true);

    $rascunhoSalvo = RequerimentoRascunho::where('objeto_chave', 'Licença de Operação')->firstOrFail();
    expect(array_key_exists('aceite_declaracao', $rascunhoSalvo->campos))->toBeFalse();

    $this->actingAs($cidadao)->putJson(route('requerimentos.rascunhos.salvar', $setor->id), [
        'objeto' => 'Autorização Ambiental',
        'campos' => [
            'motivo' => 'Rascunho independente para outro objeto',
        ],
        'etapa' => 2,
    ])->assertOk()->assertJsonPath('salvo', true);

    $formularioComRascunho = $this->get(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->assertOk()
        ->assertSee('objeto-rascunho-badge', false)
        ->assertSee('Este objeto tem um rascunho', false)
        ->assertDontSee('Continuar rascunho', false);
    expect(substr_count($formularioComRascunho->getContent(), 'objeto-rascunho-badge'))->toBe(2);

    $this->actingAs($cidadao)->post(route('requerimentos.rascunhos.arquivos.salvar', $setor->id), [
        'objeto' => 'Licença de Operação',
        'etapa' => 3,
        'campo' => 'arquivos[]',
        'arquivos' => [UploadedFile::fake()->create('anexo.pdf', 10, 'application/pdf')],
    ])->assertOk()->assertJsonPath('quantidade', 1);

    $arquivo = RequerimentoRascunhoArquivo::firstOrFail();
    Storage::disk('local')->assertExists($arquivo->caminho);

    $this->getJson(route('requerimentos.rascunhos.show', [
        'setor' => $setor->id,
        'objeto' => 'Licença de Operação',
    ]))
        ->assertOk()
        ->assertJsonPath('rascunho.campos.motivo', 'Continuar de outro dispositivo')
        ->assertJsonPath('rascunho.campos.representante_legal_id', (string) $cidadao->id)
        ->assertJsonPath('rascunho.campos.bacia_hidrografica', 'Bacia salva no rascunho')
        ->assertJsonMissingPath('rascunho.campos.campo_nao_permitido')
        ->assertJsonPath('rascunho.objeto', 'Licença de Operação')
        ->assertJsonPath('rascunho.etapa', 3)
        ->assertJsonPath('arquivos.0.nome', 'anexo.pdf');

    $this->actingAs($outroCidadao)
        ->getJson(route('requerimentos.rascunhos.show', [
            'setor' => $setor->id,
            'objeto' => 'Licença de Operação',
        ]))
        ->assertOk()
        ->assertJsonPath('rascunho', null);

    $this->actingAs($outroCidadao)
        ->get(route('requerimentos.rascunhos.arquivos.baixar', [$setor->id, $arquivo->id]))
        ->assertNotFound();

    $this->actingAs($cidadao)
        ->deleteJson(route('requerimentos.rascunhos.destruir', [
            'setor' => $setor->id,
            'objeto' => 'Licença de Operação',
        ]))
        ->assertOk()
        ->assertJsonPath('removido', true);

    expect(RequerimentoRascunho::count())->toBe(1)
        ->and(RequerimentoRascunhoArquivo::count())->toBe(0);
    Storage::disk('local')->assertMissing($arquivo->caminho);

    $this->actingAs($cidadao)->getJson(route('requerimentos.rascunhos.show', [
        'setor' => $setor->id,
        'objeto' => 'Autorização Ambiental',
    ]))
        ->assertOk()
        ->assertJsonPath('rascunho.campos.motivo', 'Rascunho independente para outro objeto');

    $this->actingAs($cidadao)->deleteJson(route('requerimentos.rascunhos.destruir', [
        'setor' => $setor->id,
        'objeto' => 'Autorização Ambiental',
    ]))->assertOk();
    expect(RequerimentoRascunho::count())->toBe(0);
});
