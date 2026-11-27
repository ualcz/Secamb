@if($tecnicos->isNotEmpty())
    <details class="tecnicos-responsaveis">
        <summary class="tecnicos-responsaveis-resumo">
            <span>Ver técnicos responsáveis ({{ $tecnicos->count() }})</span>
            <svg aria-hidden="true" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </summary>
        <div class="tecnicos-responsaveis-lista">
            @foreach($tecnicos as $tecnico)
                <section class="tecnico-responsavel-card">
                    <h3>{{ $tecnico->nome }}</h3>
                    <div class="tecnico-responsavel-dados">
                        <div class="info-grupo">
                            <span class="info-label">CPF</span>
                            <span class="info-valor">{{ $tecnico->cpf ?: 'Não informado' }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">E-mail</span>
                            <span class="info-valor">{{ $tecnico->email ?: 'Não informado' }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">Celular / WhatsApp</span>
                            <span class="info-valor">{{ $tecnico->celular ?: 'Não informado' }}</span>
                        </div>
                        <div class="info-grupo">
                            <span class="info-label">Tipo de cadastro</span>
                            <span class="info-valor">{{ $tecnico->tipo_pessoa_formatado }}</span>
                        </div>
                        @if($tecnico->pivot->created_at)
                            <div class="info-grupo">
                                <span class="info-label">Vinculado em</span>
                                <span class="info-valor">{{ $tecnico->pivot->created_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </details>
@endif
