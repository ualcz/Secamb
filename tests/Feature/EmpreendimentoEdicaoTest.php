<?php

use App\Models\Empreendimento;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a edição usa endereço por CEP e mantém município e UF fixos em Seabra', function () {
    $usuario = Usuario::create([
        'nome' => 'Pessoa Usuária',
        'email' => 'usuario@example.com',
        'password' => bcrypt('password'),
        'role' => 'cidadao',
        'ativo' => true,
    ]);

    $empreendimento = Empreendimento::create([
        'usuario_id' => $usuario->id,
        'nome' => 'Empreendimento de teste',
        'cidade' => 'Cidade anterior',
        'estado' => 'SP',
    ]);

    $this->actingAs($usuario)
        ->get(route('empreendimentos.edit', $empreendimento))
        ->assertOk()
        ->assertDontSee('name="cidade"', false)
        ->assertDontSee('name="estado"', false)
        ->assertSee('js/endereco.js');

    $this->put(route('empreendimentos.update', $empreendimento), [
        'nome' => 'Empreendimento atualizado',
        'cep' => '46900-000',
    ])->assertRedirect(route('empreendimentos.index'));

    $this->assertDatabaseHas('empreendimentos', [
        'id' => $empreendimento->id,
        'nome' => 'Empreendimento atualizado',
        'cidade' => 'Seabra',
        'estado' => 'BA',
    ]);
});
