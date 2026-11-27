@extends('layouts.app')

@section('title', 'SECAMB - Secretaria Municipal de Meio Ambiente de Seabra')
@section('tag', 'Início')
@section('page-class', 'home-main')

@section('content')

@vite('resources/css/pages/home.css')

<div class="home-page">
    <section id="inicio" class="home-hero">
        <div class="home-container hero-layout">
            <div class="hero-copy">
                <div class="official-label">
                    <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="" aria-hidden="true">
                    <span>Prefeitura Municipal de Seabra <span aria-hidden="true">·</span> Bahia</span>
                </div>
                <p class="eyebrow">Secretaria Municipal de Meio Ambiente</p>
                <h1>Licenciamento ambiental com mais facilidade e transparência.</h1>
                <p class="hero-description">
                    Acesse os serviços ambientais do município, envie solicitações e acompanhe seus processos em um só lugar.
                </p>
                <div class="hero-actions">
                    @auth
                        <a href="{{ route('requerimentos.cidadao.novo') }}" class="button button-primary">
                            Iniciar requerimento
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('requerimentos.cidadao.meusRequerimentos') }}" class="button button-secondary">
                            Acompanhar solicitações
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="button button-primary">
                            Acessar serviços
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('register') }}" class="button button-secondary">
                            Criar cadastro
                        </a>
                    @endauth
                </div>
                <div class="hero-assurance">
                    <span class="assurance-icon" aria-hidden="true">✓</span>
                    <span>Atendimento digital oficial da Prefeitura de Seabra</span>
                </div>
            </div>
            <div class="hero-visual">
                <div class="visual-frame">
                    <img src="{{ asset('img/home.webp') }}" alt="Ilustração de serviços públicos ambientais digitais">
                </div>
                <div class="visual-caption">
                    <span class="caption-dot" aria-hidden="true"></span>
                    <span>Serviços ambientais municipais</span>
                </div>
            </div>
        </div>
    </section>

    <section id="servicos" class="services-section">
        <div class="home-container">
            <div class="section-heading">
                <p class="eyebrow">Serviços ao cidadão</p>
                <h2>Resolva suas solicitações ambientais online</h2>
                <p>Consulte os principais serviços oferecidos pela Secretaria Municipal de Meio Ambiente.</p>
            </div>
            <div class="services-grid">
                <article class="service-card">
                    <div class="service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h7l4.25 4.5v11A1.75 1.75 0 0116.5 21h-9A1.75 1.75 0 015.75 19.25v-13A2.5 2.5 0 018.25 3.75M14 4v5h4M9 13h6m-6 4h6"/></svg>
                    </div>
                    <h3>Licenciamento ambiental</h3>
                    <p>Solicite licenças e serviços relacionados às atividades e empreendimentos no município.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5l7 3v5.25c0 4.45-2.95 7.65-7 8.75-4.05-1.1-7-4.3-7-8.75V6.5l7-3zM9 12l2 2 4-4"/></svg>
                    </div>
                    <h3>Autorizações ambientais</h3>
                    <p>Encaminhe pedidos de autorização e demais requerimentos ambientais de forma digital.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 016.5 3H20v16H6.5A2.5 2.5 0 014 16.5v-11zM4 16.5A2.5 2.5 0 016.5 14H20M8 7h8m-8 3h5"/></svg>
                    </div>
                    <h3>Acompanhamento de processos</h3>
                    <p>Consulte o andamento das solicitações e acesse as atualizações do seu protocolo.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="como-funciona" class="process-section">
        <div class="home-container process-layout">
            <div class="process-copy">
                <p class="eyebrow">Simples e digital</p>
                <h2>Como fazer sua solicitação</h2>
                <p>Use sua conta para enviar documentos e acompanhar cada etapa do atendimento.</p>
                @auth
                    <a class="text-link" href="{{ route('requerimentos.cidadao.meusRequerimentos') }}">Acompanhar minhas solicitações <span aria-hidden="true">→</span></a>
                @else
                    <a class="text-link" href="{{ route('login') }}">Acessar minha conta <span aria-hidden="true">→</span></a>
                @endauth
            </div>
            <ol class="process-steps">
                <li>
                    <span class="step-number">01</span>
                    <div><h3>Acesse ou crie sua conta</h3><p>Entre no sistema com seus dados ou faça seu cadastro.</p></div>
                </li>
                <li>
                    <span class="step-number">02</span>
                    <div><h3>Preencha o requerimento</h3><p>Escolha o serviço e informe os dados e documentos solicitados.</p></div>
                </li>
                <li>
                    <span class="step-number">03</span>
                    <div><h3>Acompanhe o protocolo</h3><p>Consulte atualizações e movimentações da sua solicitação online.</p></div>
                </li>
            </ol>
        </div>
    </section>
</div>

@if (session('error'))
    <div id="modal-error" class="modal" onclick="closeModal('modal-error')">
        <div class="modal-box">
            <div class="icon-circle bg-error">
                <img loading="lazy" src="{{ asset('img/icones_claros/x.png') }}" alt="Erro">
            </div>
            <h2>Algo deu errado!</h2>
            <p>{{ session('error') }}</p>
        </div>
    </div>
@endif

@if (session('success'))
    <div id="modal-success" class="modal" onclick="closeModal('modal-success')">
        <div class="modal-box">
            <div class="icon-circle bg-success">
                <img loading="lazy" src="{{ asset('img/icones_claros/check.png') }}" alt="Sucesso">
            </div>
            <h2>Sucesso!</h2>
            <p>{{ session('success') }}</p>
        </div>
    </div>
@endif



<script src="{{ asset('js/home.js') }}"></script>
@endsection
