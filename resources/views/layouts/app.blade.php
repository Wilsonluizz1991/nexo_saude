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
    <x-app-header />
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
