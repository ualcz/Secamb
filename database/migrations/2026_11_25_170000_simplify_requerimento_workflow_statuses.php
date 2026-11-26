<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $statusLegados = [
            'Em triagem' => 'Em análise',
            'Em vistoria' => 'Aguardando vistoria',
            'Aguardando parecer técnico' => 'Em análise técnica',
            'Aguardando decisão' => 'Análise concluída',
        ];

        foreach (['requerimentos', 'historico_requerimentos'] as $table) {
            foreach ($statusLegados as $statusAntigo => $novoStatus) {
                DB::table($table)
                    ->where('status', $statusAntigo)
                    ->update(['status' => $novoStatus]);
            }
        }
    }

    public function down(): void
    {
        // Legacy statuses are mapped to newer stages and cannot be restored safely.
    }
};
