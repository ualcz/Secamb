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
        Schema::create('requerimentos', function (Blueprint $table) {
            $table->id();

            // Identificação e protocolo oficial do município
            $table->string('numero_protocolo', 50)->nullable()->unique();

            // Relacionamentos principais
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('empreendimento_id')->nullable()->constrained('empreendimentos')->onDelete('set null');
            $table->foreignId('setor_id')->constrained('setores')->onDelete('cascade');
            $table->foreignId('setor_retorno_id')->nullable()->constrained('setores')->onDelete('set null');
            $table->foreignId('tecnico_responsavel_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->foreignId('assunto_requerimento_id')->nullable()->constrained('assuntos_requerimentos')->onDelete('set null');

            // Detalhes do processo
            $table->string('objetoDoRequerimento')->nullable();
            $table->string('tipo_processo')->nullable();
            $table->text('descricao')->nullable();

            // Controle de status e tramitação
            $table->string('status', 40)->default('Novo'); // Novo, Em Atendimento, Devolvido, Finalizado, Indeferido, Expirado
            $table->text('observacao_analise')->nullable();
            $table->timestamp('data_devolucao')->nullable();
            $table->string('email_message_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requerimentos');
    }
};
