<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexo Saúde</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            background: #020b22;
            font-family: Arial, Helvetica, sans-serif;
            color: #ffffff;
        }

        body {
            overflow-x: hidden;
        }

        .nexo-home {
            width: 100%;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            padding: 38px 72px 42px;
            background:
                radial-gradient(circle at 88% 18%, rgba(0, 94, 255, 0.28), transparent 34%),
                radial-gradient(circle at 12% 78%, rgba(0, 110, 255, 0.22), transparent 30%),
                linear-gradient(135deg, #020817 0%, #03183b 48%, #020b22 100%);
        }

        .nexo-home::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 48px 48px;
            opacity: .45;
            pointer-events: none;
        }

        .nexo-home::after {
            content: "";
            position: absolute;
            left: -12%;
            right: -12%;
            bottom: 185px;
            height: 220px;
            background: radial-gradient(ellipse at center, rgba(0, 110, 255, .55), transparent 64%);
            filter: blur(18px);
            opacity: .75;
            pointer-events: none;
            z-index: 1;
        }

        .nexo-wave {
            position: absolute;
            left: -8%;
            right: -8%;
            bottom: 168px;
            height: 240px;
            z-index: 2;
            opacity: .95;
            background:
                linear-gradient(100deg, transparent 0%, rgba(0, 92, 255, .16) 35%, rgba(0, 176, 255, .82) 64%, rgba(0, 95, 255, .22) 84%, transparent 100%);
            clip-path: polygon(0 66%, 11% 72%, 25% 68%, 39% 58%, 52% 63%, 66% 49%, 77% 51%, 91% 34%, 100% 26%, 100% 100%, 0 100%);
            filter: drop-shadow(0 0 26px rgba(0, 128, 255, .85));
        }

        .nexo-wave::after {
            content: "";
            position: absolute;
            inset: 68px 0 0;
            background-image: radial-gradient(circle, rgba(0, 132, 255, .9) 1px, transparent 1.5px);
            background-size: 18px 14px;
            opacity: .42;
            transform: perspective(620px) rotateX(55deg);
            transform-origin: bottom center;
        }

        .nexo-header,
        .nexo-main,
        .nexo-footer {
            position: relative;
            z-index: 5;
        }

        .nexo-header {
            height: 82px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nexo-logo img {
            height: 82px;
            width: auto;
            display: block;
            object-fit: contain;
        }

        .nexo-nav {
            display: flex;
            align-items: center;
            gap: 48px;
            margin-left: auto;
            margin-right: 72px;
        }

        .nexo-nav a {
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            opacity: .95;
        }

        .nexo-header-actions {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .nexo-btn {
            height: 56px;
            min-width: 148px;
            border-radius: 999px;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: .1px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: #ffffff;
            border: 1px solid rgba(92, 154, 255, .46);
            background:
                linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.02)),
                rgba(3, 21, 55, .54);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.14),
                0 14px 34px rgba(0, 0, 0, .18);
            transition: .22s ease;
        }

        .nexo-btn:hover {
            transform: translateY(-2px);
            border-color: rgba(78, 170, 255, .9);
            background:
                linear-gradient(135deg, rgba(16, 119, 255, .22), rgba(255,255,255,.03)),
                rgba(3, 21, 55, .72);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.18),
                0 18px 40px rgba(0, 102, 255, .18);
        }

        .nexo-btn-primary {
            min-width: 206px;
            border: 0;
            color: #ffffff;
            background:
                linear-gradient(135deg, #19b8ff 0%, #0874ff 42%, #0057ff 100%);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.28),
                0 18px 44px rgba(0, 102, 255, .42),
                0 0 0 1px rgba(85, 170, 255, .34);
        }

        .nexo-btn-primary:hover {
            background:
                linear-gradient(135deg, #32c4ff 0%, #1580ff 42%, #0060ff 100%);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.32),
                0 22px 52px rgba(0, 102, 255, .52),
                0 0 0 1px rgba(110, 190, 255, .48);
        }

        .nexo-btn-arrow {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.16);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.18);
            font-size: 17px;
            line-height: 1;
        }

        .nexo-main {
            display: grid;
            grid-template-columns: 48% 52%;
            gap: 36px;
            padding-top: 92px;
            min-height: 672px;
        }

        .nexo-title {
            font-size: 64px;
            line-height: 1.08;
            letter-spacing: -2.4px;
            font-weight: 900;
            color: #ffffff;
            max-width: 620px;
        }

        .nexo-title span {
            color: #0874ff;
        }

        .nexo-subtitle {
            margin-top: 34px;
            max-width: 650px;
            font-size: 25px;
            line-height: 1.48;
            font-weight: 400;
            color: rgba(255,255,255,.9);
        }

        .nexo-hero-actions {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-top: 48px;
        }

        .nexo-metrics {
            display: flex;
            align-items: center;
            gap: 32px;
            margin-top: 64px;
        }

        .nexo-metric {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-right: 32px;
            border-right: 1px solid rgba(255,255,255,.24);
        }

        .nexo-metric:last-child {
            border-right: 0;
        }

        .nexo-metric img {
            width: 44px;
            height: 44px;
            display: block;
            object-fit: contain;
        }

        .nexo-metric strong {
            display: block;
            font-size: 28px;
            line-height: 1.05;
            font-weight: 900;
            color: #ffffff;
        }

        .nexo-metric small {
            display: block;
            margin-top: 5px;
            font-size: 15px;
            line-height: 1.25;
            color: rgba(255,255,255,.84);
        }

        .nexo-cards {
            width: 82%;
            margin-left: auto;
            margin-right: 48px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            align-content: start;
        }

        .nexo-card {
            min-height: 178px;
            padding: 26px 24px;
            border-radius: 16px;
            background: linear-gradient(145deg, rgba(9, 35, 84, .88), rgba(4, 20, 52, .72));
            border: 1px solid rgba(31, 111, 255, .58);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
        }

        .nexo-card-icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            overflow: visible;
        }

        .nexo-card-icon img {
            width: 56px;
            height: 56px;
            display: block;
            object-fit: contain;
            transform: scale(2.8);
            transform-origin: center;
        }

        .nexo-card h3 {
            font-size: 15px;
            line-height: 1.35;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .nexo-card p {
            font-size: 13px;
            line-height: 1.6;
            font-weight: 400;
            color: rgba(255,255,255,.84);
        }

        .nexo-footer {
            min-height: 205px;
            margin-top: 8px;
            border-radius: 16px;
            border: 1px solid rgba(62, 134, 255, .42);
            background: rgba(4, 24, 61, .74);
            backdrop-filter: blur(18px);
            display: grid;
            grid-template-columns: 28% 22% 22% 28%;
            padding: 30px 42px;
        }

        .footer-brand,
        .footer-column,
        .footer-contact {
            min-height: 145px;
        }

        .footer-brand {
            padding-right: 55px;
            border-right: 1px solid rgba(255,255,255,.18);
        }

        .footer-brand img {
            height: 78px;
            width: auto;
            object-fit: contain;
            display: block;
            margin-bottom: 18px;
        }

        .footer-brand p {
            font-size: 17px;
            line-height: 1.62;
            color: rgba(255,255,255,.88);
        }

        .footer-column,
        .footer-contact {
            padding-left: 72px;
            border-right: 1px solid rgba(255,255,255,.18);
        }

        .footer-contact {
            border-right: 0;
        }

        .footer-column h4,
        .footer-contact h4 {
            font-size: 19px;
            line-height: 1;
            margin-bottom: 24px;
            color: #ffffff;
            font-weight: 900;
        }

        .footer-column a {
            display: block;
            color: rgba(255,255,255,.88);
            text-decoration: none;
            font-size: 17px;
            line-height: 1;
            margin-bottom: 18px;
        }

        .footer-contact p {
            display: flex;
            align-items: center;
            gap: 14px;
            color: rgba(255,255,255,.88);
            font-size: 17px;
            line-height: 1;
            margin-bottom: 18px;
        }

        .footer-contact-icon {
            width: 18px;
            height: 18px;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1280px) {
            .nexo-home {
                padding: 32px 56px 38px;
            }

            .nexo-title {
                font-size: 56px;
            }

            .nexo-subtitle {
                font-size: 22px;
            }

            .nexo-nav {
                margin-right: 42px;
            }

            .nexo-cards {
                width: 84%;
                margin-right: 34px;
                gap: 22px;
            }
        }

        @media (max-width: 1100px) {
            .nexo-main {
                grid-template-columns: 1fr;
                gap: 44px;
            }

            .nexo-cards {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .nexo-footer {
                grid-template-columns: 1fr 1fr;
                gap: 32px;
            }

            .footer-brand,
            .footer-column,
            .footer-contact {
                border-right: 0;
                padding-left: 0;
                padding-right: 0;
            }
        }

        @media (max-width: 768px) {
            .nexo-home {
                padding: 24px 20px 30px;
            }

            .nexo-header {
                height: auto;
                align-items: flex-start;
                flex-direction: column;
                gap: 24px;
            }

            .nexo-nav {
                margin: 0;
                gap: 20px;
                flex-wrap: wrap;
            }

            .nexo-header-actions,
            .nexo-hero-actions {
                width: 100%;
                gap: 14px;
                flex-direction: column;
            }

            .nexo-btn {
                width: 100%;
            }

            .nexo-main {
                padding-top: 52px;
            }

            .nexo-title {
                font-size: 42px;
                letter-spacing: -1.4px;
            }

            .nexo-subtitle {
                font-size: 19px;
            }

            .nexo-metrics {
                align-items: flex-start;
                flex-direction: column;
                gap: 20px;
            }

            .nexo-metric {
                border-right: 0;
                padding-right: 0;
            }

            .nexo-cards {
                grid-template-columns: 1fr;
            }

            .nexo-footer {
                grid-template-columns: 1fr;
                padding: 28px;
            }
        }
    </style>
</head>

<body>
    <section class="nexo-home">
        <div class="nexo-wave"></div>

        <header class="nexo-header">
            <a href="/" class="nexo-logo">
                <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
            </a>

            <nav class="nexo-nav">
                <a href="#sobre">Sobre nós</a>
                <a href="#recursos">Recursos</a>
                <a href="#contato">Contato</a>
            </nav>

            <div class="nexo-header-actions">
                <a href="{{ route('login') }}" class="nexo-btn">Entrar</a>
                <a href="{{ route('register') }}" class="nexo-btn nexo-btn-primary">
                    Começar agora
                    <span class="nexo-btn-arrow">→</span>
                </a>
            </div>
        </header>

        <main class="nexo-main">
            <section>
                <h1 class="nexo-title">
                    O futuro da corretagem já começou.</span>
                </h1>

                <p class="nexo-subtitle">
                    Uma nova forma de gerenciar leads, propostas, implantações e clientes com inteligência, automação e excelência operacional.
                </p>

                <div class="nexo-metrics">
                    <div class="nexo-metric">
                        <img src="{{ asset('assets/usuarios.svg') }}" alt="">
                        <div>
                            <strong>CRM + IA</strong>
                            <small>tecnologia criada para corretores modernos</small>
                        </div>
                    </div>

                    <div class="nexo-metric">
                        <img src="{{ asset('assets/alvo.svg') }}" alt="">
                        <div>
                            <strong>Fluxo Completo</strong>
                            <small>do lead à implantação em uma única plataforma</small>
                        </div>
                    </div>

                    <div class="nexo-metric">
                        <img src="{{ asset('assets/satisfacao.svg') }}" alt="">
                        <div>
                            <strong>100% Digital</strong>
                            <small>vendas, documentos e acompanhamento online</small>
                        </div>
                    </div>
                </div>
            </section>

            <section class="nexo-cards" id="recursos">
                <article class="nexo-card">
                    <div class="nexo-card-icon">
                        <img src="{{ asset('assets/visao-360.png') }}" alt="">
                    </div>
                    <h3>Visão 360° do seu negócio</h3>
                    <p>Tenha uma visão completa da sua carteira, produção e resultados.</p>
                </article>

                <article class="nexo-card">
                    <div class="nexo-card-icon">
                        <img src="{{ asset('assets/produtividade.png') }}" alt="">
                    </div>
                    <h3>Produtividade que escala</h3>
                    <p>Automatize tarefas e ganhe tempo para o que realmente importa.</p>
                </article>

                <article class="nexo-card">
                    <div class="nexo-card-icon">
                        <img src="{{ asset('assets/inteligencia.png') }}" alt="">
                    </div>
                    <h3>Decisões com inteligência</h3>
                    <p>Dados e relatórios que mostram o caminho para crescer.</p>
                </article>

                <article class="nexo-card">
                    <div class="nexo-card-icon">
                        <img src="{{ asset('assets/seguranca.png') }}" alt="">
                    </div>
                    <h3>Segurança e confiabilidade</h3>
                    <p>Seus dados protegidos com o que há de mais avançado.</p>
                </article>

                <article class="nexo-card">
                    <div class="nexo-card-icon">
                        <img src="{{ asset('assets/suporte.png') }}" alt="">
                    </div>
                    <h3>Suporte que entende você</h3>
                    <p>Time especializado para apoiar você em cada passo.</p>
                </article>

                <article class="nexo-card">
                    <div class="nexo-card-icon">
                        <img src="{{ asset('assets/evolucao.png') }}" alt="">
                    </div>
                    <h3>Sempre evoluindo</h3>
                    <p>Atualizações constantes para você estar sempre à frente.</p>
                </article>
            </section>
        </main>

        <footer class="nexo-footer" id="contato">
            <div class="footer-brand">
                <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
                <p>
                    Conectando corretores ao futuro da saúde com tecnologia,
                    inteligência e resultados.
                </p>
            </div>

            <div class="footer-column" id="sobre">
                <h4>Sobre nós</h4>
                <a href="#">Quem somos</a>
                <a href="#">Nosso propósito</a>
                <a href="#">Recursos</a>
            </div>

            <div class="footer-column">
                <h4>Recursos</h4>
                <a href="#">Funcionalidades</a>
                <a href="#">Planos</a>
                <a href="#">Integrações</a>
            </div>

            <div class="footer-contact">
                <h4>Contato</h4>

                <p>
                    <span class="footer-contact-icon">✉</span>
                    contato@nexosaude.com.br
                </p>

                <p>
                    <span class="footer-contact-icon">☎</span>
                    (11) 99999-9999
                </p>
            </div>
        </footer>
    </section>
</body>
</html>