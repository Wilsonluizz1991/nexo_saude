<x-layouts.app title="Acesso restrito | Nexo Saúde">
    <main class="nexo-access-denied-page">
        <section class="nexo-access-denied-card">
            <div class="nexo-access-denied-brand">
                <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">

                <span>
                    Área protegida
                </span>

                <h1>
                    Acesso restrito ao administrador
                </h1>

                <p>
                    Esta área é exclusiva para administradores do sistema. Se você acredita que deveria ter acesso, fale com o responsável pela plataforma.
                </p>

                <div class="nexo-access-denied-actions">
                    <a href="{{ route('dashboard') }}" class="nexo-primary-action">
                        Voltar para o painel
                        <i class="bi bi-arrow-right"></i>
                    </a>

                    <form method="post" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="nexo-secondary-action">
                            Sair da conta
                        </button>
                    </form>
                </div>
            </div>

            <div class="nexo-access-denied-visual">
                <div class="nexo-lock-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>

                <strong>
                    403
                </strong>

                <span>
                    Permissão insuficiente
                </span>
            </div>
        </section>
    </main>

    <style>
        .nexo-access-denied-page {
            min-height: 100vh;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                linear-gradient(135deg, #061C3F 0%, #0D2F57 48%, #F4F7FB 48%, #FFFFFF 100%);
        }

        .nexo-access-denied-card {
            width: min(1080px, 100%);
            min-height: 560px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            overflow: hidden;
            border-radius: 34px;
            background: #FFFFFF;
            box-shadow: 0 32px 90px rgba(6, 28, 63, 0.24);
        }

        .nexo-access-denied-brand {
            padding: 52px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .nexo-access-denied-brand img {
            width: 100%;
            max-width: 230px;
            margin-bottom: 34px;
        }

        .nexo-access-denied-brand span {
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.78rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 18px;
        }

        .nexo-access-denied-brand h1 {
            color: #061C3F;
            font-size: clamp(2.2rem, 4vw, 4rem);
            line-height: 1.02;
            letter-spacing: -0.075em;
            font-weight: 950;
            margin: 0 0 18px;
        }

        .nexo-access-denied-brand p {
            color: #64748B;
            font-size: 1.02rem;
            line-height: 1.6;
            max-width: 560px;
            margin: 0;
        }

        .nexo-access-denied-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .nexo-primary-action,
        .nexo-secondary-action {
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

        .nexo-primary-action {
            border: 0;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.24);
        }

        .nexo-secondary-action {
            border: 1px solid #D8E2EF;
            background: #FFFFFF;
            color: #061C3F;
        }

        .nexo-primary-action:hover,
        .nexo-secondary-action:hover {
            transform: translateY(-2px);
        }

        .nexo-access-denied-visual {
            position: relative;
            padding: 48px;
            background:
                radial-gradient(circle at top right, rgba(125, 181, 255, 0.22), transparent 32%),
                linear-gradient(180deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .nexo-access-denied-visual::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            right: -90px;
            bottom: -90px;
            background: rgba(47, 128, 237, 0.18);
        }

        .nexo-lock-icon {
            position: relative;
            z-index: 1;
            width: 128px;
            height: 128px;
            border-radius: 38px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.8rem;
            color: #7DB5FF;
            margin-bottom: 28px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
        }

        .nexo-access-denied-visual strong,
        .nexo-access-denied-visual span {
            position: relative;
            z-index: 1;
        }

        .nexo-access-denied-visual strong {
            font-size: 5rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.08em;
        }

        .nexo-access-denied-visual span {
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.78);
            font-weight: 850;
        }

        @media (max-width: 900px) {
            .nexo-access-denied-card {
                grid-template-columns: 1fr;
            }

            .nexo-access-denied-brand,
            .nexo-access-denied-visual {
                padding: 34px 24px;
            }

            .nexo-access-denied-visual {
                min-height: 300px;
            }
        }

        @media (max-width: 560px) {
            .nexo-access-denied-page {
                padding: 12px;
            }

            .nexo-access-denied-card {
                border-radius: 26px;
            }

            .nexo-access-denied-actions {
                flex-direction: column;
            }

            .nexo-primary-action,
            .nexo-secondary-action {
                width: 100%;
            }
        }
    </style>
</x-layouts.app>