<?php

use App\Models\Endereco;
use App\Models\AssuntoRequerimento;
use App\Models\Setor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('social users see their saved information in the document preview', function () {
    $usuario = Usuario::create([
        'nome' => 'Maria de Souza',
        'email' => 'maria@example.com',
        'google_id' => 'google-maria-123',
        'role' => 'cidadao',
        'ativo' => true,
        'cpf' => '123.456.789-00',
        'celular' => '(75) 99999-0000',
    ]);

    Endereco::create([
        'usuario_id' => $usuario->id,
        'rua' => 'Rua das Flores, 10',
        'bairro' => 'Centro',
        'cidade' => 'Seabra',
        'estado' => 'BA',
        'cep' => '46900-000',
    ]);

    $setor = Setor::create([
        'setor_sigla' => 'SEMA',
        'setor_nome' => 'Secretaria de Meio Ambiente',
        'ativo' => true,
        'is_interno' => false,
    ]);

    foreach ([
        'Licença de Instalação (LI)',
        'Licença de Operação (LO)',
        'Licença de Regularização Ambiental (LRA)',
    ] as $descricao) {
        AssuntoRequerimento::create([
            'setor_id' => $setor->id,
            'descricao' => $descricao,
            'ativo' => true,
        ]);
    }

    $response = $this->actingAs($usuario)->get(route('requerimentos.visualizar-blade'));

    $response->assertOk()
        ->assertSee('Maria de Souza')
        ->assertSee('123.456.789-00')
        ->assertSee('Rua das Flores, 10')
        ->assertSee('maria@example.com')
        ->assertSee('Licença de Instalação (LI)')
        ->assertSee('Licença de Operação (LO)')
        ->assertSee('Licença de Regularização Ambiental', false)
        ->assertSee('class="check-indicator"', false)
        ->assertDontSee('&amp;nbsp;', false)
        ->assertDontSee('Requerente / Responsável Legal')
        ->assertDontSee('Servidor Responsável pelo Recebimento');

    $this->get(route('requerimentos.cidadao.novo', ['setor' => $setor->id]))
        ->assertOk()
        ->assertSee('Enviar Requerimento')
        ->assertDontSee('Baixar requerimento preenchido para assinar');
});

test('incomplete social users are sent to complete their profile before previewing', function () {
    $usuario = Usuario::create([
        'nome' => 'João de Souza',
        'email' => 'joao@example.com',
        'google_id' => 'google-joao-123',
        'role' => 'cidadao',
        'ativo' => true,
    ]);

    $response = $this->actingAs($usuario)->get(route('requerimentos.visualizar-blade'));

    $response->assertRedirect(route('perfil.completar'));
});
