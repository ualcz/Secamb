<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('assuntos_requerimentos')
        ->where('link_norma','https://seabra.ba.gov.br/legislacao-ambiental')
        ->update(['link_norma' => 'https://seabra.ba.gov.br/licenciamento-ambiental']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         DB::table('assuntos_requerimentos')
        ->where('link_norma','https://seabra.ba.gov.br/licenciamento-ambiental')
        ->update(['link_norma' => 'https://seabra.ba.gov.br/']);

    }
};
