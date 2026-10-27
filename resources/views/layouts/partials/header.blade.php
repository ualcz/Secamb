@auth
    <header class="site-header">
        <div class="header-inner">

            {{-- Logotipo SECAMB --}}
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isServidor() ? route('servidor.dashboard') : route('requerimentos.cidadao.meusRequerimentos')) }}" class="header-brand">
                <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="Logo Prefeitura Seabra">
                <div class="header-brand-text">
                    <span class="header-brand-title">SECAMB</span>
                    <span class="header-brand-subtitle">Requisição de Licenciamento Ambiental</span>
                </div>
            </a>

            {{-- Navegação + Usuário --}}
            <div style="display:flex; align-items:center; gap:4px;">

                <nav class="header-nav">

                    {{-- Dashboard para administradores e servidores --}}
                    @if(auth()->user()->isAdmin() || auth()->user()->isServidor())
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('servidor.dashboard') }}"
                           class="nav-link {{ request()->routeIs('admin.dashboard', 'servidor.dashboard') ? 'active' : '' }}">
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('admin.consultar-requerimentos') }}"
                           class="nav-link {{ request()->routeIs('admin.consultar-requerimentos') ? 'active' : '' }}">
                            <span>Consultar requerimentos</span>
                        </a>
                    @else
                        {{-- Menu para Cidadão --}}
                        <a href="{{ route('home') }}"
                           class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5M5 9v11h14V9M9 20v-6h6v6"/>
                            </svg>
                            <span>Home</span>
                        </a>

                        <a href="{{ route('empreendimentos.index') }}"
                           class="nav-link {{ request()->routeIs('empreendimentos.*') ? 'active' : '' }}">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V6.5A2.5 2.5 0 015.5 4H18a2 2 0 012 2v15M3 21h18M8 8h3m-3 4h3m5-4h2m-2 4h2"/>
                            </svg>
                            <span>Empreendimentos</span>
                        </a>

                        <a href="{{ route('requerimentos.cidadao.novo') }}"
                           class="nav-link {{ request()->routeIs('requerimentos.cidadao.novo') ? 'active' : '' }}">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Novo Requerimento</span>
                        </a>

                        <a href="{{ route('requerimentos.cidadao.meusRequerimentos') }}"
                           class="nav-link {{ request()->routeIs('requerimentos.cidadao.meusRequerimentos', 'requerimentos.cidadao.visualizar') ? 'active' : '' }}">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Meus Requerimentos</span>
                        </a>
                    @endif

                    {{-- Dropdown de Setores (para quem tem setores sob responsabilidade) --}}
                    @if(auth()->user()->ehResponsavel() && auth()->user()->setoresSobResponsabilidade->count() > 0)
                        @php
                            $setorAtivo = auth()->user()->setoresSobResponsabilidade->first(function($setor) {
                                return request()->is("setor/{$setor->id}*");
                            });
                        @endphp

                        <div class="dropdown-setores">
                            <button type="button" class="dropdown-toggle-btn {{ $setorAtivo ? 'active' : '' }}" onclick="toggleDropdownSetores(event)">
                                <div style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span style="overflow: hidden; text-overflow: ellipsis;">
                                        {{ $setorAtivo ? ($setorAtivo->setor_sigla ?? $setorAtivo->nome) : 'Seus Setores' }}
                                    </span>
                                </div>
                                <svg class="dropdown-arrow {{ $setorAtivo ? 'rotated' : '' }}" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div class="dropdown-menu-setores" id="menuSetores">
                                @foreach(auth()->user()->setoresSobResponsabilidade as $setor)
                                    <a href="{{ route('setor.responsavel.dashboard', $setor->id) }}"
                                       class="dropdown-item {{ request()->is("setor/{$setor->id}*") ? 'active' : '' }}">
                                        <span>{{ $setor->setor_sigla ?? $setor->nome }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </nav>

                <div class="header-user">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.setores.index') }}" class="mr-[-10px] nav-link {{ request()->routeIs('admin.setores.*', 'admin.modelos.*') ? 'active' : '' }}" title="Configurações">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Configurações</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}" title="Usuários">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span>Usuários</span>
                        </a>
                    @else
                        @if(auth()->user()->ehResponsavel())
                            <a href="{{ route('admin.setores.index') }}" class="nav-link {{ request()->routeIs('admin.setores.index') ? 'active' : '' }}" title="Configurações">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Configurações</span>
                            </a>
                        @endif
                    @endif

                    {{-- Menu de Perfil / Ações do Usuário --}}
                    <details class="user-menu desktop-profile-menu">
                        <summary class="desktop-profile-trigger" aria-label="Abrir menu do perfil">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 21a7 7 0 0114 0"></path>
                            </svg>
                            <span>{{ explode(' ', auth()->user()->nome ?? 'Usuário')[0] }}</span>
                        </summary>
                        <div class="user-menu-panel">
                            <a href="{{ route('perfil.index') }}">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Meu Perfil
                            </a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('perfil.senha') }}">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    Alterar Senha
                                </a>
                            @else
                                <a href="{{ route('perfil.edit') }}">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Editar Dados
                                </a>
                                <a href="{{ route('perfil.senha') }}">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    Alterar Senha
                                </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" style="color: #dc2626;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sair da conta
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            </div>

        </div>
    </header>
@endauth

{{-- Header para Visitante / Deslogado --}}
@guest
    <header class="site-header">
        <div class="header-inner">

            {{-- Logotipo SECAMB --}}
            <a href="{{ route('home') }}" class="header-brand">
                <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="Logo Prefeitura Seabra">
                <div class="header-brand-text">
                    <span class="header-brand-title">SECAMB</span>
                    <span class="header-brand-subtitle">Requisição de Licenciamento Ambiental</span>
                </div>
            </a>

            {{-- Links centrais na landing page --}}
            <ul class="hidden md:flex space-x-6 md:space-x-4 xl:space-x-8 text-sm md:text-xs xl:text-base font-medium absolute left-1/2 transform -translate-x-1/2">
                <li><a href="#inicio" class="nav-link scroll-link text-gray-700 hover:text-emerald-600 transition">Início</a></li>
                <li><a href="#funcionalidades" class="nav-link scroll-link text-gray-700 hover:text-emerald-600 transition">Funcionalidades</a></li>
                <li><a href="#sobre" class="nav-link scroll-link text-gray-700 hover:text-emerald-600 transition">Sobre</a></li>
                <li><a href="#devs" class="nav-link scroll-link text-gray-700 hover:text-emerald-600 transition">Desenvolvedores</a></li>
            </ul>

            {{-- Navegação da direita --}}
            <div style="display:flex; align-items:center; gap:8px;">
                <nav class="header-nav">
                    <a href="{{ route('home') }}" class="nav-link">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5M5 9v11h14V9M9 20v-6h6v6"/>
                        </svg>
                        <span>Home</span>
                    </a>
                </nav>

                <div class="header-user" style="display:flex; gap:8px; align-items:center;">
                    <a class="nav-link active" href="{{ route('login') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        <span>Entrar</span>
                    </a>
                    <a class="nav-link" href="{{ route('register') }}" style="background:#0284c7; color:#fff; border-radius:8px; padding:6px 12px; font-weight:600;">
                        <span>Cadastre-se</span>
                    </a>
                </div>
            </div>

        </div>
    </header>
@endguest
