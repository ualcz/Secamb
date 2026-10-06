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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();

            // Identificação (Cidadão / Empresa / Servidor)
            $table->string('tipo_registro', 20)->default('fisica'); // 'fisica' ou 'juridica'
            $table->string('nome');                                 // Nome Completo ou Nome Fantasia
            $table->string('razao_social')->nullable();             // Razão Social (PJ)
            $table->string('cpf', 14)->nullable()->index();         // CPF (PF)
            $table->string('cnpj', 18)->nullable()->index();        // CNPJ (PJ)

            // Contato
            $table->string('email')->unique();
            $table->string('telefone', 30)->nullable();
            $table->string('celular', 30)->nullable();

            // Autenticação e Acesso
            $table->string('password');
            $table->string('role', 30)->default('cidadao'); // 'cidadao', 'servidor', 'admin'
            $table->boolean('ativo')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('usuarios');
    }
};
