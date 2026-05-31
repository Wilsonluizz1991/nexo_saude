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
        <div class="nexo-leads-header mb-4">
            <div>
                <span class="nexo-page-label">Operação</span>

                <h1>Leads</h1>

                <p>Acompanhe apenas os registros que ainda estão na primeira etapa do funil.</p>
            </div>
        </div>

        <div class="nexo-leads-summary-grid mb-4">
            <div class="nexo-leads-summary-card">
                <div class="nexo-leads-summary-icon">
                    <i class="bi bi-people"></i>
                </div>

                <div>
                    <span>Total de Leads</span>
                    <strong>{{ $indicacoes->total() }}</strong>
                </div>
            </div>

            <div class="nexo-leads-summary-card">
                <div class="nexo-leads-summary-icon">
                    <i class="bi bi-telephone"></i>
                </div>

                <div>
                    <span>Novas</span>
                    <strong>
                        {{ $indicacoes->where('status', 'nova')->count() }}
                    </strong>
                </div>
            </div>

            <div class="nexo-leads-summary-card">
                <div class="nexo-leads-summary-icon">
                    <i class="bi bi-envelope"></i>
                </div>

                <div>
                    <span>Com e-mail</span>
                    <strong>
                        {{ $indicacoes->filter(fn($indicacao) => filled($indicacao->email))->count() }}
                    </strong>
                </div>
            </div>

            <div class="nexo-leads-summary-card">
                <div class="nexo-leads-summary-icon">
                    <i class="bi bi-heart-pulse"></i>
                </div>

                <div>
                    <span>Vidas informadas</span>
                    <strong>
                        {{ $indicacoes->sum('quantidade_vidas') }}
                    </strong>
                </div>
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

                                        <span>
                                            {{ $indicacao->email }}
                                        </span>
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
        .nexo-leads-table td:nth-child(3),
        .nexo-leads-table td:nth-child(4),
        .nexo-leads-table td:nth-child(5) {
            text-align: center;
        }

        .nexo-leads-table td:nth-child(3),
        .nexo-leads-table td:nth-child(4),
        .nexo-leads-table td:nth-child(5) {
            vertical-align: middle;
        }

        .nexo-leads-header {
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

        .nexo-leads-header h1 {
            color: #061C3F;
            font-size: 2.25rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.045em;
            margin: 0 0 8px;
        }

        .nexo-leads-header p {
            color: #64748B;
            margin: 0;
            font-size: 1rem;
        }

        .nexo-leads-new-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 50px;
            padding: 0 22px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.22);
            transition: 0.2s ease;
        }

        .nexo-leads-new-btn:hover {
            transform: translateY(-2px);
            color: #FFFFFF;
        }

        .nexo-leads-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .nexo-leads-summary-card {
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

        .nexo-leads-summary-card::after {
            content: "";
            position: absolute;
            width: 74px;
            height: 74px;
            right: -20px;
            top: -20px;
            border-radius: 999px;
            background: rgba(47, 128, 237, 0.08);
        }

        .nexo-leads-summary-icon {
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

        .nexo-leads-summary-card span {
            display: block;
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-leads-summary-card strong {
            display: block;
            color: #061C3F;
            font-size: 2rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.05em;
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
            display: block;
            color: #061C3F;
            margin-bottom: 2px;
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

        .nexo-open-btn:hover {
            background: #2F80ED;
            border-color: #2F80ED;
            color: #FFFFFF;
        }

        @media (max-width: 1200px) {
            .nexo-leads-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .nexo-leads-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-leads-summary-grid {
                grid-template-columns: 1fr;
            }

            .nexo-leads-new-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</x-layouts.app>
