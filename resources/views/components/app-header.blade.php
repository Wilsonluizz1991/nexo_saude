@php
    $user = auth()->user();
    $isAdmin = (bool) ($user?->is_admin || $user?->perfil === 'admin');
    $userRole = $isAdmin ? 'Administrador' : 'Corretor';
    $homeRoute = $isAdmin ? route('admin.dashboard') : route('dashboard');

    $iniciais = collect(explode(' ', trim($user?->name ?? 'Usuário')))
        ->filter()
        ->take(2)
        ->map(fn($parte) => mb_substr($parte, 0, 1))
        ->implode('');

    $menu = $isAdmin
        ? [
            [
                'label' => 'Painel Admin',
                'icon' => 'bi-speedometer2',
                'url' => route('admin.dashboard'),
                'active' => request()->routeIs('admin.dashboard'),
            ],
            [
                'label' => 'Usuários',
                'icon' => 'bi-people-fill',
                'url' => route('admin.usuarios.index'),
                'active' => request()->routeIs('admin.usuarios.*'),
            ],
        ]
        : [
            [
                'label' => 'Dashboard',
                'icon' => 'bi-house-door',
                'url' => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
            ],
            [
                'label' => 'Leads',
                'icon' => 'bi-people',
                'url' => route('indicacoes.index'),
                'active' => request()->routeIs('indicacoes.*'),
            ],
            [
                'label' => 'Propostas',
                'icon' => 'bi-file-earmark-text',
                'url' => route('paginas.simples', 'propostas'),
                'active' => request()->routeIs('propostas.*') || request('pagina') === 'propostas',
            ],
            [
                'label' => 'Pré-cadastros',
                'icon' => 'bi-clipboard2-check',
                'url' => route('paginas.simples', 'pre-cadastros'),
                'active' => request()->routeIs('pre-cadastros.*') || request('pagina') === 'pre-cadastros',
            ],
            [
                'label' => 'Implantações',
                'icon' => 'bi-rocket-takeoff',
                'url' => route('paginas.simples', 'implantacoes'),
                'active' => request()->routeIs('implantacoes.*') || request('pagina') === 'implantacoes',
            ],
            [
                'label' => 'Clientes',
                'icon' => 'bi-person-lines-fill',
                'url' => route('paginas.simples', 'clientes'),
                'active' => request()->routeIs('clientes.*') || request('pagina') === 'clientes',
            ],
            [
                'label' => 'Carteira',
                'icon' => 'bi-briefcase',
                'url' => route('paginas.simples', 'carteira'),
                'active' => request('pagina') === 'carteira',
            ],
        ];
@endphp

<header class="nexo-header">
    <div class="nexo-header-top">
        <div class="nexo-header-container">
            <div class="nexo-header-row">
                <a class="nexo-logo" href="{{ $homeRoute }}" aria-label="Nexo Saúde">
                    <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
                </a>

                @if (!$isAdmin)
                    <form class="nexo-search" role="search" method="GET" action="{{ route('busca.index') }}"
                        data-global-search-form>
                        <i class="bi bi-search nexo-search-icon" aria-hidden="true"></i>
                        <input type="search" name="q" value="{{ request('q') }}"
                            placeholder="Buscar por leads, clientes, propostas..."
                            aria-label="Buscar por leads, clientes, propostas" autocomplete="off"
                            data-global-search-input>
                    </form>
                @else
                    <form class="nexo-search" role="search" method="GET"
                        action="{{ route('admin.usuarios.index') }}">
                        <i class="bi bi-search nexo-search-icon" aria-hidden="true"></i>
                        <input type="search" name="q" value="{{ request('q') }}"
                            placeholder="Buscar usuários, perfis e assinaturas..."
                            aria-label="Buscar usuários, perfis e assinaturas" autocomplete="off">
                    </form>
                @endif

                <div class="nexo-header-actions">
                    <div class="dropdown nexo-user-menu">
                        <button class="btn p-0 border-0 d-flex align-items-center gap-3 text-start" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            @if ($isAdmin)
                                <span class="nexo-avatar-placeholder nexo-avatar-admin">
                                    <i class="bi bi-shield-lock-fill"></i>
                                </span>
                            @elseif($user?->avatar_path)
                                <img class="nexo-avatar" src="{{ asset('storage/' . $user->avatar_path) }}"
                                    alt="Avatar do usuário">
                            @else
                                <span class="nexo-avatar-placeholder">
                                    {{ $iniciais ?: 'U' }}
                                </span>
                            @endif

                            <span class="nexo-user-text">
                                <span class="nexo-user-name">{{ $user->name }}</span>
                                <span class="nexo-user-role">{{ $userRole }}</span>
                            </span>

                            <i class="bi bi-chevron-down nexo-user-chevron" aria-hidden="true"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">
                            @if ($isAdmin)
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Painel
                                        administrativo</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.usuarios.index') }}">Gerenciar
                                        usuários</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ route('configuracoes.perfil') }}">Configurações
                                        da Conta</a></li>
                                <li><a class="dropdown-item" href="{{ route('perfil-publico.edit') }}">Perfil
                                        público</a></li>
                                <li><a class="dropdown-item"
                                        href="{{ route('configuracoes.assinatura') }}">Assinatura</a></li>
                            @endif

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <form method="post" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">Sair</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="nexo-header-nav" aria-label="Navegação principal">
        <div class="nexo-header-container">
            <div class="nexo-nav-links">
                @foreach ($menu as $item)
                    <a class="nexo-nav-item {{ $item['active'] ? 'nexo-nav-active' : '' }}"
                        href="{{ $item['url'] }}">
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            @if (!$isAdmin)
                <a class="nexo-btn-primary" href="{{ route('indicacoes.create') }}">
                    <i class="bi bi-plus-lg fs-4" aria-hidden="true"></i>
                    Nova Lead
                </a>
            @else
                <a class="nexo-btn-primary" href="{{ route('admin.usuarios.create') }}">
                    <i class="bi bi-person-plus fs-4" aria-hidden="true"></i>
                    Novo usuário
                </a>
            @endif
        </div>
    </nav>

    <style>
        .nexo-avatar-placeholder {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            border: 3px solid rgba(255, 255, 255, 0.96);
            font-size: 1.05rem;
            font-weight: 950;
            box-shadow: 0 12px 26px rgba(47, 128, 237, 0.22);
        }

        .nexo-avatar-admin {
            background: linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            color: #7DB5FF;
            font-size: 1.35rem;
        }
    </style>
</header>
