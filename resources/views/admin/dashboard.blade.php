@extends('layouts.app')

@section('title', 'Dashboard - Secamb')
@section('tag', 'Administração')

@section('content')
    @php($dashboardRoute = auth()->user()->isAdmin() ? 'admin.dashboard' : 'servidor.dashboard')
    <style>
        {!! file_get_contents(public_path('css/dashboard.css')) !!}
    </style>

<div class="cards">
    <div class="card_individual total shadow-[0_2px_2px_0_rgba(0,0,0,0.14),_0_3px_1px_-2px_rgba(0,0,0,0.2),_0_1px_5px_0_rgba(0,0,0,0.12)]">
        <div class="linha1">Total de requerimentos</div>
        <div class="linha2">{{ $totalRequerimentos }}</div>
        <div class="linha3">
            <p class="info">Em {{ date('Y') }}</p>
        </div>
        <div class="coluna_mesclada">
            <div class="icone_total">
                <svg xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 384 512"
                    width="32" height="32"
                    fill="#ffffff">
                <path d="M224 136V0H24C10.7 0 0 10.7 0 24v464c0
                        13.3 10.7 24 24 24h336c13.3 0 24-10.7
                        24-24V160H248c-13.3 0-24-10.7-24-24zm64
                        236c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6
                        5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zm0-64c0
                        6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6
                        5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zm0-64c0
                        6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-8c0-6.6
                        5.4-12 12-12h168c6.6 0 12 5.4 12 12v8zM377
                        105L279.1 7c-4.5-4.5-10.6-7-17-7h-6v128h128v-6c0-6.4-2.5-12.5-7-17z"/>
                </svg>
            </div>
        </div>
    </div>

    <div name="analise" class="card_individual analise shadow-[0_2px_2px_0_rgba(0,0,0,0.14),_0_3px_1px_-2px_rgba(0,0,0,0.2),_0_1px_5px_0_rgba(0,0,0,0.12)]">
        <div class="linha1">Requerimentos em análise</div>
        <div class="linha2">
            <p class="numero">{{ $totalAnalise }}</p>
        </div>
        <div class="linha3">
            <p class="info">Em análise atualmente</p>
        </div>
        <div class="coluna_mesclada">
            <div class="icone_analise">
                <svg xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512"
                    width="32" height="32"
                    fill="#ffffff">
                <path d="M256 8C119 8 8 119 8 256s111 248 248
                        248 248-111 248-248S393 8 256 8zm0
                        448c-110.5 0-200-89.5-200-200S145.5
                        56 256 56s200 89.5 200 200-89.5
                        200-200 200zm61.8-104.4l-84.9-61.8c-3.1-2.3-5-5.9-5-9.8V128c0-6.6
                        5.4-12 12-12h24c6.6 0 12 5.4
                        12 12v128l70.6 51.4c5.4 3.9
                        6.5 11.4 2.6 16.8l-14.3
                        19.6c-3.9 5.4-11.4 6.5-16.8 2.6z"/>
                </svg>
            </div>
        </div>
    </div>

    <div name="recebidos" class="card_individual recebidos shadow-[0_2px_2px_0_rgba(0,0,0,0.14),_0_3px_1px_-2px_rgba(0,0,0,0.2),_0_1px_5px_0_rgba(0,0,0,0.12)]">
        <div class="linha1">
            Requerimentos recebidos
        </div>
        <div class="linha2">{{ $totalRecebidos }}</div>
        <div class="linha3">
            <p class="info">{{ $periodo === 'semana' ? 'Esta semana' : 'Este mês' }}</p>
        </div>
        <div class="coluna_mesclada">
            <div class="icone_recebidos">
                <svg xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 448 512"
                    width="32" height="32"
                    fill="#ffffff">
                <path d="M96 0C78.3 0 64 14.3 64 32v448c0 17.7
                        14.3 32 32 32h288c17.7 0 32-14.3
                        32-32V32c0-17.7-14.3-32-32-32H96zm0
                        64h288v384H96V64zm32 64c-8.8 0-16
                        7.2-16 16s7.2 16 16 16h224c8.8 0
                        16-7.2 16-16s-7.2-16-16-16H128zm0
                        96c-8.8 0-16 7.2-16 16s7.2 16
                        16 16h224c8.8 0 16-7.2 16-16s-7.2-16-16-16H128z"/>
                </svg>
            </div>
        </div>

        <div class="menu-container">
            <div class="menu-icon">⋮</div>
            <ul class="menu-options">
                <li><a href="{{ route($dashboardRoute, ['periodo' => 'semana']) }}">Esta semana</a></li>
                <li><a href="{{ route($dashboardRoute, ['periodo' => 'mes']) }}">Este mês</a></li>
            </ul>
        </div>

    </div>
</div>

<div class="graficos mb-5">
    <div class="p-3 bg-white rounded shadow-[0_2px_2px_0_rgba(0,0,0,0.14),_0_3px_1px_-2px_rgba(0,0,0,0.2),_0_1px_5px_0_rgba(0,0,0,0.12)] grafico-linha">
        {!! $chart->container() !!}
    </div>

    <div class="p-3 bg-white rounded shadow-[0_2px_2px_0_rgba(0,0,0,0.14),_0_3px_1px_-2px_rgba(0,0,0,0.2),_0_1px_5px_0_rgba(0,0,0,0.12)] grafico-pizza">
         {!! $pieChart->container() !!}
    </div>
</div>

    <div class="mb-[-15px] p-3 bg-white rounded shadow-[0_2px_2px_0_rgba(0,0,0,0.14),_0_3px_1px_-2px_rgba(0,0,0,0.2),_0_1px_5px_0_rgba(0,0,0,0.12)] grafico-pizza">
        {!! $progressBarChart->container() !!}
    </div>

<script src="{{ $chart->cdn() }}"></script>
<script src="{{ $pieChart->cdn() }}"></script>
<script src="{{ $progressBarChart->cdn() }}"></script>

{{ $chart->script() }}
{{ $pieChart->script() }}
{{ $progressBarChart->script() }}
@endsection
