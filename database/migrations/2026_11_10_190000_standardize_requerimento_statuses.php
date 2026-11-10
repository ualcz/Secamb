<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $mapeamentos = [
            ['Protocolado', 'Aberto', 'aberto', 'Novo', 'novo'],
            ['Em análise', 'Em Análise', 'Em Analise', 'Em Atendimento', 'em análise', 'em analise', 'em atendimento'],
            ['Aguardando complementação', 'Devolvido', 'devolvido'],
            ['Encerrado', 'Concluído', 'Concluido', 'concluído', 'concluido', 'Finalizado', 'finalizado'],
        ];

        foreach (['requerimentos', 'historico_requerimentos'] as $tabela) {
            foreach ($mapeamentos as $mapeamento) {
                $novoStatus = $mapeamento[0];
                $statusAntigos = array_slice($mapeamento, 1);

                DB::table($tabela)
                    ->whereIn('status', $statusAntigos)
                    ->update(['status' => $novoStatus]);
            }

            DB::table($tabela)
                ->whereIn('status', ['Despacho', 'despacho'])
                ->update(['status' => 'Em análise']);
        }

        DB::table('requerimentos')
            ->where('status', 'Aguardando complementação')
            ->whereNull('data_devolucao')
            ->update(['data_devolucao' => DB::raw('COALESCE(updated_at, created_at)')]);

        Schema::table('requerimentos', function (Blueprint $table) {
            $table->string('status', 40)->default('Protocolado')->change();
        });
    }

    public function down(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            $table->string('status', 40)->default('Novo')->change();
        });
    }
};
