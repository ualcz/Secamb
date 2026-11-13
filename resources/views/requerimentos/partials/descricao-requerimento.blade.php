<!-- Etapa 2: Descrição da Solicitação -->
<div class="form-step" data-step="2" style="display: none;">
    <fieldset>
        <legend>Descrição da Solicitação / Observações <span aria-hidden="true">*</span></legend>
        <div class="campo">
            <textarea name="motivo" rows="5" required placeholder="Ex: Solicito Licença Unificada para atividade de extração mineral conforme projetos em anexo...">{{ old('motivo') }}</textarea>
        </div>
    </fieldset>

    <div class="step-nav">
        <button type="button" class="btn-voltar" onclick="mudarPasso(1)">Anterior</button>
        <button type="button" class="btn-enviar" onclick="mudarPasso(3)">Próximo</button>
    </div>
</div>
