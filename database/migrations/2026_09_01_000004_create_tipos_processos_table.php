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
        Schema::create('tipos_processos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');            // Ex: Licença Prévia
            $table->string('sigla', 20)->nullable(); // Ex: LP
            $table->text('descricao')->nullable();
            $table->text('legislacao_base')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_processos');
    }
};
