@extends('layouts.app')

@section('title', 'Editar Modelo - ' . $modelo->setor_sigla)
@section('tag', 'Administração')

@section('content')
@vite('resources/css/admin/setor.css')
<style>
    main.container {
        max-width: 95% !important;
        width: 95% !important;
    }
</style>

<div class="admin-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: #111827;">
                Setor: {{ $modelo->setor_sigla }} &mdash; {{ $modelo->setor_nome }}
            </h2>
        </div>
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.setores.index') : route('setor.responsavel.dashboard', $modelo->id) }}" class="btn-voltar">
            &larr; Voltar
        </a>
    </div>

    {{-- Feedback de mensagens e erros --}}
    @if(session('success'))
        <div style="background: #e6f4ea; color: #137333; padding: 12px 16px; margin-bottom: 20px; border: 1px solid #ceead6; border-radius: 6px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fce8e6; color: #c5221f; padding: 12px 16px; margin-bottom: 20px; border: 1px solid #fad2cf; border-radius: 6px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Seção 1: Dados Gerais do Setor / Modelo --}}
    @include('admin.setor.partials.dados-gerais', ['modelo' => $modelo])

    {{-- Seção 2: Requerimentos e Documentos Obrigatórios --}}
    @include('admin.setor.partials.tabela-assuntos', ['modelo' => $modelo])
</div>

<script>
function toggleDocumentos(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'table-row' : 'none';
}
</script>
@endsection
