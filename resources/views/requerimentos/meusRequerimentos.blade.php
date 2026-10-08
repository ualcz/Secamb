@extends('layouts.app')

@section('title', 'Meus Requerimentos - SECAMB')
@section('tag', 'Cidadão')

@section('content')
<link rel="stylesheet" href="{{ asset('css/meusRequerimentos.css') }}?v={{ filemtime(public_path('css/meusRequerimentos.css')) }}">

<div class="req-container">

    {{-- Filtro de Pesquisa com Campo Único --}}
    <div class="req-filter-card">
        <form method="GET" action="{{ route('requerimentos.cidadao.meusRequerimentos') }}" class="req-filter-form">
            <input
                type="text"
                name="busca"
                value="{{ request('busca') }}"
                placeholder="Buscar por objeto do requerimento, protocolo ou status..."
                class="req-input"
            />

            <button type="submit" class="req-btn-filtrar">
                Buscar
            </button>

            @if(request()->filled('busca'))
                <a href="{{ route('requerimentos.cidadao.meusRequerimentos') }}" class="req-btn-limpar">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    {{-- Conteúdo / Tabela ou Cards --}}
    @if($requerimentos->isEmpty())
        <div class="req-empty-card">
            <p class="req-empty-title">Nenhum requerimento encontrado.</p>
            <p style="margin-top: 4px;">
                @if(request()->filled('busca'))
                    Tente buscar por outro termo.
                @else
                    Você ainda não realizou nenhum requerimento.
                @endif
            </p>
        </div>
    @else

        {{-- Visualização em Tabela (Desktop) --}}
        <div class="req-table-card">
            <div class="req-table-scroll">
                <table class="req-table">
                    <thead>
                        <tr>
                            <th class="req-col-data">Data</th>
                            <th class="req-col-protocolo">Protocolo</th>
                            <th class="req-col-setor">CPNJ</th>
                            <th class="req-col-setor">Empreendimento</th>
                            <th class="req-col-objeto">Objeto do Requerimento</th>
                            <th class="req-col-status">Status</th>
                            <th class="req-col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requerimentos as $requerimento)
                            <tr>
                                <td class="req-col-data">
                                    {{ isset($requerimento->created_at) && $requerimento->created_at ? $requerimento->created_at->format('d/m/Y H:i') : (isset($requerimento['created_at']) && $requerimento['created_at'] ? \Carbon\Carbon::parse($requerimento['created_at'])->format('d/m/Y H:i') : date('d/m/Y')) }}
                                </td>
                                <td class="req-col-protocolo">
                                    <span class="req-badge-protocolo">
                                         <div >     
                                            <span>{{ $requerimento->id }}</span>
                                        </div>
                                    </span>
                                </td>
                                <td class="req-col-setor">
                                    <span class="req-text-setor">
                                       {{ $requerimento->empreendimento?->cnpj ?? 'Sem CPNJ' }}
                                    </span>
                                </td>
                                <td class="req-col-setor">
                                    <span class="req-text-setor">
                                       {{ $requerimento->empreendimento?->nome ?? 'Sem Empreendimento' }}
                                    </span>
                                </td>
                                <td class="req-col-objeto">
                                    <div> {{ $requerimento['objetoDoRequerimento'] ?? $requerimento->objetoDoRequerimento }}</div>
                                </td>
                                @if($requerimento->status)
                                    <td class="req-col-status {{ $requerimento->status }} {{ Str::slug($requerimento->status) }}">
                                        <span>
                                            {{ $requerimento->status }}
                                        </span>
                                    </td>
                                @else
                                    <td class="req-col-status Aberto status-aberto">
                                        <span>Aberto</span>
                                    </td>
                                @endif
                                <td class="req-col-acoes">
                                    <a href="{{ route('requerimentos.cidadao.visualizar', $requerimento->id) }}" class="req-btn-imprimir">Ver mais</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Visualização em Cards (Celular) --}}
        <div class="req-mobile-cards">
            @foreach($requerimentos as $requerimento)
                <div class="req-card">
                    <div class="req-card-top">
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <span class="req-badge-protocolo">
                                #{{ $requerimento['id'] ?? $requerimento->id ?? 'S/N' }}
                            </span>
                            <span class="req-text-setor">
                                {{ $requerimento->setor?->setor_sigla ?? $requerimento->setor_sigla ?? 'N/A' }}
                            </span>
                        </div>
                        <span class="req-card-date">
                            {{ isset($requerimento->created_at) && $requerimento->created_at ? $requerimento->created_at->format('d/m/Y H:i') : (isset($requerimento['created_at']) && $requerimento['created_at'] ? \Carbon\Carbon::parse($requerimento['created_at'])->format('d/m/Y H:i') : date('d/m/Y')) }}
                        </span>
                    </div>

                    <div class="req-card-objeto">
                        <strong>{{ $requerimento['objetoDoRequerimento'] ?? $requerimento->objetoDoRequerimento }}</strong>
                        @if($requerimento->empreendimento)
                            <div style="font-size: 0.8rem; color: #059669; font-weight: 600; margin-top: 3px;">
                                {{ $requerimento->empreendimento->nome }}
                            </div>
                        @endif
                    </div>

                    <div class="req-card-actions">
                        <div class="req-card-status {{ $requerimento->status ?? 'Aberto' }} {{ Str::slug($requerimento->status ?? 'aberto') }}">
                            <span>{{ $requerimento->status ?? 'Aberto' }}</span>
                        </div>
                        <a href="{{ route('requerimentos.cidadao.visualizar', $requerimento->id) }}" class="req-btn-imprimir">Ver mais</a>
                    </div>
                </div>
            @endforeach
        </div>

    @endif

</div>
@endsection
