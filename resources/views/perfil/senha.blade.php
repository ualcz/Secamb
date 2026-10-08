@extends('layouts.app')

@section('title', 'Alterar Minha Senha - SECAMB Seabra')
@section('tag', 'Perfil')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ filemtime(public_path('css/profile.css')) }}">

<div class="edit-perfil-wrapper">

    {{-- Botão Voltar --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('perfil.index') }}" class="btn-cancelar" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 0.88rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Voltar ao Perfil</span>
        </a>
    </div>

    {{-- Feedback de Mensagens --}}
    @if (session('success') || session('sucesso'))
        <div class="alert-sucesso">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Sucesso!</strong> {{ session('success') ?? session('sucesso') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-erro">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Atenção:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Card Exclusivo de Alteração de Senha --}}
    <div class="edit-perfil-card">
        <div class="edit-perfil-card-header">
            <h2>Segurança &bull; Alterar Senha de Acesso</h2>
            <p>
                @if($usuario->hasLoginSocial() && empty($usuario->password))
                    Sua conta foi criada via Google OAuth. Você pode cadastrar uma senha para também poder fazer login diretamente com seu e-mail e senha.
                @else
                    Altere sua senha de acesso ao sistema SECAMB. Escolha uma senha segura com pelo menos 6 caracteres.
                @endif
            </p>
        </div>

        <form action="{{ route('perfil.senha.update') }}" method="POST" id="formAlterarSenha">
            @csrf
            @method('PUT')

            {{-- Senha Atual (exigida apenas se o usuário já possuir uma senha definida) --}}
            @if(!empty($usuario->password))
                <div class="form-group">
                    <label for="senha_atual" class="form-label">
                        Senha Atual <span class="required">*</span>
                    </label>
                    <div class="form-input-wrap">
                        <span class="form-input-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" name="senha_atual" id="senha_atual"
                            class="form-control has-icon {{ $errors->has('senha_atual') ? 'is-invalid' : '' }}"
                            placeholder="Digite sua senha atual" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibilidadeSenha('senha_atual', this)" aria-label="Mostrar ou ocultar senha">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('senha_atual')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="password" class="form-label">
                        Nova Senha <span class="required">*</span>
                    </label>
                    <div class="form-input-wrap">
                        <span class="form-input-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" name="password" id="password"
                            class="form-control has-icon {{ $errors->has('password') ? 'is-invalid' : '' }}"
                            placeholder="Mínimo de 6 caracteres" minlength="6" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibilidadeSenha('password', this)" aria-label="Mostrar ou ocultar senha">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        Confirmar Nova Senha <span class="required">*</span>
                    </label>
                    <div class="form-input-wrap">
                        <span class="form-input-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control has-icon"
                            placeholder="Digite a nova senha novamente" minlength="6" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibilidadeSenha('password_confirmation', this)" aria-label="Mostrar ou ocultar senha">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('perfil.index') }}" class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn-salvar-perfil btn-salvar-senha">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Atualizar Senha</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function toggleVisibilidadeSenha(inputId, botao) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        if (isPassword) {
            botao.innerHTML = `
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                </svg>
            `;
        } else {
            botao.innerHTML = `
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            `;
        }
    }
</script>
@endsection
