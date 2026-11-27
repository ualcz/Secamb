<div data-caracterizacao-form>
    <fieldset class="caracterizacao-fieldset caracterizacao-fieldset-empreendimento">
        <legend>Empreendimento Vinculado</legend>
        <p class="caracterizacao-ajuda">Selecione o empreendimento para o qual está sendo feito este requerimento.</p>
        <div class="campo campo-empreendimento-select">
            <label for="empreendimento_id">Empreendimento <span aria-hidden="true">*</span></label>
            <select name="empreendimento_id" id="empreendimento_id" class="form-control" required data-step-required
                onchange="atualizarPreviewEmpreendimento(this)">
                <option value="">Selecione um empreendimento</option>
                @foreach($empreendimentos ?? [] as $emp)
                    <option value="{{ $emp->id }}"
                        data-nome="{{ $emp->nome }}"
                        data-cnpj="{{ $emp->cnpj ?? 'Não informado' }}"
                        data-endereco="{{ $emp->endereco_completo ?: 'Não informado' }}"
                        data-fase="{{ $emp->fase_operacao ?? 'Não informada' }}"
                        data-porte="{{ $emp->porte ?? 'Não informado' }}"
                        {{ (string) old('empreendimento_id', $empreendimentoSelecionadoId ?? '') === (string) $emp->id ? 'selected' : '' }}>
                        {{ $emp->nome }} {{ $emp->cnpj ? '— CNPJ: ' . $emp->cnpj : '' }}
                    </option>
                @endforeach
            </select>
            @if(($empreendimentos ?? collect())->isEmpty())
                <p class="caracterizacao-vazio">Cadastre um empreendimento ou peça ao responsável para vinculá-lo à sua conta.</p>
            @endif
        </div>

        <div id="card-preview-empreendimento" class="card-preview-empreendimento">
            <div class="card-preview-empreendimento-nome" id="prev-emp-nome"></div>
            <div class="card-preview-empreendimento-dados">
                <div><strong>CNPJ:</strong> <span id="prev-emp-cnpj"></span></div>
                <div><strong>Porte:</strong> <span id="prev-emp-porte"></span></div>
                <div><strong>Localização:</strong> <span id="prev-emp-endereco"></span></div>
                <div><strong>Fase:</strong> <span id="prev-emp-fase"></span></div>
            </div>
        </div>
    </fieldset>

    @include('requerimentos.partials.caracterizacao-campos')
</div>
