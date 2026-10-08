<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@secamb.local')],
            [
                'nome' => env('ADMIN_NAME', 'Administrador SECAMB'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')),
                'role' => 'admin',
                'ativo' => true,
            ]
        );
    }
}
