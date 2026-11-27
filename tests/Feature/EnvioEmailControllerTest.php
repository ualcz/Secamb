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
    ]);
    $dadosRequerimento = [
        'setor_id' => $setor->id,
        'empreendimento_id' => $empreendimento->id,
        'objeto' => 'Licença de Instalação',
        'motivo' => 'Solicitação de licença ambiental.',
    ];

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
        ->assertSee('Visualizar');
});
