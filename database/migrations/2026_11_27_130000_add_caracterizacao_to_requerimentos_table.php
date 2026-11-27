<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            $table->json('caracterizacao_empreendimento')->nullable()->after('motivo');
        });
    }

    public function down(): void
    {
        Schema::table('requerimentos', function (Blueprint $table) {
            $table->dropColumn('caracterizacao_empreendimento');
        });
    }
};
