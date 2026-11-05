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
            $table->string('processo_semma_numero')->after('portaria_inema_validade')->nullable();
            $table->string('processo_semma_tipo')->after('processo_semma_numero')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empreendimentos', function (Blueprint $table) {
            $table->dropColumn('processo_semma_numero');
            $table->dropColumn('processo_semma_tipo');
        });
    }
};
