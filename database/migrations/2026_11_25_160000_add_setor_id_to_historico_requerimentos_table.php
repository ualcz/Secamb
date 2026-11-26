<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historico_requerimentos', function (Blueprint $table) {
            $table->foreignId('setor_id')
                ->nullable()
                ->after('user_id')
                ->constrained('setores')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('historico_requerimentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('setor_id');
        });
    }
};
