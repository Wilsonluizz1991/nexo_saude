<x-layouts.app title="Assinatura | Nexo Saúde">
    @php
        $valor = $assinatura?->valor ?? $assinatura?->valor_assinatura ?? 49.90;
        $trialEndsAt = $assinatura?->trial_ends_at ?? $assinatura?->data_fim_teste_gratis;
        $nextPaymentAt = $assinatura?->next_payment_at ?? $assinatura?->vencimento_assinatura;
        $canceledAt = $assinatura?->canceled_at;
        $statusAssinatura = $assinatura?->status ?? $assinatura?->status_assinatura;
    @endphp

    <main class="nexo-subscription-lock-page">
        <section class="nexo-subscription-lock-card">
            <div class="nexo-subscription-lock-hero">
                <div class="nexo-subscription-lock-logo">
                    <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
                </div>

                <span class="nexo-subscription-lock-badge">
                    <i class="bi bi-shield-lock-fill"></i>
                    Acesso controlado por assinatura
                </span>

                <h1>
                    Sua assinatura precisa de atenção
                </h1>

                <p>
                    Para continuar usando a Nexo Saúde, regularize sua assinatura ou reative seu plano.
                </p>

                <div class="nexo-subscription-lock-benefits">
                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>CRM completo para gestão comercial</span>
                    </div>

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Carteira, propostas e implantações em um só lugar</span>
                    </div>

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Assinatura mensal de R$ 49,90</span>
                    </div>
                </div>
            </div>

            <div class="nexo-subscription-lock-content">
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

                <div class="nexo-subscription-status-panel">
                    <span class="nexo-subscription-status-label">
                        Status atual
                    </span>

                    @if($statusComercial === 'teste_gratis')
                        <strong class="is-warning">
                            Teste gratuito em andamento
                        </strong>

                        <p>
                            Ainda restam {{ $diasRestantesTeste }} dia(s) do seu teste gratuito.
                        </p>
                    @elseif($statusComercial === 'inadimplente')
                        <strong class="is-danger">
                            Pagamento pendente
                        </strong>

                        <p>
                            Identificamos uma pendência na sua assinatura. Assim que o pagamento for confirmado, o acesso será restaurado automaticamente.
                        </p>
                    @elseif($statusComercial === 'cancelada')
                        <strong class="is-danger">
                            Assinatura cancelada
                        </strong>

                        <p>
                            Sua conta continua preservada. Para voltar a usar a plataforma, reative sua assinatura com um novo cartão.
                        </p>
                    @elseif($statusComercial === 'sem_assinatura')
                        <strong class="is-danger">
                            Assinatura não encontrada
                        </strong>

                        <p>
                            Não encontramos uma assinatura ativa vinculada à sua conta.
                        </p>
                    @else
                        <strong class="is-danger">
                            Acesso temporariamente bloqueado
                        </strong>

                        <p>
                            Sua assinatura não está liberada para acesso neste momento.
                        </p>
                    @endif
                </div>

                <div class="nexo-subscription-details-grid">
                    <div>
                        <span>Plano</span>
                        <strong>Profissional</strong>
                    </div>

                    <div>
                        <span>Valor</span>
                        <strong>R$ {{ number_format($valor, 2, ',', '.') }}/mês</strong>
                    </div>

                    @if($statusComercial === 'cancelada')
                        <div>
                            <span>Cancelada em</span>
                            <strong>{{ optional($canceledAt)->format('d/m/Y') ?? '-' }}</strong>
                        </div>

                        <div>
                            <span>Situação</span>
                            <strong>Reativação disponível</strong>
                        </div>
                    @else
                        <div>
                            <span>Fim do teste</span>
                            <strong>{{ optional($trialEndsAt)->format('d/m/Y') ?? '-' }}</strong>
                        </div>

                        <div>
                            <span>Próxima cobrança</span>
                            <strong>{{ optional($nextPaymentAt)->format('d/m/Y') ?? '-' }}</strong>
                        </div>
                    @endif

                    <div>
                        <span>Cartão</span>
                        <strong>
                            @if($assinatura?->card_brand && $assinatura?->card_last_four)
                                {{ strtoupper($assinatura->card_brand) }} •••• {{ $assinatura->card_last_four }}
                            @else
                                Não informado
                            @endif
                        </strong>
                    </div>

                    <div>
                        <span>Status da assinatura</span>

                        <strong>
                            @switch($statusAssinatura)
                                @case('ACTIVE')
                                @case('active')
                                    Assinatura ativa
                                    @break

                                @case('OVERDUE')
                                @case('overdue')
                                    Pagamento pendente
                                    @break

                                @case('PENDING')
                                @case('pending')
                                    Pagamento aguardando confirmação
                                    @break

                                @case('CANCELED')
                                @case('cancelled')
                                @case('canceled')
                                    Assinatura cancelada
                                    @break

                                @case('EXPIRED')
                                @case('expired')
                                    Assinatura expirada
                                    @break

                                @case('TRIAL')
                                @case('trialing')
                                @case('teste_gratis')
                                    Teste gratuito
                                    @break

                                @default
                                    Em análise
                            @endswitch
                        </strong>
                    </div>
                </div>

                <div class="nexo-subscription-actions">
                    @if($statusComercial === 'cancelada')
                        <button type="button" class="nexo-subscription-primary-action" data-nexo-modal-open="reactivate">
                            Reativar assinatura
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    @else
                        <a href="{{ route('configuracoes.assinatura') }}" class="nexo-subscription-primary-action">
                            Ver minha assinatura
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif

                    <form method="post" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="nexo-subscription-secondary-action">
                            Sair da conta
                        </button>
                    </form>
                </div>

                @if($statusComercial === 'cancelada')
                    <div class="nexo-subscription-note is-warning">
                        <i class="bi bi-shield-check"></i>

                        <span>
                            Seus dados continuam preservados. Reative sua assinatura para voltar a acessar leads, clientes, propostas e pré-cadastros.
                        </span>
                    </div>
                @else
                    <div class="nexo-subscription-note">
                        <i class="bi bi-info-circle-fill"></i>

                        <span>
                            Assim que o pagamento for confirmado, seu acesso será liberado automaticamente.
                        </span>
                    </div>
                @endif
            </div>
        </section>

        @if($statusComercial === 'cancelada')
            <div class="nexo-modal-backdrop" id="nexo-reactivate-modal" aria-hidden="true">
                <div class="nexo-modal-card">
                    <div class="nexo-modal-header">
                        <div>
                            <span>Reativação</span>
                            <h3>Reativar assinatura</h3>
                        </div>

                        <button type="button" data-nexo-modal-close>
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="nexo-reactivate-info">
                        <i class="bi bi-shield-check"></i>

                        <div>
                            <strong>Sua conta será preservada</strong>
                            <p>Seus leads, clientes, propostas, pré-cadastros e histórico continuarão disponíveis após a reativação.</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('assinatura.reativar') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">CPF/CNPJ do titular</label>
                                <input name="billing_cpf_cnpj" class="form-control" placeholder="Digite o CPF ou CNPJ" value="{{ old('billing_cpf_cnpj') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Telefone do titular</label>
                                <input name="holder_phone" class="form-control" placeholder="(11) 99999-9999" value="{{ old('holder_phone', auth()->user()?->telefone) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Nome impresso no cartão</label>
                                <input name="card_holder_name" class="form-control" placeholder="Nome como aparece no cartão" value="{{ old('card_holder_name', auth()->user()?->name) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Número do cartão</label>
                                <input name="card_number" class="form-control" placeholder="0000 0000 0000 0000" inputmode="numeric" autocomplete="cc-number" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Mês</label>
                                <input name="card_expiry_month" class="form-control" placeholder="MM" inputmode="numeric" maxlength="2" value="{{ old('card_expiry_month') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Ano</label>
                                <input name="card_expiry_year" class="form-control" placeholder="AAAA" inputmode="numeric" maxlength="4" value="{{ old('card_expiry_year') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">CVV</label>
                                <input name="card_ccv" class="form-control" placeholder="123" inputmode="numeric" maxlength="4" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">CEP do titular</label>
                                <input name="holder_postal_code" class="form-control" placeholder="01001-000" value="{{ old('holder_postal_code') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Número</label>
                                <input name="holder_address_number" class="form-control" placeholder="100" value="{{ old('holder_address_number') }}">
                            </div>
                        </div>

                        <div class="nexo-modal-warning">
                            <i class="bi bi-lock-fill"></i>
                            <span>A cobrança de R$ 49,90 será processada para reativar sua assinatura mensal.</span>
                        </div>

                        <button class="nexo-modal-submit">
                            Reativar agora
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </main>

    <style>
        .nexo-subscription-lock-page {
            min-height: 100vh;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                linear-gradient(135deg, #061C3F 0%, #0D2F57 48%, #F4F7FB 48%, #FFFFFF 100%);
        }

        .nexo-subscription-lock-card {
            width: min(1120px, 100%);
            display: grid;
            grid-template-columns: 0.88fr 1.12fr;
            overflow: hidden;
            border-radius: 32px;
            background: #FFFFFF;
            box-shadow: 0 32px 90px rgba(6, 28, 63, 0.24);
        }

        .nexo-subscription-lock-hero {
            position: relative;
            padding: 48px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.28), transparent 32%),
                linear-gradient(180deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
            overflow: hidden;
        }

        .nexo-subscription-lock-hero::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            right: -80px;
            bottom: -80px;
            background: rgba(47, 128, 237, 0.16);
        }

        .nexo-subscription-lock-logo,
        .nexo-subscription-lock-badge,
        .nexo-subscription-lock-hero h1,
        .nexo-subscription-lock-hero p,
        .nexo-subscription-lock-benefits {
            position: relative;
            z-index: 1;
        }

        .nexo-subscription-lock-logo {
            margin-bottom: 30px;
        }

        .nexo-subscription-lock-logo img {
            width: 100%;
            max-width: 240px;
            display: block;
        }

        .nexo-subscription-lock-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 9px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.11);
            color: #DDEBFF;
            font-size: 0.76rem;
            font-weight: 900;
            margin-bottom: 20px;
        }

        .nexo-subscription-lock-hero h1 {
            font-size: clamp(2rem, 3vw, 3.4rem);
            line-height: 1.02;
            font-weight: 950;
            letter-spacing: -0.07em;
            margin: 0 0 18px;
        }

        .nexo-subscription-lock-hero p {
            color: rgba(255, 255, 255, 0.84);
            font-size: 1rem;
            line-height: 1.55;
            max-width: 430px;
            margin: 0;
        }

        .nexo-subscription-lock-benefits {
            display: grid;
            gap: 12px;
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }

        .nexo-subscription-lock-benefits div {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.93);
        }

        .nexo-subscription-lock-benefits i {
            color: #7DB5FF;
        }

        .nexo-subscription-lock-content {
            padding: 48px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.07), transparent 26%),
                #FFFFFF;
        }

        .nexo-subscription-alert {
            min-height: 52px;
            padding: 14px 16px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 850;
            margin-bottom: 16px;
        }

        .nexo-subscription-alert.is-success {
            background: #ECFDF5;
            color: #166534;
        }

        .nexo-subscription-alert.is-danger {
            background: #FEF2F2;
            color: #B91C1C;
        }

        .nexo-subscription-status-panel {
            padding: 24px;
            border-radius: 24px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            margin-bottom: 20px;
        }

        .nexo-subscription-status-label {
            display: inline-flex;
            margin-bottom: 8px;
            color: #64748B;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .nexo-subscription-status-panel strong {
            display: block;
            font-size: 1.7rem;
            font-weight: 950;
            letter-spacing: -0.04em;
            margin-bottom: 6px;
        }

        .nexo-subscription-status-panel strong.is-warning {
            color: #B45309;
        }

        .nexo-subscription-status-panel strong.is-danger {
            color: #B91C1C;
        }

        .nexo-subscription-status-panel p {
            color: #64748B;
            line-height: 1.5;
            margin: 0;
        }

        .nexo-subscription-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 22px;
        }

        .nexo-subscription-details-grid div {
            padding: 16px;
            border-radius: 18px;
            background: #F4F8FD;
            border: 1px solid #DDE8F5;
        }

        .nexo-subscription-details-grid span {
            display: block;
            color: #64748B;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 5px;
        }

        .nexo-subscription-details-grid strong {
            color: #061C3F;
            font-size: 0.96rem;
            font-weight: 950;
        }

        .nexo-subscription-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nexo-subscription-primary-action,
        .nexo-subscription-secondary-action {
            min-height: 52px;
            padding: 0 22px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 950;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .nexo-subscription-primary-action {
            border: 0;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.24);
        }

        .nexo-subscription-secondary-action {
            border: 1px solid #D8E2EF;
            background: #FFFFFF;
            color: #061C3F;
        }

        .nexo-subscription-primary-action:hover,
        .nexo-subscription-secondary-action:hover {
            transform: translateY(-2px);
        }

        .nexo-subscription-note {
            margin-top: 20px;
            padding: 14px;
            border-radius: 16px;
            background: #ECFDF5;
            color: #166534;
            display: flex;
            gap: 10px;
            font-size: 0.84rem;
            font-weight: 750;
            line-height: 1.45;
        }

        .nexo-subscription-note.is-warning {
            background: #EAF3FF;
            color: #1D4ED8;
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
            width: min(640px, 100%);
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

        .nexo-reactivate-info {
            margin-bottom: 18px;
            padding: 16px;
            border-radius: 18px;
            background: #EAF3FF;
            color: #1D4ED8;
            display: flex;
            gap: 12px;
        }

        .nexo-reactivate-info i {
            font-size: 1.4rem;
        }

        .nexo-reactivate-info strong {
            display: block;
            color: #061C3F;
            font-weight: 950;
            margin-bottom: 4px;
        }

        .nexo-reactivate-info p {
            margin: 0;
            color: #475569;
            line-height: 1.45;
        }

        .nexo-modal-warning {
            margin-top: 18px;
            padding: 14px;
            border-radius: 16px;
            display: flex;
            gap: 10px;
            line-height: 1.45;
            font-size: 0.86rem;
            font-weight: 750;
            background: #ECFDF5;
            color: #166534;
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

        @media (max-width: 992px) {
            .nexo-subscription-lock-card {
                grid-template-columns: 1fr;
            }

            .nexo-subscription-lock-hero,
            .nexo-subscription-lock-content {
                padding: 32px 24px;
            }
        }

        @media (max-width: 576px) {
            .nexo-subscription-lock-page {
                padding: 12px;
            }

            .nexo-subscription-lock-card {
                border-radius: 24px;
            }

            .nexo-subscription-details-grid {
                grid-template-columns: 1fr;
            }

            .nexo-subscription-primary-action,
            .nexo-subscription-secondary-action {
                width: 100%;
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
                    const modal = document.getElementById('nexo-' + modalType + '-modal');

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
</x-layouts.app>