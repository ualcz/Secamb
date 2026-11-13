<?php

use App\Models\AssuntoRequerimento;
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

    $this->actingAs($cidadao)
        ->get(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->assertOk()
        ->assertSee(route('requerimentos.rascunhos.show', $setor->id), false)
        ->assertSee('data-step="2"', false)
        ->assertSee('name="motivo"', false)
        ->assertSee('data-step="3"', false)
        ->assertSee('Finalizar')
        ->assertDontSee('name="email"', false)
        ->assertDontSee('name="celular"', false)
        ->assertDontSee('name="rua"', false);

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
            'objetoDoRequerimento' => 'Licença de Operação',
            'campo_nao_permitido' => 'não deve ser salvo',
        ],
        'etapa' => 2,
    ])->assertOk()->assertJsonPath('salvo', true);

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
