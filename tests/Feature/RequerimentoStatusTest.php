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
        ->and($historico->observacao)->toBeNull()
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
        ->assertSee('Documentos (1)')
        ->assertDontSee('Status alterado para Análise concluída pelo setor.')
        ->assertSee('parecer.pdf');
});

test('a sector can attach documents without changing status or emailing the citizen', function () {
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
    $statusOriginal = $requerimento->status;
    $historicoStatusAtual = $requerimento->historicos()->firstOrFail();
    $historicoLegado = \App\Models\HistoricoRequerimento::create([
        'requerimento_id' => $requerimento->id,
        'user_id' => $servidor->id,
        'setor_id' => $setor->id,
        'status' => $statusOriginal,
        'observacao' => 'Documento anexado pelo setor.',
    ]);
    $historicoLegado->forceFill([
        'created_at' => now()->addSecond(),
        'updated_at' => now()->addSecond(),
    ])->save();
    app(\App\Services\DocumentoRequerimentoService::class)->salvarArquivos(
        requerimento: $requerimento,
        historico: $historicoLegado,
        arquivos: [UploadedFile::fake()->createWithContent('legado.pdf', 'Documento legado')],
        usuario: $servidor,
        titulo: 'Documento legado'
    );
    $historicosAntes = $requerimento->historicos()->count();

    $this->actingAs($servidor)
        ->post(route('setor.requerimentos.anexarDocumento', [$setor->id, $requerimento->id]), [
            'titulo_documento' => 'Parecer técnico',
            'observacao' => 'Parecer complementar.',
            'arquivos' => [UploadedFile::fake()->createWithContent('parecer.pdf', 'Parecer técnico')],
        ])
        ->assertRedirect(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->assertSessionHasNoErrors();

    $this->post(route('setor.requerimentos.anexarDocumento', [$setor->id, $requerimento->id]), [
        'titulo_documento' => 'Anexo complementar',
        'arquivos' => [UploadedFile::fake()->createWithContent('anexo.pdf', 'Anexo')],
    ])->assertRedirect(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->assertSessionHasNoErrors();

    $requerimento->refresh();
    $historico = $historicoStatusAtual->fresh();
    $eventosDocumento = $requerimento->historicos()
        ->where('tipo_evento', \App\Models\HistoricoRequerimento::TIPO_EVENTO_DOCUMENTO_ANEXADO)
        ->orderBy('id')
        ->get();
    $documentos = $eventosDocumento->flatMap(fn ($evento) => $evento->documentos()->orderBy('id')->get());

    expect($requerimento->status)->toBe($statusOriginal)
        ->and($requerimento->historicos()->count())->toBe($historicosAntes + 2)
        ->and($historico->id)->toBe($historicoStatusAtual->id)
        ->and($historico->status)->toBe($statusOriginal)
        ->and($historico->setor_id)->toBeNull()
        ->and($historico->observacao)->toBeNull()
        ->and($eventosDocumento)->toHaveCount(2)
        ->and($eventosDocumento->every(fn ($evento) => $evento->status === $statusOriginal && $evento->setor_id === $setor->id))->toBeTrue()
        ->and($eventosDocumento->first()->observacao)->toBe('Parecer complementar.')
        ->and($documentos)->toHaveCount(2)
        ->and($documentos->pluck('nome_documento')->all())->toBe(['Parecer técnico', 'Anexo complementar']);

    foreach ($documentos as $documento) {
        Storage::disk('local')->assertExists($documento->caminho);
    }
    Mail::assertNothingSent();

    $resposta = $this->get(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->assertOk()
        ->assertSee('Anexar documento')
        ->assertSee('Parecer técnico')
        ->assertSee('Anexo complementar')
        ->assertSee('Documento legado')
        ->assertSee('Parecer complementar')
        ->assertSee('Documentos anexados')
        ->assertDontSee('Documento anexado pelo setor')
        ->assertSee('Documento juntado ao processo. O status foi mantido.');

    expect(substr_count($resposta->getContent(), 'class="badge-status"'))->toBe(1)
        ->and(substr_count($resposta->getContent(), 'class="timeline-anexo-item"'))->toBe(3);
});

test('protocol documents use the same compact timeline group as sector attachments', function () {
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
    $historico = $requerimento->historicos()->firstOrFail();
    $caminhoDocumento = 'documentos_requerimentos/' . $requerimento->id . '/comprovante.pdf';
    Storage::disk('local')->put($caminhoDocumento, 'documento de teste');
    \App\Models\DocumentoRequerimento::create([
        'requerimento_id' => $requerimento->id,
        'historico_requerimento_id' => $historico->id,
        'user_id' => $cidadao->id,
        'nome_documento' => 'Comprovante de endereço',
        'nome_original' => 'comprovante.pdf',
        'caminho' => $caminhoDocumento,
        'extensao' => 'pdf',
        'mime_type' => 'application/pdf',
        'tamanho' => 100,
    ]);

    $resposta = $this->actingAs($servidor)
        ->get(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->assertOk()
        ->assertSee('Documentos anexados')
        ->assertSee('1 arquivo')
        ->assertSee('comprovante.pdf')
        ->assertDontSee('Documentos (1)');

    expect(substr_count($resposta->getContent(), 'class="timeline-anexo-item"'))->toBe(1);
});

test('a sector cannot attach documents to a finalized process', function () {
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
        'status' => Requerimento::STATUS_LICENCA_EMITIDA,
    ]);
    $historicosAntes = $requerimento->historicos()->count();

    $this->actingAs($servidor)
        ->post(route('setor.requerimentos.anexarDocumento', [$setor->id, $requerimento->id]), [
            'arquivos' => [UploadedFile::fake()->createWithContent('documento.pdf', 'Documento')],
        ])
        ->assertStatus(409);

    expect($requerimento->fresh()->status)->toBe(Requerimento::STATUS_LICENCA_EMITIDA)
        ->and($requerimento->fresh()->historicos()->count())->toBe($historicosAntes)
        ->and($requerimento->fresh()->documentos()->count())->toBe(0);
});

test('document attachment becomes available after protocolado enters analysis', function () {
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
        'status' => Requerimento::STATUS_PROTOCOLADO,
    ]);
    $this->actingAs($servidor);

    $this->get(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->assertOk()
        ->assertDontSee('data-modal-target="modal-anexar-documento"', false);

    $this->post(route('setor.requerimentos.anexarDocumento', [$setor->id, $requerimento->id]), [
        'arquivos' => [UploadedFile::fake()->createWithContent('parecer.pdf', 'Parecer')],
    ])->assertStatus(409);

    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_EM_ANALISE,
    ])->assertSessionDoesntHaveErrors();

    $this->get(route('setor.requerimentos.show', [$setor->id, $requerimento->id]))
        ->assertOk()
        ->assertSee('data-modal-target="modal-anexar-documento"', false);

    expect($requerimento->fresh()->status)->toBe(Requerimento::STATUS_EM_ANALISE);
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
