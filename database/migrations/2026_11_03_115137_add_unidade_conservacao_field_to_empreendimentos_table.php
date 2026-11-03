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
            $table->string('unidade_conservacao')->default('Não possui.')->after('fase_operacao')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empreendimentos', function (Blueprint $table) {
            $table->dropColumn('unidade_conservacao');
        });
    }
};
