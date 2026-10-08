@extends('layouts.app')

@section('title', 'SECAMB - Secretaria Municipal de Meio Ambiente de Seabra')
@section('tag', 'Início')

@section('content')

<link rel="stylesheet" href="{{ asset('css/home.css') }}">

<section id="inicio" class="fade-up">    
    <div class="container">
        <div class="hero-wrapper">
            <div class="hero-text">
                <div class="title">
                    <h1>Sistema de Gestão Ambiental e Licenciamento</h1>
                    <h2>Prefeitura Municipal de Seabra - SECAMB</h2>
                </div>
                <p>
                    O SECAMB Digital foi desenvolvido para facilitar a emissão de licenças, autorizações e o acompanhamento de processos ambientais municipais, garantindo transparência, sustentabilidade e agilidade ao cidadão e empreendedor.
                </p>
                <div class="hero-actions">
                    @auth
                        <div style="display:flex; gap:12px; flex-wrap:wrap;">
                            <a href="{{ route('requerimentos.cidadao.novo') }}" class="btn-primary">
                                Novo Requerimento →
                            </a>
                            <a href="{{ route('empreendimentos.index') }}" class="btn-primary" style="background:#0284c7; border-color:#0284c7;">
                                Meus Empreendimentos
                            </a>
                        </div>
                    @else
                        <div style="display:flex; gap:12px; flex-wrap:wrap;">
                            <a href="{{ route('login') }}" class="btn-primary">
                                Acessar Sistema →
                            </a>
                            <a href="{{ route('register') }}" class="btn-primary" style="background:#0284c7; border-color:#0284c7;">
                                Cadastre-se
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
            <div class="hero-image">
                <img src="{{ asset('img/home.webp') }}" alt="Página Inicial SECAMB">
            </div>
        </div>
    </div>
</section>

<section id="funcionalidades" class="fade-up">
    <div class="container">
        <h2>Funcionalidades do SECAMB Digital</h2>
        <div class="cards-grid">
            <div class="card">
                <h3>Abertura de Processos</h3>
                <p>Solicite Licenças Ambientais (LP, LI, LO), Autorizações de Supressão e Dispensa de forma 100% digital.</p>
            </div>

            <div class="card">
                <h3>Acompanhamento em Tempo Real</h3>
                <p>Monitore o parecer técnico, parecer jurídico e despachos dos analistas ambientais do município.</p>
            </div>

            <div class="card">
                <h3>Emissão de Documentos</h3>
                <p>Gere requerimentos oficiais e comprovantes de protocolo com autenticação e validação eletrônica.</p>
            </div>
        </div>
    </div>
</section>

<section id="sobre" class="fade-up">
    <div class="container">
        <div class="sobre-wrapper">
            <div class="sobre-text">
                <span class="subtitle">Sobre o sistema</span>
                <h2>Como funciona a tramitação</h2>

                <p>
                    O sistema conecta cidadãos e empreendedores aos setores responsáveis da Secretaria de Meio Ambiente de Seabra. Cada processo é protocolado com numeração única e passa por análise técnica, vistorias e deliberação com total segurança e conformidade legal.
                </p>

                <div class="secamb-notice"><span class="notice-title">Sistema Municipal de Licenciamento</span><p>Seus dados são preenchidos automaticamente ao fazer login com seu e-mail e senha cadastrados. Mantenha seu cadastro sempre atualizado.</p></div>
            </div>

            <div class="sobre-image">
                <img loading="lazy" src="{{ asset('img/home_sistema.webp') }}" alt="Ilustração do sistema SECAMB" class="fade-right">
            </div>
        </div>
    </div>
</section>

<section id="devs" class="fade-up">
    <div class="container">
        <h2>Desenvolvedores</h2>
        <div class="devs-grid">
            <div class="card-dev">
                <div class="avatar">
                    <img loading="lazy" src="{{ asset('img/perfil/caio.webp') }}" alt="Caio Souza dos Anjos">
                </div>
                <h3>Caio Souza dos Anjos</h3>
                <p class="role">Discente IFBA</p>
                <p class="desc">Desenvolvedor do projeto em parceria com o IFBA Seabra.</p>
            </div>
            <div class="card-dev">
                <div class="avatar">
                    <img loading="lazy" src="{{ asset('img/perfil/Clau.jpg') }}" alt="Claudeilson Souza Assunção">
                </div>
                <h3>Claudeilson Souza Assunção</h3>
                <p class="role">Discente IFBA</p>
                <p class="desc">Desenvolvedor do projeto em parceria com o IFBA Seabra.</p>
            </div>
            <div class="card-dev">
                <div class="avatar">
                    <img loading="lazy" src="{{ asset('img/perfil/graziele.webp') }}" alt="Graziele Brandão Silva">
                </div>
                <h3>Graziele Brandão Silva</h3>
                <p class="role">Discente IFBA</p>
                <p class="desc">Desenvolvedora do projeto em parceria com o IFBA Seabra.</p>
            </div>
            <div class="card-dev">
                <div class="avatar">
                    <img loading="lazy" src="{{ asset('img/perfil/larissa.jpg') }}" alt="Larissa Souza Rocha">
                </div>
                <h3>Larissa Souza Rocha</h3>
                <p class="role">Discente IFBA</p>
                <p class="desc">Desenvolvedora do projeto em parceria com o IFBA Seabra.</p>
            </div>

            <div class="card-dev">
                <div class="avatar">
                    <img loading="lazy" src="{{ asset('img/perfil/perfil_Charles.webp') }}" alt="Monck Charles Albuquerque">
                </div>
                <h3>Monck Charles Albuquerque</h3>
                <p class="role">Docente / Orientador</p>
                <p class="desc">Docente no IFBA - Campus Seabra e orientador do projeto.</p>
            </div>
        </div>
    </div>
</section>

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
