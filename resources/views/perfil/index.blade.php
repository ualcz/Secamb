@extends('layouts.app')

@section('title', 'Meu Perfil - SECAMB Seabra')
@section('tag', 'Perfil')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile/cards.css') }}?v={{ filemtime(public_path('css/profile/cards.css')) }}">

<div class="profile-container">

    {{-- Feedback de Mensagens --}}
    @if (session('success') || session('sucesso') || session('status'))
        <div class="alert-sucesso">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong>Sucesso!</strong> {{ session('success') ?? session('sucesso') ?? session('status') }}
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

    {{-- Hero Card do Usuário --}}
    @include('perfil.partials.hero')

    {{-- Grid com Informações Cadastrais --}}
    <div class="profile-grid">
        {{-- Card 1: Identificação do Usuário --}}
        @include('perfil.partials.card-identificacao')

        {{-- Card 2: Endereço (somente para não-admin) --}}
        @if(!$usuario->isAdmin())
            @include('perfil.partials.card-endereco')
        @endif

        {{-- Card 3: Conta &bull; Acesso e Segurança (Full Width) --}}
        @include('perfil.partials.card-seguranca')
    </div>

</div>
@endsection
