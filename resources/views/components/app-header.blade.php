@php
    $user = auth()->user();
    $isAdmin = (bool) ($user?->is_admin || $user?->perfil === 'admin');
    $userRole = $isAdmin ? 'Administrador' : 'Corretor';
    $homeRoute = $isAdmin ? route('admin.dashboard') : route('dashboard');

    $avatarPath = $user?->avatar_path ?: $user?->corretorPerfil?->foto_path;
    $adminSearchRoute = request()->routeIs('admin.auditoria.*') ? route('admin.auditoria.index') : route('admin.usuarios.index');
    $adminSearchPlaceholder = request()->routeIs('admin.auditoria.*')
        ? 'Buscar auditorias, ações, administradores...'
        : 'Buscar usuários, perfis e assinaturas...';

    $nomeCompletoUsuario = trim($user?->name ?? 'Usuário');
    $partesNomeUsuario = collect(preg_split('/\s+/', $nomeCompletoUsuario))
        ->filter()
        ->values();

    $nomeExibicaoUsuario = $partesNomeUsuario->count() > 1
        ? $partesNomeUsuario->first() . ' ' . $partesNomeUsuario->last()
        : ($partesNomeUsuario->first() ?: 'Usuário');

    $iniciais = $partesNomeUsuario
        ->take(2)
        ->map(fn($parte) => mb_substr($parte, 0, 1))
        ->implode('');

    $compromissosHoje = $cabecalho['compromissosHoje'] ?? collect();
    $tarefasPendentes = $cabecalho['tarefasPendentes'] ?? collect();
    $alertasNaoLidos = $cabecalho['alertasNaoLidos'] ?? collect();

    $quantidadeCompromissosHoje = $cabecalho['quantidadeCompromissosHoje'] ?? 0;
    $quantidadeTarefasPendentes = $cabecalho['quantidadeTarefasPendentes'] ?? 0;
    $quantidadeAlertasNaoLidos = $cabecalho['quantidadeAlertasNaoLidos'] ?? 0;
    $quantidadePreCadastrosPendentes = $cabecalho['quantidadePreCadastrosPendentes'] ?? 0;

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
            [
                'label' => 'Auditoria',
                'icon' => 'bi-shield-check',
                'url' => route('admin.auditoria.index'),
                'active' => request()->routeIs('admin.auditoria.*'),
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
                'badge' => $quantidadePreCadastrosPendentes,
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

                @if(!$isAdmin)
                    <form class="nexo-search" role="search" method="GET" action="{{ route('busca.index') }}" data-global-search-form>
                        <i class="bi bi-search nexo-search-icon" aria-hidden="true"></i>
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Buscar por leads, clientes, propostas..."
                            aria-label="Buscar por leads, clientes, propostas"
                            autocomplete="off"
                            data-global-search-input
                        >
                    </form>
                @else
                    <form class="nexo-search" role="search" method="GET" action="{{ $adminSearchRoute }}">
                        <i class="bi bi-search nexo-search-icon" aria-hidden="true"></i>
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="{{ $adminSearchPlaceholder }}"
                            aria-label="{{ $adminSearchPlaceholder }}"
                            autocomplete="off"
                        >
                    </form>
                @endif

                <div class="nexo-header-actions">
                    @if(!$isAdmin)
                        <div class="dropdown nexo-action-dropdown">
                            <button class="nexo-action-icon btn p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Calendário">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>

                                @if($quantidadeCompromissosHoje > 0)
                                    <span class="nexo-badge">{{ $quantidadeCompromissosHoje }}</span>
                                @endif
                            </button>

                            <div class="dropdown-menu dropdown-menu-end nexo-header-panel">
                                <div class="nexo-panel-title">Compromissos de hoje</div>

                                <div class="nexo-panel-list">
                                    @forelse($compromissosHoje as $compromisso)
                                        <a class="nexo-panel-item" href="{{ route('agenda.index') }}">
                                            <strong>{{ $compromisso->titulo }}</strong>
                                            <span>{{ $compromisso->vencimento ? $compromisso->vencimento->format('d/m/Y') : 'Sem vencimento' }}</span>
                                        </a>
                                    @empty
                                        <div class="nexo-panel-empty">Nenhum compromisso para hoje.</div>
                                    @endforelse
                                </div>

                                <a class="nexo-panel-footer" href="{{ route('agenda.index') }}">Ver agenda completa</a>
                            </div>
                        </div>

                        <div class="dropdown nexo-action-dropdown">
                            <button class="nexo-action-icon btn p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Tarefas">
                                <i class="bi bi-clipboard-check" aria-hidden="true"></i>

                                @if($quantidadeTarefasPendentes > 0)
                                    <span class="nexo-badge">{{ $quantidadeTarefasPendentes }}</span>
                                @endif
                            </button>

                            <div class="dropdown-menu dropdown-menu-end nexo-header-panel">
                                <div class="nexo-panel-title">Tarefas pendentes</div>

                                <div class="nexo-panel-list">
                                    @forelse($tarefasPendentes as $tarefa)
                                        <a class="nexo-panel-item" href="{{ route('tarefas.index') }}">
                                            <strong>{{ $tarefa->titulo }}</strong>
                                            <span>{{ ucfirst($tarefa->status) }}{{ $tarefa->vencimento ? ' · '.$tarefa->vencimento->format('d/m/Y') : '' }}</span>
                                        </a>
                                    @empty
                                        <div class="nexo-panel-empty">Nenhuma tarefa pendente.</div>
                                    @endforelse
                                </div>

                                <a class="nexo-panel-footer" href="{{ route('tarefas.index') }}">Ver todas as tarefas</a>
                            </div>
                        </div>

                        <div class="dropdown nexo-action-dropdown">
                            <button class="nexo-action-icon btn p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificações">
                                <i class="bi bi-bell" aria-hidden="true"></i>

                                @if($quantidadeAlertasNaoLidos > 0)
                                    <span class="nexo-badge">{{ $quantidadeAlertasNaoLidos }}</span>
                                @endif
                            </button>

                            <div class="dropdown-menu dropdown-menu-end nexo-header-panel">
                                <div class="nexo-panel-title">Alertas do sistema</div>

                                <div class="nexo-panel-list">
                                    @forelse($alertasNaoLidos as $alerta)
                                        <a class="nexo-panel-item" href="{{ route('alertas.abrir', $alerta) }}">
                                            <strong>{{ $alerta->titulo }}</strong>
                                            <span>{{ $alerta->mensagem ?: ucfirst($alerta->tipo) }}</span>
                                        </a>
                                    @empty
                                        <div class="nexo-panel-empty">Nenhum alerta no momento.</div>
                                    @endforelse
                                </div>

                                <a class="nexo-panel-footer" href="{{ route('alertas.index') }}">Ver todos os alertas</a>
                            </div>
                        </div>
                    @endif

                    <div class="dropdown nexo-user-menu">
                        <button class="btn p-0 border-0 d-flex align-items-center gap-3 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if($isAdmin)
                                <span class="nexo-avatar-placeholder nexo-avatar-admin">
                                    <i class="bi bi-shield-lock-fill"></i>
                                </span>
                            @elseif($avatarPath)
                                <img class="nexo-avatar" src="{{ asset('storage/' . $avatarPath) }}" alt="Avatar do usuário">
                            @else
                                <span class="nexo-avatar-placeholder">
                                    {{ $iniciais ?: 'U' }}
                                </span>
                            @endif

                            <span class="nexo-user-text">
                                <span class="nexo-user-name" title="{{ $nomeCompletoUsuario }}">{{ $nomeExibicaoUsuario }}</span>
                                <span class="nexo-user-role">{{ $userRole }}</span>
                            </span>

                            <i class="bi bi-chevron-down nexo-user-chevron" aria-hidden="true"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($isAdmin)
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Painel administrativo</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.usuarios.index') }}">Gerenciar usuários</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.auditoria.index') }}">Auditoria</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ route('configuracoes.perfil') }}">Configurações da Conta</a></li>
                                <li><a class="dropdown-item" href="{{ route('perfil-publico.edit') }}">Perfil público</a></li>
                                <li><a class="dropdown-item" href="{{ route('configuracoes.assinatura') }}">Assinatura</a></li>
                            @endif

                            <li><hr class="dropdown-divider"></li>

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
                @foreach($menu as $item)
                    <a class="nexo-nav-item {{ $item['active'] ? 'nexo-nav-active' : '' }}" href="{{ $item['url'] }}">
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                        {{ $item['label'] }}

                        @if(($item['badge'] ?? 0) > 0)
                            <span class="nexo-nav-badge">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            @if(!$isAdmin)
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


        .nexo-avatar {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            flex: none;
            object-fit: cover;
            object-position: center 18%;
            border: 2px solid rgba(255, 255, 255, 0.92);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        }

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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('[data-global-search-form]');

        if (!form) {
            return;
        }

        const input = form.querySelector('[data-global-search-input]');
        const searchUrl = form.getAttribute('action');
        const debounceTime = 500;
        let timeout = null;
        let lastSubmittedValue = input ? input.value.trim() : '';

        if (!input || !searchUrl) {
            return;
        }

        const isSearchPage = function () {
            return window.location.pathname.replace(/\/$/, '') === new URL(searchUrl, window.location.origin).pathname.replace(/\/$/, '');
        };

        const redirectToSearch = function (value) {
            const normalizedValue = value.trim();

            if (normalizedValue === lastSubmittedValue && isSearchPage()) {
                return;
            }

            lastSubmittedValue = normalizedValue;

            if (normalizedValue.length === 0) {
                window.location.href = searchUrl;
                return;
            }

            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('q', normalizedValue);
            window.location.href = url.toString();
        };

        input.addEventListener('input', function () {
            const value = input.value.trim();

            window.clearTimeout(timeout);

            timeout = window.setTimeout(function () {
                if (value.length >= 3) {
                    redirectToSearch(value);
                    return;
                }

                if (value.length === 0 && isSearchPage()) {
                    redirectToSearch('');
                }
            }, debounceTime);
        });

        form.addEventListener('submit', function (event) {
            const value = input.value.trim();

            if (value.length >= 3) {
                return;
            }

            event.preventDefault();

            if (value.length === 0) {
                redirectToSearch('');
            }
        });
    });
</script>