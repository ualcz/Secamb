<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuários de teste - SECAMB (Prefeitura Municipal de Seabra)
 * Cria cidadãos exemplo para testes de desenvolvimento.
 */
class CidadaosTesteSeeder extends Seeder
{
    public function run(): void
    {
        $cidadaos = [
            [
                'nome'          => 'João da Silva (PF Teste)',
                'email'         => 'joao.silva@cidadao.test',
                'cpf'           => '111.111.111-11',
                'tipo_registro' => 'fisica',
                'celular'       => '(75) 99999-0001',
                'role'          => 'cidadao',
            ],
            [
                'nome'          => 'Empresa Teste Ltda (PJ Teste)',
                'email'         => 'empresa.teste@pj.test',
                'cnpj'          => '11.111.111/0001-11',
                'tipo_registro' => 'juridica',
                'celular'       => '(75) 99999-0002',
                'role'          => 'cidadao',
            ],
            [
                'nome'          => 'Servidor Técnico Teste',
                'email'         => 'tecnico@secamb.test',
                'cpf'           => '222.222.222-22',
                'tipo_registro' => 'fisica',
                'role'          => 'servidor',
            ],
        ];

        foreach ($cidadaos as $dados) {
            Usuario::updateOrCreate(
                ['email' => $dados['email']],
                [
                    ...$dados,
                    'password' => Hash::make('secamb123'),
                    'ativo'    => true,
                ]
            );
        }
    }
}
