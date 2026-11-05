<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empreendimentos', function (Blueprint $table) {
            $table->string('notificacao_pendencia')->after('processo_semma_tipo')->nullable();
            $table->string('embargo_pendencia')->after('notificacao_pendencia')->nullable();
            $table->string('advertencia_pendencia')->after('embargo_pendencia')->nullable();
            $table->string('apreensao_pendencia')->after('advertencia_pendencia')->nullable();
            $table->string('multa_pendencia')->after('apreensao_pendencia')->nullable();
            $table->string('interdicao_pendencia')->after('multa_pendencia')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empreendimentos', function (Blueprint $table) {
            $table->dropColumn('notificacao_pendencia');
            $table->dropColumn('embargo_pendencia');
            $table->dropColumn('advertencia_pendencia');
            $table->dropColumn('apreensao_pendencia');
            $table->dropColumn('multa_pendencia');
            $table->dropColumn('interdicao_pendencia');
        });
    }
};
