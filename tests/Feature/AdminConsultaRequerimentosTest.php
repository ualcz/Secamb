<?php

use App\Models\Requerimento;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function criarUsuarioConsulta(string $nome, string $email, string $role): Usuario
{
    return Usuario::create([
        'nome' => $nome,
        'email' => $email,
        'password' => Hash::make('password'),
        'role' => $role,
        'ativo' => true,
    ]);
}

test('admin consultation includes older requests and displays pagination', function () {
    $admin = criarUsuarioConsulta('Admin SECAMB', 'admin@example.com', 'admin');
    $cidadao = criarUsuarioConsulta('Maria de Souza', 'maria@example.com', 'cidadao');
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);

    $requerimentoAntigo = Requerimento::create([
        'usuario_id' => $cidadao->id,
        'setor_id' => $setor->id,
        'objetoDoRequerimento' => 'Licença de Instalação antiga',
        'status' => Requerimento::STATUS_PROTOCOLADO,
    ]);
    $requerimentoAntigo->forceFill(['created_at' => now()->subDays(45)])->save();

    foreach (range(1, 15) as $numero) {
        Requerimento::create([
            'usuario_id' => $cidadao->id,
            'setor_id' => $setor->id,
            'objetoDoRequerimento' => "Licença de Operação {$numero}",
            'status' => Requerimento::STATUS_PROTOCOLADO,
        ]);
    }

    $response = $this->actingAs($admin)
        ->get(route('admin.consultar-requerimentos'))
        ->assertOk();

    expect($response->viewData('requerimentos')->total())->toBe(16)
        ->and($response->viewData('requerimentos')->lastPage())->toBe(2);

    $paginaDois = $this->actingAs($admin)
        ->get(route('admin.consultar-requerimentos', ['page' => 2]))
        ->assertOk()
        ->viewData('requerimentos');

    expect($paginaDois->getCollection()->pluck('id'))->toContain($requerimentoAntigo->id);
});

test('admin consultation applies date filters and retains them in pagination links', function () {
    $admin = criarUsuarioConsulta('Admin SECAMB', 'admin@example.com', 'admin');
    $cidadao = criarUsuarioConsulta('Maria de Souza', 'maria@example.com', 'cidadao');
    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);

    foreach (range(1, 16) as $numero) {
        Requerimento::create([
            'usuario_id' => $cidadao->id,
            'setor_id' => $setor->id,
            'objetoDoRequerimento' => "Processo {$numero}",
            'status' => Requerimento::STATUS_PROTOCOLADO,
        ]);
    }

    $response = $this->actingAs($admin)
        ->get(route('admin.consultar-requerimentos', [
            'data_inicio' => now()->toDateString(),
            'data_fim' => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('data_inicio=' . now()->toDateString(), false);

    expect($response->viewData('requerimentos')->total())->toBe(16);
});
