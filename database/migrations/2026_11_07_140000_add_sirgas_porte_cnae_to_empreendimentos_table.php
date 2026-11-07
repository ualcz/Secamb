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
            $table->string('porte', 30)->nullable()->after('tipo_atividade');       // Micro, Pequeno, Médio, Grande
            $table->string('cnae', 20)->nullable()->after('porte');                 // Código CNAE / Secretaria da Receita Federal
            $table->string('latitude', 50)->nullable()->after('recurso_hidrico');   // SIRGAS 2000
            $table->string('longitude', 50)->nullable()->after('latitude');         // SIRGAS 2000
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empreendimentos', function (Blueprint $table) {
            $table->dropColumn(['porte', 'cnae', 'latitude', 'longitude']);
        });
    }
};

