<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historico_requerimentos', function (Blueprint $table) {
            $table->string('tipo_evento', 40)->default('tramitacao')->after('status');
        });

        DB::table('historico_requerimentos')
            ->where('observacao', 'like', 'Documento anexado pelo setor.%')
            ->update(['tipo_evento' => 'documento_anexado']);
    }

    public function down(): void
    {
        Schema::table('historico_requerimentos', function (Blueprint $table) {
            $table->dropColumn('tipo_evento');
        });
    }
};
