<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        // ─── Seeders do SECAMB ────────────────────────────────────────────────
        $this->call([
            AdminSeeder::class,
            CidadaosTesteSeeder::class,
            SecambSeeder::class,
        ]);
    }
}
