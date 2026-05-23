@php
    $menu = [
        ['label' => 'Dashboard', 'icon' => 'bi-house-door', 'url' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
        ['label' => 'Leads', 'icon' => 'bi-people', 'url' => route('indicacoes.index'), 'active' => request()->routeIs('indicacoes.*')],
        ['label' => 'Propostas', 'icon' => 'bi-file-earmark-text', 'url' => route('paginas.simples', 'propostas'), 'active' => request()->routeIs('propostas.*') || request('pagina') === 'propostas'],
        ['label' => 'Pré-cadastros', 'icon' => 'bi-clipboard2-check', 'url' => route('paginas.simples', 'pre-cadastros'), 'active' => request()->routeIs('pre-cadastros.*') || request('pagina') === 'pre-cadastros', 'badge' => $cabecalho['quantidadePreCadastrosPendentes'] ?? 0],
        ['label' => 'Implantações', 'icon' => 'bi-rocket-takeoff', 'url' => route('paginas.simples', 'implantacoes'), 'active' => request()->routeIs('implantacoes.*') || request('pagina') === 'implantacoes'],
        ['label' => 'Clientes', 'icon' => 'bi-person-lines-fill', 'url' => route('paginas.simples', 'clientes'), 'active' => request()->routeIs('clientes.*') || request('pagina') === 'clientes'],
        ['label' => 'Carteira', 'icon' => 'bi-briefcase', 'url' => route('paginas.simples', 'carteira'), 'active' => request('pagina') === 'carteira'],
    ];

    $user = auth()->user();
    $compromissosHoje = $cabecalho['compromissosHoje'] ?? collect();
    $tarefasPendentes = $cabecalho['tarefasPendentes'] ?? collect();
    $alertasNaoLidos = $cabecalho['alertasNaoLidos'] ?? collect();
    $quantidadeCompromissosHoje = $cabecalho['quantidadeCompromissosHoje'] ?? 0;
    $quantidadeTarefasPendentes = $cabecalho['quantidadeTarefasPendentes'] ?? 0;
    $quantidadeAlertasNaoLidos = $cabecalho['quantidadeAlertasNaoLidos'] ?? 0;
@endphp

<header class="nexo-header">
    <div class="nexo-header-top">
        <div class="nexo-header-container">
            <div class="nexo-header-row">
                <a class="nexo-logo" href="{{ route('dashboard') }}" aria-label="Nexo Saúde">
                    <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
                </a>

                <form class="nexo-search" role="search" method="GET" action="{{ route('busca.index') }}">
                    <i class="bi bi-search nexo-search-icon" aria-hidden="true"></i>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Buscar por leads, clientes, propostas..."
                        aria-label="Buscar por leads, clientes, propostas"
                    >
                </form>

                <div class="nexo-header-actions">
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

                    <div class="dropdown nexo-user-menu">
                        <button class="btn p-0 border-0 d-flex align-items-center gap-3 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="nexo-avatar" src="{{ $user->avatar_path ? asset('storage/'.$user->avatar_path) : 'https://i.pravatar.cc/160?img=12' }}" alt="Avatar do corretor">
                            <span class="nexo-user-text">
                                <span class="nexo-user-name">{{ $user->name }}</span>
                                <span class="nexo-user-role">Corretor</span>
                            </span>
                            <i class="bi bi-chevron-down nexo-user-chevron" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('configuracoes.perfil') }}">Configurações da Conta</a></li>
                            <li><a class="dropdown-item" href="{{ route('perfil-publico.edit') }}">Perfil público</a></li>
                            <li><a class="dropdown-item" href="{{ route('assinatura.bloqueada') }}">Assinatura</a></li>
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

            <a class="nexo-btn-primary" href="{{ route('indicacoes.create') }}">
                <i class="bi bi-plus-lg fs-4" aria-hidden="true"></i>
                Nova Lead
            </a>
        </div>
    </nav>
</header>
