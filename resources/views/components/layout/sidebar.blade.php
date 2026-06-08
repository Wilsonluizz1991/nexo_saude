@php
    $user = auth()->user();
    $isAdmin = (bool) ($user?->is_admin || $user?->perfil === 'admin');
    $homeRoute = $isAdmin ? route('admin.dashboard') : route('dashboard');
    $nomeCompletoUsuario = trim($user?->name ?? 'Usuário');
    $partesNomeUsuario = collect(preg_split('/\s+/', $nomeCompletoUsuario))->filter()->values();
    $nomeExibicaoUsuario = $partesNomeUsuario->count() > 1
        ? ucfirst(mb_strtolower($partesNomeUsuario->first())).' '.ucfirst(mb_strtolower($partesNomeUsuario->last()))
        : ucfirst(mb_strtolower($partesNomeUsuario->first() ?: 'Usuário'));
    $iniciais = $partesNomeUsuario->take(2)->map(fn ($parte) => mb_substr($parte, 0, 1))->implode('') ?: 'NS';
    $avatarPath = $user?->avatar_path ?: $user?->corretorPerfil?->foto_path;
    $avatarUrl = $avatarPath
        ? (\Illuminate\Support\Str::startsWith($avatarPath, ['http://', 'https://', '/']) ? $avatarPath : asset('storage/'.$avatarPath))
        : null;
    $cargoMenu = $isAdmin ? 'Administrador' : 'Corretor Parceiro';
    $dadosCabecalho = $cabecalho ?? [];
    $alertasTotal = $dadosCabecalho['quantidadeAlertasNaoLidos'] ?? 0;

    $menu = $isAdmin
        ? [
            ['label' => 'Painel Admin', 'icon' => 'layout-dashboard', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
            ['label' => 'Usuários', 'icon' => 'users', 'url' => route('admin.usuarios.index'), 'active' => request()->routeIs('admin.usuarios.*')],
            ['label' => 'Auditoria', 'icon' => 'shield-check', 'url' => route('admin.auditoria.index'), 'active' => request()->routeIs('admin.auditoria.*')],
        ]
        : [
            ['label' => 'Visão Geral', 'icon' => 'home', 'url' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
            ['label' => 'Leads', 'icon' => 'users', 'url' => route('indicacoes.index'), 'active' => request()->routeIs('indicacoes.*')],
            ['label' => 'Propostas', 'icon' => 'file-text', 'url' => route('paginas.simples', 'propostas'), 'active' => request()->routeIs('propostas.*') || request('pagina') === 'propostas'],
            ['label' => 'Pré-cadastros', 'icon' => 'file-check-2', 'url' => route('paginas.simples', 'pre-cadastros'), 'active' => request()->routeIs('pre-cadastros.*') || request('pagina') === 'pre-cadastros'],
            ['label' => 'Implantações', 'icon' => 'clipboard-list', 'url' => route('paginas.simples', 'implantacoes'), 'active' => request()->routeIs('implantacoes.*') || request('pagina') === 'implantacoes'],
            ['label' => 'Carteira', 'icon' => 'briefcase', 'url' => route('paginas.simples', 'carteira'), 'active' => request('pagina') === 'carteira'],
        ];

    $menuSecundario = $isAdmin
        ? [
            ['label' => 'Sair da conta', 'icon' => 'log-out', 'url' => null, 'active' => false],
        ]
        : [
            ['label' => 'Alertas', 'icon' => 'bell', 'url' => route('alertas.index'), 'active' => request()->routeIs('alertas.*'), 'badge' => $alertasTotal],
            ['label' => 'Configurações', 'icon' => 'settings', 'url' => route('configuracoes.perfil'), 'active' => request()->routeIs('configuracoes.*') || request()->routeIs('perfil-publico.*')],
        ];
@endphp

<div class="nexo-sidebar-backdrop" data-sidebar-close></div>

<aside class="nexo-sidebar" data-nexo-sidebar aria-label="Menu principal da Nexo Saúde">
    <div class="nexo-sidebar-top">
        <a href="{{ $homeRoute }}" class="nexo-sidebar-logo" aria-label="Nexo Saúde">
            <img src="{{ asset('assets/logo-nexo-texto-branco.png') }}" alt="Nexo Saúde">
        </a>
        <p>Seu crescimento<br>conectado.</p>
        <x-layout.global-search />
    </div>

    <nav class="nexo-sidebar-nav" aria-label="Navegação principal">
        @foreach($menu as $item)
            <x-layout.sidebar-link
                :href="$item['url']"
                :icon="$item['icon']"
                :active="$item['active']"
                :badge="$item['badge'] ?? null"
            >
                {{ $item['label'] }}
            </x-layout.sidebar-link>
        @endforeach

        <div class="nexo-sidebar-separator"></div>

        @foreach($menuSecundario as $item)
            @if($item['url'])
                <x-layout.sidebar-link
                    :href="$item['url']"
                    :icon="$item['icon']"
                    :active="$item['active']"
                    :badge="$item['badge'] ?? null"
                >
                    {{ $item['label'] }}
                </x-layout.sidebar-link>
            @else
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="nexo-sidebar-link w-full" type="submit">
                        <i data-lucide="{{ $item['icon'] }}" class="nexo-sidebar-link-icon" aria-hidden="true"></i>
                        <span class="nexo-sidebar-link-label">{{ $item['label'] }}</span>
                    </button>
                </form>
            @endif
        @endforeach
    </nav>

    <div class="nexo-sidebar-footer">
        <div class="nexo-sidebar-user">
            <div class="nexo-sidebar-avatar">
                @if($isAdmin)
                    <i data-lucide="shield-lock" class="h-[18px] w-[18px]" aria-hidden="true"></i>
                @elseif($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="{{ $nomeExibicaoUsuario }}">
                @else
                    <span>{{ $iniciais }}</span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p>{{ $nomeExibicaoUsuario }}</p>
                <span>{{ $cargoMenu }}</span>
            </div>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" aria-label="Sair da conta">
                    <i data-lucide="log-out" class="h-[15px] w-[15px]" aria-hidden="true"></i>
                </button>
            </form>
        </div>

        @unless($isAdmin)
            <div class="nexo-sidebar-referral">
                <img src="{{ asset('assets/dashboard/icons/rocket-bonus.png') }}" alt="">
                <strong>Indique e ganhe</strong>
                <span>Convide outros corretores<br>e ganhe bônus!</span>
                <a href="{{ route('configuracoes.perfil') }}">Indicar agora</a>
            </div>
        @endunless
    </div>
</aside>
