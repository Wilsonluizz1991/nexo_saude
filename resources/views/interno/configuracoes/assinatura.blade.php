<x-configuracoes.layout titulo="Assinatura">
    @php
        $statusBase = $assinatura?->status ?? $assinatura?->status_assinatura;
        $valor = $assinatura?->valor ?? $assinatura?->valor_assinatura ?? 49.90;
        $vencimento = $assinatura?->next_payment_at ?? $assinatura?->vencimento_assinatura;
        $fimTeste = $assinatura?->trial_ends_at ?? $assinatura?->data_fim_teste_gratis;
        $cardBrand = $assinatura?->card_brand ? strtoupper($assinatura->card_brand) : null;
        $cardLastFour = $assinatura?->card_last_four;

        $statusLabel = match ($statusBase) {
            'trialing', 'teste_gratis' => 'Teste gratuito',
            'active', 'ativa', 'paid' => 'Assinatura ativa',
            'overdue', 'inadimplente', 'past_due', 'dunning' => 'Pagamento pendente',
            'canceled', 'cancelled', 'cancelada' => 'Assinatura cancelada',
            'expired', 'expirada' => 'Assinatura expirada',
            default => 'Em análise',
        };

        $statusClass = match ($statusBase) {
            'trialing', 'teste_gratis' => 'is-warning',
            'active', 'ativa', 'paid' => 'is-success',
            'overdue', 'inadimplente', 'past_due', 'dunning' => 'is-danger',
            'canceled', 'cancelled', 'cancelada', 'expired', 'expirada' => 'is-danger',
            default => 'is-neutral',
        };
    @endphp

    <div class="nexo-subscription-page">
        @if(session('status'))
            <div class="nexo-subscription-alert is-success">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="nexo-subscription-alert is-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <section class="nexo-subscription-hero">
            <div>
                <span class="nexo-subscription-kicker">
                    Minha assinatura
                </span>

                <h2>
                    Plano Corretor Pro
                </h2>

                <p>
                    Acompanhe sua assinatura, valor mensal, vencimento e forma de pagamento cadastrada.
                </p>
            </div>

            <div class="nexo-subscription-price">
                <span>Valor mensal</span>

                <strong>
                    R$ {{ number_format($valor, 2, ',', '.') }}
                </strong>

                <small>
                    Cobrança automática mensal
                </small>
            </div>
        </section>

        <section class="nexo-subscription-status {{ $statusClass }}">
            <div class="nexo-subscription-status-icon">
                @if($statusClass === 'is-success')
                    <i class="bi bi-check-circle-fill"></i>
                @elseif($statusClass === 'is-warning')
                    <i class="bi bi-hourglass-split"></i>
                @elseif($statusClass === 'is-danger')
                    <i class="bi bi-exclamation-triangle-fill"></i>
                @else
                    <i class="bi bi-info-circle-fill"></i>
                @endif
            </div>

            <div>
                <span>Status da assinatura</span>

                <strong>
                    {{ $statusLabel }}
                </strong>

                @if($statusClass === 'is-success')
                    <p>Sua assinatura está regular e seu acesso à plataforma está liberado.</p>
                @elseif($statusBase === 'trialing' || $statusBase === 'teste_gratis')
                    <p>Você está no período gratuito. A primeira cobrança será feita ao final do teste.</p>
                @elseif($statusClass === 'is-danger')
                    <p>Regularize sua assinatura para manter o acesso aos recursos da Nexo Saúde.</p>
                @else
                    <p>Sua assinatura está sendo analisada. Caso necessário, aguarde a atualização automática.</p>
                @endif
            </div>
        </section>

        <section class="nexo-subscription-grid">
            <div class="nexo-subscription-card">
                <span>Plano</span>
                <strong>Corretor Pro</strong>
                <small>CRM completo para corretor autônomo</small>
            </div>

            <div class="nexo-subscription-card">
                <span>Vencimento</span>
                <strong>{{ optional($vencimento)->format('d/m/Y') ?? '-' }}</strong>
                <small>Próxima cobrança programada</small>
            </div>

            <div class="nexo-subscription-card">
                <span>Fim do teste</span>
                <strong>{{ optional($fimTeste)->format('d/m/Y') ?? '-' }}</strong>
                <small>Período gratuito inicial</small>
            </div>

            <div class="nexo-subscription-card">
                <span>Forma de pagamento</span>

                <strong>
                    @if($cardBrand && $cardLastFour)
                        {{ $cardBrand }} •••• {{ $cardLastFour }}
                    @else
                        Não informado
                    @endif
                </strong>

                <small>Cartão autorizado para cobrança</small>
            </div>
        </section>

        <section class="nexo-subscription-panels">
            <div class="nexo-subscription-panel">
                <div class="nexo-panel-header">
                    <div>
                        <span>Pagamento</span>
                        <h3>Cartão cadastrado</h3>
                    </div>

                    <i class="bi bi-credit-card-2-front"></i>
                </div>

                <div class="nexo-card-preview">
                    <span>{{ $cardBrand ?? 'Cartão' }}</span>

                    <strong>
                        @if($cardBrand && $cardLastFour)
                            •••• •••• •••• {{ $cardLastFour }}
                        @else
                            Cartão não informado
                        @endif
                    </strong>

                    <small>
                        Seus dados sensíveis não são armazenados pela Nexo.
                    </small>
                </div>
            </div>

            <div class="nexo-subscription-panel">
                <div class="nexo-panel-header">
                    <div>
                        <span>Resumo</span>
                        <h3>Detalhes da cobrança</h3>
                    </div>

                    <i class="bi bi-calendar-check"></i>
                </div>

                <div class="nexo-billing-list">
                    <div>
                        <span>Valor</span>
                        <strong>R$ {{ number_format($valor, 2, ',', '.') }}</strong>
                    </div>

                    <div>
                        <span>Periodicidade</span>
                        <strong>Mensal</strong>
                    </div>

                    <div>
                        <span>Status</span>
                        <strong>{{ $statusLabel }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="nexo-subscription-actions">
            <button class="nexo-action-primary" type="button" data-nexo-modal-open="card">
                Atualizar cartão
            </button>

            <button class="nexo-action-outline" type="button" disabled>
                Atualizar assinatura
            </button>

            <button class="nexo-action-danger" type="button" data-nexo-modal-open="cancel">
                Cancelar assinatura
            </button>
        </section>
    </div>

    <div class="nexo-modal-backdrop" id="nexo-card-modal" aria-hidden="true">
        <div class="nexo-modal-card">
            <div class="nexo-modal-header">
                <div>
                    <span>Forma de pagamento</span>
                    <h3>Atualizar cartão</h3>
                </div>

                <button type="button" data-nexo-modal-close>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form method="post" action="{{ route('configuracoes.assinatura.cartao.update') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">CPF/CNPJ do titular</label>
                        <input name="billing_cpf_cnpj" class="form-control" placeholder="Digite o CPF ou CNPJ" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Nome impresso no cartão</label>
                        <input name="card_holder_name" class="form-control" placeholder="Nome como aparece no cartão" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Número do cartão</label>
                        <input name="card_number" class="form-control" placeholder="0000 0000 0000 0000" inputmode="numeric" autocomplete="cc-number" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Mês</label>
                        <input name="card_expiry_month" class="form-control" placeholder="MM" inputmode="numeric" maxlength="2" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Ano</label>
                        <input name="card_expiry_year" class="form-control" placeholder="AAAA" inputmode="numeric" maxlength="4" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">CVV</label>
                        <input name="card_ccv" class="form-control" placeholder="123" inputmode="numeric" maxlength="4" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">CEP do titular</label>
                        <input name="holder_postal_code" class="form-control" placeholder="01001-000">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Número</label>
                        <input name="holder_address_number" class="form-control" placeholder="100">
                    </div>
                </div>

                <div class="nexo-modal-warning">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>A Nexo não salva número completo nem CVV. Os dados são enviados com segurança para atualização da assinatura no Asaas.</span>
                </div>

                <button class="nexo-modal-submit">
                    Salvar novo cartão
                </button>
            </form>
        </div>
    </div>

    <div class="nexo-modal-backdrop" id="nexo-cancel-modal" aria-hidden="true">
        <div class="nexo-modal-card">
            <div class="nexo-modal-header">
                <div>
                    <span>Cancelamento</span>
                    <h3>Cancelar assinatura</h3>
                </div>

                <button type="button" data-nexo-modal-close>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="nexo-cancel-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>

                <div>
                    <strong>Antes de cancelar</strong>
                    <p>Ao cancelar, novas cobranças serão interrompidas e sua conta poderá perder acesso aos recursos premium da Nexo Saúde.</p>
                </div>
            </div>

            <form method="post" action="{{ route('configuracoes.assinatura.cancelar') }}">
                @csrf

                <label class="nexo-cancel-check">
                    <input type="checkbox" name="confirmar_cancelamento" value="1" required>
                    <span>Confirmo que desejo cancelar minha assinatura.</span>
                </label>

                <button class="nexo-modal-submit is-danger">
                    Confirmar cancelamento
                </button>
            </form>
        </div>
    </div>

    <style>
        .nexo-subscription-page {
            display: grid;
            gap: 22px;
        }

        .nexo-subscription-alert {
            padding: 14px 16px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 850;
        }

        .nexo-subscription-alert.is-success {
            background: #ECFDF5;
            color: #166534;
        }

        .nexo-subscription-alert.is-danger {
            background: #FEF2F2;
            color: #B91C1C;
        }

        .nexo-subscription-hero {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding: 28px;
            border-radius: 28px;
            color: #FFFFFF;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.28), transparent 32%),
                linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            box-shadow: 0 18px 45px rgba(6, 28, 63, 0.18);
        }

        .nexo-subscription-kicker {
            display: inline-flex;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #DDEBFF;
            font-size: 0.76rem;
            font-weight: 900;
            margin-bottom: 12px;
        }

        .nexo-subscription-hero h2 {
            margin: 0 0 8px;
            font-size: clamp(1.8rem, 3vw, 2.7rem);
            font-weight: 950;
            letter-spacing: -0.05em;
        }

        .nexo-subscription-hero p {
            margin: 0;
            max-width: 620px;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.55;
        }

        .nexo-subscription-price {
            min-width: 230px;
            padding: 20px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.14);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .nexo-subscription-price span,
        .nexo-subscription-price small {
            color: rgba(255, 255, 255, 0.74);
            font-size: 0.78rem;
            font-weight: 800;
        }

        .nexo-subscription-price strong {
            display: block;
            margin: 6px 0;
            font-size: 1.9rem;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .nexo-subscription-status {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 22px;
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(6, 28, 63, 0.05);
        }

        .nexo-subscription-status-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex: 0 0 auto;
        }

        .nexo-subscription-status.is-success .nexo-subscription-status-icon {
            background: #ECFDF5;
            color: #15803D;
        }

        .nexo-subscription-status.is-warning .nexo-subscription-status-icon {
            background: #FFFBEB;
            color: #B45309;
        }

        .nexo-subscription-status.is-danger .nexo-subscription-status-icon {
            background: #FEF2F2;
            color: #B91C1C;
        }

        .nexo-subscription-status.is-neutral .nexo-subscription-status-icon {
            background: #EAF3FF;
            color: #2F80ED;
        }

        .nexo-subscription-status span {
            display: block;
            color: #64748B;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .nexo-subscription-status strong {
            display: block;
            color: #061C3F;
            font-size: 1.45rem;
            font-weight: 950;
            letter-spacing: -0.04em;
            margin-bottom: 5px;
        }

        .nexo-subscription-status p {
            margin: 0;
            color: #64748B;
            line-height: 1.45;
        }

        .nexo-subscription-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .nexo-subscription-card {
            padding: 18px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(6, 28, 63, 0.05);
        }

        .nexo-subscription-card span,
        .nexo-billing-list span,
        .nexo-panel-header span {
            display: block;
            color: #64748B;
            font-size: 0.74rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 5px;
        }

        .nexo-subscription-card strong {
            display: block;
            color: #061C3F;
            font-size: 1.04rem;
            font-weight: 950;
        }

        .nexo-subscription-card small {
            display: block;
            color: #64748B;
            font-size: 0.78rem;
            margin-top: 4px;
        }

        .nexo-subscription-panels {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .nexo-subscription-panel {
            padding: 22px;
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(6, 28, 63, 0.05);
        }

        .nexo-panel-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .nexo-panel-header h3 {
            margin: 0;
            color: #061C3F;
            font-size: 1.28rem;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .nexo-panel-header i {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            background: #EAF3FF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .nexo-card-preview {
            padding: 20px;
            border-radius: 22px;
            color: #FFFFFF;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.18), transparent 34%),
                linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
        }

        .nexo-card-preview span {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.78rem;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .nexo-card-preview strong {
            display: block;
            font-size: 1.4rem;
            font-weight: 950;
            letter-spacing: 0.08em;
        }

        .nexo-card-preview small {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            margin-top: 16px;
        }

        .nexo-billing-list {
            display: grid;
            gap: 10px;
        }

        .nexo-billing-list div {
            padding: 14px;
            border-radius: 16px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
        }

        .nexo-billing-list strong {
            display: block;
            color: #061C3F;
            font-size: 0.96rem;
            font-weight: 900;
        }

        .nexo-subscription-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .nexo-action-primary,
        .nexo-action-outline,
        .nexo-action-danger {
            min-height: 46px;
            padding: 0 18px;
            border-radius: 14px;
            font-weight: 950;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-action-primary {
            border: 0;
            color: #FFFFFF;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            box-shadow: 0 14px 30px rgba(47, 128, 237, 0.22);
        }

        .nexo-action-outline {
            border: 1px solid #D8E2EF;
            background: #FFFFFF;
            color: #061C3F;
        }

        .nexo-action-danger {
            border: 1px solid #FCA5A5;
            background: #FFFFFF;
            color: #B91C1C;
        }

        .nexo-action-primary:disabled,
        .nexo-action-outline:disabled,
        .nexo-action-danger:disabled {
            opacity: 0.56;
            cursor: not-allowed;
        }

        .nexo-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(6, 28, 63, 0.56);
            backdrop-filter: blur(6px);
        }

        .nexo-modal-backdrop.is-open {
            display: flex;
        }

        .nexo-modal-card {
            width: min(620px, 100%);
            max-height: 92vh;
            overflow-y: auto;
            padding: 26px;
            border-radius: 28px;
            background: #FFFFFF;
            box-shadow: 0 30px 80px rgba(6, 28, 63, 0.26);
        }

        .nexo-modal-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .nexo-modal-header span {
            display: block;
            color: #64748B;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 5px;
        }

        .nexo-modal-header h3 {
            color: #061C3F;
            font-size: 1.6rem;
            font-weight: 950;
            letter-spacing: -0.05em;
            margin: 0;
        }

        .nexo-modal-header button {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 12px;
            background: #F1F5F9;
            color: #061C3F;
        }

        .nexo-modal-warning,
        .nexo-cancel-warning {
            margin-top: 18px;
            padding: 14px;
            border-radius: 16px;
            display: flex;
            gap: 10px;
            line-height: 1.45;
            font-size: 0.86rem;
            font-weight: 750;
        }

        .nexo-modal-warning {
            background: #ECFDF5;
            color: #166534;
        }

        .nexo-cancel-warning {
            background: #FEF2F2;
            color: #B91C1C;
            margin-bottom: 18px;
        }

        .nexo-cancel-warning strong {
            display: block;
            color: #7F1D1D;
            margin-bottom: 4px;
        }

        .nexo-cancel-warning p {
            margin: 0;
        }

        .nexo-cancel-check {
            display: flex;
            gap: 10px;
            padding: 14px;
            border-radius: 16px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            color: #475569;
            font-weight: 750;
            margin-bottom: 16px;
        }

        .nexo-cancel-check input {
            margin-top: 4px;
            accent-color: #B91C1C;
        }

        .nexo-modal-submit {
            width: 100%;
            min-height: 52px;
            margin-top: 18px;
            border: 0;
            border-radius: 16px;
            color: #FFFFFF;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            font-weight: 950;
            box-shadow: 0 14px 30px rgba(47, 128, 237, 0.22);
        }

        .nexo-modal-submit.is-danger {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            box-shadow: 0 14px 30px rgba(220, 38, 38, 0.20);
        }

        @media (max-width: 1100px) {
            .nexo-subscription-hero {
                flex-direction: column;
            }

            .nexo-subscription-grid,
            .nexo-subscription-panels {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .nexo-subscription-grid,
            .nexo-subscription-panels {
                grid-template-columns: 1fr;
            }

            .nexo-subscription-hero,
            .nexo-subscription-status,
            .nexo-subscription-panel {
                padding: 18px;
                border-radius: 22px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const openButtons = document.querySelectorAll('[data-nexo-modal-open]');
            const closeButtons = document.querySelectorAll('[data-nexo-modal-close]');

            openButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const modalType = button.getAttribute('data-nexo-modal-open');
                    const modal = document.getElementById(modalType === 'card' ? 'nexo-card-modal' : 'nexo-cancel-modal');

                    if (modal) {
                        modal.classList.add('is-open');
                        modal.setAttribute('aria-hidden', 'false');
                    }
                });
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const modal = button.closest('.nexo-modal-backdrop');

                    if (modal) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                    }
                });
            });

            document.querySelectorAll('.nexo-modal-backdrop').forEach(function (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                    }
                });
            });
        });
    </script>
</x-configuracoes.layout>