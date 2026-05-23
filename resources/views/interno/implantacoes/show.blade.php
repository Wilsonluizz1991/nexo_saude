<x-layouts.app title="Implantação | Nexo Saúde">
    @php
        $statusLegiveis = [
            'contrato_em_analise' => 'Contrato em análise',
            'pendencia_na_operadora' => 'Pendência na operadora',
            'aguardando_vigencia' => 'Aguardando vigência',
            'contrato_vigente' => 'Contrato vigente',
            'contrato_recusado' => 'Contrato recusado',
        ];

        $statusClasses = [
            'contrato_em_analise' => 'nexo-status-info',
            'pendencia_na_operadora' => 'nexo-status-warning',
            'aguardando_vigencia' => 'nexo-status-primary',
            'contrato_vigente' => 'nexo-status-success',
            'contrato_recusado' => 'nexo-status-danger',
        ];

        $proposta = $indicacao->propostas->sortByDesc('created_at')->first();
        $vidas = $indicacao->preCadastro?->vidas ?? collect();
        $statusAtual = $statusLegiveis[$indicacao->status] ?? ucfirst(str_replace('_', ' ', $indicacao->status));
        $classeStatusAtual = $statusClasses[$indicacao->status] ?? 'nexo-status-primary';
        $valorMensalFormatado = $proposta?->valor_mensal ? 'R$ '.number_format((float) $proposta->valor_mensal, 2, ',', '.') : '';
    @endphp

    <main class="nexo-main">
        <div class="nexo-implantacao-header mb-4">
            <div>
                <span class="nexo-page-label">
                    <i class="bi bi-rocket-takeoff"></i>
                    Implantação
                </span>

                <h1>
                    {{ $indicacao->nome_cliente }}
                </h1>

                <p>
                    Controle da análise da operadora, pendências, vigência e conversão para carteira.
                </p>
            </div>

            <a
                class="nexo-secondary-btn"
                href="{{ route('paginas.simples', 'implantacoes') }}"
            >
                <i class="bi bi-arrow-left"></i>
                Voltar para Implantações
            </a>
        </div>

        <div class="nexo-implantacao-summary mb-4">
            <div class="nexo-implantacao-summary-card">
                <div class="nexo-summary-icon">
                    <i class="bi bi-activity"></i>
                </div>

                <div>
                    <span>Status</span>
                    <strong>{{ $statusAtual }}</strong>
                </div>
            </div>

            <div class="nexo-implantacao-summary-card">
                <div class="nexo-summary-icon">
                    <i class="bi bi-hospital"></i>
                </div>

                <div>
                    <span>Operadora</span>
                    <strong>{{ $proposta?->operadora?->nome ?: 'Não informada' }}</strong>
                </div>
            </div>

            <div class="nexo-implantacao-summary-card">
                <div class="nexo-summary-icon">
                    <i class="bi bi-people"></i>
                </div>

                <div>
                    <span>Vidas</span>
                    <strong>{{ $vidas->count() ?: $indicacao->quantidade_vidas }}</strong>
                </div>
            </div>

            <div class="nexo-implantacao-summary-card">
                <div class="nexo-summary-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>

                <div>
                    <span>Início</span>
                    <strong>{{ $implantacao->data_inicio?->format('d/m/Y') ?: 'Sem data' }}</strong>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <section class="nexo-implantacao-panel mb-4">
                    <div class="nexo-panel-header">
                        <div>
                            <h2>Status da implantação</h2>
                            <p>Atualize a situação operacional sem voltar para a ficha de Lead.</p>
                        </div>

                        <span class="nexo-status-pill {{ $classeStatusAtual }}">
                            {{ $statusAtual }}
                        </span>
                    </div>

                    <form method="post" action="{{ route('indicacoes.implantacao.status', $indicacao) }}" class="nexo-status-form mb-4">
                        @csrf

                        <div class="row g-3 align-items-end">
                            <div class="col-lg-8">
                                <label class="form-label">Status da implantação</label>

                                <select class="form-select" name="status">
                                    @foreach([
                                        'contrato_em_analise' => 'Contrato em análise',
                                        'pendencia_na_operadora' => 'Pendência na operadora',
                                        'aguardando_vigencia' => 'Aguardando vigência',
                                        'contrato_recusado' => 'Contrato recusado',
                                    ] as $valor => $label)
                                        <option value="{{ $valor }}" @selected($indicacao->status === $valor)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-4">
                                <div class="nexo-status-actions">
                                    <button class="nexo-secondary-btn" type="submit">
                                        <i class="bi bi-arrow-repeat"></i>
                                        Atualizar status
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="nexo-contract-grid">
                        <div class="nexo-contract-card">
                            <div class="nexo-contract-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>

                            <div>
                                <span>Proposta vinculada</span>
                                <strong>{{ $proposta?->titulo ?: 'Sem proposta vinculada' }}</strong>

                                @if($proposta?->valor_mensal)
                                    <p>R$ {{ number_format((float) $proposta->valor_mensal, 2, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="nexo-contract-card">
                            <div class="nexo-contract-icon">
                                <i class="bi bi-card-text"></i>
                            </div>

                            <div>
                                <span>Observações da implantação</span>
                                <strong>{{ $implantacao->observacoes ?: 'Sem observações' }}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="nexo-implantacao-panel">
                    <div class="nexo-panel-header">
                        <div>
                            <h2>Beneficiários em implantação</h2>
                            <p>Vidas vinculadas ao pré-cadastro aprovado.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle nexo-implantacao-table mb-0">
                            <thead>
                                <tr>
                                    <th>Beneficiário</th>
                                    <th>Tipo</th>
                                    <th>Documento</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($vidas->sortBy('ordem') as $vida)
                                    <tr>
                                        <td>
                                            <div class="nexo-beneficiario-user">
                                                <div class="nexo-beneficiario-avatar">
                                                    {{ strtoupper(substr($vida->nome ?: 'B', 0, 1)) }}
                                                </div>

                                                <div>
                                                    <strong>{{ $vida->nome ?: 'Beneficiário '.$vida->ordem }}</strong>
                                                    <small>Vida {{ $vida->ordem }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="nexo-light-pill">
                                                {{ ucfirst(str_replace('_', ' ', $vida->tipo)) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $vida->cpf ?: 'Não informado' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">
                                            <div class="nexo-empty-state">
                                                <i class="bi bi-people"></i>
                                                <p>Nenhum beneficiário vinculado ao pré-cadastro.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="nexo-final-action">
                        <button
                            class="nexo-primary-btn"
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#contratoVigenteModal"
                        >
                            <i class="bi bi-check-circle"></i>
                            Contrato vigente
                        </button>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="nexo-implantacao-panel h-100">
                    <div class="nexo-panel-header">
                        <div>
                            <h2>Timeline da implantação</h2>
                            <p>Histórico de movimentações e atualizações.</p>
                        </div>
                    </div>

                    <div class="nexo-timeline">
                        @forelse($indicacao->timelineEventos->sortByDesc('created_at') as $evento)
                            <div class="nexo-timeline-item">
                                <div class="nexo-timeline-dot"></div>

                                <div class="nexo-timeline-content">
                                    <strong>{{ $evento->titulo }}</strong>
                                    <p>{{ $evento->descricao }}</p>
                                    <span>{{ $evento->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="nexo-empty-state">
                                <i class="bi bi-clock-history"></i>
                                <p>Nenhum evento registrado.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="mt-4">
                    @include('interno.indicacoes.partials.lembretes-card', ['indicacao' => $indicacao])
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="contratoVigenteModal" tabindex="-1" aria-labelledby="contratoVigenteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="post" action="{{ route('indicacoes.implantacao.aprovar', $indicacao) }}" class="modal-content nexo-modal-content">
                @csrf

                <div class="modal-header nexo-modal-header">
                    <div class="nexo-modal-title-wrap">
                        <div class="nexo-modal-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>

                        <div>
                            <span class="nexo-modal-kicker">
                                Contrato vigente
                            </span>

                            <h2 class="modal-title" id="contratoVigenteLabel">
                                Confirmar dados finais do contrato
                            </h2>

                            <p>
                                Revise as informações comerciais, defina datas importantes e confirme a entrada do cliente na carteira.
                            </p>
                        </div>
                    </div>

                    <button type="button" class="btn-close nexo-modal-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body nexo-modal-body">
                    <div class="nexo-modal-summary-card">
                        <div>
                            <span>Cliente</span>
                            <strong>{{ $indicacao->nome_cliente }}</strong>
                        </div>

                        <div>
                            <span>Operadora sugerida</span>
                            <strong>{{ $proposta?->operadora?->nome ?: 'Não informada' }}</strong>
                        </div>

                        <div>
                            <span>Vidas</span>
                            <strong>{{ $vidas->count() ?: $indicacao->quantidade_vidas }}</strong>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Operadora</label>

                            <select class="form-select" name="operadora_id" required>
                                <option value="">Selecione</option>

                                @foreach($operadoras as $operadora)
                                    <option value="{{ $operadora->id }}" @selected($proposta?->operadora_id === $operadora->id)>
                                        {{ $operadora->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tipo de contrato</label>

                            <select class="form-select" name="tipo_contrato" data-plan-type required>
                                <option value="individual">Individual</option>
                                <option value="familiar">Familiar</option>
                                <option value="pme">PME</option>
                                <option value="empresarial">Empresarial</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Quantidade de vidas</label>

                            <input
                                class="form-control"
                                name="quantidade_vidas"
                                type="number"
                                min="1"
                                value="{{ $vidas->count() ?: $indicacao->quantidade_vidas }}"
                                data-lives-count
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de vigência</label>

                            <div class="nexo-date-field">
                                <i class="bi bi-calendar2-check"></i>

                                <input
                                    class="form-control nexo-date-display"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="dd/mm/aaaa"
                                    autocomplete="off"
                                    required
                                >

                                <input
                                    class="nexo-date-hidden"
                                    name="data_vigencia"
                                    type="hidden"
                                    required
                                >

                                <button class="nexo-date-button" type="button" aria-label="Abrir calendário">
                                    <i class="bi bi-calendar3"></i>
                                </button>

                                <div class="nexo-calendar"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Valor mensal</label>

                            <input
                                class="form-control"
                                name="valor_mensal_formatado"
                                type="text"
                                inputmode="numeric"
                                value="{{ $valorMensalFormatado }}"
                                placeholder="R$ 0,00"
                                data-money-mask
                                required
                            >

                            <input
                                type="hidden"
                                name="valor_mensal"
                                value="{{ $proposta?->valor_mensal }}"
                                data-money-hidden
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Renovação</label>

                            <div class="nexo-date-field">
                                <i class="bi bi-calendar2-check"></i>

                                <input
                                    class="form-control nexo-date-display"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="dd/mm/aaaa"
                                    autocomplete="off"
                                    required
                                >

                                <input
                                    class="nexo-date-hidden"
                                    name="renovacao_em"
                                    type="hidden"
                                    required
                                >

                                <button class="nexo-date-button" type="button" aria-label="Abrir calendário">
                                    <i class="bi bi-calendar3"></i>
                                </button>

                                <div class="nexo-calendar"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reajuste</label>

                            <div class="nexo-date-field">
                                <i class="bi bi-calendar2-check"></i>

                                <input
                                    class="form-control nexo-date-display"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="dd/mm/aaaa"
                                    autocomplete="off"
                                    required
                                >

                                <input
                                    class="nexo-date-hidden"
                                    name="reajuste_em"
                                    type="hidden"
                                    required
                                >

                                <button class="nexo-date-button" type="button" aria-label="Abrir calendário">
                                    <i class="bi bi-calendar3"></i>
                                </button>

                                <div class="nexo-calendar"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Número do contrato</label>

                            <input class="form-control" name="numero_contrato">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações</label>

                            <textarea class="form-control" name="observacoes" rows="3">{{ $implantacao->observacoes }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="nexo-check-card">
                                <input class="form-check-input" type="checkbox" name="enviar_email" value="1">
                                <span>
                                    <strong>Enviar e-mail automático</strong>
                                    <small>Notificar o cliente sobre o contrato vigente.</small>
                                </span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="nexo-check-card">
                                <input class="form-check-input" type="checkbox" name="enviar_sms" value="1">
                                <span>
                                    <strong>Enviar SMS automático</strong>
                                    <small>Enviar confirmação resumida ao cliente.</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer nexo-modal-footer">
                    <div class="nexo-modal-footer-note">
                        <i class="bi bi-info-circle"></i>
                        Esta ação registra o contrato como vigente e move o cliente para a carteira.
                    </div>

                    <div class="nexo-modal-footer-actions">
                        <button type="button" class="nexo-secondary-btn" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button class="nexo-primary-btn">
                            <i class="bi bi-check-circle"></i>
                            Confirmar contrato vigente
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .nexo-implantacao-header {
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

        .nexo-implantacao-header h1 {
            color: #061C3F;
            font-size: 2.35rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.05em;
            margin: 0 0 8px;
        }

        .nexo-implantacao-header p {
            color: #64748B;
            margin: 0;
            font-size: 1rem;
        }

        .nexo-primary-btn,
        .nexo-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 0 18px;
            border-radius: 14px;
            font-weight: 900;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .nexo-primary-btn {
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            border: none;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.22);
        }

        .nexo-primary-btn:hover {
            color: #FFFFFF;
            transform: translateY(-2px);
        }

        .nexo-secondary-btn {
            background: #FFFFFF;
            border: 1px solid #D7E7FF;
            color: #2F80ED;
        }

        .nexo-secondary-btn:hover {
            background: #2F80ED;
            color: #FFFFFF;
        }

        .nexo-implantacao-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .nexo-implantacao-summary-card {
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

        .nexo-implantacao-summary-card::after {
            content: "";
            position: absolute;
            width: 74px;
            height: 74px;
            right: -20px;
            top: -20px;
            border-radius: 999px;
            background: rgba(47, 128, 237, 0.08);
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

        .nexo-implantacao-summary-card span {
            display: block;
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-implantacao-summary-card strong {
            display: block;
            color: #061C3F;
            font-size: 1.45rem;
            line-height: 1.12;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .nexo-implantacao-panel {
            border-radius: 28px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.05);
            padding: 28px;
        }

        .nexo-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .nexo-panel-header h2 {
            color: #061C3F;
            font-size: 1.35rem;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .nexo-panel-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 900;
            white-space: nowrap;
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

        .nexo-status-form {
            padding: 22px;
            border-radius: 24px;
            background: #F8FBFF;
            border: 1px solid #DCEBFF;
        }

        .nexo-status-form .form-label,
        .nexo-modal-body .form-label {
            color: #061C3F;
            font-weight: 800;
        }

        .nexo-status-form .form-select,
        .nexo-modal-body .form-select,
        .nexo-modal-body .form-control {
            min-height: 50px;
            border-radius: 12px;
            border: 1px solid #D8E2EF;
        }

        .nexo-status-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nexo-contract-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .nexo-contract-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 20px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-contract-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: rgba(47, 128, 237, 0.12);
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .nexo-contract-card span {
            display: block;
            color: #64748B;
            font-size: 0.82rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-contract-card strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
        }

        .nexo-contract-card p {
            color: #64748B;
            margin: 4px 0 0;
        }

        .nexo-implantacao-table thead th {
            color: #64748B;
            font-size: 0.8rem;
            font-weight: 900;
            text-transform: uppercase;
            border-color: #E8EEF6;
            padding-bottom: 16px;
        }

        .nexo-implantacao-table tbody td {
            padding-top: 18px;
            padding-bottom: 18px;
            border-color: #EDF2F7;
            vertical-align: middle;
        }

        .nexo-beneficiario-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nexo-beneficiario-avatar {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2F80ED 0%, #5BA7FF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            flex-shrink: 0;
        }

        .nexo-beneficiario-user strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
        }

        .nexo-beneficiario-user small {
            color: #64748B;
        }

        .nexo-light-pill {
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

        .nexo-final-action {
            display: flex;
            justify-content: flex-end;
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #EDF2F7;
        }

        .nexo-timeline {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .nexo-timeline-item {
            position: relative;
            display: flex;
            gap: 16px;
        }

        .nexo-timeline-dot {
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: #2F80ED;
            margin-top: 6px;
            flex-shrink: 0;
            box-shadow: 0 0 0 6px rgba(47, 128, 237, 0.10);
        }

        .nexo-timeline-content {
            position: relative;
            padding-bottom: 18px;
            flex: 1;
        }

        .nexo-timeline-content::before {
            content: "";
            position: absolute;
            left: -23px;
            top: 22px;
            width: 2px;
            height: calc(100% - 6px);
            background: #DCEBFF;
        }

        .nexo-timeline-item:last-child .nexo-timeline-content::before {
            display: none;
        }

        .nexo-timeline-content strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
            margin-bottom: 6px;
        }

        .nexo-timeline-content p {
            color: #64748B;
            margin: 0 0 8px;
            font-size: 0.92rem;
        }

        .nexo-timeline-content span {
            color: #94A3B8;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .nexo-empty-state {
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #64748B;
            text-align: center;
        }

        .nexo-empty-state i {
            color: #2F80ED;
            font-size: 2.4rem;
            margin-bottom: 12px;
        }

        .nexo-empty-state p {
            margin: 0;
            font-weight: 700;
        }

        .nexo-modal-content {
            border: 0;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 28px 70px rgba(6, 28, 63, 0.22);
        }

        .nexo-modal-header {
            align-items: flex-start;
            padding: 28px;
            border-bottom: 1px solid #E8EEF6;
            background: linear-gradient(180deg, #F8FBFF 0%, #FFFFFF 100%);
        }

        .nexo-modal-header h2 {
            color: #061C3F;
            font-size: 1.6rem;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .nexo-modal-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-modal-body {
            padding: 28px;
        }

        .nexo-modal-footer {
            padding: 22px 28px;
            border-top: 1px solid #E8EEF6;
            background: #F8FBFF;
        }

        .nexo-check-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid #DCEBFF;
            background: #F8FBFF;
            cursor: pointer;
            height: 100%;
        }

        .nexo-check-card input {
            margin-top: 4px;
            accent-color: #2F80ED;
        }

        .nexo-check-card strong {
            display: block;
            color: #061C3F;
            font-size: 0.92rem;
            margin-bottom: 2px;
        }

        .nexo-check-card small {
            display: block;
            color: #64748B;
            line-height: 1.35;
        }


        .nexo-modal-title-wrap {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .nexo-modal-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            box-shadow: 0 16px 34px rgba(47, 128, 237, 0.22);
        }

        .nexo-modal-kicker {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.74rem;
            font-weight: 950;
            margin-bottom: 8px;
        }

        .nexo-modal-close {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            background-color: #F1F7FF;
            opacity: 1;
        }

        .nexo-modal-summary-card {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 16px;
            border-radius: 22px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.12), transparent 32%),
                #F8FBFF;
            border: 1px solid #DCEBFF;
            margin-bottom: 22px;
        }

        .nexo-modal-summary-card div {
            padding: 12px;
            border-radius: 16px;
            background: #FFFFFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-modal-summary-card span {
            display: block;
            color: #64748B;
            font-size: 0.78rem;
            font-weight: 850;
            margin-bottom: 4px;
        }

        .nexo-modal-summary-card strong {
            display: block;
            color: #061C3F;
            font-size: 0.95rem;
            font-weight: 950;
            line-height: 1.2;
        }

        .nexo-modal-body .form-label {
            font-size: 0.86rem;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .nexo-modal-body .form-select,
        .nexo-modal-body .form-control {
            min-height: 52px;
            border-radius: 15px;
            border: 1px solid #D8E2EF;
            color: #061C3F;
            padding: 12px 15px;
            font-weight: 700;
        }

        .nexo-modal-body textarea.form-control {
            min-height: 112px;
            resize: vertical;
        }

        .nexo-modal-body .form-select:focus,
        .nexo-modal-body .form-control:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-date-field {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 48px;
            gap: 10px;
        }

        .nexo-date-field > .bi-calendar2-check {
            position: absolute;
            top: 50%;
            left: 16px;
            color: #2F80ED;
            z-index: 2;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .nexo-date-display {
            padding-left: 44px !important;
            cursor: text;
            background: #FFFFFF !important;
        }

        .nexo-date-hidden {
            display: none;
        }

        .nexo-date-button {
            width: 48px;
            min-height: 52px;
            border: 1px solid #D8E2EF;
            border-radius: 15px;
            background: #F8FBFF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-date-button:hover,
        .nexo-date-button:focus {
            background: #EAF3FF;
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-calendar {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            z-index: 5000;
            width: min(350px, 92vw);
            padding: 16px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 24px 60px rgba(6, 28, 63, 0.18);
            display: none;
        }

        .nexo-calendar.is-open {
            display: block;
        }

        .nexo-calendar-header {
            display: grid;
            grid-template-columns: 38px 1fr 38px;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .nexo-calendar-title {
            display: grid;
            grid-template-columns: 1fr 86px;
            gap: 8px;
        }

        .nexo-calendar-title select {
            min-height: 38px;
            border-radius: 12px;
            border: 1px solid #D8E2EF;
            background: #F8FBFF;
            color: #061C3F;
            font-size: 0.82rem;
            font-weight: 850;
            padding: 0 10px;
            outline: none;
        }

        .nexo-calendar-title select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 3px rgba(47, 128, 237, 0.12);
        }

        .nexo-calendar-nav-button {
            width: 38px;
            height: 38px;
            border: 1px solid #D8E2EF;
            border-radius: 13px;
            background: #F8FBFF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-calendar-nav-button:hover {
            background: #EAF3FF;
            border-color: #2F80ED;
        }

        .nexo-calendar-weekdays,
        .nexo-calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }

        .nexo-calendar-weekdays {
            margin-bottom: 8px;
        }

        .nexo-calendar-weekdays span {
            color: #64748B;
            font-size: 0.72rem;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .nexo-calendar-day {
            height: 36px;
            border: 0;
            border-radius: 12px;
            background: #F8FBFF;
            color: #061C3F;
            font-size: 0.86rem;
            font-weight: 850;
            transition: 0.2s ease;
        }

        .nexo-calendar-day:hover {
            background: #EAF3FF;
            color: #2F80ED;
        }

        .nexo-calendar-day.is-selected {
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            box-shadow: 0 10px 22px rgba(47, 128, 237, 0.22);
        }

        .nexo-calendar-day.is-today {
            outline: 2px solid rgba(47, 128, 237, 0.18);
        }

        .nexo-calendar-empty {
            height: 36px;
        }

        .nexo-modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .nexo-modal-footer-note {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748B;
            font-size: 0.86rem;
            font-weight: 750;
        }

        .nexo-modal-footer-note i {
            color: #2F80ED;
        }

        .nexo-modal-footer-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }


        @media (max-width: 1200px) {
            .nexo-implantacao-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .nexo-contract-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .nexo-implantacao-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-implantacao-summary {
                grid-template-columns: 1fr;
            }

            .nexo-panel-header {
                flex-direction: column;
            }

            .nexo-status-actions {
                flex-direction: column;
            }

            .nexo-status-actions .nexo-secondary-btn {
                width: 100%;
            }

            .nexo-final-action {
                justify-content: stretch;
            }

            .nexo-final-action .nexo-primary-btn {
                width: 100%;
            }

            .nexo-implantacao-panel {
                padding: 22px;
            }

            .nexo-modal-header,
            .nexo-modal-body {
                padding: 22px;
            }

            .nexo-modal-title-wrap {
                flex-direction: column;
            }

            .nexo-modal-summary-card {
                grid-template-columns: 1fr;
            }

            .nexo-modal-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .nexo-modal-footer-actions {
                flex-direction: column-reverse;
                width: 100%;
            }

            .nexo-modal-footer-actions .nexo-primary-btn,
            .nexo-modal-footer-actions .nexo-secondary-btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const moneyInputs = document.querySelectorAll('[data-money-mask]');

            function onlyDigits(value) {
                return value.replace(/\D/g, '');
            }

            function formatMoneyFromDigits(digits) {
                if (!digits) {
                    return '';
                }

                let value = (Number(digits) / 100).toFixed(2);

                value = value.replace('.', ',');
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                return 'R$ ' + value;
            }

            function updateHiddenValue(input) {
                const hiddenInput = input.closest('.col-md-4')?.querySelector('[data-money-hidden]');
                const digits = onlyDigits(input.value);

                if (!hiddenInput) {
                    return;
                }

                if (!digits) {
                    hiddenInput.value = '';
                    return;
                }

                hiddenInput.value = (Number(digits) / 100).toFixed(2);
            }


            const months = [
                'Janeiro',
                'Fevereiro',
                'Março',
                'Abril',
                'Maio',
                'Junho',
                'Julho',
                'Agosto',
                'Setembro',
                'Outubro',
                'Novembro',
                'Dezembro',
            ];

            const weekdays = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];
            const pad = (value) => String(value).padStart(2, '0');

            const formatDate = (value) => {
                const onlyNumbers = value.replace(/\D/g, '').slice(0, 8);
                const day = onlyNumbers.slice(0, 2);
                const month = onlyNumbers.slice(2, 4);
                const year = onlyNumbers.slice(4, 8);

                if (onlyNumbers.length > 4) {
                    return `${day}/${month}/${year}`;
                }

                if (onlyNumbers.length > 2) {
                    return `${day}/${month}`;
                }

                return day;
            };

            const toHiddenDate = (value) => {
                const parts = value.split('/');

                if (parts.length !== 3) {
                    return '';
                }

                const [day, month, year] = parts;

                if (day.length !== 2 || month.length !== 2 || year.length !== 4) {
                    return '';
                }

                const date = new Date(Number(year), Number(month) - 1, Number(day));

                if (
                    date.getFullYear() !== Number(year) ||
                    date.getMonth() !== Number(month) - 1 ||
                    date.getDate() !== Number(day)
                ) {
                    return '';
                }

                return `${year}-${month}-${day}`;
            };

            const parseDate = (value) => {
                if (! value) {
                    return null;
                }

                const [year, month, day] = value.split('-').map(Number);

                if (! year || ! month || ! day) {
                    return null;
                }

                return new Date(year, month - 1, day);
            };

            const sameDay = (first, second) => {
                return first &&
                    second &&
                    first.getFullYear() === second.getFullYear() &&
                    first.getMonth() === second.getMonth() &&
                    first.getDate() === second.getDate();
            };

            const setDateValue = (field, date) => {
                const displayInput = field.querySelector('.nexo-date-display');
                const hiddenInput = field.querySelector('.nexo-date-hidden');

                hiddenInput.value = `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
                displayInput.value = `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`;
            };

            const closeCalendars = (except = null) => {
                document.querySelectorAll('.nexo-calendar.is-open').forEach((calendar) => {
                    if (calendar !== except) {
                        calendar.classList.remove('is-open');
                    }
                });
            };

            const openCalendar = (field) => {
                const calendar = field.querySelector('.nexo-calendar');

                closeCalendars(calendar);
                renderCalendar(field, field._nexoCalendarDate);
                calendar.classList.add('is-open');
            };

            const renderCalendar = (field, referenceDate) => {
                const calendar = field.querySelector('.nexo-calendar');
                const hiddenInput = field.querySelector('.nexo-date-hidden');
                const selectedDate = parseDate(hiddenInput.value);
                const today = new Date();
                const year = referenceDate.getFullYear();
                const month = referenceDate.getMonth();
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);

                calendar.innerHTML = '';

                const header = document.createElement('div');
                header.className = 'nexo-calendar-header';

                const previous = document.createElement('button');
                previous.type = 'button';
                previous.className = 'nexo-calendar-nav-button';
                previous.innerHTML = '<i class="bi bi-chevron-left"></i>';

                const next = document.createElement('button');
                next.type = 'button';
                next.className = 'nexo-calendar-nav-button';
                next.innerHTML = '<i class="bi bi-chevron-right"></i>';

                const title = document.createElement('div');
                title.className = 'nexo-calendar-title';

                const monthSelect = document.createElement('select');
                const yearSelect = document.createElement('select');

                months.forEach((monthName, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = monthName;
                    option.selected = index === month;
                    monthSelect.appendChild(option);
                });

                for (let optionYear = today.getFullYear() - 2; optionYear <= today.getFullYear() + 15; optionYear++) {
                    const option = document.createElement('option');
                    option.value = optionYear;
                    option.textContent = optionYear;
                    option.selected = optionYear === year;
                    yearSelect.appendChild(option);
                }

                title.append(monthSelect, yearSelect);
                header.append(previous, title, next);

                const weekdaysWrapper = document.createElement('div');
                weekdaysWrapper.className = 'nexo-calendar-weekdays';

                weekdays.forEach((weekday) => {
                    const item = document.createElement('span');
                    item.textContent = weekday;
                    weekdaysWrapper.appendChild(item);
                });

                const daysWrapper = document.createElement('div');
                daysWrapper.className = 'nexo-calendar-days';

                for (let index = 0; index < firstDay.getDay(); index++) {
                    const empty = document.createElement('div');
                    empty.className = 'nexo-calendar-empty';
                    daysWrapper.appendChild(empty);
                }

                for (let day = 1; day <= lastDay.getDate(); day++) {
                    const date = new Date(year, month, day);
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'nexo-calendar-day';
                    button.textContent = day;

                    if (sameDay(date, today)) {
                        button.classList.add('is-today');
                    }

                    if (sameDay(date, selectedDate)) {
                        button.classList.add('is-selected');
                    }

                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();

                        setDateValue(field, date);
                        calendar.classList.remove('is-open');
                    });

                    daysWrapper.appendChild(button);
                }

                previous.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    field._nexoCalendarDate = new Date(year, month - 1, 1);
                    renderCalendar(field, field._nexoCalendarDate);
                    calendar.classList.add('is-open');
                });

                next.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    field._nexoCalendarDate = new Date(year, month + 1, 1);
                    renderCalendar(field, field._nexoCalendarDate);
                    calendar.classList.add('is-open');
                });

                monthSelect.addEventListener('click', (event) => event.stopPropagation());
                yearSelect.addEventListener('click', (event) => event.stopPropagation());

                monthSelect.addEventListener('change', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    field._nexoCalendarDate = new Date(Number(yearSelect.value), Number(monthSelect.value), 1);
                    renderCalendar(field, field._nexoCalendarDate);
                    calendar.classList.add('is-open');
                });

                yearSelect.addEventListener('change', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    field._nexoCalendarDate = new Date(Number(yearSelect.value), Number(monthSelect.value), 1);
                    renderCalendar(field, field._nexoCalendarDate);
                    calendar.classList.add('is-open');
                });

                calendar.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

                calendar.append(header, weekdaysWrapper, daysWrapper);
            };

            document.querySelectorAll('.nexo-date-field').forEach((field) => {
                const displayInput = field.querySelector('.nexo-date-display');
                const hiddenInput = field.querySelector('.nexo-date-hidden');
                const button = field.querySelector('.nexo-date-button');
                const calendar = field.querySelector('.nexo-calendar');

                if (! displayInput || ! hiddenInput || ! button || ! calendar) {
                    return;
                }

                field._nexoCalendarDate = parseDate(hiddenInput.value) || new Date();

                field.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

                displayInput.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    openCalendar(field);
                });

                displayInput.addEventListener('focus', () => {
                    openCalendar(field);
                });

                displayInput.addEventListener('input', () => {
                    displayInput.value = formatDate(displayInput.value);
                    hiddenInput.value = toHiddenDate(displayInput.value);

                    const selectedDate = parseDate(hiddenInput.value);

                    if (selectedDate) {
                        field._nexoCalendarDate = selectedDate;
                        renderCalendar(field, field._nexoCalendarDate);
                        calendar.classList.add('is-open');
                    }
                });

                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    openCalendar(field);
                });

                renderCalendar(field, field._nexoCalendarDate);
            });

            document.addEventListener('click', () => {
                closeCalendars();
            });

            moneyInputs.forEach(function (input) {
                input.addEventListener('input', function (event) {
                    const digits = onlyDigits(event.target.value);

                    event.target.value = formatMoneyFromDigits(digits);

                    updateHiddenValue(event.target);
                });

                input.closest('form')?.addEventListener('submit', function () {
                    updateHiddenValue(input);
                });

                updateHiddenValue(input);
            });
        });
    </script>
</x-layouts.app>
