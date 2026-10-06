<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder principal - SECAMB (Prefeitura Municipal de Seabra)
 *
 * Cria o usuário administrador inicial e invoca os seeders do sistema municipal.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ─── Administrador Geral da SECAMB ────────────────────────────────────
        Usuario::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@secamb.local')],
            [
                'nome'     => env('ADMIN_NAME', 'Administrador SECAMB'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')),
                'role'     => 'admin',
                'ativo'    => true,
            ]
        );

        // ─── Seeders do SECAMB ────────────────────────────────────────────────
        $this->call([
            CidadaosTesteSeeder::class,
            SecambSeeder::class,
        ]);
    }
}
