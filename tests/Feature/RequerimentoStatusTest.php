<?php

use App\Models\Requerimento;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('analysis and optional inspection follow the permitted status sequence', function () {
    $protocolado = new Requerimento(['status' => Requerimento::STATUS_PROTOCOLADO]);
    $emAnalise = new Requerimento(['status' => Requerimento::STATUS_EM_ANALISE]);
    $analiseTecnica = new Requerimento(['status' => Requerimento::STATUS_EM_ANALISE_TECNICA]);
    $analiseConcluida = new Requerimento(['status' => Requerimento::STATUS_ANALISE_CONCLUIDA]);
    $vistoriaPendente = new Requerimento(['status' => Requerimento::STATUS_AGUARDANDO_VISTORIA]);
    $vistoriaConcluida = new Requerimento(['status' => Requerimento::STATUS_VISTORIA_CONCLUIDA]);

    expect($protocolado->proximosStatusPossiveis())
        ->toBe([Requerimento::STATUS_EM_ANALISE])
        ->and($emAnalise->proximosStatusPossiveis())
        ->toBe([
            Requerimento::STATUS_EM_ANALISE_TECNICA,
            Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
            Requerimento::STATUS_ENCERRADO,
        ])
        ->and($analiseTecnica->proximosStatusPossiveis())
        ->toBe([
            Requerimento::STATUS_ANALISE_CONCLUIDA,
            Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
            Requerimento::STATUS_ENCERRADO,
        ])
        ->and($analiseConcluida->proximosStatusPossiveis())
        ->toBe([
            Requerimento::STATUS_AGUARDANDO_VISTORIA,
            Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
            Requerimento::STATUS_DEFERIDO,
            Requerimento::STATUS_INDEFERIDO,
            Requerimento::STATUS_ENCERRADO,
        ])
        ->and($vistoriaPendente->proximosStatusPossiveis())
        ->toBe([
            Requerimento::STATUS_VISTORIA_CONCLUIDA,
            Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
            Requerimento::STATUS_ENCERRADO,
        ])
        ->and($vistoriaConcluida->proximosStatusPossiveis())
        ->toBe([
            Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
            Requerimento::STATUS_DEFERIDO,
            Requerimento::STATUS_INDEFERIDO,
            Requerimento::STATUS_ENCERRADO,
        ]);
});

test('a process follows the configured status transitions', function () {
    Mail::fake();
    Storage::fake('local');

    $cidadao = Usuario::create([
        'nome' => 'Maria de Souza',
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
        'role' => 'cidadao',
        'ativo' => true,
    ]);
    $servidor = Usuario::create([
        'nome' => 'Servidor Ambiental',
        'email' => 'servidor@example.com',
        'password' => Hash::make('password'),
        'role' => 'servidor',
        'ativo' => true,
    ]);
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);
    $servidor->setoresSobResponsabilidade()->attach($setor->id);

    $requerimento = Requerimento::create([
        'usuario_id' => $cidadao->id,
        'setor_id' => $setor->id,
        'objetoDoRequerimento' => 'Licença de Instalação',
    ]);

    expect($requerimento->fresh()->status)->toBe(Requerimento::STATUS_PROTOCOLADO)
        ->and($requerimento->fresh()->historicos()->first()->status)->toBe(Requerimento::STATUS_PROTOCOLADO);

    $this->actingAs($servidor)
        ->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
            'status' => Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
            'observacao' => 'Envie o relatório ambiental.',
            'nome_documento_solicitado' => 'Relatório ambiental',
        ])
        ->assertSessionHasErrors('status');

    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_ENCERRADO,
        'observacao' => 'Encerramento solicitado.',
    ])->assertSessionHasErrors('status');

    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_EM_ANALISE,
        'observacao' => 'Documentação recebida; análise iniciada.',
    ])->assertSessionDoesntHaveErrors();
    expect($requerimento->fresh()->historicos()->reorder()->latest('id')->first()->observacao)
        ->toBe('Documentação recebida; análise iniciada.');

    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
        'observacao' => 'Envie o relatório ambiental.',
        'nome_documento_solicitado' => 'Relatório ambiental',
    ])->assertSessionDoesntHaveErrors();

    $requerimento->refresh();
    expect($requerimento->status)->toBe(Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)
        ->and($requerimento->data_devolucao)->not->toBeNull()
        ->and($requerimento->historicos()->reorder()->orderByDesc('id')->first()->status)->toBe(Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO);

    $this->actingAs($cidadao)
        ->from(route('requerimentos.cidadao.visualizar', $requerimento->id))
        ->post(route('requerimentos.reenviar', $requerimento->id), [
            'motivo_correcao' => 'Enviei o relatório solicitado.',
            'arquivos' => [UploadedFile::fake()->createWithContent('relatorio.pdf', 'Relatório ambiental')],
        ])
        ->assertRedirect(route('requerimentos.cidadao.visualizar', $requerimento->id));

    expect($requerimento->fresh()->status)->toBe(Requerimento::STATUS_EM_ANALISE)
        ->and($requerimento->fresh()->data_devolucao)->toBeNull()
    ->and($requerimento->fresh()->historicos()->reorder()->latest('id')->first()->status)->toBe(Requerimento::STATUS_EM_ANALISE);

    $this->actingAs($servidor)
    ->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_EM_ANALISE_TECNICA,
    ])
    ->assertSessionDoesntHaveErrors();
    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_ANALISE_CONCLUIDA,
    ])->assertSessionDoesntHaveErrors();

    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_AGUARDANDO_VISTORIA,
    ])->assertSessionDoesntHaveErrors();
    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_VISTORIA_CONCLUIDA,
    ])->assertSessionDoesntHaveErrors();

    $this->actingAs($servidor)
        ->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
            'status' => Requerimento::STATUS_DEFERIDO,
        ])->assertSessionDoesntHaveErrors();

    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_LICENCA_EMITIDA,
    ])->assertSessionHasErrors('arquivos');

    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_LICENCA_EMITIDA,
        'arquivos' => [UploadedFile::fake()->createWithContent('licenca.pdf', 'Licença emitida')],
    ])->assertSessionDoesntHaveErrors();

    $documentoLicenca = $requerimento->fresh()->documentos()->latest('id')->firstOrFail();
    expect($requerimento->fresh()->status)->toBe(Requerimento::STATUS_LICENCA_EMITIDA)
        ->and($documentoLicenca->titulo)->toBe('Licença ambiental emitida');
});

