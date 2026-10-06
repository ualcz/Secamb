@extends('layouts.app')

@section('title', 'Painel do Servidor - SECAMB')
@section('tag', 'Servidor')

@section('content')
<div class="banner servidor">
    <h2>Olá, {{ auth()->user()->nome }}!</h2>
    <p>Painel do Servidor - Secretaria Municipal de Meio Ambiente de Seabra.</p>
</div>

<div class="card">
    <h3>Seus Dados</h3>
    
    <div class="grid">
        <div class="item">
            <div class="item-label">Nome Completo</div>
            <div class="item-value">{{ auth()->user()->nome }}</div>
        </div>

        <div class="item">
            <div class="item-label">Matrícula / Registro Funcional</div>
            <div class="item-value">{{ auth()->user()->matricula ?? 'N/A' }}</div>
        </div>

        <div class="item">
            <div class="item-label">E-mail</div>
            <div class="item-value">{{ auth()->user()->email }}</div>
        </div>

        <div class="item">
            <div class="item-label">Vínculo</div>
            <div class="item-value"><span class="badge">Servidor ({{ ucfirst(auth()->user()->role) }})</span></div>
        </div>

        <div class="item">
            <div class="item-label">Autenticação</div>
            <div class="item-value">Servidor Municipal</div>
        </div>
    </div>
</div>
@endsection
