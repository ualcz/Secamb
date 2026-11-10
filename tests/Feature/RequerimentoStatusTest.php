<?php

use App\Models\Requerimento;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('a process follows the configured status transitions', function () {
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
        'status' => Requerimento::STATUS_EM_ANALISE,
    ])->assertSessionDoesntHaveErrors();

    $this->patch(route('setor.requerimentos.atualizarStatus', [$setor->id, $requerimento->id]), [
        'status' => Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
        'observacao' => 'Envie o relatório ambiental.',
        'nome_documento_solicitado' => 'Relatório ambiental',
    ])->assertSessionDoesntHaveErrors();

    $requerimento->refresh();
    expect($requerimento->status)->toBe(Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO)
        ->and($requerimento->data_devolucao)->not->toBeNull()
        ->and($requerimento->historicos()->reorder()->orderByDesc('id')->first()->status)->toBe(Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO);
});

test('processes awaiting a response for 90 days expire and receive a history entry', function () {
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
        'data_devolucao' => now()->subDays(91),
    ]);
    $recente = Requerimento::create([
        'usuario_id' => $cidadao->id,
        'setor_id' => $setor->id,
        'status' => Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO,
        'data_devolucao' => now()->subDays(89),
    ]);

    $this->artisan('requerimentos:expirar')
        ->expectsOutput('Processos expirados: 1.')
        ->assertSuccessful();

    expect($antigo->fresh()->status)->toBe(Requerimento::STATUS_EXPIRADO)
        ->and($antigo->fresh()->historicos()->reorder()->orderByDesc('id')->first()->status)->toBe(Requerimento::STATUS_EXPIRADO)
        ->and($recente->fresh()->status)->toBe(Requerimento::STATUS_AGUARDANDO_COMPLEMENTACAO);
});
