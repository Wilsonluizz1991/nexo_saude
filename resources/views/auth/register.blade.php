<x-layouts.app title="Criar conta | Nexo Saúde">
    <main class="nexo-register-page">
        <section class="nexo-register-card">
            <div class="nexo-register-brand">
                <div class="nexo-register-logo">
                    <img
                        src="{{ asset('assets/nexo-logo-topo.png') }}"
                        alt="Nexo Saúde"
                    >
                </div>

                <span class="nexo-register-badge">
                    <i class="bi bi-stars"></i>
                    CRM premium para corretoras
                </span>

                <h1>
                    Transforme sua operação comercial
                </h1>

                <p>
                    Organize leads, propostas, pré-cadastros, implantações e carteira de clientes em uma única plataforma moderna.
                </p>

                <div class="nexo-register-benefits">
                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Pipeline inteligente para vendas</span>
                    </div>

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Gestão operacional completa</span>
                    </div>

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Análises estratégicas da carteira</span>
                    </div>
                </div>

                <div class="nexo-register-highlight">
                    <span>30 dias grátis</span>
                    <strong>Comece agora</strong>
                </div>
            </div>

            <div class="nexo-register-form-area">
                <div class="nexo-register-form-header">
                    <span>Nova conta</span>

                    <h2>
                        Criar conta de corretor
                    </h2>

                    <p>
                        Configure sua operação em poucos minutos.
                    </p>
                </div>

                <form method="post" action="{{ route('register.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>

                            <input
                                name="name"
                                class="form-control"
                                placeholder="Seu nome"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>

                            <input
                                name="telefone"
                                class="form-control"
                                placeholder="(11) 99999-9999"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label">E-mail</label>

                            <input
                                name="email"
                                type="email"
                                class="form-control"
                                placeholder="voce@email.com"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Senha</label>

                            <input
                                name="password"
                                type="password"
                                class="form-control"
                                placeholder="Digite sua senha"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirmar senha</label>

                            <input
                                name="password_confirmation"
                                type="password"
                                class="form-control"
                                placeholder="Confirme sua senha"
                                required
                            >
                        </div>
                    </div>

                    <button class="nexo-register-submit mt-4">
                        Criar conta
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <div class="nexo-register-footer">
                    <span>Já possui conta?</span>

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
            overflow: hidden;
            height: 100%;
        }

        body {
            min-height: 100vh;
        }

        .nexo-register-page {
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
            width: min(1120px, 100%);
            height: min(92vh, 760px);
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            border-radius: 30px;
            background: #FFFFFF;
            box-shadow: 0 28px 80px rgba(6, 28, 63, 0.20);
        }

        .nexo-register-brand {
            position: relative;
            padding: 42px 52px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.28), transparent 32%),
                linear-gradient(180deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
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

        .nexo-register-logo {
            position: relative;
            z-index: 1;
            margin-bottom: 28px;
        }

        .nexo-register-logo img {
            width: 100%;
            max-width: 250px;
            display: block;
        }

        .nexo-register-badge {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 8px 15px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            color: #DDEBFF;
            font-size: 0.78rem;
            font-weight: 900;
            margin-bottom: 18px;
        }

        .nexo-register-brand h1 {
            position: relative;
            z-index: 1;
            font-size: clamp(2.1rem, 3vw, 3.6rem);
            line-height: 0.98;
            font-weight: 950;
            letter-spacing: -0.07em;
            margin: 0 0 16px;
        }

        .nexo-register-brand p {
            position: relative;
            z-index: 1;
            color: rgba(255, 255, 255, 0.78);
            font-size: 1rem;
            line-height: 1.5;
            max-width: 430px;
            margin: 0;
        }

        .nexo-register-benefits {
            position: relative;
            z-index: 1;
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
            font-weight: 700;
            color: rgba(255, 255, 255, 0.92);
        }

        .nexo-register-benefits i {
            color: #7DB5FF;
            font-size: 1rem;
        }

        .nexo-register-highlight {
            position: relative;
            z-index: 1;
            margin-top: 28px;
            padding: 20px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
        }

        .nexo-register-highlight span {
            display: block;
            color: rgba(255, 255, 255, 0.70);
            font-size: 0.78rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-register-highlight strong {
            font-size: 1.8rem;
            font-weight: 950;
            color: #FFFFFF;
            letter-spacing: -0.04em;
        }

        .nexo-register-form-area {
            padding: 42px 52px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.07), transparent 26%),
                #FFFFFF;
        }

        .nexo-register-form-header {
            margin-bottom: 24px;
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
            font-size: 2rem;
            font-weight: 950;
            letter-spacing: -0.04em;
            margin: 0 0 6px;
        }

        .nexo-register-form-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-register-form-area .form-label {
            color: #061C3F;
            font-weight: 850;
            margin-bottom: 7px;
        }

        .nexo-register-form-area .form-control {
            min-height: 52px;
            border-radius: 14px;
            border: 1px solid #D8E2EF;
            padding: 12px 15px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .nexo-register-form-area .form-control:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-register-submit {
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
        }

        .nexo-register-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(47, 128, 237, 0.30);
        }

        .nexo-register-footer {
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
        }

        .nexo-register-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            html,
            body {
                overflow-y: auto;
                height: auto;
            }

            .nexo-register-page {
                height: auto;
                min-height: 100vh;
                padding: 16px;
            }

            .nexo-register-card {
                height: auto;
                grid-template-columns: 1fr;
            }

            .nexo-register-brand,
            .nexo-register-form-area {
                padding: 32px 24px;
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

            .nexo-register-brand h1 {
                font-size: 2rem;
            }

            .nexo-register-form-header h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</x-layouts.app>