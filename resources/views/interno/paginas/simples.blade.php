<x-layouts.app title="{{ ucfirst(str_replace('-', ' ', $pagina)) }} | Nexo Saúde">
    <main class="nexo-main">
        @php
            $titulo = [
                'pre-cadastros' => 'Pré-cadastros',
                'implantacoes' => 'Implantações',
                'relatorios' => 'Relatórios',
                'agenda' => 'Agenda',
                'clientes' => 'Clientes',
                'carteira' => 'Carteira',
                'tarefas' => 'Tarefas',
                'alertas' => 'Alertas',
                'propostas' => 'Propostas',
            ][$pagina] ?? ucfirst($pagina);

            $subtitulo = [
                'pre-cadastros' => 'Acompanhe os formulários enviados, pendentes e em análise documental.',
                'implantacoes' => 'Gerencie contratos em análise, pendências na operadora e vigências.',
                'relatorios' => 'Resumo operacional da sua carteira, tarefas, alertas e clientes.',
                'agenda' => 'Compromissos e retornos previstos para hoje.',
                'clientes' => 'Clientes ativos e registros já convertidos.',
                'carteira' => 'Acompanhe sua carteira ativa, vendas do mês, comissão e evolução comercial.',
                'tarefas' => 'Atividades pendentes e ações que exigem acompanhamento.',
                'alertas' => 'Notificações importantes do sistema e da sua operação.',
                'propostas' => 'Propostas enviadas e prontas para iniciar pré-cadastro.',
            ][$pagina] ?? 'Visão operacional com dados reais da sua carteira.';

            $iconePagina = [
                'pre-cadastros' => 'bi-clipboard2-check',
                'implantacoes' => 'bi-rocket-takeoff',
                'relatorios' => 'bi-bar-chart',
                'agenda' => 'bi-calendar3',
                'clientes' => 'bi-person-lines-fill',
                'carteira' => 'bi-briefcase',
                'tarefas' => 'bi-check2-square',
                'alertas' => 'bi-bell',
                'propostas' => 'bi-file-earmark-text',
            ][$pagina] ?? 'bi-grid';
        @endphp

        <div class="nexo-page-header mb-4">
            <div>
                <span class="nexo-page-label">
                    <i class="bi {{ $iconePagina }}"></i>
                    Operação
                </span>

                <h1>
                    {{ $titulo }}
                </h1>

                <p>
                    {{ $subtitulo }}
                </p>
            </div>
        </div>

        <div class="nexo-page-panel">
            @if($pagina === 'relatorios')
                <div class="nexo-summary-grid">
                    <div class="nexo-summary-card">
                        <div class="nexo-summary-icon">
                            <i class="bi bi-people"></i>
                        </div>

                        <div>
                            <span>Leads</span>
                            <strong>{{ method_exists($indicacoes, 'total') ? $indicacoes->total() : $indicacoes->count() }}</strong>
                        </div>
                    </div>

                    <div class="nexo-summary-card">
                        <div class="nexo-summary-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>

                        <div>
                            <span>Clientes</span>
                            <strong>{{ method_exists($clientes, 'total') ? $clientes->total() : $clientes->count() }}</strong>
                        </div>
                    </div>

                    <div class="nexo-summary-card">
                        <div class="nexo-summary-icon">
                            <i class="bi bi-check2-square"></i>
                        </div>

                        <div>
                            <span>Tarefas</span>
                            <strong>{{ method_exists($tarefas, 'total') ? $tarefas->total() : $tarefas->count() }}</strong>
                        </div>
                    </div>

                    <div class="nexo-summary-card">
                        <div class="nexo-summary-icon">
                            <i class="bi bi-bell"></i>
                        </div>

                        <div>
                            <span>Alertas</span>
                            <strong>{{ method_exists($alertas, 'total') ? $alertas->total() : $alertas->count() }}</strong>
                        </div>
                    </div>
                </div>
            @elseif($pagina === 'agenda')
                <div class="table-responsive">
                    <table class="table align-middle nexo-page-table">
                        <thead>
                            <tr>
                                <th>Compromisso</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($tarefasHoje as $tarefa)
                                <tr>
                                    <td>
                                        <strong>{{ $tarefa->titulo }}</strong>
                                    </td>

                                    <td>
                                        {{ optional($tarefa->vencimento)->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        <span class="nexo-status-pill">
                                            {{ ucfirst($tarefa->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="nexo-empty-state">
                                            <i class="bi bi-calendar-check"></i>
                                            <p>Nenhum compromisso para hoje.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $tarefasHoje->links('vendor.pagination.nexo') }}
            @elseif($pagina === 'clientes')
                @php
                    $whatsapp = app(\App\Services\WhatsAppLinkService::class);
                @endphp

                <div class="table-responsive">
                    <table class="table align-middle nexo-page-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Contato</th>
                                <th>Status</th>
                                <th class="text-center">Ação</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($clientes as $cliente)
                                <tr>
                                    <td>
                                        <div class="nexo-table-user">
                                            <span>{{ strtoupper(substr($cliente->nome, 0, 1)) }}</span>

                                            <strong>{{ $cliente->nome }}</strong>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="nexo-phone-actions">
                                            <span>{{ $cliente->telefone }}</span>

                                            @if($cliente->telefone)
                                                <a
                                                    href="{{ $whatsapp->buildClientLink($cliente->telefone) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="nexo-whatsapp-link"
                                                    title="Conversar no WhatsApp"
                                                    aria-label="Conversar no WhatsApp"
                                                >
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <span class="nexo-status-pill">
                                            {{ ucfirst($cliente->status) }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <a class="nexo-action-btn" href="{{ route('clientes.show', $cliente) }}">
                                            Detalhes
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="nexo-empty-state">
                                            <i class="bi bi-person-check"></i>
                                            <p>Nenhum cliente ativo ainda.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $clientes->links('vendor.pagination.nexo') }}
            @elseif($pagina === 'carteira')
                @php
                    $moeda = fn ($valor) => 'R$ '.number_format((float) ($valor ?? 0), 2, ',', '.');
                    $moedaInput = fn ($valor) => $valor !== null ? 'R$ '.number_format((float) $valor, 2, ',', '.') : '';
                    $classeComparacao = fn ($comparacao) => [
                        'positivo' => 'nexo-trend-positive',
                        'negativo' => 'nexo-trend-negative',
                        'neutro' => 'nexo-trend-neutral',
                    ][$comparacao['direcao'] ?? 'neutro'] ?? 'nexo-trend-neutral';
                    $iconeComparacao = fn ($comparacao) => [
                        'positivo' => 'bi-arrow-up-right',
                        'negativo' => 'bi-arrow-down-right',
                        'neutro' => 'bi-dash-lg',
                    ][$comparacao['direcao'] ?? 'neutro'] ?? 'bi-dash-lg';
                    $percentualMetaVisual = min(100, max(0, (float) $percentualMetaMesAtual));
                    $metaDefinida = $metaMesAtual !== null && (float) $metaMesAtual > 0;
                    $metaBatida = $metaDefinida && (float) $percentualMetaMesAtual >= 100;
                    $faltanteMeta = $metaDefinida ? max(0, (float) $metaMesAtual - (float) $comissaoMesAtual) : 0;

                    $cardsPerformance = [
                        [
                            'icone' => 'bi-person-plus',
                            'label' => 'Leads no mês',
                            'valor' => $leadsMesAtual,
                            'comparacao' => $comparacaoLeads,
                        ],
                        [
                            'icone' => 'bi-file-earmark-check',
                            'label' => 'Contratos fechados',
                            'valor' => $contratosFechadosMesAtual,
                            'comparacao' => $comparacaoContratos,
                        ],
                        [
                            'icone' => 'bi-people',
                            'label' => 'Vidas vendidas',
                            'valor' => $vidasVendidasMesAtual,
                            'comparacao' => $comparacaoVidas,
                        ],
                        [
                            'icone' => 'bi-percent',
                            'label' => 'Conversão',
                            'valor' => number_format((float) $taxaConversaoMesAtual, 1, ',', '.').'%',
                            'comparacao' => $comparacaoConversao,
                        ],
                    ];
                @endphp

                <section class="nexo-carteira-hero">
                    <div>
                        <span class="nexo-carteira-eyebrow">
                            <i class="bi bi-graph-up-arrow"></i>
                            Inteligência comercial
                        </span>

                        <h2>Carteira estratégica</h2>

                        <p>
                            Acompanhe resultado mensal, evolução da meta, comissão acumulada, conversão e força real da sua carteira.
                        </p>
                    </div>

                    <div class="nexo-carteira-hero-result {{ $metaBatida ? 'is-success' : '' }}">
                        <span>{{ $metaBatida ? 'Meta batida' : 'Progresso da meta' }}</span>
                        <strong>{{ number_format((float) $percentualMetaMesAtual, 1, ',', '.') }}%</strong>
                        <small>
                            {{ $metaBatida ? 'Excelente mês comercial.' : ($metaDefinida ? 'Faltam '.$moeda($faltanteMeta).' para bater a meta.' : 'Defina uma meta para acompanhar.') }}
                        </small>
                    </div>
                </section>

                <section class="nexo-carteira-dashboard">
                    <div class="nexo-carteira-main-metric">
                        <span>Comissão realizada no mês</span>
                        <strong>{{ $moeda($comissaoMesAtual) }}</strong>

                        <small class="{{ $classeComparacao($comparacaoComissao) }}">
                            <i class="bi {{ $iconeComparacao($comparacaoComissao) }}"></i>
                            {{ $comparacaoComissao['texto'] }}
                        </small>
                    </div>

                    <div class="nexo-carteira-mini-metrics">
                        @foreach($cardsPerformance as $card)
                            <article class="nexo-carteira-metric-card">
                                <div class="nexo-carteira-metric-icon">
                                    <i class="bi {{ $card['icone'] }}"></i>
                                </div>

                                <div>
                                    <span>{{ $card['label'] }}</span>
                                    <strong>{{ $card['valor'] }}</strong>

                                    <small class="{{ $classeComparacao($card['comparacao']) }}">
                                        <i class="bi {{ $iconeComparacao($card['comparacao']) }}"></i>
                                        {{ $card['comparacao']['texto'] }}
                                    </small>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="nexo-carteira-goal-card" data-carteira-goal-card>
                    <div class="nexo-carteira-goal-main">
                        <div class="nexo-carteira-section-header mb-3">
                            <div>
                                <span>Meta comercial</span>
                                <h2>Meta mensal de comissão</h2>
                            </div>

                            <strong>{{ now()->format('m/Y') }}</strong>
                        </div>

                        <div class="nexo-carteira-goal-values">
                            <div>
                                <span>Atual</span>
                                <strong>{{ $moeda($comissaoMesAtual) }}</strong>
                            </div>

                            <div>
                                <span>Meta</span>
                                <strong>{{ $metaDefinida ? $moeda($metaMesAtual) : 'Sem meta' }}</strong>
                            </div>

                            <div>
                                <span>Status</span>
                                <strong>{{ $metaBatida ? 'Conquistada' : number_format((float) $percentualMetaMesAtual, 1, ',', '.').'%' }}</strong>
                            </div>
                        </div>

                        <div class="nexo-goal-progress" aria-label="Progresso da meta mensal">
                            <div style="width: {{ $percentualMetaVisual }}%"></div>
                        </div>

                        <p class="nexo-goal-empty">
                            {{ $metaBatida ? 'Parabéns! Você superou sua meta mensal.' : ($metaDefinida ? 'Continue registrando suas comissões para acompanhar a evolução.' : 'Defina sua meta mensal para acompanhar sua evolução.') }}
                        </p>
                    </div>

                    <div class="nexo-carteira-goal-forms">
                        <form method="post" action="{{ route('carteira.meta-mensal.store') }}" class="nexo-carteira-goal-form" data-carteira-form>
                            @csrf
                            <input type="hidden" name="tipo_acao" value="salvar_meta">

                            <div>
                                <label class="form-label">Meta de comissão do mês</label>
                                <input
                                    class="form-control"
                                    name="meta_comissao"
                                    value="{{ old('tipo_acao') === 'salvar_meta' ? old('meta_comissao') : $moedaInput($metaMesAtual) }}"
                                    placeholder="R$ 0,00"
                                    inputmode="numeric"
                                    data-money-input
                                >

                                @error('meta_comissao')
                                    <div class="text-danger small fw-semibold mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="nexo-action-btn nexo-carteira-save-btn">
                                <i class="bi bi-bullseye"></i>
                                Salvar meta
                            </button>
                        </form>

                        <form method="post" action="{{ route('carteira.meta-mensal.store') }}" class="nexo-carteira-goal-form" data-carteira-form>
                            @csrf
                            <input type="hidden" name="tipo_acao" value="adicionar_comissao">

                            <div>
                                <label class="form-label">Adicionar comissão de venda</label>
                                <input
                                    class="form-control"
                                    name="comissao_lancamento"
                                    value="{{ old('tipo_acao') === 'adicionar_comissao' ? old('comissao_lancamento') : '' }}"
                                    placeholder="R$ 0,00"
                                    inputmode="numeric"
                                    data-money-input
                                >
                                <p class="nexo-goal-helper">Informe a comissão desta venda. O valor será somado ao acumulado do mês.</p>

                                @error('comissao_lancamento')
                                    <div class="text-danger small fw-semibold mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="nexo-action-btn nexo-carteira-save-btn">
                                <i class="bi bi-plus-circle"></i>
                                Adicionar comissão
                            </button>
                        </form>
                    </div>
                </section>

                <section class="nexo-carteira-analysis nexo-analysis-{{ $analiseCarteira['tipo'] }}">
                    <div class="nexo-carteira-analysis-icon">
                        <i class="bi {{ $analiseCarteira['icone'] }}"></i>
                    </div>

                    <div>
                        <span>Análise do mês</span>
                        <strong>{{ $analiseCarteira['titulo'] }}</strong>
                        <p>{{ $analiseCarteira['descricao'] }}</p>
                    </div>
                </section>

                <section class="nexo-carteira-operational-grid">
                    <article>
                        <i class="bi bi-person-check"></i>
                        <span>Clientes ativos</span>
                        <strong>{{ $clientesAtivosCarteira }}</strong>
                    </article>

                    <article>
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Renovações próximas</span>
                        <strong>{{ $metricasCarteira['renovacoes_proximas'] ?? 0 }}</strong>
                    </article>

                    <article>
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Reajustes próximos</span>
                        <strong>{{ $metricasCarteira['reajustes_proximos'] ?? 0 }}</strong>
                    </article>

                    <article>
                        <i class="bi bi-people"></i>
                        <span>Dependentes</span>
                        <strong>{{ $metricasCarteira['dependentes'] ?? 0 }}</strong>
                    </article>
                </section>

                <section class="nexo-carteira-section">
                    <div class="nexo-carteira-section-header">
                        <div>
                            <span>Carteira ativa</span>
                            <h2>Clientes e contratos vigentes</h2>
                        </div>

                        <strong>{{ $clientesAtivosCarteira }} clientes ativos</strong>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle nexo-page-table nexo-carteira-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Contratos</th>
                                    <th>Vidas</th>
                                    <th>Próxima renovação</th>
                                    <th>Próximo reajuste</th>
                                    <th>Status</th>
                                        
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($clientes as $cliente)
                                    @php
                                        $contratosAtivos = $cliente->contratos->whereIn('status', ['ativo', 'vigente']);
                                        $vidasContrato = (int) $contratosAtivos->sum('quantidade_vidas');
                                        $vidasCarteira = $vidasContrato > 0 ? $vidasContrato : $cliente->dependentes->count() + 1;
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="nexo-table-user">
                                                <span>{{ strtoupper(substr($cliente->nome, 0, 1)) }}</span>

                                                <div>
                                                    <strong>{{ $cliente->nome }}</strong>
                                                    <small>{{ $cliente->telefone }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ $contratosAtivos->count() }}</td>

                                        <td>{{ $vidasCarteira }}</td>

                                        <td>{{ $cliente->contratos->pluck('renovacao_em')->filter()->sort()->first()?->format('d/m/Y') ?: 'Sem data' }}</td>

                                        <td>{{ $cliente->contratos->pluck('reajuste_em')->filter()->sort()->first()?->format('d/m/Y') ?: 'Sem data' }}</td>

                                        <td>
                                            <span class="nexo-status-pill">
                                                {{ ucfirst($cliente->status) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <a class="nexo-action-btn" href="{{ route('clientes.show', $cliente) }}">
                                                Abrir
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="nexo-empty-state">
                                                <i class="bi bi-briefcase"></i>
                                                <p>Nenhum cliente ativo na carteira.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $clientes->links('vendor.pagination.nexo') }}
                </section>
            @elseif($pagina === 'tarefas')
                <div class="table-responsive">
                    <table class="table align-middle nexo-page-table">
                        <thead>
                            <tr>
                                <th>Tarefa</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($tarefas as $tarefa)
                                <tr>
                                    <td>
                                        <strong>{{ $tarefa->titulo }}</strong>
                                    </td>

                                    <td>
                                        {{ optional($tarefa->vencimento)->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        <span class="nexo-status-pill">
                                            {{ ucfirst($tarefa->status) }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        @if($tarefa->status !== 'concluida')
                                            <form method="post" action="{{ route('tarefas.concluir', $tarefa) }}">
                                                @csrf

                                                <button class="nexo-action-btn">
                                                    Concluir
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="nexo-empty-state">
                                            <i class="bi bi-check2-square"></i>
                                            <p>Nenhuma tarefa pendente.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $tarefas->links('vendor.pagination.nexo') }}
            @elseif($pagina === 'alertas')
                <div class="table-responsive">
                    <table class="table align-middle nexo-page-table">
                        <thead>
                            <tr>
                                <th>Alerta</th>
                                <th>Mensagem</th>
                                <th>Tipo</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($alertas as $alerta)
                                <tr>
                                    <td>
                                        <strong>{{ $alerta->titulo }}</strong>
                                    </td>

                                    <td>
                                        {{ $alerta->mensagem }}
                                    </td>

                                    <td>
                                        <span class="nexo-status-pill">
                                            {{ ucfirst($alerta->tipo) }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a class="nexo-action-btn" href="{{ route('alertas.abrir', $alerta) }}">
                                                Abrir
                                            </a>
                                            @if(! $alerta->lido)
                                                <form method="post" action="{{ route('alertas.resolver', $alerta) }}">
                                                    @csrf

                                                    <button class="nexo-action-btn">
                                                        Resolver
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="nexo-empty-state">
                                            <i class="bi bi-bell"></i>
                                            <p>Nenhum alerta no momento.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $alertas->links('vendor.pagination.nexo') }}
            @else
                <div class="table-responsive">
                    <table class="table align-middle nexo-page-table">
                        <thead>
                            <tr>
                                <th>
                                    @if($pagina === 'pre-cadastros')
                                        Pré-cadastro
                                    @elseif($pagina === 'implantacoes')
                                        Implantação
                                    @else
                                        Registro
                                    @endif
                                </th>
                                <th>Plano</th>
                                <th>Vidas</th>
                                <th>Etapa</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($indicacoes as $indicacao)
                                <tr>
                                    <td>
                                        <div class="nexo-table-user">
                                            <span>{{ strtoupper(substr($indicacao->nome_cliente, 0, 1)) }}</span>

                                            <strong>{{ $indicacao->nome_cliente }}</strong>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="nexo-plan-chip">
                                            <i class="bi bi-shield-check"></i>
                                            {{ $indicacao->tipo_plano }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="nexo-lives-chip">
                                            {{ $indicacao->quantidade_vidas }} vida(s)
                                        </span>
                                    </td>

                                    <td>
                                        <span class="nexo-status-pill">
                                            {{ str_replace('_', ' ', $indicacao->status) }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        @if($pagina === 'propostas')
                                            <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                                <a class="nexo-action-btn" href="{{ route('propostas.show', $indicacao) }}">
                                                    Adicionar proposta
                                                </a>

                                                <a class="nexo-action-btn" href="{{ route('pre-cadastros.create', $indicacao) }}">
                                                    Gerar link de pré-cadastro
                                                </a>
                                            </div>
                                        @elseif($pagina === 'pre-cadastros' && $indicacao->preCadastro)
                                            <a class="nexo-action-btn" href="{{ route('pre-cadastros.show', $indicacao->preCadastro) }}">
                                                Abrir pré-cadastro
                                            </a>
                                        @elseif($pagina === 'implantacoes' && $indicacao->implantacao)
                                            <a class="nexo-action-btn" href="{{ route('implantacoes.show', $indicacao->implantacao) }}">
                                                Abrir implantação
                                            </a>
                                        @else
                                            <a class="nexo-action-btn" href="{{ route('indicacoes.show', $indicacao) }}">
                                                Abrir
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="nexo-empty-state">
                                            <i class="bi bi-inbox"></i>
                                            <p>Nenhum registro nesta etapa.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $indicacoes->links('vendor.pagination.nexo') }}
            @endif
        </div>
    </main>

    <style>
        .nexo-page-table th:last-child {
    text-align: center !important;
}

.nexo-page-table td:last-child {
    text-align: center !important;
}

.nexo-page-table td:last-child > .d-inline-flex {
    justify-content: center !important;
}
        .nexo-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .nexo-page-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.78rem;
            font-weight: 900;
            padding: 6px 11px;
            margin-bottom: 10px;
        }

        .nexo-page-header h1 {
            color: #061C3F;
            font-size: 2.25rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.045em;
            margin: 0 0 8px;
        }

        .nexo-page-header p {
            color: #64748B;
            margin: 0;
            font-size: 1rem;
        }

        .nexo-floating-toast {
            position: fixed;
            top: 28px;
            right: 28px;
            z-index: 99999;
            width: min(520px, calc(100vw - 32px));
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 22px;
            border-radius: 26px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background:
                linear-gradient(135deg, rgba(6, 28, 63, 0.98) 0%, rgba(15, 58, 104, 0.98) 100%);
            backdrop-filter: blur(18px);
            box-shadow:
                0 28px 70px rgba(6, 28, 63, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            animation: nexoToastIn 0.45s cubic-bezier(.19, 1, .22, 1);
        }

        .nexo-floating-toast::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(91, 167, 255, 0.24), transparent 34%);
            pointer-events: none;
        }

        .nexo-floating-toast-icon {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 20px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(135deg, #16A34A 0%, #22C55E 100%);
            color: #FFFFFF;
            font-size: 1.55rem;
            box-shadow:
                0 12px 28px rgba(34, 197, 94, 0.32),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }

        .nexo-floating-toast-content {
            position: relative;
            flex: 1;
            min-width: 0;
        }

        .nexo-floating-toast-content strong {
            display: block;
            color: #FFFFFF;
            font-size: 0.95rem;
            font-weight: 950;
            letter-spacing: -0.02em;
            margin-bottom: 3px;
        }

        .nexo-floating-toast-content span {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .nexo-floating-toast-close {
            position: relative;
            width: 44px;
            height: 44px;
            border: none;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.72);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-floating-toast-close:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #FFFFFF;
        }

        .nexo-floating-toast-progress {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 5px;
            width: 100%;
            background:
                linear-gradient(90deg, #5BA7FF 0%, #22C55E 100%);
            transform-origin: left;
            animation: nexoToastProgress 4.5s linear forwards;
        }

        .nexo-page-panel {
            border-radius: 28px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.05);
            padding: 28px;
        }

        .nexo-carteira-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 24px;
            align-items: end;
            margin-bottom: 24px;
            padding: 30px;
            border-radius: 30px;
            color: #FFFFFF;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.34), transparent 34%),
                linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            box-shadow: 0 28px 70px rgba(6, 28, 63, 0.16);
        }

        .nexo-carteira-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            color: #DDEBFF;
            font-size: 0.78rem;
            font-weight: 950;
            margin-bottom: 14px;
        }

        .nexo-carteira-hero h2 {
            color: #FFFFFF;
            font-size: clamp(2.2rem, 4vw, 3.8rem);
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.065em;
            margin: 0 0 14px;
        }

        .nexo-carteira-hero p {
            max-width: 720px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 1rem;
            line-height: 1.55;
            margin: 0;
        }

        .nexo-carteira-hero-result {
            padding: 22px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
        }

        .nexo-carteira-hero-result.is-success {
            background: rgba(22, 163, 74, 0.18);
            border-color: rgba(187, 247, 208, 0.32);
        }

        .nexo-carteira-hero-result span,
        .nexo-carteira-hero-result small {
            display: block;
            color: rgba(255, 255, 255, 0.76);
            font-weight: 850;
        }

        .nexo-carteira-hero-result strong {
            display: block;
            color: #FFFFFF;
            font-size: 2.6rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.06em;
            margin: 8px 0;
        }

        .nexo-carteira-dashboard {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 18px;
            margin-bottom: 24px;
        }

        .nexo-carteira-main-metric {
            padding: 24px;
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.12), transparent 32%),
                linear-gradient(180deg, #FFFFFF 0%, #F8FBFF 100%);
            border: 1px solid #DCEBFF;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.05);
        }

        .nexo-carteira-main-metric > span {
            display: block;
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .nexo-carteira-main-metric > strong {
            display: block;
            color: #061C3F;
            font-size: 2.6rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.06em;
            margin-bottom: 14px;
        }

        .nexo-carteira-mini-metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .nexo-carteira-metric-card {
            min-height: 150px;
            padding: 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFF 100%);
            border: 1px solid #E4EBF5;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.045);
        }

        .nexo-carteira-metric-icon {
            width: 44px;
            height: 44px;
            border-radius: 15px;
            background: #EAF3FF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.12rem;
            margin-bottom: 14px;
        }

        .nexo-carteira-metric-card span,
        .nexo-carteira-goal-values span,
        .nexo-carteira-analysis span {
            display: block;
            color: #64748B;
            font-size: 0.82rem;
            font-weight: 850;
            margin-bottom: 5px;
        }

        .nexo-carteira-metric-card strong {
            display: block;
            color: #061C3F;
            font-size: 1.42rem;
            line-height: 1.05;
            font-weight: 950;
            letter-spacing: -0.045em;
            margin-bottom: 10px;
        }

        .nexo-carteira-metric-card small,
        .nexo-carteira-main-metric small {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-height: 28px;
            padding: 0 9px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 950;
            white-space: nowrap;
        }

        .nexo-trend-positive {
            background: #EAFBF1;
            color: #16834F;
        }

        .nexo-trend-negative {
            background: #FFF4E5;
            color: #B45309;
        }

        .nexo-trend-neutral {
            background: #F1F5F9;
            color: #64748B;
        }

        .nexo-carteira-section {
            margin-bottom: 24px;
        }

        .nexo-carteira-section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }

        .nexo-carteira-section-header span {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.74rem;
            font-weight: 950;
            margin-bottom: 8px;
        }

        .nexo-carteira-section-header h2 {
            color: #061C3F;
            font-size: 1.35rem;
            font-weight: 950;
            letter-spacing: -0.035em;
            margin: 0;
        }

        .nexo-carteira-section-header > strong {
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 900;
            white-space: nowrap;
        }

        .nexo-carteira-goal-card {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
            gap: 22px;
            margin-bottom: 24px;
            padding: 24px;
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.10), transparent 30%),
                #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.05);
        }

        .nexo-carteira-goal-values {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .nexo-carteira-goal-values > div {
            padding: 16px;
            border-radius: 18px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-carteira-goal-values strong {
            display: block;
            color: #061C3F;
            font-size: 1.25rem;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .nexo-goal-progress {
            width: 100%;
            height: 16px;
            overflow: hidden;
            border-radius: 999px;
            background: #E8EEF6;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.05);
        }

        .nexo-goal-progress > div {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #2F80ED 0%, #5BA7FF 55%, #16A34A 100%);
            box-shadow: 0 8px 20px rgba(47, 128, 237, 0.24);
            transition: width 0.7s ease;
        }

        .nexo-goal-empty {
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 700;
            margin: 12px 0 0;
        }

        .nexo-carteira-goal-forms {
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .nexo-carteira-goal-form {
            display: grid;
            gap: 14px;
            align-content: start;
            padding: 18px;
            border-radius: 22px;
            background: #F8FBFF;
            border: 1px solid #DCEBFF;
        }

        .nexo-carteira-goal-form .form-label {
            color: #061C3F;
            font-size: 0.86rem;
            font-weight: 900;
            margin-bottom: 7px;
        }

        .nexo-carteira-goal-form .form-control {
            min-height: 48px;
            border-radius: 14px;
            border-color: #D8E2EF;
            color: #061C3F;
            font-weight: 850;
        }

        .nexo-carteira-goal-form .form-control:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-goal-helper {
            margin: 8px 0 0;
            color: #64748B;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.45;
        }

        .nexo-carteira-save-btn {
            width: 100%;
            min-height: 46px;
            background: #2F80ED;
            border-color: #2F80ED;
            color: #FFFFFF;
            box-shadow: 0 14px 28px rgba(47, 128, 237, 0.18);
        }

        .nexo-carteira-save-btn:hover {
            background: #1B6DFF;
            border-color: #1B6DFF;
            color: #FFFFFF;
        }

        .nexo-carteira-analysis {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
            padding: 20px;
            border-radius: 24px;
            border: 1px solid #DCEBFF;
            background: #F8FBFF;
        }

        .nexo-carteira-analysis-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.2rem;
            background: #EAF3FF;
            color: #2F80ED;
        }

        .nexo-carteira-analysis strong {
            display: block;
            color: #061C3F;
            font-size: 1rem;
            font-weight: 950;
            margin-bottom: 4px;
        }

        .nexo-carteira-analysis p {
            color: #64748B;
            font-weight: 700;
            margin: 0;
        }

        .nexo-carteira-operational-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .nexo-carteira-operational-grid article {
            display: grid;
            gap: 5px;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid #E6EEF9;
            background: #F8FBFF;
        }

        .nexo-carteira-operational-grid i {
            color: #2F80ED;
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .nexo-carteira-operational-grid span {
            color: #64748B;
            font-size: 0.82rem;
            font-weight: 850;
        }

        .nexo-carteira-operational-grid strong {
            color: #061C3F;
            font-size: 1.5rem;
            font-weight: 950;
            line-height: 1;
        }

        .nexo-analysis-positivo {
            border-color: #C7F0D9;
            background: #F2FFF7;
        }

        .nexo-analysis-positivo .nexo-carteira-analysis-icon {
            background: #EAFBF1;
            color: #16834F;
        }

        .nexo-analysis-atencao {
            border-color: #FFE2B8;
            background: #FFF9EF;
        }

        .nexo-analysis-atencao .nexo-carteira-analysis-icon {
            background: #FFF4E5;
            color: #B45309;
        }

        .nexo-carteira-table tbody td {
            padding-top: 16px;
            padding-bottom: 16px;
        }

        .nexo-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .nexo-summary-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 16px;
            min-height: 112px;
            padding: 22px;
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .nexo-summary-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: linear-gradient(135deg, #EAF3FF 0%, #DCEEFF 100%);
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .nexo-summary-card span {
            display: block;
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-summary-card strong {
            display: block;
            color: #061C3F;
            font-size: 2rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.05em;
        }

        .nexo-page-table thead th {
            color: #64748B;
            font-size: 0.8rem;
            font-weight: 900;
            text-transform: uppercase;
            border-color: #E8EEF6;
            padding-bottom: 16px;
        }

        .nexo-page-table tbody td {
            padding-top: 18px;
            padding-bottom: 18px;
            border-color: #EDF2F7;
            vertical-align: middle;
        }

        .nexo-table-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nexo-table-user > span {
            width: 44px;
            height: 44px;
            border-radius: 15px;
            background: linear-gradient(135deg, #2F80ED 0%, #5BA7FF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            flex-shrink: 0;
        }

        .nexo-table-user strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
        }

        .nexo-table-user small {
            display: block;
            color: #64748B;
            font-size: 0.84rem;
        }

        .nexo-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.82rem;
            font-weight: 900;
            text-transform: capitalize;
        }

        .nexo-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1px solid #D7E7FF;
            background: #FFFFFF;
            color: #2F80ED;
            font-size: 0.86rem;
            font-weight: 900;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .nexo-action-btn:hover {
            background: #2F80ED;
            border-color: #2F80ED;
            color: #FFFFFF;
        }

        button.nexo-action-btn {
            cursor: pointer;
        }

        .nexo-empty-state {
            display: flex;
            min-height: 180px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #64748B;
        }

        .nexo-empty-state i {
            color: #2F80ED;
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        .nexo-empty-state p {
            margin: 0;
            font-weight: 700;
        }

        .nexo-celebration-banner {
            position: fixed;
            left: 50%;
            top: 28px;
            z-index: 10000;
            transform: translateX(-50%);
            padding: 14px 20px;
            border-radius: 999px;
            background: linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 950;
            box-shadow: 0 20px 50px rgba(6, 28, 63, 0.24);
            animation: nexoCelebrationPop 0.45s ease both;
        }

        .nexo-confetti-piece {
            position: fixed;
            z-index: 9999;
            pointer-events: none;
            animation: nexoConfettiFall cubic-bezier(.19, .72, .31, 1) forwards;
        }

        .nexo-confetti-piece.is-star {
            clip-path: polygon(50% 0%, 61% 36%, 98% 36%, 68% 57%, 79% 92%, 50% 70%, 21% 92%, 32% 57%, 2% 36%, 39% 36%);
        }

        .nexo-confetti-piece.is-ribbon {
            border-radius: 3px;
        }

        @keyframes nexoToastIn {
            from {
                opacity: 0;
                transform: translateY(-16px) scale(0.94);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes nexoToastOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateY(-12px) scale(0.94);
            }
        }

        @keyframes nexoToastProgress {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        @keyframes nexoCelebrationPop {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-12px) scale(0.92);
            }

            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0) scale(1);
            }
        }

        @keyframes nexoConfettiFall {
            0% {
                transform: translate3d(0, 0, 0) rotate(0deg) scale(1);
                opacity: 1;
            }

            70% {
                opacity: 1;
            }

            100% {
                transform: translate3d(var(--nexo-confetti-drift), 110vh, 0) rotate(var(--nexo-confetti-rotation)) scale(0.88);
                opacity: 0;
            }
        }

        @media (max-width: 1200px) {
            .nexo-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .nexo-carteira-dashboard {
                grid-template-columns: 1fr;
            }

            .nexo-carteira-mini-metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .nexo-carteira-goal-card {
                grid-template-columns: 1fr;
            }

            .nexo-carteira-operational-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .nexo-page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-summary-grid {
                grid-template-columns: 1fr;
            }

            .nexo-page-panel {
                padding: 20px;
            }

            .nexo-carteira-hero {
                grid-template-columns: 1fr;
                padding: 24px;
            }

            .nexo-carteira-hero-result {
                padding: 18px;
            }

            .nexo-carteira-mini-metrics,
            .nexo-carteira-goal-values {
                grid-template-columns: 1fr;
            }

            .nexo-carteira-section-header {
                flex-direction: column;
            }

            .nexo-carteira-operational-grid {
                grid-template-columns: 1fr;
            }

            .nexo-floating-toast {
                top: 16px;
                right: 16px;
                left: 16px;
                width: auto;
                border-radius: 20px;
            }
        }
    </style>

    @if($pagina === 'carteira')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const formatarMoeda = (valor) => {
                    const digitos = valor.replace(/\D/g, '');
                    const centavos = parseInt(digitos || '0', 10);

                    return (centavos / 100).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                    });
                };

                document.querySelectorAll('[data-money-input]').forEach((input) => {
                    if (input.value.trim() !== '') {
                        input.value = formatarMoeda(input.value);
                    }

                    input.addEventListener('input', () => {
                        input.value = formatarMoeda(input.value);
                    });
                });

                document.querySelectorAll('[data-carteira-form]').forEach((form) => {
                    form.addEventListener('submit', () => {
                        sessionStorage.setItem('nexoCarteiraScrollY', String(window.scrollY));
                    });
                });

                const scrollSalvo = sessionStorage.getItem('nexoCarteiraScrollY');

                if (scrollSalvo !== null) {
                    window.requestAnimationFrame(() => {
                        window.scrollTo({
                            top: Number(scrollSalvo),
                            left: 0,
                            behavior: 'auto',
                        });

                        sessionStorage.removeItem('nexoCarteiraScrollY');
                    });
                }

                const metaAtingida = @json((bool) session('meta_atingida'));

                const dispararConfetePremium = () => {
                    const cores = ['#2F80ED', '#1B6DFF', '#0F3A68', '#16A34A', '#7DB5FF', '#FFFFFF', '#BFD8FF'];
                    const formatos = ['is-star', 'is-ribbon', 'is-ribbon'];
                    const quantidade = 190;

                    const banner = document.createElement('div');

                    banner.className = 'nexo-celebration-banner';
                    banner.innerHTML = '<i class="bi bi-stars"></i> Meta batida! Excelente resultado comercial.';

                    document.body.appendChild(banner);

                    window.setTimeout(() => {
                        banner.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        banner.style.opacity = '0';
                        banner.style.transform = 'translateX(-50%) translateY(-10px) scale(0.96)';

                        window.setTimeout(() => banner.remove(), 320);
                    }, 3600);

                    for (let i = 0; i < quantidade; i += 1) {
                        const confete = document.createElement('span');
                        const tamanho = Math.floor(Math.random() * 9) + 6;
                        const origemLateral = i % 3 === 0;
                        const esquerda = origemLateral
                            ? (Math.random() > 0.5 ? -4 : 104)
                            : Math.random() * 100;

                        confete.className = `nexo-confetti-piece ${formatos[Math.floor(Math.random() * formatos.length)]}`;
                        confete.style.left = `${esquerda}vw`;
                        confete.style.top = `${origemLateral ? Math.random() * 38 + 4 : -24}px`;
                        confete.style.width = `${tamanho}px`;
                        confete.style.height = `${Math.max(5, tamanho - 2)}px`;
                        confete.style.background = cores[Math.floor(Math.random() * cores.length)];
                        confete.style.animationDuration = `${Math.random() * 1.8 + 2.9}s`;
                        confete.style.animationDelay = `${Math.random() * 0.55}s`;
                        confete.style.setProperty('--nexo-confetti-drift', `${Math.random() * 340 - 170}px`);
                        confete.style.setProperty('--nexo-confetti-rotation', `${Math.random() * 1080 + 540}deg`);

                        document.body.appendChild(confete);

                        window.setTimeout(() => confete.remove(), 5600);
                    }
                };

                if (metaAtingida) {
                    dispararConfetePremium();
                }
            });
        </script>
    @endif
</x-layouts.app>
