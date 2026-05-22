<x-layouts.app title="Dashboard | Nexo Saúde">
    @php
        $statusLegiveis = [
            'nova' => 'Nova',
            'proposta_enviada' => 'Proposta enviada',
            'aguardando_envio' => 'Aguardando envio',
            'documentacao_pendente' => 'Documentação pendente',
            'documentacao_em_analise' => 'Documentação em análise',
            'documentacao_aprovada' => 'Documentação aprovada',
            'correcao_solicitada' => 'Correção solicitada',
            'contrato_em_analise' => 'Contrato em análise',
            'pendencia_na_operadora' => 'Pendência na operadora',
            'aguardando_vigencia' => 'Aguardando vigência',
            'contrato_vigente' => 'Contrato vigente',
            'contrato_recusado' => 'Contrato recusado',
        ];

        $iconesTotais = [
            'leads' => 'bi-people',
            'propostas' => 'bi-file-earmark-text',
            'pre-cadastros' => 'bi-clipboard2-check',
            'implantacoes' => 'bi-rocket-takeoff',
            'clientes' => 'bi-person-lines-fill',
            'carteira' => 'bi-briefcase',
        ];

        $iconesOperacao = [
            'retornos' => 'bi-telephone-outbound',
            'documentos' => 'bi-file-earmark-check',
            'alertas' => 'bi-bell',
            'tarefas' => 'bi-check2-square',
            'aniversarios' => 'bi-gift',
            'renovacoes' => 'bi-arrow-repeat',
        ];
    @endphp

    <main class="nexo-main">
        <div class="nexo-dashboard-header mb-4">
            <div>
                <span class="nexo-page-label">
                    Visão operacional
                </span>

                <h1>
                    Dashboard
                </h1>

                <p>
                    Acompanhe Leads, pré-cadastros, implantações e carteira em uma visão rápida.
                </p>
            </div>

            <a
                href="{{ route('publico.corretor', auth()->user()->corretorPerfil->slug) }}"
                class="nexo-dashboard-public-link"
            >
                <i class="bi bi-box-arrow-up-right"></i>
                Abrir página pública
            </a>
        </div>

        <div class="nexo-dashboard-grid mb-4">
            @foreach($totais as $label => $total)
                <div class="nexo-dashboard-metric">
                    <div class="nexo-dashboard-metric-icon">
                        <i class="bi {{ $iconesTotais[$label] ?? 'bi-bar-chart' }}"></i>
                    </div>

                    <div class="nexo-dashboard-metric-content">
                        <span>
                            {{ ucfirst(str_replace('-', ' ', $label)) }}
                        </span>

                        <strong>
                            {{ $total }}
                        </strong>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="nexo-dashboard-section-title mb-3">
            <div>
                <h2>
                    O que precisa de atenção hoje
                </h2>

                <p>
                    Pendências e atividades que merecem acompanhamento imediato.
                </p>
            </div>
        </div>

        <div class="nexo-dashboard-grid nexo-dashboard-attention-grid mb-4">
            @foreach($operacaoHoje as $label => $total)
                <div class="nexo-dashboard-attention">
                    <div class="nexo-dashboard-attention-icon">
                        <i class="bi {{ $iconesOperacao[$label] ?? 'bi-exclamation-circle' }}"></i>
                    </div>

                    <div>
                        <span>
                            {{ ucfirst(str_replace('_', ' ', $label)) }}
                        </span>

                        <strong>
                            {{ $total }}
                        </strong>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="nexo-dashboard-panel">
                    <div class="nexo-dashboard-panel-header">
                        <div>
                            <h2>
                                Leads recentes
                            </h2>

                            <p>
                                Últimas oportunidades recebidas e movimentadas.
                            </p>
                        </div>

                        <a href="{{ route('indicacoes.index') }}">
                            Ver todos
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle nexo-dashboard-table">
                            <thead>
                                <tr>
                                    <th>Lead</th>
                                    <th>Tipo</th>
                                    <th>Vidas</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($indicacoes as $indicacao)
                                    <tr>
                                        <td>
                                            <div class="nexo-dashboard-lead">
                                                <span>
                                                    {{ strtoupper(substr($indicacao->nome_cliente, 0, 1)) }}
                                                </span>

                                                <div>
                                                    <strong>
                                                        {{ $indicacao->nome_cliente }}
                                                    </strong>

                                                    <small>
                                                        {{ $indicacao->cidade }}/{{ $indicacao->estado }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            {{ $indicacao->tipo_plano }}
                                        </td>

                                        <td>
                                            {{ $indicacao->quantidade_vidas }}
                                        </td>

                                        <td>
                                            <span class="status-pill">
                                                {{ $statusLegiveis[$indicacao->status] ?? str_replace('_', ' ', $indicacao->status) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <a
                                                href="{{ route('indicacoes.show', $indicacao) }}"
                                                class="nexo-dashboard-open"
                                            >
                                                Abrir
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="nexo-dashboard-panel h-100">
                    <div class="nexo-dashboard-panel-header">
                        <div>
                            <h2>
                                Alertas
                            </h2>

                            <p>
                                Itens importantes da operação.
                            </p>
                        </div>
                    </div>

                    <div class="nexo-dashboard-alerts">
                        @forelse($alertas as $alerta)
                            <div class="nexo-dashboard-alert">
                                <div class="nexo-dashboard-alert-icon">
                                    <i class="bi bi-bell"></i>
                                </div>

                                <div>
                                    <strong>
                                        {{ $alerta->titulo }}
                                    </strong>

                                    <p>
                                        {{ $alerta->mensagem }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="nexo-dashboard-empty">
                                <i class="bi bi-check-circle"></i>

                                <p>
                                    Nenhum alerta pendente.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .nexo-dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .nexo-page-label {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.78rem;
            font-weight: 900;
            padding: 6px 11px;
            margin-bottom: 10px;
        }

        .nexo-dashboard-header h1 {
            color: #061C3F;
            font-size: 2.25rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.045em;
            margin: 0 0 8px;
        }

        .nexo-dashboard-header p {
            color: #64748B;
            margin: 0;
            font-size: 1rem;
        }

        .nexo-dashboard-public-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            border-radius: 14px;
            border: 1px solid #CFE2FF;
            background: #FFFFFF;
            color: #2F80ED;
            font-weight: 900;
            padding: 0 18px;
            text-decoration: none;
            box-shadow: 0 14px 32px rgba(47, 128, 237, 0.10);
            white-space: nowrap;
        }

        .nexo-dashboard-public-link:hover {
            background: #2F80ED;
            color: #FFFFFF;
        }

        .nexo-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .nexo-dashboard-metric {
            position: relative;
            display: flex;
            align-items: center;
            gap: 16px;
            min-height: 112px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.055);
            padding: 20px;
            overflow: hidden;
        }

        .nexo-dashboard-metric::after {
            content: "";
            position: absolute;
            width: 74px;
            height: 74px;
            right: -24px;
            top: -24px;
            border-radius: 999px;
            background: rgba(47, 128, 237, 0.08);
        }

        .nexo-dashboard-metric-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: linear-gradient(135deg, #EAF3FF 0%, #D8EBFF 100%);
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .nexo-dashboard-metric-content span {
            display: block;
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .nexo-dashboard-metric-content strong {
            display: block;
            color: #061C3F;
            font-size: 2.15rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.05em;
        }

        .nexo-dashboard-section-title h2 {
            color: #061C3F;
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            margin: 0 0 4px;
        }

        .nexo-dashboard-section-title p {
            color: #64748B;
            margin: 0;
        }

        .nexo-dashboard-attention {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 94px;
            padding: 18px;
            border-radius: 20px;
            background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFF 100%);
            border: 1px solid #E4EBF5;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.045);
        }

        .nexo-dashboard-attention-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: rgba(47, 128, 237, 0.12);
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.18rem;
            flex-shrink: 0;
        }

        .nexo-dashboard-attention span {
            display: block;
            color: #64748B;
            font-size: 0.86rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-dashboard-attention strong {
            color: #061C3F;
            font-size: 1.75rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .nexo-dashboard-panel {
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.055);
            padding: 26px;
        }

        .nexo-dashboard-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .nexo-dashboard-panel-header h2 {
            color: #061C3F;
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            margin: 0 0 5px;
        }

        .nexo-dashboard-panel-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-dashboard-panel-header a {
            color: #2F80ED;
            font-weight: 900;
            text-decoration: none;
            white-space: nowrap;
        }

        .nexo-dashboard-table thead th {
            color: #64748B;
            font-size: 0.82rem;
            font-weight: 900;
            text-transform: uppercase;
            border-color: #E5EAF0;
            padding-bottom: 14px;
        }

        .nexo-dashboard-table tbody td {
            padding-top: 16px;
            padding-bottom: 16px;
            border-color: #EDF2F7;
        }

        .nexo-dashboard-lead {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .nexo-dashboard-lead > span {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2F80ED, #5AA5FF);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            flex-shrink: 0;
        }

        .nexo-dashboard-lead strong {
            display: block;
            color: #162033;
            font-weight: 900;
        }

        .nexo-dashboard-lead small {
            color: #64748B;
        }

        .nexo-dashboard-open {
            color: #2F80ED;
            font-weight: 900;
            text-decoration: none;
        }

        .nexo-dashboard-alerts {
            display: grid;
            gap: 12px;
        }

        .nexo-dashboard-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border-radius: 18px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
            padding: 16px;
        }

        .nexo-dashboard-alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            background: rgba(47, 128, 237, 0.12);
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .nexo-dashboard-alert strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .nexo-dashboard-alert p {
            color: #64748B;
            margin: 0;
            font-size: 0.92rem;
        }

        .nexo-dashboard-empty {
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #64748B;
            text-align: center;
        }

        .nexo-dashboard-empty i {
            color: #2F80ED;
            font-size: 2.4rem;
            margin-bottom: 12px;
        }

        .nexo-dashboard-empty p {
            margin: 0;
            font-weight: 700;
        }

        @media (max-width: 1200px) {
            .nexo-dashboard-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .nexo-dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-dashboard-grid {
                grid-template-columns: 1fr;
            }

            .nexo-dashboard-public-link {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</x-layouts.app>