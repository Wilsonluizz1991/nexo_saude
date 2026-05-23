@props(['titulo'])

@php
    $itens = [
        ['Meu Perfil', 'configuracoes.perfil', 'bi-person'],
        ['Segurança', 'configuracoes.seguranca', 'bi-shield-lock'],
        ['Assinatura', 'configuracoes.assinatura', 'bi-credit-card'],
        ['Preferências', 'configuracoes.preferencias', 'bi-sliders'],
        ['Mensagem WhatsApp', 'configuracoes.mensagem-whatsapp', 'bi-whatsapp'],
        ['Privacidade', 'configuracoes.privacidade', 'bi-file-lock'],
        ['Sessões Ativas', 'configuracoes.sessoes', 'bi-laptop'],
        ['Excluir Conta', 'configuracoes.excluir-conta', 'bi-trash'],
    ];
@endphp

<x-layouts.app title="{{ $titulo }} | Nexo Saúde">
    <main class="nexo-main">
        <div class="nexo-settings">
            <aside class="nexo-settings-sidebar nexo-card">
                <h1>Configurações da Conta</h1>
                @foreach($itens as [$label, $route, $icon])
                    <a class="nexo-settings-link {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">
                        <i class="bi {{ $icon }}"></i>{{ $label }}
                    </a>
                @endforeach
            </aside>
            <section class="nexo-settings-content nexo-card">
                {{ $slot }}
            </section>
        </div>
    </main>
</x-layouts.app>
