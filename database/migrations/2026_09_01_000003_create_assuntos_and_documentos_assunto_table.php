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
        Schema::create('assuntos_requerimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setor_id')->constrained('setores')->onDelete('cascade');
            $table->string('descricao');
            $table->text('observacao')->nullable();
            $table->string('link_norma')->nullable();
            $table->integer('ordem')->default(1);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('documentos_assunto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assunto_requerimento_id')->constrained('assuntos_requerimentos')->onDelete('cascade');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('link_modelo')->nullable();
            $table->boolean('obrigatorio')->default(true);
            $table->string('tipos_aceitos')->nullable()->default('pdf,png,jpg,jpeg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_assunto');
        Schema::dropIfExists('assuntos_requerimentos');
    }
};
