<x-layouts.app title="Criar conta | Nexo Saúde">
    <main class="nexo-register-page">
        <section class="nexo-register-card">
            <div class="nexo-register-brand">
                <div class="nexo-register-brand-content">
                    <div class="nexo-register-logo">
                        <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
                    </div>

                    <span class="nexo-register-badge">
                        <i class="bi bi-stars"></i>
                        Plataforma premium para corretores de alta performance
                    </span>

                    <h1>
                        Teste a Nexo Saúde por 30 dias grátis
                    </h1>

                    <p>
                        Organize sua operação, fortaleça sua carteira e venda como um corretor de alto nível.
                    </p>

                    <div class="nexo-register-benefits">
                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>30 dias grátis, sem cobrança hoje</span>
                        </div>

                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Depois, apenas R$ 49,90 por mês</span>
                        </div>

                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Cancele antes do fim do teste se não quiser continuar</span>
                        </div>
                    </div>

                    <div class="nexo-register-highlight">
                        <span>Plano Profissional</span>

                        <strong>R$ 0 hoje</strong>

                        <small>
                            Após 30 dias: R$ 49,90/mês no cartão cadastrado.
                        </small>
                    </div>
                </div>
            </div>

            <div class="nexo-register-form-area">
                <div class="nexo-register-form-header">
                    <span>Comece agora</span>

                    <h2>
                        Criar conta de corretor
                    </h2>

                    <p>
                        Cadastre sua conta e forma de pagamento para liberar seu teste gratuito.
                    </p>
                </div>

                <form method="post" action="{{ route('register.store') }}" id="nexo-register-form">
                    @csrf

                    <input type="hidden" name="card_brand" id="card_brand" value="">
                    <input type="hidden" name="card_last_four" id="card_last_four" value="">

                    <div class="nexo-form-scroll">
                        <div class="nexo-plan-summary">
                            <div>
                                <span>Hoje</span>

                                <strong>R$ 0,00</strong>

                                <small>30 dias grátis</small>
                            </div>

                            <div>
                                <span>Depois do teste</span>

                                <strong>R$ 49,90/mês</strong>

                                <small>Cobrança automática mensal</small>
                            </div>
                        </div>

                        <div class="nexo-section-title">
                            <i class="bi bi-person-badge"></i>

                            <div>
                                <strong>Dados do corretor</strong>

                                <span>
                                    Essas informações serão usadas para criar sua conta.
                                </span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Nome completo
                                </label>

                                <input
                                    name="name"
                                    class="form-control"
                                    placeholder="Seu nome completo"
                                    value="{{ old('name') }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    CPF/CNPJ
                                </label>

                                <input
                                    name="billing_cpf_cnpj"
                                    id="billing_cpf_cnpj"
                                    class="form-control"
                                    placeholder="Digite seu CPF ou CNPJ"
                                    value="{{ old('billing_cpf_cnpj') }}"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    maxlength="18"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Telefone / WhatsApp
                                </label>

                                <input
                                    name="telefone"
                                    id="telefone"
                                    class="form-control"
                                    placeholder="(11) 99999-9999"
                                    value="{{ old('telefone') }}"
                                    inputmode="numeric"
                                    autocomplete="tel"
                                    maxlength="15"
                                    minlength="14"
                                    required
                                >

                                <small class="nexo-field-feedback" id="telefone_feedback"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    E-mail
                                </label>

                                <input
                                    name="email"
                                    type="email"
                                    class="form-control"
                                    placeholder="voce@email.com"
                                    value="{{ old('email') }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Senha
                                </label>

                                <input
                                    name="password"
                                    type="password"
                                    class="form-control"
                                    placeholder="Digite sua senha"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Confirmar senha
                                </label>

                                <input
                                    name="password_confirmation"
                                    type="password"
                                    class="form-control"
                                    placeholder="Confirme sua senha"
                                    required
                                >
                            </div>
                        </div>

                        <div class="nexo-section-title mt-4">
                            <i class="bi bi-credit-card-2-front"></i>

                            <div>
                                <strong>Forma de pagamento</strong>

                                <span>
                                    Necessária para ativar a continuidade da assinatura após o teste.
                                </span>
                            </div>
                        </div>

                        <div class="nexo-payment-commercial-box">
                            <div>
                                <strong>
                                    Nada será cobrado agora.
                                </strong>

                                <span>
                                    Você só será cobrado após o período gratuito de 30 dias.
                                </span>
                            </div>

                            <div class="nexo-card-flags" aria-label="Cartões aceitos">
                                <img src="{{ asset('assets/visa.svg') }}" alt="Visa" data-brand-flag="visa">
                                <img src="{{ asset('assets/mastercard.svg') }}" alt="Mastercard" data-brand-flag="mastercard">
                                <img src="{{ asset('assets/elo.svg') }}" alt="Elo" data-brand-flag="elo">
                                <img src="{{ asset('assets/amex.svg') }}" alt="American Express" data-brand-flag="amex">
                                <img src="{{ asset('assets/hipercard.svg') }}" alt="Hipercard" data-brand-flag="hipercard">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label">
                                    Nome impresso no cartão
                                </label>

                                <input
                                    name="card_holder_name"
                                    class="form-control"
                                    placeholder="Nome como aparece no cartão"
                                    value="{{ old('card_holder_name') }}"
                                    autocomplete="cc-name"
                                    required
                                >
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    Número do cartão
                                </label>

                                <div class="nexo-card-number-wrapper">
                                    <input
                                        name="card_number"
                                        id="card_number"
                                        class="form-control"
                                        placeholder="0000 0000 0000 0000"
                                        inputmode="numeric"
                                        autocomplete="cc-number"
                                        maxlength="23"
                                        required
                                    >

                                    <div class="nexo-detected-card" id="detected_card">
                                        <i class="bi bi-credit-card-2-front" id="detected_card_icon"></i>
                                        <img src="" alt="" id="detected_card_logo">
                                    </div>
                                </div>

                                <small class="nexo-field-feedback" id="card_number_feedback"></small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Mês
                                </label>

                                <input
                                    name="card_expiry_month"
                                    id="card_expiry_month"
                                    class="form-control"
                                    placeholder="MM"
                                    inputmode="numeric"
                                    autocomplete="cc-exp-month"
                                    maxlength="2"
                                    minlength="2"
                                    required
                                >

                                <small class="nexo-field-feedback" id="card_expiry_month_feedback"></small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Ano
                                </label>

                                <input
                                    name="card_expiry_year"
                                    id="card_expiry_year"
                                    class="form-control"
                                    placeholder="AAAA"
                                    inputmode="numeric"
                                    autocomplete="cc-exp-year"
                                    maxlength="4"
                                    minlength="4"
                                    required
                                >

                                <small class="nexo-field-feedback" id="card_expiry_year_feedback"></small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    CVV
                                </label>

                                <input
                                    name="card_ccv"
                                    id="card_ccv"
                                    class="form-control"
                                    placeholder="123"
                                    inputmode="numeric"
                                    autocomplete="cc-csc"
                                    maxlength="4"
                                    minlength="3"
                                    required
                                >

                                <small class="nexo-field-feedback" id="card_ccv_feedback"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    CEP do titular do cartão
                                </label>

                                <input
                                    name="holder_postal_code"
                                    id="holder_postal_code"
                                    class="form-control"
                                    placeholder="00000-000"
                                    value="{{ old('holder_postal_code') }}"
                                    inputmode="numeric"
                                    autocomplete="postal-code"
                                    maxlength="9"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Número do endereço
                                </label>

                                <input
                                    name="holder_address_number"
                                    class="form-control"
                                    placeholder="Ex.: 123"
                                    value="{{ old('holder_address_number') }}"
                                    autocomplete="address-line2"
                                    maxlength="20"
                                    required
                                >
                            </div>
                        </div>

                        <div class="nexo-payment-security">
                            <i class="bi bi-shield-lock-fill"></i>

                            <span>
                                Pagamento seguro. O cartão será usado apenas para a cobrança automática de R$ 49,90/mês após o fim dos 30 dias grátis.
                            </span>
                        </div>

                        <label class="nexo-terms-check">
                            <input
                                type="checkbox"
                                name="accepted_terms"
                                value="1"
                                required
                            >

                            <span>
                                Declaro que aceito iniciar meu período gratuito de 30 dias. Após esse prazo, autorizo a cobrança automática mensal de R$ 49,90 no cartão cadastrado, salvo cancelamento antes do fim do teste.
                            </span>
                        </label>
                    </div>

                    <button class="nexo-register-submit" id="nexo-register-submit">
                        Iniciar 30 dias grátis

                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <div class="nexo-register-footer">
                    <span>
                        Já possui conta?
                    </span>

                    <a href="{{ route('login') }}">
                        Entrar na plataforma
                    </a>
                </div>
            </div>
        </section>
    </main>

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            min-height: 100vh;
        }

        .nexo-register-page {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            overflow: hidden;
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                linear-gradient(135deg, #061C3F 0%, #0D2F57 48%, #F4F7FB 48%, #FFFFFF 100%);
        }

        .nexo-register-card {
            width: min(1140px, 100%);
            height: calc(100vh - 36px);
            max-height: 860px;
            min-height: 680px;
            display: grid;
            grid-template-columns: 0.92fr 1.08fr;
            overflow: hidden;
            border-radius: 32px;
            background: #FFFFFF;
            box-shadow: 0 32px 90px rgba(6, 28, 63, 0.24);
        }

        .nexo-register-brand {
            position: relative;
            height: 100%;
            padding: 42px 52px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.28), transparent 32%),
                linear-gradient(180deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
            overflow: hidden;
        }

        .nexo-register-brand-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .nexo-register-brand::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            right: -80px;
            bottom: -80px;
            background: rgba(47, 128, 237, 0.16);
        }

        .nexo-register-brand::before {
            content: "";
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 999px;
            left: -70px;
            top: 48%;
            background: rgba(125, 181, 255, 0.08);
            filter: blur(2px);
        }

        .nexo-register-logo {
            margin-bottom: 28px;
        }

        .nexo-register-logo img {
            width: 100%;
            max-width: 250px;
            display: block;
        }

        .nexo-register-badge {
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
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.10);
        }

        .nexo-register-brand h1 {
            font-size: clamp(2.2rem, 3vw, 3.55rem);
            line-height: 1.02;
            font-weight: 950;
            letter-spacing: -0.07em;
            margin: 0 0 18px;
            max-width: 100%;
            overflow-wrap: anywhere;
        }

        .nexo-register-brand p {
            color: rgba(255, 255, 255, 0.84);
            font-size: 1.05rem;
            line-height: 1.55;
            max-width: 430px;
            margin: 0;
            overflow-wrap: break-word;
        }

        .nexo-register-benefits {
            display: grid;
            gap: 12px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }

        .nexo-register-benefits div {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.93);
        }

        .nexo-register-benefits i {
            color: #7DB5FF;
            font-size: 1rem;
        }

        .nexo-register-highlight {
            margin-top: 34px;
            padding: 22px;
            border-radius: 24px;
            background: linear-gradient(
                180deg,
                rgba(255, 255, 255, 0.12) 0%,
                rgba(255, 255, 255, 0.06) 100%
            );
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
            min-width: 0;
        }

        .nexo-register-highlight span {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.78rem;
            font-weight: 850;
            margin-bottom: 5px;
        }

        .nexo-register-highlight strong {
            display: block;
            font-size: 2rem;
            font-weight: 950;
            color: #FFFFFF;
            letter-spacing: -0.04em;
        }

        .nexo-register-highlight small {
            display: block;
            margin-top: 7px;
            color: rgba(255, 255, 255, 0.74);
            font-size: 0.88rem;
            line-height: 1.4;
            overflow-wrap: break-word;
        }

        .nexo-register-form-area {
            height: 100%;
            padding: 58px 44px 34px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.07), transparent 26%),
                #FFFFFF;
            min-height: 0;
            overflow: hidden;
        }

        .nexo-register-form-header {
            flex: 0 0 auto;
            margin-bottom: 26px;
            padding-top: 6px;
        }

        .nexo-register-form-header span {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.76rem;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .nexo-register-form-header h2 {
            color: #061C3F;
            font-size: 1.9rem;
            font-weight: 950;
            letter-spacing: -0.04em;
            margin: 0 0 6px;
        }

        .nexo-register-form-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-register-form-area form {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .nexo-form-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 8px;
        }

        .nexo-form-scroll::-webkit-scrollbar {
            width: 7px;
        }

        .nexo-form-scroll::-webkit-scrollbar-track {
            background: #EEF4FB;
            border-radius: 999px;
        }

        .nexo-form-scroll::-webkit-scrollbar-thumb {
            background: #B9CCE3;
            border-radius: 999px;
        }

        .nexo-plan-summary {
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            gap: 12px;
            margin-bottom: 22px;
        }

        .nexo-plan-summary div {
            padding: 16px;
            border-radius: 20px;
            background: linear-gradient(180deg, #F8FBFF 0%, #EEF6FF 100%);
            border: 1px solid #DDEBFA;
        }

        .nexo-plan-summary span {
            display: block;
            color: #64748B;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 5px;
        }

        .nexo-plan-summary strong {
            display: block;
            color: #061C3F;
            font-size: 1.15rem;
            font-weight: 950;
            letter-spacing: -0.03em;
        }

        .nexo-plan-summary small {
            display: block;
            color: #64748B;
            margin-top: 3px;
            font-size: 0.78rem;
        }

        .nexo-section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .nexo-section-title i {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            background: #EAF3FF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex: 0 0 auto;
        }

        .nexo-section-title strong {
            display: block;
            color: #061C3F;
            font-size: 0.98rem;
            font-weight: 950;
            line-height: 1.2;
        }

        .nexo-section-title span {
            display: block;
            color: #64748B;
            font-size: 0.82rem;
            line-height: 1.3;
        }

        .nexo-register-form-area .form-label {
            color: #061C3F;
            font-weight: 850;
            margin-bottom: 7px;
            font-size: 0.88rem;
        }

        .nexo-register-form-area .form-control {
            min-height: 50px;
            border-radius: 14px;
            border: 1px solid #D8E2EF;
            padding: 12px 15px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .nexo-register-form-area .form-control:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-register-form-area .form-control.is-valid {
            border-color: #22C55E;
            background: #F0FDF4;
        }

        .nexo-register-form-area .form-control.is-invalid {
            border-color: #EF4444;
            background: #FEF2F2;
        }

        .nexo-field-feedback {
            display: none;
            margin-top: 6px;
            font-size: 0.76rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .nexo-field-feedback.is-valid {
            display: block;
            color: #15803D;
        }

        .nexo-field-feedback.is-invalid {
            display: block;
            color: #B91C1C;
        }

        .nexo-payment-commercial-box {
            display: grid;
            gap: 12px;
            padding: 16px;
            border-radius: 20px;
            background: #F4F8FD;
            border: 1px solid #DDE8F5;
            margin-bottom: 14px;
        }

        .nexo-payment-commercial-box strong {
            display: block;
            color: #061C3F;
            font-size: 0.95rem;
            font-weight: 950;
            margin-bottom: 3px;
        }

        .nexo-payment-commercial-box span {
            color: #64748B;
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .nexo-card-flags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .nexo-card-flags img {
            height: 42px;
            min-width: 68px;
            padding: 8px 12px;
            border-radius: 12px;
            background: #FFFFFF;
            border: 1px solid #D8E2EF;
            object-fit: contain;
            box-shadow: 0 10px 22px rgba(6, 28, 63, 0.08);
            transition: transform 0.2s ease, opacity 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .nexo-card-flags img:hover {
            transform: translateY(-2px);
        }

        .nexo-card-flags.has-brand img {
            opacity: 0.35;
            filter: grayscale(1);
        }

        .nexo-card-flags.has-brand img.is-active {
            opacity: 1;
            filter: none;
            border-color: #2F80ED;
            box-shadow: 0 14px 30px rgba(47, 128, 237, 0.18);
            transform: translateY(-2px);
        }

        .nexo-card-number-wrapper {
            position: relative;
        }

        .nexo-card-number-wrapper .form-control {
            padding-right: 116px;
        }

        .nexo-detected-card {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            min-width: 90px;
            height: 34px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #F1F5F9;
            border: 1px solid #D8E2EF;
            color: #64748B;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            transition: 0.2s ease;
        }

        .nexo-detected-card.is-detected {
            background: #FFFFFF;
            border-color: #B9D8FF;
            box-shadow: 0 10px 22px rgba(47, 128, 237, 0.12);
        }

        .nexo-detected-card i {
            font-size: 0.9rem;
        }

        .nexo-detected-card img {
            display: none;
            max-width: 68px;
            max-height: 22px;
            object-fit: contain;
        }

        .nexo-detected-card.is-detected i {
            display: none;
        }

        .nexo-detected-card.is-detected img {
            display: block;
        }

        .nexo-payment-security {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 16px;
            padding: 14px;
            border-radius: 16px;
            background: #ECFDF5;
            color: #166534;
            font-size: 0.82rem;
            font-weight: 750;
            line-height: 1.45;
        }

        .nexo-payment-security i {
            margin-top: 2px;
        }

        .nexo-terms-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 16px;
            padding: 15px;
            border-radius: 16px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            color: #475569;
            font-size: 0.82rem;
            line-height: 1.45;
            cursor: pointer;
        }

        .nexo-terms-check input {
            margin-top: 4px;
            accent-color: #2F80ED;
        }

        .nexo-register-submit {
            flex: 0 0 auto;
            width: 100%;
            min-height: 56px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 950;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.24);
            transition: 0.2s ease;
            margin-top: 22px;
        }

        .nexo-register-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(47, 128, 237, 0.30);
        }

        .nexo-register-footer {
            flex: 0 0 auto;
            margin-top: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            color: #64748B;
            font-size: 0.92rem;
        }

        .nexo-register-footer a {
            color: #2F80ED;
            text-decoration: none;
            font-weight: 850;
            border-radius: 999px;
            padding: 6px 10px;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .nexo-register-footer a:hover {
            color: #1B6DFF;
            background: #EFF6FF;
            text-decoration: none;
        }

        @media (max-width: 992px) {
            html,
            body {
                height: auto;
                overflow-y: auto;
            }

            .nexo-register-page {
                height: auto;
                min-height: 100vh;
                padding: 16px;
                overflow: visible;
            }

            .nexo-register-card {
                height: auto;
                max-height: none;
                min-height: auto;
                grid-template-columns: 1fr;
                overflow: visible;
            }

            .nexo-register-brand {
                height: auto;
                padding: 32px 24px;
                overflow: hidden;
            }

            .nexo-register-brand-content {
                height: auto;
                justify-content: flex-start;
            }

            .nexo-register-form-area {
                height: auto;
                padding: 32px 24px;
                overflow: visible;
            }

            .nexo-register-form-area form {
                overflow: visible;
            }

            .nexo-form-scroll {
                overflow-y: visible;
                padding-right: 0;
            }

            .nexo-register-logo img {
                max-width: 220px;
            }

            .nexo-register-brand h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 576px) {
            .nexo-register-page {
                padding: 12px;
            }

            .nexo-register-card {
                border-radius: 24px;
            }

            .nexo-register-brand,
            .nexo-register-form-area {
                padding: 24px 18px;
            }

            .nexo-register-badge {
                width: 100%;
                white-space: normal;
                flex-wrap: wrap;
            }

            .nexo-register-brand h1 {
                font-size: 1.68rem;
                line-height: 1.12;
                letter-spacing: -0.03em;
            }

            .nexo-register-brand p,
            .nexo-register-benefits,
            .nexo-register-highlight {
                max-width: 100%;
            }

            .nexo-register-benefits div {
                align-items: flex-start;
            }

            .nexo-register-highlight {
                padding: 18px;
            }

            .nexo-register-form-header h2 {
                font-size: 1.6rem;
            }

            .nexo-plan-summary {
                grid-template-columns: 1fr;
            }

            .nexo-card-number-wrapper .form-control {
                padding-right: 15px;
            }

            .nexo-payment-commercial-box {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-card-flags {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .nexo-detected-card {
                position: static;
                transform: none;
                width: fit-content;
                margin-top: 8px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const onlyNumbers = function (value) {
                return value.replace(/\D/g, '');
            };

            const telefoneInput = document.getElementById('telefone');
            const telefoneFeedback = document.getElementById('telefone_feedback');
            const billingCpfCnpjInput = document.getElementById('billing_cpf_cnpj');
            const holderPostalCodeInput = document.getElementById('holder_postal_code');
            const cardNumberInput = document.getElementById('card_number');
            const cardNumberFeedback = document.getElementById('card_number_feedback');
            const cardExpiryMonthInput = document.getElementById('card_expiry_month');
            const cardExpiryMonthFeedback = document.getElementById('card_expiry_month_feedback');
            const cardExpiryYearInput = document.getElementById('card_expiry_year');
            const cardExpiryYearFeedback = document.getElementById('card_expiry_year_feedback');
            const cardCcvInput = document.getElementById('card_ccv');
            const cardCcvFeedback = document.getElementById('card_ccv_feedback');
            const cardBrandInput = document.getElementById('card_brand');
            const cardLastFourInput = document.getElementById('card_last_four');
            const detectedCard = document.getElementById('detected_card');
            const detectedCardIcon = document.getElementById('detected_card_icon');
            const detectedCardLogo = document.getElementById('detected_card_logo');
            const cardFlagsWrapper = document.querySelector('.nexo-card-flags');
            const cardFlags = document.querySelectorAll('[data-brand-flag]');

            const cardBrands = [
                {
                    key: 'visa',
                    label: 'Visa',
                    lengths: [13, 16, 19],
                    cvvLength: 3,
                    pattern: /^4/
                },
                {
                    key: 'mastercard',
                    label: 'Mastercard',
                    lengths: [16],
                    cvvLength: 3,
                    pattern: /^(5[1-5]|2(2[2-9][1-9]|2[3-9]\d|[3-6]\d{2}|7[01]\d|720))/
                },
                {
                    key: 'amex',
                    label: 'Amex',
                    lengths: [15],
                    cvvLength: 4,
                    pattern: /^3[47]/
                },
                {
                    key: 'elo',
                    label: 'Elo',
                    lengths: [16],
                    cvvLength: 3,
                    pattern: /^(4011|4312|4389|4514|4573|4576|5041|5066|5067|5090|6277|6362|6363|6500|6504|6505|6507|6509|6516|6550)/
                },
                {
                    key: 'hipercard',
                    label: 'Hipercard',
                    lengths: [13, 16, 19],
                    cvvLength: 3,
                    pattern: /^(606282|3841|637095|637599)/
                }
            ];

            const brandLogoSources = {
                visa: "{{ asset('assets/visa.svg') }}",
                mastercard: "{{ asset('assets/mastercard.svg') }}",
                elo: "{{ asset('assets/elo.svg') }}",
                amex: "{{ asset('assets/amex.svg') }}",
                hipercard: "{{ asset('assets/hipercard.svg') }}"
            };

            const setFeedback = function (input, feedback, status, message) {
                if (!input || !feedback) {
                    return;
                }

                input.classList.remove('is-valid', 'is-invalid');
                feedback.classList.remove('is-valid', 'is-invalid');
                feedback.textContent = '';

                if (!status || !message) {
                    return;
                }

                input.classList.add(status === 'valid' ? 'is-valid' : 'is-invalid');
                feedback.classList.add(status === 'valid' ? 'is-valid' : 'is-invalid');
                feedback.textContent = message;
            };

            const detectCardBrand = function (number) {
                return cardBrands.find(function (brand) {
                    return brand.pattern.test(number);
                }) || null;
            };

            const clearCardBrandUi = function () {
                if (cardBrandInput) {
                    cardBrandInput.value = '';
                }

                if (detectedCard) {
                    detectedCard.classList.remove('is-detected');
                }

                if (detectedCardLogo) {
                    detectedCardLogo.removeAttribute('src');
                    detectedCardLogo.setAttribute('alt', '');
                }

                if (detectedCardIcon) {
                    detectedCardIcon.style.display = '';
                }

                if (cardFlagsWrapper) {
                    cardFlagsWrapper.classList.remove('has-brand');
                }

                cardFlags.forEach(function (flag) {
                    flag.classList.remove('is-active');
                });
            };

            const updateCardBrandUi = function (brand) {
                if (!brand) {
                    clearCardBrandUi();
                    return;
                }

                if (cardBrandInput) {
                    cardBrandInput.value = brand.key;
                }

                if (detectedCard) {
                    detectedCard.classList.add('is-detected');
                }

                if (detectedCardLogo) {
                    detectedCardLogo.setAttribute('src', brandLogoSources[brand.key] || '');
                    detectedCardLogo.setAttribute('alt', brand.label);
                }

                if (detectedCardIcon) {
                    detectedCardIcon.style.display = 'none';
                }

                if (cardFlagsWrapper) {
                    cardFlagsWrapper.classList.add('has-brand');
                }

                cardFlags.forEach(function (flag) {
                    flag.classList.toggle('is-active', flag.getAttribute('data-brand-flag') === brand.key);
                });
            };

            const isValidLuhn = function (number) {
                if (!number || number.length < 12) {
                    return false;
                }

                let sum = 0;
                let shouldDouble = false;

                for (let index = number.length - 1; index >= 0; index--) {
                    let digit = parseInt(number.charAt(index), 10);

                    if (shouldDouble) {
                        digit *= 2;

                        if (digit > 9) {
                            digit -= 9;
                        }
                    }

                    sum += digit;
                    shouldDouble = !shouldDouble;
                }

                return sum % 10 === 0;
            };

            const getMaxCardLength = function (brand) {
                if (!brand) {
                    return 19;
                }

                return Math.max.apply(null, brand.lengths);
            };

            const formatCardNumber = function (value, brand) {
                if (brand && brand.key === 'amex') {
                    return value.replace(/^(\d{0,4})(\d{0,6})(\d{0,5}).*/, function (_, first, second, third) {
                        return [first, second, third].filter(Boolean).join(' ');
                    });
                }

                return value.replace(/(\d{4})(?=\d)/g, '$1 ');
            };

            const validatePhone = function () {
                if (!telefoneInput) {
                    return true;
                }

                const numbers = onlyNumbers(telefoneInput.value);

                if (!numbers.length) {
                    setFeedback(telefoneInput, telefoneFeedback, null, null);
                    return false;
                }

                if (numbers.length < 10) {
                    setFeedback(telefoneInput, telefoneFeedback, 'invalid', 'Informe um telefone válido com DDD.');
                    return false;
                }

                setFeedback(telefoneInput, telefoneFeedback, 'valid', 'Telefone válido.');
                return true;
            };

            const validateCardNumber = function () {
                if (!cardNumberInput) {
                    return true;
                }

                const numbers = onlyNumbers(cardNumberInput.value);
                const brand = detectCardBrand(numbers);

                updateCardBrandUi(brand);

                if (cardLastFourInput) {
                    cardLastFourInput.value = numbers.length >= 4 ? numbers.slice(-4) : '';
                }

                if (!numbers.length) {
                    setFeedback(cardNumberInput, cardNumberFeedback, null, null);
                    return false;
                }

                if (!brand && numbers.length >= 6) {
                    setFeedback(cardNumberInput, cardNumberFeedback, 'invalid', 'Bandeira não reconhecida. Use Visa, Mastercard, Elo, Amex ou Hipercard.');
                    return false;
                }

                if (brand && !brand.lengths.includes(numbers.length)) {
                    setFeedback(cardNumberInput, cardNumberFeedback, 'invalid', 'Número incompleto para ' + brand.label + '.');
                    return false;
                }

                if (brand && brand.lengths.includes(numbers.length) && !isValidLuhn(numbers)) {
                    setFeedback(cardNumberInput, cardNumberFeedback, 'invalid', 'Número do cartão inválido.');
                    return false;
                }

                if (brand && brand.lengths.includes(numbers.length) && isValidLuhn(numbers)) {
                    setFeedback(cardNumberInput, cardNumberFeedback, 'valid', brand.label + ' válido.');
                    return true;
                }

                setFeedback(cardNumberInput, cardNumberFeedback, null, null);
                return false;
            };

            const validateMonth = function () {
                if (!cardExpiryMonthInput) {
                    return true;
                }

                const value = onlyNumbers(cardExpiryMonthInput.value);

                if (!value.length) {
                    setFeedback(cardExpiryMonthInput, cardExpiryMonthFeedback, null, null);
                    return false;
                }

                if (value.length !== 2 || Number(value) < 1 || Number(value) > 12) {
                    setFeedback(cardExpiryMonthInput, cardExpiryMonthFeedback, 'invalid', 'Mês inválido.');
                    return false;
                }

                setFeedback(cardExpiryMonthInput, cardExpiryMonthFeedback, 'valid', 'Mês válido.');
                return true;
            };

            const validateYear = function () {
                if (!cardExpiryYearInput) {
                    return true;
                }

                const value = onlyNumbers(cardExpiryYearInput.value);
                const currentYear = new Date().getFullYear();

                if (!value.length) {
                    setFeedback(cardExpiryYearInput, cardExpiryYearFeedback, null, null);
                    return false;
                }

                if (value.length !== 4 || Number(value) < currentYear || Number(value) > currentYear + 20) {
                    setFeedback(cardExpiryYearInput, cardExpiryYearFeedback, 'invalid', 'Ano inválido.');
                    return false;
                }

                setFeedback(cardExpiryYearInput, cardExpiryYearFeedback, 'valid', 'Ano válido.');
                return true;
            };

            const validateExpirationDate = function () {
                const isMonthValid = validateMonth();
                const isYearValid = validateYear();

                if (!isMonthValid || !isYearValid) {
                    return false;
                }

                const month = Number(onlyNumbers(cardExpiryMonthInput.value));
                const year = Number(onlyNumbers(cardExpiryYearInput.value));
                const now = new Date();
                const currentMonth = now.getMonth() + 1;
                const currentYear = now.getFullYear();

                if (year === currentYear && month < currentMonth) {
                    setFeedback(cardExpiryMonthInput, cardExpiryMonthFeedback, 'invalid', 'Cartão vencido.');
                    setFeedback(cardExpiryYearInput, cardExpiryYearFeedback, 'invalid', 'Cartão vencido.');
                    return false;
                }

                return true;
            };

            const validateCcv = function () {
                if (!cardCcvInput) {
                    return true;
                }

                const numbers = onlyNumbers(cardCcvInput.value);
                const cardNumber = cardNumberInput ? onlyNumbers(cardNumberInput.value) : '';
                const brand = detectCardBrand(cardNumber);
                const expectedLength = brand ? brand.cvvLength : 3;

                cardCcvInput.setAttribute('maxlength', String(expectedLength));

                if (!numbers.length) {
                    setFeedback(cardCcvInput, cardCcvFeedback, null, null);
                    return false;
                }

                if (numbers.length !== expectedLength) {
                    setFeedback(cardCcvInput, cardCcvFeedback, 'invalid', brand && brand.key === 'amex' ? 'Amex usa CVV com 4 dígitos.' : 'CVV deve ter 3 dígitos.');
                    return false;
                }

                setFeedback(cardCcvInput, cardCcvFeedback, 'valid', 'CVV válido.');
                return true;
            };

            const applyPhoneMask = function (input) {
                let value = onlyNumbers(input.value).slice(0, 11);

                if (value.length <= 10) {
                    value = value.replace(/^(\d{0,2})(\d{0,4})(\d{0,4}).*/, function (_, ddd, first, second) {
                        let formatted = '';

                        if (ddd) {
                            formatted += '(' + ddd;
                        }

                        if (ddd.length === 2) {
                            formatted += ') ';
                        }

                        if (first) {
                            formatted += first;
                        }

                        if (second) {
                            formatted += '-' + second;
                        }

                        return formatted;
                    });
                } else {
                    value = value.replace(/^(\d{0,2})(\d{0,5})(\d{0,4}).*/, function (_, ddd, first, second) {
                        let formatted = '';

                        if (ddd) {
                            formatted += '(' + ddd;
                        }

                        if (ddd.length === 2) {
                            formatted += ') ';
                        }

                        if (first) {
                            formatted += first;
                        }

                        if (second) {
                            formatted += '-' + second;
                        }

                        return formatted;
                    });
                }

                input.value = value;
                validatePhone();
            };

            const applyCpfCnpjMask = function (input) {
                const numbers = onlyNumbers(input.value).slice(0, 14);

                if (numbers.length <= 11) {
                    input.value = numbers.replace(/^(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2}).*/, function (_, first, second, third, fourth) {
                        let formatted = first;

                        if (second) {
                            formatted += '.' + second;
                        }

                        if (third) {
                            formatted += '.' + third;
                        }

                        if (fourth) {
                            formatted += '-' + fourth;
                        }

                        return formatted;
                    });

                    return;
                }

                input.value = numbers.replace(/^(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2}).*/, function (_, first, second, third, fourth, fifth) {
                    let formatted = first;

                    if (second) {
                        formatted += '.' + second;
                    }

                    if (third) {
                        formatted += '.' + third;
                    }

                    if (fourth) {
                        formatted += '/' + fourth;
                    }

                    if (fifth) {
                        formatted += '-' + fifth;
                    }

                    return formatted;
                });
            };

            const applyPostalCodeMask = function (input) {
                input.value = onlyNumbers(input.value)
                    .slice(0, 8)
                    .replace(/^(\d{0,5})(\d{0,3}).*/, function (_, first, second) {
                        return second ? first + '-' + second : first;
                    });
            };

            const applyCardNumberMask = function (input) {
                let numbers = onlyNumbers(input.value);
                let brand = detectCardBrand(numbers);

                numbers = numbers.slice(0, getMaxCardLength(brand));
                brand = detectCardBrand(numbers);

                input.value = formatCardNumber(numbers, brand);
                validateCardNumber();
                validateCcv();
            };

            const applyMonthMask = function (input) {
                let value = onlyNumbers(input.value).slice(0, 2);

                if (value.length === 1 && Number(value) > 1) {
                    value = '0' + value;
                }

                if (value.length === 2) {
                    let month = Number(value);

                    if (month < 1) {
                        value = '01';
                    }

                    if (month > 12) {
                        value = '12';
                    }
                }

                input.value = value;
                validateExpirationDate();
            };

            const applyYearMask = function (input) {
                input.value = onlyNumbers(input.value).slice(0, 4);
                validateExpirationDate();
            };

            const applyCcvMask = function (input) {
                const cardNumber = cardNumberInput ? onlyNumbers(cardNumberInput.value) : '';
                const brand = detectCardBrand(cardNumber);
                const expectedLength = brand ? brand.cvvLength : 4;

                input.value = onlyNumbers(input.value).slice(0, expectedLength);
                validateCcv();
            };

            if (telefoneInput) {
                applyPhoneMask(telefoneInput);

                telefoneInput.addEventListener('input', function () {
                    applyPhoneMask(telefoneInput);
                });

                telefoneInput.addEventListener('blur', function () {
                    validatePhone();
                });

                telefoneInput.addEventListener('paste', function () {
                    setTimeout(function () {
                        applyPhoneMask(telefoneInput);
                    }, 0);
                });
            }

            if (billingCpfCnpjInput) {
                applyCpfCnpjMask(billingCpfCnpjInput);

                billingCpfCnpjInput.addEventListener('input', function () {
                    applyCpfCnpjMask(billingCpfCnpjInput);
                });

                billingCpfCnpjInput.addEventListener('paste', function () {
                    setTimeout(function () {
                        applyCpfCnpjMask(billingCpfCnpjInput);
                    }, 0);
                });
            }

            if (holderPostalCodeInput) {
                applyPostalCodeMask(holderPostalCodeInput);

                holderPostalCodeInput.addEventListener('input', function () {
                    applyPostalCodeMask(holderPostalCodeInput);
                });

                holderPostalCodeInput.addEventListener('paste', function () {
                    setTimeout(function () {
                        applyPostalCodeMask(holderPostalCodeInput);
                    }, 0);
                });
            }

            if (cardNumberInput) {
                applyCardNumberMask(cardNumberInput);

                cardNumberInput.addEventListener('input', function () {
                    applyCardNumberMask(cardNumberInput);
                });

                cardNumberInput.addEventListener('blur', function () {
                    validateCardNumber();
                });

                cardNumberInput.addEventListener('paste', function () {
                    setTimeout(function () {
                        applyCardNumberMask(cardNumberInput);
                    }, 0);
                });
            }

            if (cardExpiryMonthInput) {
                applyMonthMask(cardExpiryMonthInput);

                cardExpiryMonthInput.addEventListener('input', function () {
                    applyMonthMask(cardExpiryMonthInput);
                });

                cardExpiryMonthInput.addEventListener('blur', function () {
                    validateExpirationDate();
                });

                cardExpiryMonthInput.addEventListener('paste', function () {
                    setTimeout(function () {
                        applyMonthMask(cardExpiryMonthInput);
                    }, 0);
                });
            }

            if (cardExpiryYearInput) {
                applyYearMask(cardExpiryYearInput);

                cardExpiryYearInput.addEventListener('input', function () {
                    applyYearMask(cardExpiryYearInput);
                });

                cardExpiryYearInput.addEventListener('blur', function () {
                    validateExpirationDate();
                });

                cardExpiryYearInput.addEventListener('paste', function () {
                    setTimeout(function () {
                        applyYearMask(cardExpiryYearInput);
                    }, 0);
                });
            }

            if (cardCcvInput) {
                applyCcvMask(cardCcvInput);

                cardCcvInput.addEventListener('input', function () {
                    applyCcvMask(cardCcvInput);
                });

                cardCcvInput.addEventListener('blur', function () {
                    validateCcv();
                });

                cardCcvInput.addEventListener('paste', function () {
                    setTimeout(function () {
                        applyCcvMask(cardCcvInput);
                    }, 0);
                });
            }

            const registerForm = document.getElementById('nexo-register-form');

            if (registerForm) {
                registerForm.addEventListener('submit', function (event) {
                    const isPhoneValid = validatePhone();
                    const isCardValid = validateCardNumber();
                    const isExpirationValid = validateExpirationDate();
                    const isCcvValid = validateCcv();

                    if (!isPhoneValid || !isCardValid || !isExpirationValid || !isCcvValid) {
                        event.preventDefault();

                        const firstInvalid = registerForm.querySelector('.form-control.is-invalid');

                        if (firstInvalid) {
                            firstInvalid.focus();
                        }
                    }
                });
            }
        });
    </script>
</x-layouts.app>
