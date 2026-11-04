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
            $table->string('localizacao_licenca')->after('unidade_conservacao')->nullable();
            $table->string('licenca_anterior_tipo')->after('localizacao_licenca')->nullable();
            $table->string('licenca_anterior_processo')->after('licenca_anterior_tipo')->nullable();
            $table->string('resolucao_cepram')->after('licenca_anterior_processo')->nullable();
            $table->string('portaria_inema')->after('resolucao_cepram')->nullable();
            $table->date('resolucao_cepram_validade')->after('portaria_inema')->nullable();
            $table->date('portaria_inema_validade')->after('resolucao_cepram_validade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empreendimentos', function (Blueprint $table) {
            $table->dropColumn('localizacao_licenca');
            $table->dropColumn('licenca_anterior_tipo');
            $table->dropColumn('licenca_anterior_processo');
            $table->dropColumn('resolucao_cepram');
            $table->dropColumn('portaria_inema');
            $table->dropColumn('resolucao_cepram_validade');
            $table->dropColumn('portaria_inema_validade');
        });
    }
};
