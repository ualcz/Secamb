{{-- 1. DADOS GERAIS DO SETOR --}}
<div class="secao-bloco">
    <div style="margin-bottom: 16px;">
        <h3 style="margin: 0 0 4px 0; font-size: 1.15rem; font-weight: 700; color: #1e293b;">
            1. Dados Gerais do Setor
        </h3>
    </div>

    <form action="{{ route('admin.setores.update', $modelo->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid-dados-gerais">
            {{-- Sigla do Setor --}}
            <div class="form-group col-span-3">
                <label for="setor_sigla">Sigla do Setor:*</label>
                <input type="text" id="setor_sigla" name="setor_sigla" value="{{ old('setor_sigla', $modelo->setor_sigla) }}" placeholder="Ex: CORES" required>
            </div>

            {{-- Nome Completo do Setor --}}
            <div class="form-group col-span-9">
                <label for="setor_nome">Nome Completo do Setor:*</label>
                <input type="text" id="setor_nome" name="setor_nome" value="{{ old('setor_nome', $modelo->setor_nome) }}" placeholder="Ex: Coordenação de Registros Escolares" required>
            </div>

            {{-- Título do Formulário --}}
            <div class="form-group col-span-12">
                <label for="titulo">Título do Formulário:*</label>
                <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $modelo->titulo) }}" placeholder="Ex: Requerimento - Registro Escolar (CORES)" required>
            </div>

            {{-- E-mail de Recebimento --}}
            <div class="form-group col-span-12">
                <label for="email">E-mail Oficial do Setor:*</label>
                <input type="email" id="email" name="email" value="{{ old('email', $modelo->email) }}" placeholder="protocolos.seabra@ifba.edu.br">
            </div>

           @if(auth()->user()->role === 'admin')
           {{-- Responsáveis pelo Setor --}}
            <div class="form-group col-span-12">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: #334155;">
                    Responsáveis pelo Setor:
                </label>

                @php
                    $selecionados = old('responsaveis', isset($modelo->responsaveis) ? $modelo->responsaveis->pluck('id')->toArray() : []);

                    // Reordena a coleção: quem está em $selecionados fica em primeiro (true > false)
                    $usuariosOrdenados = $usuarios->sortByDesc(function ($usuario) use ($selecionados) {
                        return in_array($usuario->id, $selecionados);
                    });
                @endphp

                <input type="text" id="buscar-usuario" placeholder="🔍 Digite para buscar um usuário..."
                    style="width: 100%; padding: 8px 12px; margin-bottom: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem;">

                <div id="lista-responsaveis" style="max-height: 200px; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; background-color: #f8fafc;">
                    @foreach($usuariosOrdenados as $usuario)
                        @php $checked = in_array($usuario->id, $selecionados); @endphp
                        <label class="usuario-item" style="display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 4px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" name="responsaveis[]" value="{{ $usuario->id }}" {{ $checked ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #2563eb;">
                            <span style="font-size: 0.875rem; color: #1e293b;" class="usuario-texto">
                                <strong>{{ $usuario->nome }}</strong> <span style="color: #64748b;">({{ $usuario->matricula ?? $usuario->email }})</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif

            @if(auth()->user()->role === 'admin')
                {{-- Status Ativo --}}
                <div class="form-group col-span-12" style="margin-top: 4px;">
                    <label class="card-toggle-ativo">
                        <input type="checkbox" name="ativo" value="1" {{ old('ativo', $modelo->ativo) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer; accent-color: #2563eb;">
                        <div>
                            <strong style="display: block; font-size: 0.875rem; color: #1e293b;">Setor Ativo</strong>
                            <span style="font-size: 0.75rem; color: #64748b;">Define se o setor está em funcionamento no sistema.</span>
                        </div>
                    </label>
                </div>

                {{-- Setor Interno --}}
                <div class="form-group col-span-12" style="margin-top: 4px;">
                    <label class="card-toggle-ativo">
                        <input type="checkbox" name="is_interno" value="1" {{ old('is_interno', $modelo->is_interno) ? 'checked' : '' }} style="width: 18px; height: 18px; cursor: pointer; accent-color: #7c3aed;">
                        <div>
                            <strong style="display: block; font-size: 0.875rem; color: #1e293b;">Setor Interno</strong>
                            <span style="font-size: 0.75rem; color: #64748b;">Setores internos <strong>não aparecem para o usuário comum/cidadão</strong> na tela de abertura de processos (utilizado para despachos técnicos e tramitações internas).</span>
                        </div>
                    </label>
                </div>
            @endif

            {{-- Botão de Salvar --}}
            <div class="col-span-12" style="margin-top: 10px;">
                <button type="submit" class="btn-salvar">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Salvar Dados Gerais
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    const campoBuscaUsuario = document.getElementById('buscar-usuario');

    if (campoBuscaUsuario) {
        campoBuscaUsuario.addEventListener('input', function() {
        const termo = this.value.toLowerCase();
        const itens = document.querySelectorAll('.usuario-item');

        itens.forEach(item => {
            const texto = item.querySelector('.usuario-texto').textContent.toLowerCase();
            if (texto.includes(termo)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
        });
    }
</script>
