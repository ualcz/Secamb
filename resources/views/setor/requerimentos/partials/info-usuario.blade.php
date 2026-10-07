<details class="card-painel info-aluno-accordion">
    <summary class="accordion-header">
        <div class="accordion-titulo-wrapper">
            <h2 class="card-titulo">
                <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Informações do Solicitante
            </h2>

            <!-- Resumo visível mesmo quando colapsado -->
            <div class="grid-3 preview-aluno">
                <div class="info-grupo">
                    <span class="info-label">Nome Completo</span>
                    <span class="info-valor">{{ $requerimento->usuario->nome }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">CPF / CNPJ</span>
                    <span class="info-valor">{{ $requerimento->usuario->cpf ?? $requerimento->usuario->matricula ?? 'Não informado' }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Telefone / WhatsApp</span>
                    <span class="info-valor">{{ $requerimento->usuario->celular ?? 'Não informado' }}</span>
                </div>
            </div>
        </div>

        <svg class="icone-seta" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </summary>

    <!-- Conteúdo expandido -->
    <div class="accordion-body">
        <div class="grid-3 grid-complementar">
            <div class="info-grupo">
                <span class="info-label">CPF / Documento</span>
                <span class="info-valor">{{ $requerimento->usuario->cpf ?? 'Não informado' }}</span>
            </div>
            <div class="info-grupo">
                <span class="info-label">Tipo de Cadastro</span>
                <span class="info-valor">{{ $requerimento->usuario->tipo_processo_formatado ?? ($requerimento->usuario->tipo_registro ?? 'Pessoa Física') }}</span>
            </div>
            <div class="info-grupo">
                <span class="info-label">E-mail de Contato</span>
                <span class="info-valor">{{ $requerimento->usuario->email }}</span>
            </div>
        </div>

        <hr class="divisor-secao">

        <!-- Seção de Endereço Residencial / Comercial -->
        <h3 class="subtitulo-secao">Endereço Cadastrado</h3>
        @if($requerimento->usuario->endereco)
            <div class="grid-3">
                <div class="info-grupo">
                    <span class="info-label">Rua / Logradouro</span>
                    <span class="info-valor">{{ $requerimento->usuario->endereco->rua }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Nº</span>
                    <span class="info-valor">{{ $requerimento->usuario->endereco->numero }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Bairro</span>
                    <span class="info-valor">{{ $requerimento->usuario->endereco->bairro }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">Cidade / UF</span>
                    <span class="info-valor">{{ $requerimento->usuario->endereco->cidade }} / {{ $requerimento->usuario->endereco->estado }}</span>
                </div>
                <div class="info-grupo">
                    <span class="info-label">CEP</span>
                    <span class="info-valor">{{ $requerimento->usuario->endereco->cep }}</span>
                </div>
            </div>
        @else
            <p class="texto-vazio">Nenhum endereço cadastrado para este usuário.</p>
        @endif
    </div>
</details>
