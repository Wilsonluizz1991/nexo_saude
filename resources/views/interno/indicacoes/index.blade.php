<x-layouts.app title="Leads | Nexo Saúde">
    @php
        $whatsapp = app(\App\Services\WhatsAppLinkService::class);

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

        $statusCores = [
            'nova' => 'primary',
            'proposta_enviada' => 'warning',
            'documentacao_pendente' => 'danger',
            'documentacao_em_analise' => 'info',
            'documentacao_aprovada' => 'success',
            'correcao_solicitada' => 'warning',
            'contrato_vigente' => 'success',
        ];
    @endphp

    <main class="nexo-main">
        <div class="nexo-leads-hero mb-4">
            <div class="nexo-leads-hero-content">
                <div>
                    <span class="nexo-page-label">
                        <i class="bi bi-lightning-charge"></i>
                        Operação
                    </span>

                    <h1>Leads</h1>

                    <p>Acompanhe apenas os registros que ainda estão na primeira etapa do funil.</p>
                </div>

                <div class="nexo-leads-hero-metrics">
                    <div class="nexo-leads-hero-metric">
                        <i class="bi bi-people"></i>

                        <div>
                            <strong>{{ $indicacoes->total() }}</strong>
                            <span>Total</span>
                        </div>
                    </div>

                    <div class="nexo-leads-hero-metric">
                        <i class="bi bi-telephone"></i>

                        <div>
                            <strong>{{ $indicacoes->where('status', 'nova')->count() }}</strong>
                            <span>Novas</span>
                        </div>
                    </div>

                    <div class="nexo-leads-hero-metric">
                        <i class="bi bi-envelope"></i>

                        <div>
                            <strong>{{ $indicacoes->filter(fn($indicacao) => filled($indicacao->email))->count() }}</strong>
                            <span>Com e-mail</span>
                        </div>
                    </div>

                    <div class="nexo-leads-hero-metric">
                        <i class="bi bi-heart-pulse"></i>

                        <div>
                            <strong>{{ $indicacoes->sum('quantidade_vidas') }}</strong>
                            <span>Vidas</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('indicacoes.create') }}" class="nexo-leads-new-btn">
                    <i class="bi bi-plus-lg"></i>
                    Nova Lead
                </a>
            </div>
        </div>

        <div class="nexo-leads-panel">
            <div class="nexo-leads-panel-header">
                <div>
                    <h2>
                        Leads em acompanhamento
                    </h2>

                    <p>
                        Ao anexar uma proposta, o registro sai desta tela e passa para Propostas.
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle nexo-leads-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Contato</th>
                            <th>Plano</th>
                            <th>Vidas</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($indicacoes as $indicacao)
                            <tr>
                                <td>
                                    <div class="nexo-lead-user">
                                        <div class="nexo-lead-avatar">
                                            {{ strtoupper(substr($indicacao->nome_cliente, 0, 1)) }}
                                        </div>

                                        <div>
                                            <strong>
                                                {{ $indicacao->nome_cliente }}
                                            </strong>

                                            <small>
                                                {{ $indicacao->cidade }}/{{ $indicacao->estado }}
                                            </small>

                                            @if (filled($indicacao->observacoes))
                                                <small class="nexo-lead-observation">
                                                    {{ \Illuminate\Support\Str::limit($indicacao->observacoes, 90) }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="nexo-lead-contact">
                                        <div class="nexo-phone-actions">
                                            <strong>
                                                {{ $indicacao->telefone }}
                                            </strong>

                                            @if ($indicacao->telefone)
                                                <a href="{{ $whatsapp->buildLeadLink($indicacao, auth()->user()) }}"
                                                    target="_blank" rel="noopener" class="nexo-whatsapp-link"
                                                    title="Conversar no WhatsApp" aria-label="Conversar no WhatsApp">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="nexo-plan-pill">
                                        {{ $indicacao->tipo_plano }}
                                    </span>
                                </td>

                                <td>
                                    <div class="nexo-vidas-box">
                                        <strong>
                                            {{ $indicacao->quantidade_vidas }}
                                        </strong>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="nexo-status-pill nexo-status-{{ $statusCores[$indicacao->status] ?? 'primary' }}">
                                        {{ $statusLegiveis[$indicacao->status] ?? str_replace('_', ' ', $indicacao->status) }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a class="nexo-open-btn" href="{{ route('indicacoes.show', $indicacao) }}">
                                        Acompanhar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $indicacoes->links('vendor.pagination.nexo') }}
            </div>
        </div>
    </main>

    <style>
        .nexo-leads-table th:nth-child(3),
        .nexo-leads-table th:nth-child(4),
        .nexo-leads-table th:nth-child(5),
        .nexo-leads-table th:nth-child(6),
        .nexo-leads-table td:nth-child(3),
        .nexo-leads-table td:nth-child(4),
        .nexo-leads-table td:nth-child(5),
        .nexo-leads-table td:nth-child(6) {
            text-align: center;
        }

        .nexo-leads-table td:nth-child(3),
        .nexo-leads-table td:nth-child(4),
        .nexo-leads-table td:nth-child(5),
        .nexo-leads-table td:nth-child(6) {
            vertical-align: middle;
        }

        .nexo-leads-table th:nth-child(6),
        .nexo-leads-table td:nth-child(6) {
            width: 148px;
            min-width: 148px;
        }

        .nexo-leads-table th:nth-child(2),
        .nexo-leads-table td:nth-child(2) {
            width: 220px;
            min-width: 220px;
        }

        .nexo-leads-hero {
            position: relative;
            border-radius: 28px;
            background:
                radial-gradient(circle at 78% 12%, rgba(47, 128, 237, 0.38) 0, rgba(47, 128, 237, 0) 34%),
                radial-gradient(circle at 18% 100%, rgba(91, 167, 255, 0.2) 0, rgba(91, 167, 255, 0) 32%),
                linear-gradient(135deg, #061C3F 0%, #071A38 48%, #021026 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 26px 60px rgba(2, 16, 38, 0.18);
            overflow: hidden;
        }

        .nexo-leads-hero::before {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            right: -180px;
            top: -250px;
            border-radius: 999px;
            border: 1px solid rgba(91, 167, 255, 0.18);
            box-shadow:
                0 0 0 34px rgba(91, 167, 255, 0.035),
                0 0 0 72px rgba(91, 167, 255, 0.025),
                0 0 0 118px rgba(91, 167, 255, 0.018);
        }

        .nexo-leads-hero-content {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: minmax(240px, 1fr) auto auto;
            align-items: center;
            gap: 28px;
            min-height: 174px;
            padding: 34px;
        }

        .nexo-page-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: rgba(47, 128, 237, 0.16);
            color: #BBD8FF;
            font-size: 0.78rem;
            font-weight: 900;
            padding: 7px 12px;
            margin-bottom: 12px;
            border: 1px solid rgba(187, 216, 255, 0.14);
        }

        .nexo-leads-hero h1 {
            color: #FFFFFF;
            font-size: 2.35rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.055em;
            margin: 0 0 10px;
        }

        .nexo-leads-hero p {
            color: rgba(255, 255, 255, 0.76);
            margin: 0;
            font-size: 1rem;
        }

        .nexo-leads-hero-metrics {
            display: grid;
            grid-template-columns: repeat(4, max-content);
            align-items: center;
            gap: 0;
        }

        .nexo-leads-hero-metric {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 128px;
            padding: 8px 22px;
            border-right: 1px solid rgba(255, 255, 255, 0.16);
        }

        .nexo-leads-hero-metric:first-child {
            padding-left: 0;
        }

        .nexo-leads-hero-metric:last-child {
            border-right: 0;
            padding-right: 0;
        }

        .nexo-leads-hero-metric i {
            color: #72B6FF;
            font-size: 1.35rem;
        }

        .nexo-leads-hero-metric strong {
            display: block;
            color: #FFFFFF;
            font-size: 1.45rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.045em;
            margin-bottom: 4px;
        }

        .nexo-leads-hero-metric span {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .nexo-leads-new-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 50px;
            padding: 0 22px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            font-weight: 900;
            white-space: nowrap;
            text-decoration: none !important;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.28);
            transition: 0.2s ease;
        }

        .nexo-leads-new-btn:hover,
        .nexo-leads-new-btn:focus {
            transform: translateY(-2px);
            color: #FFFFFF;
            text-decoration: none !important;
        }

        .nexo-leads-panel {
            border-radius: 28px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.05);
            padding: 28px;
        }

        .nexo-leads-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;
        }

        .nexo-leads-panel-header h2 {
            color: #061C3F;
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            margin: 0 0 5px;
        }

        .nexo-leads-panel-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-leads-table thead th {
            color: #64748B;
            font-size: 0.8rem;
            font-weight: 900;
            text-transform: uppercase;
            border-color: #E8EEF6;
            padding-bottom: 16px;
        }

        .nexo-leads-table tbody td {
            padding-top: 18px;
            padding-bottom: 18px;
            border-color: #EDF2F7;
        }

        .nexo-lead-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nexo-lead-avatar {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2F80ED 0%, #5BA7FF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .nexo-lead-user strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .nexo-lead-user small {
            display: block;
            color: #64748B;
        }

        .nexo-lead-observation {
            max-width: 320px;
            color: #7C8BA1 !important;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .nexo-lead-contact strong {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #061C3F;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .nexo-lead-contact span {
            color: #64748B;
            font-size: 0.92rem;
        }

        .nexo-plan-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: #F3F8FF;
            color: #2F80ED;
            font-size: 0.82rem;
            font-weight: 900;
        }

        .nexo-vidas-box strong {
            display: block;
            color: #061C3F;
            font-size: 1.15rem;
            line-height: 1;
            font-weight: 900;
        }

        .nexo-vidas-box span {
            color: #64748B;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .nexo-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 900;
        }

        .nexo-status-primary {
            background: #EAF3FF;
            color: #2F80ED;
        }

        .nexo-status-success {
            background: #EAFBF1;
            color: #1E9E63;
        }

        .nexo-status-warning {
            background: #FFF5E8;
            color: #D9822B;
        }

        .nexo-status-danger {
            background: #FFECEC;
            color: #E14D4D;
        }

        .nexo-status-info {
            background: #EEF7FF;
            color: #3A8DDE;
        }

        .nexo-open-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 112px;
            min-height: 38px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1px solid #D7E7FF;
            background: #FFFFFF;
            color: #2F80ED;
            font-size: 0.86rem;
            font-weight: 900;
            line-height: 1;
            white-space: nowrap;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .nexo-open-btn:hover {
            background: #2F80ED;
            border-color: #2F80ED;
            color: #FFFFFF;
        }

        @media (max-width: 1320px) {
            .nexo-leads-hero-content {
                grid-template-columns: 1fr;
                align-items: flex-start;
            }

            .nexo-leads-hero-metrics {
                width: 100%;
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .nexo-leads-hero-metric {
                min-width: 0;
            }

            .nexo-leads-new-btn {
                width: max-content;
            }
        }

        @media (max-width: 768px) {
            .nexo-leads-hero-content {
                padding: 26px;
            }

            .nexo-leads-hero h1 {
                font-size: 2rem;
            }

            .nexo-leads-hero-metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
            }

            .nexo-leads-hero-metric {
                padding: 14px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.06);
            }

            .nexo-leads-new-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .nexo-leads-hero-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-layouts.app>