test('a sector status update saves the attachment in history and identifies its sector', function () {
    Mail::fake();
    Storage::fake('local');

    $cidadao = Usuario::create([
        'nome' => 'Maria de Souza',
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
        'role' => 'cidadao',
        'ativo' => true,
    ]);
    $servidor = Usuario::create([
        'nome' => 'Servidor Ambiental',
        'email' => 'servidor@example.com',
        'password' => Hash::make('password'),
        'role' => 'servidor',
        'ativo' => true,
    ]);
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);
    $servidor->setoresSobResponsabilidade()->attach($setor->id);
    $requerimento = Requerimento::create([
        'usuario_id' => $cidadao->id,
        'setor_id' => $setor->id,
        'status' => Requerimento::STATUS_EM_ANALISE_TECNICA,
        'objetoDoRequerimento' => 'Licença de Instalação',
    ]);

    $this->actingAs($servidor)
        ->from(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
            'status' => Requerimento::STATUS_ANALISE_CONCLUIDA,
            'arquivos' => [UploadedFile::fake()->createWithContent('parecer.pdf', 'Parecer técnico')],
        ])
        ->assertRedirect(route('setor.requerimentos.show', [$setor->id, $requerimento->id]));

    $historico = $requerimento->fresh()->historicos()->reorder()->latest('id')->firstOrFail();
    $documento = $historico->documentos()->firstOrFail();

    expect($historico->setor_id)->toBe($setor->id)
        ->and($historico->observacao)->toBe('Status alterado para Análise concluída pelo setor.')
        ->and($documento->historico_requerimento_id)->toBe($historico->id);

    Storage::disk('local')->assertExists($documento->caminho);

    $this->get(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->assertOk()
        ->assertSee('Status do requerimento atualizado')
        ->assertDontSee('data-open-on-load="true"', false)
        ->assertSee('data-modal-target="modal-atender"', false)
        ->assertSee('data-modal-target="modal-encaminhar"', false)
        ->assertSee('id="acao-loading-overlay"', false)
        ->assertSee('Registrando atendimento...')
        ->assertSee('Documento anexado pelo setor')
        ->assertSee('SEMA')
        ->assertSee('parecer.pdf');
});

test('processes awaiting a response for 120 days are archived and receive a history entry', function () {
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

    $antigo = Requerimento::create([
        'usuario_id' => $cidadao->id,
        'setor_id' => $setor->id,
        'status' => Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
        'data_devolucao' => now()->subDays(120),
    ]);
    $recente = Requerimento::create([
        'usuario_id' => $cidadao->id,
        'setor_id' => $setor->id,
        'status' => Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
        'data_devolucao' => now()->subDays(119),
    ]);

    $this->artisan('requerimentos:expirar')
        ->expectsOutput('Processos arquivados: 1.')
        ->assertSuccessful();

    expect($antigo->fresh()->status)->toBe(Requerimento::STATUS_ARQUIVADO_COMPLEMENTACAO)
        ->and($antigo->fresh()->historicos()->reorder()->orderByDesc('id')->first()->status)->toBe(Requerimento::STATUS_ARQUIVADO_COMPLEMENTACAO)
        ->and($recente->fresh()->status)->toBe(Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO);
});

test('an indeferido process is final and cannot be resubmitted as a correction', function () {
    Mail::fake();

    $cidadao = Usuario::create([
        'nome' => 'Maria de Souza',
        'email' => 'maria@example.com',
        'password' => Hash::make('password'),
        'role' => 'cidadao',
        'ativo' => true,
    ]);
    $servidor = Usuario::create([
        'nome' => 'Servidor Ambiental',
        'email' => 'servidor@example.com',
        'password' => Hash::make('password'),
        'role' => 'servidor',
        'ativo' => true,
    ]);
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);
    $servidor->setoresSobResponsabilidade()->attach($setor->id);
    $requerimento = Requerimento::create([
        'usuario_id' => $cidadao->id,
        'setor_id' => $setor->id,
        'status' => Requerimento::STATUS_ANALISE_CONCLUIDA,
        'objetoDoRequerimento' => 'Licença de Instalação',
    ]);

    $this->actingAs($servidor)
        ->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
            'status' => Requerimento::STATUS_INDEFERIDO,
            'observacao' => 'A atividade não atende aos critérios ambientais.',
        ])
        ->assertSessionDoesntHaveErrors();

    expect($requerimento->fresh()->status)->toBe(Requerimento::STATUS_INDEFERIDO)
        ->and($requerimento->fresh()->proximosStatusPossiveis())->toBe([]);

    $this->actingAs($servidor)
        ->post(route('setor.requerimentos.encaminhar', [$setor->id, $requerimento->id]), [])
        ->assertStatus(409);

    $this->actingAs($cidadao)
        ->post(route('requerimentos.reenviar', $requerimento->id), [
            'motivo_correcao' => 'Solicito revisão.',
            'arquivos' => [UploadedFile::fake()->createWithContent('recurso.pdf', 'Recurso')],
        ])
        ->assertStatus(409);
});
