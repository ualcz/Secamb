<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SDP - IFBA Seabra')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
</head>

<body class="flex flex-col min-h-screen bg-zinc-100">

    @auth
        <header class="site-header">
            <div class="header-inner">

                {{-- Logotipo --}}
            <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isServidor() ? route('servidor.dashboard') : route('requerimentos.aluno'))) : url('/') }}" class="header-brand">
                    <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="Logo Prefeitura de Seabra">
                    <div class="header-brand-text">
                        <span class="header-brand-title">Secamb</span>
                        <span class="header-brand-subtitle">Sistema de Requisição de Licenciamento Ambiental</span>
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
                            {{-- vou adicionar uma página home que terá informações do sistema e devs --}}

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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Empreendimentos</span>
                            </a>

                            <a href="{{ route('requerimentos.aluno.novo') }}"
                            class="nav-link {{ request()->routeIs('requerimentos.aluno.novo') ? 'active' : '' }}">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Novo Requerimento</span>
                            </a>

                            <a href="{{ route('requerimentos.aluno.meusRequerimentos') }}"
                            class="nav-link {{ request()->routeIs('requerimentos.aluno.meusRequerimentos', 'requerimentos.aluno.visualizar') ? 'active' : '' }}">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Meus Requerimentos</span>
                            </a>
                        @endif
                        @auth
                            @if(auth()->user()->ehResponsavel())
                                @foreach(auth()->user()->setoresSobResponsabilidade as $setor)
                                    <a href="{{ route('setor.responsavel.dashboard', $setor->id) }}"
                                    class="nav-link {{ request()->is("setor/{$setor->id}*") ? 'active' : '' }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span>{{ $setor->setor_sigla ?? $setor->nome }}</span>
                                    </a>
                                @endforeach
                            @endif
                        @endauth
                    </nav>


                    <div class="header-user">
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.setores.index') }}" class="mr-[-10px] nav-link {{ request()->routeIs('admin.setores.*', 'admin.modelos.*') ? 'active' : '' }}">
                            <svg fill="gray" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="23" height="23" viewBox="0 0 50 50">
                                <path d="M 22.205078 2 A 1.0001 1.0001 0 0 0 21.21875 2.8378906 L 20.246094 8.7929688 C 19.076509 9.1331971 17.961243 9.5922728 16.910156 10.164062 L 11.996094 6.6542969 A 1.0001 1.0001 0 0 0 10.708984 6.7597656 L 6.8183594 10.646484 A 1.0001 1.0001 0 0 0 6.7070312 11.927734 L 10.164062 16.873047 C 9.583454 17.930271 9.1142098 19.051824 8.765625 20.232422 L 2.8359375 21.21875 A 1.0001 1.0001 0 0 0 2.0019531 22.205078 L 2.0019531 27.705078 A 1.0001 1.0001 0 0 0 2.8261719 28.691406 L 8.7597656 29.742188 C 9.1064607 30.920739 9.5727226 32.043065 10.154297 33.101562 L 6.6542969 37.998047 A 1.0001 1.0001 0 0 0 6.7597656 39.285156 L 10.648438 43.175781 A 1.0001 1.0001 0 0 0 11.927734 43.289062 L 16.882812 39.820312 C 17.936999 40.39548 19.054994 40.857928 20.228516 41.201172 L 21.21875 47.164062 A 1.0001 1.0001 0 0 0 22.205078 48 L 27.705078 48 A 1.0001 1.0001 0 0 0 28.691406 47.173828 L 29.751953 41.1875 C 30.920633 40.838997 32.033372 40.369697 33.082031 39.791016 L 38.070312 43.291016 A 1.0001 1.0001 0 0 0 39.351562 43.179688 L 43.240234 39.287109 A 1.0001 1.0001 0 0 0 43.34375 37.996094 L 39.787109 33.058594 C 40.355783 32.014958 40.813915 30.908875 41.154297 29.748047 L 47.171875 28.693359 A 1.0001 1.0001 0 0 0 47.998047 27.707031 L 47.998047 22.207031 A 1.0001 1.0001 0 0 0 47.160156 21.220703 L 41.152344 20.238281 C 40.80968 19.078827 40.350281 17.974723 39.78125 16.931641 L 43.289062 11.933594 A 1.0001 1.0001 0 0 0 43.177734 10.652344 L 39.287109 6.7636719 A 1.0001 1.0001 0 0 0 37.996094 6.6601562 L 33.072266 10.201172 C 32.023186 9.6248101 30.909713 9.1579916 29.738281 8.8125 L 28.691406 2.828125 A 1.0001 1.0001 0 0 0 27.705078 2 L 22.205078 2 z M 23.056641 4 L 26.865234 4 L 27.861328 9.6855469 A 1.0001 1.0001 0 0 0 28.603516 10.484375 C 30.066026 10.848832 31.439607 11.426549 32.693359 12.185547 A 1.0001 1.0001 0 0 0 33.794922 12.142578 L 38.474609 8.7792969 L 41.167969 11.472656 L 37.835938 16.220703 A 1.0001 1.0001 0 0 0 37.796875 17.310547 C 38.548366 18.561471 39.118333 19.926379 39.482422 21.380859 A 1.0001 1.0001 0 0 0 40.291016 22.125 L 45.998047 23.058594 L 45.998047 26.867188 L 40.279297 27.871094 A 1.0001 1.0001 0 0 0 39.482422 28.617188 C 39.122545 30.069817 38.552234 31.434687 37.800781 32.685547 A 1.0001 1.0001 0 0 0 37.845703 33.785156 L 41.224609 38.474609 L 38.53125 41.169922 L 33.791016 37.84375 A 1.0001 1.0001 0 0 0 32.697266 37.808594 C 31.44975 38.567585 30.074755 39.148028 28.617188 39.517578 A 1.0001 1.0001 0 0 0 27.876953 40.3125 L 26.867188 46 L 23.052734 46 L 22.111328 40.337891 A 1.0001 1.0001 0 0 0 21.365234 39.53125 C 19.90185 39.170557 18.522094 38.59371 17.259766 37.835938 A 1.0001 1.0001 0 0 0 16.171875 37.875 L 11.46875 41.169922 L 8.7734375 38.470703 L 12.097656 33.824219 A 1.0001 1.0001 0 0 0 12.138672 32.724609 C 11.372652 31.458855 10.793319 30.079213 10.427734 28.609375 A 1.0001 1.0001 0 0 0 9.6328125 27.867188 L 4.0019531 26.867188 L 4.0019531 23.052734 L 9.6289062 22.117188 A 1.0001 1.0001 0 0 0 10.435547 21.373047 C 10.804273 19.898143 11.383325 18.518729 12.146484 17.255859 A 1.0001 1.0001 0 0 0 12.111328 16.164062 L 8.8261719 11.46875 L 11.523438 8.7734375 L 16.185547 12.105469 A 1.0001 1.0001 0 0 0 17.28125 12.148438 C 18.536908 11.394293 19.919867 10.822081 21.384766 10.462891 A 1.0001 1.0001 0 0 0 22.132812 9.6523438 L 23.056641 4 z M 25 17 C 20.593567 17 17 20.593567 17 25 C 17 29.406433 20.593567 33 25 33 C 29.406433 33 33 29.406433 33 25 C 33 20.593567 29.406433 17 25 17 z M 25 19 C 28.325553 19 31 21.674447 31 25 C 31 28.325553 28.325553 31 25 31 C 21.674447 31 19 28.325553 19 25 C 19 21.674447 21.674447 19 25 19 z"></path>
                            </svg>
                            <span>Configurações</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Usuários</span>
                        </a>
                        @else
                            @if(auth()->user()->ehResponsavel())
                                <a href="{{ route('admin.setores.index') }}" class="nav-link {{ request()->routeIs('admin.setores.index') ? 'active' : '' }}">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15.1 1.65 1.65 0 0 0 3.09 14H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 8.92a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>
                                    </svg>
                                    <span>Configurações</span>
                                </a>
                            @endif
                            <a class="profile header-username nav-link {{ request()->routeIs('requerimentos.aluno') ? 'active' : '' }}" href="{{ auth()->user()->isServidor() ? route('servidor.dashboard') : route('requerimentos.aluno') }}">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm7 8a7 7 0 00-14 0"/>
                                </svg>
                                {{ explode(' ', auth()->user()->nome)[0] }}
                            </a>
                        @endif

                            <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-logout">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sair
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </header>
    @endauth
{{-- se estiver deslogado: --}}
    @guest
          <header class="site-header">
            <div class="header-inner">

                {{-- Logotipo --}}
            <a href="{{ route('home') }}" class="header-brand">
                    <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="Logo Prefeitura Seabra">
                    <div class="header-brand-text">
                        <span class="header-brand-title">Secamb</span>
                        <span class="header-brand-subtitle">Requisição de Licenciamento Ambiental</span>
                    </div>
                </a>

                   {{-- Centro: Links principais centralizados --}}
                <ul class="hidden md:flex space-x-6 md:space-x-4 xl:space-x-8 text-sm md:text-xs xl:text-base font-medium absolute left-1/2 transform -translate-x-1/2">
                    <li><a href="#inicio" class="nav-link scroll-link text-gray-700 hover:text-purple-600 transition" style="margin-right:-25px;">Início</a></li>
                    <li><a href="#funcionalidades" class="nav-link scroll-link text-gray-700 hover:text-purple-600 transition" style="margin-right:-25px;">Funcionalidades</a></li>
                    <li><a href="#sobre" class="nav-link scroll-link text-gray-700 hover:text-purple-600 transition" style="margin-right:-25px;">Sobre</a></li>
                    <li><a href="#devs" class="nav-link scroll-link text-gray-700 hover:text-purple-600 transition">Desenvolvedores</a></li>
                </ul>

                {{-- Navegação --}}
                <div style="display:flex; align-items:center; gap:4px;">

                    <nav class="header-nav">
                            <a href="{{ route('home') }}"
                            class="nav-link">
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
                            </svg>Entrar
                        </a>
                        <a class="nav-link" href="{{ route('register') }}" style="background:#0284c7; color:#fff; border-radius:8px; padding:6px 12px; font-weight:600;">
                            Cadastre-se
                        </a>
                    </div>
                </div>

            </div>
        </header>
    @endguest



    <main class="main-content flex-1 container" style="padding-top: 32px;">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-inner">

            <div class="footer-brand">
                <img src="{{ asset('img/logoVertical.png') }}" alt="Logo IFBA" class="footer-logo">
                <div>
                    <div class="footer-brand-name">Secamb</div>
                    <div class="footer-brand-sub">Sistema de Requisição de Licenciamento Ambiental</div>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            Secamb &mdash; Prefeitura Municipal de Seabra &copy; {{ date('Y') }}
        </div>
    </footer>

</body>

</html>
