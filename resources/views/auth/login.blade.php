<x-layouts.app title="Entrar | Nexo Saúde">
    <main class="nexo-login-page">
        <section class="nexo-login-card">
            <div class="nexo-login-brand">
                <div class="nexo-login-logo">
                    <img
                        src="{{ asset('assets/nexo-logo-topo.png') }}"
                        alt="Nexo Saúde"
                    >
                </div>

                <span class="nexo-login-badge">
                    <i class="bi bi-shield-check"></i>
                    Plataforma segura
                </span>

                <h1>
                    Acesse sua operação
                </h1>

                <p>
                    Gerencie leads, propostas, pré-cadastros, implantações e carteira em uma única plataforma.
                </p>

                <div class="nexo-login-benefits">
                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Controle completo da operação comercial</span>
                    </div>

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Pipeline inteligente para corretores de planos de saúde</span>
                    </div>

                    <div>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Análise de vendas, metas e performance</span>
                    </div>
                </div>

                <div class="nexo-login-highlight">
                    <span>Seu CRM premium para planos de saúde</span>
                    <strong>Nexo Saúde</strong>
                </div>
            </div>

            <div class="nexo-login-form-area">
                <div class="nexo-login-form-header">
                    <span>Bem-vindo de volta</span>

                    <h2>
                        Entrar na plataforma
                    </h2>

                    <p>
                        Informe seus dados de acesso para continuar sua operação.
                    </p>
                </div>

                <form method="post" action="{{ route('login.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>

                        <input
                            name="email"
                            type="email"
                            class="form-control"
                            value="{{ old('email') }}"
                            placeholder="voce@email.com"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Senha</label>

                        <input
                            name="password"
                            type="password"
                            class="form-control"
                            placeholder="Digite sua senha"
                            required
                        >
                    </div>

                    <button class="nexo-login-submit">
                        Entrar
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <div class="nexo-login-footer">
                    <a href="{{ route('password.request') }}">
                        Esqueci minha senha
                    </a>

                    <span></span>

                    <a href="{{ route('register') }}">
                        Criar conta
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

        .nexo-login-page {
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

        .nexo-login-card {
            width: min(1120px, 100%);
            height: min(92vh, 720px);
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            border-radius: 30px;
            background: #FFFFFF;
            box-shadow: 0 28px 80px rgba(6, 28, 63, 0.20);
        }

        .nexo-login-brand {
            position: relative;
            padding: 42px 52px;
            color: #FFFFFF;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.28), transparent 32%),
                linear-gradient(180deg, #061C3F 0%, #0F3A68 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .nexo-login-brand::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            right: -80px;
            bottom: -80px;
            background: rgba(47, 128, 237, 0.16);
        }

        .nexo-login-logo {
            position: relative;
            z-index: 1;
            margin-bottom: 28px;
        }

        .nexo-login-logo img {
            width: 100%;
            max-width: 250px;
            height: auto;
            display: block;
        }

        .nexo-login-badge {
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

        .nexo-login-brand h1 {
            position: relative;
            z-index: 1;
            color: #FFFFFF;
            font-size: clamp(2.1rem, 3vw, 3.6rem);
            line-height: 0.98;
            font-weight: 950;
            letter-spacing: -0.07em;
            margin: 0 0 16px;
        }

        .nexo-login-brand p {
            position: relative;
            z-index: 1;
            max-width: 430px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 1rem;
            line-height: 1.5;
            margin: 0;
        }

        .nexo-login-benefits {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 12px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }

        .nexo-login-benefits div {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 700;
        }

        .nexo-login-benefits i {
            color: #7DB5FF;
            font-size: 1rem;
        }

        .nexo-login-highlight {
            position: relative;
            z-index: 1;
            margin-top: 28px;
            padding: 20px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
        }

        .nexo-login-highlight span {
            display: block;
            color: rgba(255, 255, 255, 0.70);
            font-size: 0.78rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-login-highlight strong {
            color: #FFFFFF;
            font-size: 1.8rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .nexo-login-form-area {
            padding: 42px 52px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.07), transparent 26%),
                #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .nexo-login-form-header {
            margin-bottom: 24px;
        }

        .nexo-login-form-header span {
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

        .nexo-login-form-header h2 {
            color: #061C3F;
            font-size: 2rem;
            font-weight: 950;
            letter-spacing: -0.04em;
            margin: 0 0 6px;
        }

        .nexo-login-form-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-login-form-area .form-label {
            color: #061C3F;
            font-weight: 850;
            margin-bottom: 7px;
        }

        .nexo-login-form-area .form-control {
            min-height: 52px;
            border-radius: 14px;
            border: 1px solid #D8E2EF;
            color: #162033;
            padding: 12px 15px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .nexo-login-form-area .form-control:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-login-submit {
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

        .nexo-login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(47, 128, 237, 0.30);
        }

        .nexo-login-footer {
            margin-top: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            color: #94A3B8;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .nexo-login-footer span {
            width: 4px;
            height: 4px;
            border-radius: 999px;
            background: #CBD5E1;
        }

        .nexo-login-footer a {
            color: #2F80ED;
            text-decoration: none;
            font-weight: 850;
        }

        .nexo-login-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            html,
            body {
                overflow-y: auto;
                height: auto;
            }

            .nexo-login-page {
                height: auto;
                min-height: 100vh;
                padding: 16px;
            }

            .nexo-login-card {
                height: auto;
                grid-template-columns: 1fr;
            }

            .nexo-login-brand,
            .nexo-login-form-area {
                padding: 32px 24px;
            }

            .nexo-login-logo img {
                max-width: 220px;
            }

            .nexo-login-brand h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 576px) {
            .nexo-login-page {
                padding: 12px;
            }

            .nexo-login-card {
                border-radius: 24px;
            }

            .nexo-login-brand h1 {
                font-size: 2rem;
            }

            .nexo-login-form-header h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</x-layouts.app>
