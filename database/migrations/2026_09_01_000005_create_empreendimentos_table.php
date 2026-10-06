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
        Schema::create('empreendimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('nome');                            // Nome Fantasia ou Razão Social
            $table->string('cnpj', 18)->nullable()->index();   // CNPJ do empreendimento
            $table->string('tipo_atividade')->nullable();

            // Localização física do empreendimento (fixo nesta tabela)
            $table->string('endereco')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('cidade', 100)->default('Seabra');
            $table->string('estado', 2)->default('BA');

            // Aspectos ambientais
            $table->string('bacia_hidrografica')->nullable();
            $table->string('recurso_hidrico')->nullable();
            $table->string('fase_operacao')->nullable();       // 'Localização', 'Instalação', 'Operação', etc.

            // Contato / Responsável
            $table->string('contato_nome')->nullable();
            $table->string('contato_telefone', 30)->nullable();
            $table->string('contato_celular', 30)->nullable();
            $table->string('contato_email')->nullable();

            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('empreendimento_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empreendimento_id')->constrained('empreendimentos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->boolean('termo_aceito')->default(false);
            $table->timestamp('termo_aceito_em')->nullable();
            $table->string('status', 20)->default('ativo');    // 'ativo', 'pendente', 'revogado'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empreendimento_usuario');
        Schema::dropIfExists('empreendimentos');
    }
};
