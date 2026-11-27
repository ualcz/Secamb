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

        <div class="campo campo-representante-select">
            <label for="representante_legal_id">Representante legal titular do requerimento <span aria-hidden="true">*</span></label>
            <select name="representante_legal_id" id="representante_legal_id" class="form-control"
                data-step-required required disabled
                data-usuario-atual="{{ auth()->id() }}"
                data-valor-antigo="{{ old('representante_legal_id', '') }}">
                <option value="">Selecione um representante legal</option>
            </select>
            <p class="caracterizacao-ajuda">O representante selecionado constará como titular do requerimento. O técnico que o envia permanece registrado como responsável pelo protocolo.</p>
            <p class="caracterizacao-vazio" data-sem-representante hidden>
                Não há representante legal ativo e com termo aceito vinculado a este empreendimento.
            </p>
            @error('representante_legal_id')
                <p class="caracterizacao-vazio">{{ $message }}</p>
            @enderror
        </div>

        <div hidden aria-hidden="true">
            @foreach($empreendimentos ?? [] as $emp)
                <template data-representantes-empreendimento="{{ $emp->id }}">
                    @foreach($emp->representantes_elegiveis as $representante)
                        <option value="{{ $representante->id }}" data-usuario-atual="{{ (int) $representante->id === (int) auth()->id() ? 'true' : 'false' }}">
                            {{ $representante->nome }}{{ $representante->cpf ? ' — CPF: ' . $representante->cpf : '' }}
                        </option>
                    @endforeach
                </template>
            @endforeach
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
