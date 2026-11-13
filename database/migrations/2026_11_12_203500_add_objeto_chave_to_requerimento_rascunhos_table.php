<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('requerimento_rascunhos', 'objeto_chave')) {
            Schema::table('requerimento_rascunhos', function (Blueprint $table) {
                $table->string('objeto_chave', 255)->default('__sem_objeto__');
            });
        }

        DB::table('requerimento_rascunhos')
            ->select(['id', 'campos'])
            ->orderBy('id')
            ->get()
            ->each(function ($rascunho) {
                $campos = json_decode($rascunho->campos, true) ?: [];
                $objeto = $campos['objetoDoRequerimento'] ?? null;

                if (is_string($objeto) && $objeto !== '') {
                    DB::table('requerimento_rascunhos')
                        ->where('id', $rascunho->id)
                        ->update(['objeto_chave' => mb_substr($objeto, 0, 255)]);
                }
            });

        $nomesIndices = array_column(Schema::getIndexes('requerimento_rascunhos'), 'name');
        if (!in_array('requerimento_rascunhos_usuario_lookup_index', $nomesIndices, true)) {
            Schema::table('requerimento_rascunhos', function (Blueprint $table) {
                $table->index('usuario_id', 'requerimento_rascunhos_usuario_lookup_index');
            });
        }
        if (!in_array('requerimento_rascunhos_setor_lookup_index', $nomesIndices, true)) {
            Schema::table('requerimento_rascunhos', function (Blueprint $table) {
                $table->index('setor_id', 'requerimento_rascunhos_setor_lookup_index');
            });
        }
        if (in_array('requerimento_rascunhos_usuario_id_setor_id_unique', $nomesIndices, true)) {
            Schema::table('requerimento_rascunhos', function (Blueprint $table) {
                $table->dropUnique('requerimento_rascunhos_usuario_id_setor_id_unique');
            });
        }
        if (!in_array('requerimento_rascunhos_usuario_setor_objeto_unique', $nomesIndices, true)) {
            Schema::table('requerimento_rascunhos', function (Blueprint $table) {
                $table->unique(
                    ['usuario_id', 'setor_id', 'objeto_chave'],
                    'requerimento_rascunhos_usuario_setor_objeto_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('requerimento_rascunhos', function (Blueprint $table) {
            $table->dropUnique('requerimento_rascunhos_usuario_setor_objeto_unique');
            $table->dropColumn('objeto_chave');
            $table->unique(['usuario_id', 'setor_id']);
        });
    }
};
