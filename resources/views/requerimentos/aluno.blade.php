@extends('layouts.app')

@section('title', 'Meu Painel - SECAMB Seabra')
@section('tag', 'Cidadão')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

{{-- Banner de Boas-Vindas --}}
<div class="banner">
    <h2>Olá, {{ auth()->user()->nome }}!</h2>
</div>

@if (session('sucesso'))
    <div class="alert alert-success">
        <span>{{ session('sucesso') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3>Seus Dados Cadastrais</h3>
        <small style="margin-bottom: 10px; display: block">Mantenha seus dados sempre atualizados para agilizar a análise dos seus processos.</small>
    </div>

    <div class="card-body">

        {{-- Seção 1: Identificação --}}
        <div class="section-title">
            Identificação
        </div>
        <div class="grid">
            <div class="item">
                <div class="item-label">Nome Completo / Razão Social</div>
                <div class="item-value">{{ auth()->user()->nome ?? 'N/A' }}</div>
            </div>
            <div class="item">
                <div class="item-label">CPF / CNPJ</div>
                <div class="item-value">{{ auth()->user()->documento_identificacao ?? 'Não informado' }}</div>
            </div>
            <div class="item">
                <div class="item-label">Tipo de Cadastro</div>
                <div class="item-value">{{ auth()->user()->isPessoaJuridica() ? 'Pessoa Jurídica' : 'Pessoa Física' }}</div>
            </div>
        </div>

        {{-- Seção 2: Contato --}}
        <div class="section-title">
             Contato
        </div>
        <div class="grid">
            <div class="item">
                <div class="item-label">E-mail</div>
                <div class="item-value">{{ auth()->user()->email }}</div>
            </div>
            <div class="item">
                <div class="item-label">Telefone / WhatsApp</div>
                <div class="item-value">{{ auth()->user()->celular ?? 'Não informado' }}</div>
            </div>
        </div>

        {{-- Seção 3: Endereço --}}
        <div class="section-title">
             Endereço
        </div>
        <div class="grid">
            <div class="item item-wide">
                <div class="item-value">{{ auth()->user()->endereco?->formatado ?? 'Endereço não cadastrado' }}</div>
            </div>
        </div>

    </div>
</div>
@endsection
