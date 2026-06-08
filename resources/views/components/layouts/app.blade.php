<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Nexo Saúde' }}</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@auth
    <div class="nexo-auth-shell" data-auth-shell>
        <x-layout.sidebar />

        <div class="nexo-auth-main">
            <div class="nexo-mobile-topbar">
                <button type="button" class="nexo-mobile-menu-button" data-sidebar-open aria-label="Abrir menu">
                    <i data-lucide="menu" class="h-5 w-5" aria-hidden="true"></i>
                </button>
                <a href="{{ (auth()->user()?->is_admin || auth()->user()?->perfil === 'admin') ? route('admin.dashboard') : route('dashboard') }}" class="nexo-mobile-logo" aria-label="Nexo Saúde">
                    <img src="{{ asset('assets/nexo-logo-texto-preto.png') }}" alt="Nexo Saúde">
                </a>
            </div>
@endauth

@if(session('status') || $errors->any())
    <div class="nexo-floating-toast {{ $errors->any() ? 'is-danger' : '' }}" data-nexo-page-toast>
        <div class="nexo-floating-toast-icon">
            <i class="bi {{ $errors->any() ? 'bi-exclamation-triangle' : 'bi-check2-circle' }}"></i>
        </div>

        <div class="nexo-floating-toast-content">
            <strong>{{ $errors->any() ? 'Atenção' : 'Sucesso' }}</strong>
            <span>{{ $errors->any() ? $errors->first() : session('status') }}</span>
        </div>

        <button type="button" class="nexo-floating-toast-close" data-toast-close aria-label="Fechar notificação">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="nexo-floating-toast-progress"></div>
    </div>
@endif

{{ $slot }}

@auth
        </div>
    </div>
@endauth

<x-responsive-overrides />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
