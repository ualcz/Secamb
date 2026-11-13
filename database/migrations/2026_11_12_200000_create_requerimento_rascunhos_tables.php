<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requerimento_rascunhos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('setor_id')->constrained('setores')->cascadeOnDelete();
            $table->json('campos');
            $table->unsignedTinyInteger('etapa')->default(1);
            $table->timestamps();
            $table->unique(['usuario_id', 'setor_id']);
        });

        Schema::create('requerimento_rascunho_arquivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rascunho_id')->constrained('requerimento_rascunhos')->cascadeOnDelete();
            $table->string('campo', 100);
            $table->unsignedInteger('indice');
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamanho')->default(0);
            $table->timestamps();
            $table->unique(['rascunho_id', 'campo', 'indice']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requerimento_rascunho_arquivos');
        Schema::dropIfExists('requerimento_rascunhos');
    }
};
