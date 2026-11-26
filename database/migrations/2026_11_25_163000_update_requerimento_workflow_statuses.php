<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['requerimentos', 'historico_requerimentos'] as $table) {
            DB::table($table)
                ->where('status', 'Em análise')
                ->update(['status' => 'Em análise técnica']);

            DB::table($table)
                ->where('status', 'Expirado')
                ->update(['status' => 'Arquivado por falta de complementação']);
        }
    }

    public function down(): void
    {
        foreach (['requerimentos', 'historico_requerimentos'] as $table) {
            DB::table($table)
                ->whereIn('status', [
                    'Em triagem',
                    'Em análise técnica',
                    'Aguardando vistoria',
                    'Em vistoria',
                    'Aguardando parecer técnico',
                    'Aguardando decisão',
                ])
                ->update(['status' => 'Em análise']);

            DB::table($table)
                ->where('status', 'Arquivado por falta de complementação')
                ->update(['status' => 'Expirado']);
        }
    }
};
