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
        Schema::create('historico_requerimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimento_id')->constrained('requerimentos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('status', 40);
            $table->text('observacao')->nullable();
            $table->boolean('solicita_novo_documento')->default(false);
            $table->string('nome_documento_solicitado')->nullable();
            $table->timestamps();
        });

        Schema::create('documentos_requerimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimento_id')->constrained('requerimentos')->onDelete('cascade');
            $table->foreignId('historico_requerimento_id')->nullable()->constrained('historico_requerimentos')->onDelete('set null');
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('nome_documento')->nullable();
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('extensao', 20)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_requerimentos');
        Schema::dropIfExists('historico_requerimentos');
    }
};
