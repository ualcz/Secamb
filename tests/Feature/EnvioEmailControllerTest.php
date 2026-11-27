<?php

use App\Models\Empreendimento;
use App\Models\Requerimento;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function dadosCaracterizacaoRequerimentoTeste(array $substituicoes = []): array
{
    return array_merge([
        'bacia_hidrografica' => 'Bacia desta solicitação',
        'recurso_hidrico' => 'Rio de teste',
        'latitude' => '-12.3',
        'longitude' => '-41.7',
        'unidade_conservacao_resposta' => 'nao',
        'licenca_anterior' => 'nao',
        'processo_semma' => 'nao',
        'pendencia_ambiental' => 'nao',
    ], $substituicoes);
}

test('protocol stores and displays the generated request pdf in the initial history', function () {
    Mail::fake();
    Storage::fake('local');

    $cidadao = Usuario::create([
        'nome' => 'Maria de Souza',
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
        'role' => 'cidadao',
        'ativo' => true,
    ]);
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'email' => 'sema@example.com',
        'ativo' => true,
        'is_interno' => false,
    ]);
    $empreendimento = Empreendimento::create([
        'usuario_id' => $cidadao->id,
        'nome' => 'Empreendimento de teste',
        'bacia_hidrografica' => 'Bacia legada do cadastro',
    ]);
    $dadosRequerimento = array_merge([
        'setor_id' => $setor->id,
        'empreendimento_id' => $empreendimento->id,
        'objeto' => 'Licença de Instalação',
        'motivo' => 'Solicitação de licença ambiental.',
    ], dadosCaracterizacaoRequerimentoTeste());

    $this->actingAs($cidadao)
        ->from(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->post(route('cidadao.enviar-email'), $dadosRequerimento)
        ->assertRedirect(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->assertSessionHasErrors('aceite_declaracao');

    expect(Requerimento::count())->toBe(0);

    $this->actingAs($cidadao)
        ->post(route('cidadao.enviar-email'), array_merge($dadosRequerimento, [
            'aceite_declaracao' => '1',
        ]))
        ->assertRedirect(route('requerimentos.cidadao.meusRequerimentos'));

    $requerimento = Requerimento::query()->latest('id')->firstOrFail();
    expect($requerimento->caracterizacao_empreendimento['bacia_hidrografica'])
        ->toBe('Bacia desta solicitação');
    $historicoInicial = $requerimento->historicos()
        ->where('status', Requerimento::STATUS_PROTOCOLADO)
        ->firstOrFail();
    $pdfRequerimento = $historicoInicial->documentos()
        ->where('nome_documento', 'Requerimento enviado')
        ->firstOrFail();

    expect($pdfRequerimento->extensao)->toBe('pdf')
        ->and($pdfRequerimento->historico_requerimento_id)->toBe($historicoInicial->id);

    Storage::disk('local')->assertExists($pdfRequerimento->caminho);

    $this->get(route('requerimentos.cidadao.visualizar', $requerimento->id))
        ->assertOk()
        ->assertSee('Requerimento enviado')
        ->assertSee('Bacia desta solicitação')
        ->assertDontSee('Bacia legada do cadastro')
        ->assertSee('Visualizar');
});

test('environmental follow-up fields are required only for the corresponding answer', function () {
    Mail::fake();
    Storage::fake('local');
    $cidadao = Usuario::create([
        'nome' => 'Maria de Souza',
        'email' => 'maria@example.com',
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
    $empreendimento = Empreendimento::create([
        'usuario_id' => $cidadao->id,
        'nome' => 'Empreendimento de teste',
    ]);

    $baseRequest = [
        'setor_id' => $setor->id,
        'empreendimento_id' => $empreendimento->id,
        'objetoDoRequerimento' => 'Licença de Instalação',
        'motivo' => 'Solicitação de licença ambiental.',
        'aceite_declaracao' => '1',
    ];

    $this->actingAs($cidadao)
        ->from(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->post(route('cidadao.enviar-email'), array_merge($baseRequest, dadosCaracterizacaoRequerimentoTeste([
            'licenca_anterior' => 'sim',
        ])))
        ->assertRedirect(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->assertSessionHasErrors('localizacao_licenca');

    $this->actingAs($cidadao)
        ->from(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->post(route('cidadao.enviar-email'), array_merge($baseRequest, dadosCaracterizacaoRequerimentoTeste([
            'pendencia_ambiental' => 'sim',
        ])))
        ->assertRedirect(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->assertSessionHasErrors('notificacao_pendencia');

    expect(Requerimento::count())->toBe(0);
});

test('administrator can update the characterization snapshot of a request', function () {
    $admin = Usuario::create([
        'nome' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'ativo' => true,
    ]);
    $cidadao = Usuario::create([
        'nome' => 'Maria de Souza',
        'email' => 'maria@example.com',
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
    $empreendimento = Empreendimento::create([
        'usuario_id' => $cidadao->id,
        'nome' => 'Empreendimento de teste',
    ]);
    $requerimento = Requerimento::create([
        'usuario_id' => $cidadao->id,
        'setor_id' => $setor->id,
        'empreendimento_id' => $empreendimento->id,
        'objetoDoRequerimento' => 'Licença de Instalação',
        'motivo' => 'Solicitação de licença ambiental.',
        'caracterizacao_empreendimento' => dadosCaracterizacaoRequerimentoTeste(),
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.historico.caracterizacao', $requerimento), dadosCaracterizacaoRequerimentoTeste([
            'bacia_hidrografica' => 'Bacia corrigida pelo administrador',
        ]))
        ->assertRedirect(route('admin.historico', $requerimento));

    expect($requerimento->fresh()->caracterizacao_empreendimento['bacia_hidrografica'])
        ->toBe('Bacia corrigida pelo administrador');

    $this->actingAs($admin)
        ->get(route('admin.historico', $requerimento))
        ->assertOk()
        ->assertSee('Editar caracterização desta solicitação')
        ->assertSee('Bacia corrigida pelo administrador');
});
