<x-layouts.public title="Pré-cadastro em análise | Nexo Saúde">
    @php
        $corretor = $preCadastro->indicacao?->user;
        $perfil = $corretor?->corretorPerfil;
    @endphp

    <main class="nexo-analysis-page">
        <section class="nexo-analysis-card">
            <div class="nexo-analysis-brand">
                <div class="nexo-analysis-logo">
                    <img
                        src="{{ asset('assets/nexo-logo-topo.png') }}"
                        alt="Nexo Saúde"
                    >
                </div>

                <span class="nexo-analysis-badge">
                    <i class="bi bi-shield-check"></i>
                    Envio seguro
                </span>

                <h1>
                    Recebemos suas informações com sucesso
                </h1>

                <p>
                    Seu pré-cadastro foi enviado com sucesso e agora está em análise pelo corretor responsável.
                </p>

                <div class="nexo-analysis-steps">
                    <div class="active">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Informações enviadas</span>
                    </div>

                    <div class="active">
                        <i class="bi bi-hourglass-split"></i>
                        <span>Análise em andamento</span>
                    </div>

                    <div>
                        <i class="bi bi-chat-dots"></i>
                        <span>Retorno do corretor</span>
                    </div>
                </div>
            </div>

            <div class="nexo-analysis-content">
                <div class="nexo-analysis-loader">
                    <div class="nexo-analysis-loader-glow"></div>

                    <div class="nexo-analysis-loader-ring ring-one"></div>
                    <div class="nexo-analysis-loader-ring ring-two"></div>

                    <div class="nexo-analysis-loader-core">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>

                <span class="nexo-analysis-status">
                    Análise em andamento
                </span>

                <h2>
                    Tudo certo por enquanto
                </h2>

                <p>
                    Estamos analisando as informações e documentos enviados. Caso seja necessário complementar algum dado ou reenviar algum arquivo, o corretor responsável entrará em contato.
                </p>

                <div class="nexo-analysis-message">
                    <i class="bi bi-info-circle"></i>

                    <div>
                        <strong>
                            Você não precisa preencher novamente agora.
                        </strong>

                        <span>
                            O formulário ficará bloqueado temporariamente enquanto a análise estiver em andamento.
                        </span>
                    </div>
                </div>

                @if($perfil?->nome_publico || $corretor?->name)
                    <div class="nexo-analysis-broker">
                        <span>
                            Acompanhamento de
                        </span>

                        <strong>
                            {{ $perfil?->nome_publico ?? $corretor?->name }}
                        </strong>
                    </div>
                @endif
            </div>
        </section>
    </main>

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            background: #061C3F;
        }

        .nexo-analysis-page {
            min-height: 100vh;
            padding: 16px 12px;
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                linear-gradient(135deg, #061C3F 0%, #0D2F57 34%, #F4F7FB 34%, #FFFFFF 100%);
        }

        .nexo-analysis-card {
            width: min(1120px, 100%);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr;
            overflow: hidden;
            border-radius: 26px;
            background: #FFFFFF;
            box-shadow: 0 24px 60px rgba(6, 28, 63, 0.18);
        }

        .nexo-analysis-brand {
            position: relative;
            padding: 24px 20px;
            color: #FFFFFF;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.28), transparent 32%),
                linear-gradient(180deg, #061C3F 0%, #0F3A68 100%);
            overflow: hidden;
        }

        .nexo-analysis-brand::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            right: -70px;
            bottom: -70px;
            background: rgba(47, 128, 237, 0.16);
        }

        .nexo-analysis-logo {
            position: relative;
            z-index: 1;
            margin-bottom: 22px;
        }

        .nexo-analysis-logo img {
            width: 100%;
            max-width: 220px;
            height: auto;
            display: block;
        }

        .nexo-analysis-badge {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            color: #DDEBFF;
            font-size: 0.76rem;
            font-weight: 900;
            margin-bottom: 14px;
        }

        .nexo-analysis-brand h1 {
            position: relative;
            z-index: 1;
            color: #FFFFFF;
            font-size: 2rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.055em;
            margin: 0 0 12px;
        }

        .nexo-analysis-brand p {
            position: relative;
            z-index: 1;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0;
        }

        .nexo-analysis-steps {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }

        .nexo-analysis-steps div {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.92rem;
            font-weight: 750;
        }

        .nexo-analysis-steps div.active {
            color: rgba(255, 255, 255, 0.94);
        }

        .nexo-analysis-steps i {
            color: #7DB5FF;
            font-size: 1rem;
        }

        .nexo-analysis-content {
            position: relative;
            padding: 26px 20px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.07), transparent 26%),
                #FFFFFF;
        }

        .nexo-analysis-loader {
            position: relative;
            width: 74px;
            height: 74px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nexo-analysis-loader-glow {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: radial-gradient(
                circle,
                rgba(47, 128, 237, 0.20) 0%,
                rgba(47, 128, 237, 0.05) 60%,
                transparent 78%
            );
            animation: nexoPulseGlow 1.5s ease-in-out infinite;
        }

        .nexo-analysis-loader-ring {
            position: absolute;
            border-radius: 999px;
            border: 1.5px solid rgba(47, 128, 237, 0.18);
        }

        .ring-one {
            width: 74px;
            height: 74px;
            border-top-color: rgba(47, 128, 237, 0.8);
            animation: nexoRotate 2.2s linear infinite;
        }

        .ring-two {
            width: 56px;
            height: 56px;
            border-style: dashed;
            border-color: rgba(47, 128, 237, 0.28);
            animation: nexoRotateReverse 1.6s linear infinite;
        }

        .nexo-analysis-loader-core {
            position: relative;
            z-index: 2;
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            box-shadow:
                0 10px 24px rgba(47, 128, 237, 0.28),
                0 0 0 6px rgba(47, 128, 237, 0.08);
            animation: nexoFloat 1.3s ease-in-out infinite;
        }

        .nexo-analysis-status {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            min-height: 34px;
            padding: 0 13px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.8rem;
            font-weight: 900;
            margin-bottom: 14px;
        }

        .nexo-analysis-content h2 {
            color: #061C3F;
            font-size: 2rem;
            line-height: 1.02;
            font-weight: 950;
            letter-spacing: -0.06em;
            margin: 0 0 14px;
        }

        .nexo-analysis-content > p {
            color: #64748B;
            font-size: 0.95rem;
            line-height: 1.55;
            margin: 0;
        }

        .nexo-analysis-message {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-top: 22px;
            padding: 18px;
            border-radius: 20px;
            background: #F8FBFF;
            border: 1px solid #DCEBFF;
        }

        .nexo-analysis-message i {
            color: #2F80ED;
            font-size: 1.18rem;
            margin-top: 2px;
        }

        .nexo-analysis-message strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .nexo-analysis-message span {
            display: block;
            color: #64748B;
            line-height: 1.42;
            font-size: 0.92rem;
        }

        .nexo-analysis-broker {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #E8EEF6;
        }

        .nexo-analysis-broker span {
            display: block;
            color: #64748B;
            font-size: 0.82rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-analysis-broker strong {
            color: #061C3F;
            font-size: 1.08rem;
            font-weight: 950;
        }

        @keyframes nexoRotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes nexoRotateReverse {
            from {
                transform: rotate(360deg);
            }

            to {
                transform: rotate(0deg);
            }
        }

        @keyframes nexoPulseGlow {
            0% {
                transform: scale(0.92);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.08);
                opacity: 1;
            }

            100% {
                transform: scale(0.92);
                opacity: 0.7;
            }
        }

        @keyframes nexoFloat {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-5px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        @media (min-width: 768px) {
            .nexo-analysis-page {
                padding: 32px 18px;
            }

            .nexo-analysis-card {
                grid-template-columns: 0.95fr 1.05fr;
                border-radius: 30px;
            }

            .nexo-analysis-brand {
                padding: 42px 52px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .nexo-analysis-logo img {
                max-width: 250px;
            }

            .nexo-analysis-brand h1 {
                font-size: clamp(2.1rem, 3vw, 3.6rem);
                line-height: 0.98;
            }

            .nexo-analysis-brand p {
                max-width: 430px;
                font-size: 1rem;
            }

            .nexo-analysis-content {
                padding: 42px 52px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .nexo-analysis-content h2 {
                font-size: clamp(2rem, 3vw, 3rem);
            }

            .nexo-analysis-content > p {
                font-size: 1rem;
            }
        }
    </style>
</x-layouts.public>
