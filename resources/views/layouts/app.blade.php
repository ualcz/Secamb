<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SECAMB - Prefeitura Municipal de Seabra')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/header.css') }}?v={{ filemtime(public_path('css/header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/headerMobile.css') }}?v={{ filemtime(public_path('css/headerMobile.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
</head>

<body class="flex flex-col min-h-screen bg-zinc-100">

    <div class="desktop-header-wrapper">
        @include('layouts.partials.header')
    </div>

    <div class="mobile-header-wrapper">
        @include('layouts.partials.headerMobile')
    </div>

    <main class="main-content flex-1 container" style="padding-top: 32px;">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-inner">

            <div class="footer-brand">
                <img src="{{ asset('img/logo_prefeitura_seabra.png') }}" alt="Logo Prefeitura Municipal de Seabra" class="footer-logo">
                <div>
                    <div class="footer-brand-name">SECAMB</div>
                    <div class="footer-brand-sub">Sistema de Requisição de Licenciamento Ambiental</div>
                </div>
            </div>
            <div class="footer-links">
                @if(Route::has('report'))
                    <a href="{{ route('report') }}" class="footer-link">Reportar Erro</a>
                @endif
            </div>

        </div>

        <div class="footer-bottom">
            SECAMB &mdash; Prefeitura Municipal de Seabra &copy; {{ date('Y') }}
        </div>
    </footer>
    <script>
    function toggleDropdownSetores(event) {
        event.stopPropagation();
        const menu = document.getElementById('menuSetores');
        const arrow = event.currentTarget.querySelector('.dropdown-arrow');

        if (menu) {
            menu.classList.toggle('show');
        }
        if (arrow) {
            arrow.classList.toggle('rotated');
        }
    }

    // Fecha o dropdown ao clicar fora
    window.addEventListener('click', function() {
        const menu = document.getElementById('menuSetores');
        if (menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
            const arrow = document.querySelector('.dropdown-toggle-btn .dropdown-arrow');
            if (arrow) {
                arrow.classList.remove('rotated');
            }
        }
    });
    </script>
</body>
</html>
