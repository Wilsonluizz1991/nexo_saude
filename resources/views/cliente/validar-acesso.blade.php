<x-layouts.public title="Validar acesso | Nexo Saúde">
    @php
        $isPj = strtoupper((string) $preCadastro->pessoa) === 'PJ';
        $documentoLabel = $isPj ? 'CNPJ' : 'CPF do titular';
        $instrucao = $isPj
            ? 'Digite os 6 primeiros números do CNPJ'
            : 'Digite os 6 primeiros números do CPF do titular';
    @endphp

    <main class="nexo-access-page">
        <section class="nexo-access-card">
            <div class="nexo-access-brand">
                <div class="nexo-access-logo">
                    <img
                        src="{{ asset('assets/nexo-logo-topo.png') }}"
                        alt="Nexo Saúde"
                    >
                </div>

                <span class="nexo-access-badge">
                    <i class="bi bi-shield-lock"></i>
                    Acesso protegido
                </span>

                <h1>
                    Validar acesso ao pré-cadastro
                </h1>

                <p>
                    Para proteger suas informações, confirme os primeiros números do documento antes de acessar o formulário.
                </p>

                <div class="nexo-access-steps">
                    <div class="active">
                        <i class="bi bi-link-45deg"></i>
                        <span>Link único recebido</span>
                    </div>

                    <div class="active">
                        <i class="bi bi-shield-check"></i>
                        <span>Validação de segurança</span>
                    </div>

                    <div>
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Acesso ao formulário</span>
                    </div>
                </div>
            </div>

            <div class="nexo-access-content">
                <div class="nexo-access-lock">
                    <div class="nexo-access-lock-glow"></div>

                    <div class="nexo-access-lock-ring ring-one"></div>
                    <div class="nexo-access-lock-ring ring-two"></div>

                    <div class="nexo-access-lock-core">
                        <i class="bi bi-lock"></i>
                    </div>
                </div>

                <span class="nexo-access-status">
                    Confirmação necessária
                </span>

                <h2>
                    Validar acesso
                </h2>

                <p>
                    {{ $instrucao }} para liberar o preenchimento do pré-cadastro.
                </p>

                <form method="post" action="{{ route('cliente.pre-cadastro.validar-acesso', ['slug' => $slug, 'token' => $preCadastro->token]) }}" class="nexo-access-form">
                    @csrf

                    <div>
                        <label class="form-label">
                            {{ $documentoLabel }}
                        </label>

                        <input
                            class="form-control"
                            name="prefixo_documento"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            placeholder="000000"
                            value="{{ old('prefixo_documento') }}"
                            required
                            autofocus
                            data-six-digits
                        >

                        @error('prefixo_documento')
                            <div class="nexo-access-error">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="nexo-access-hint">
                            Não exibimos o {{ $documentoLabel }} completo nesta tela.
                        </div>
                    </div>

                    <button class="nexo-access-submit">
                        Acessar formulário
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <div class="nexo-access-message">
                    <i class="bi bi-info-circle"></i>

                    <div>
                        <strong>
                            Seu acesso é privado.
                        </strong>

                        <span>
                            Esta validação ajuda a impedir que terceiros acessem seus documentos pelo link público.
                        </span>
                    </div>
                </div>
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

        .nexo-access-page {
            min-height: 100vh;
            padding: 16px 12px;
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                linear-gradient(135deg, #061C3F 0%, #0D2F57 34%, #F4F7FB 34%, #FFFFFF 100%);
        }

        .nexo-access-card {
            width: min(1120px, 100%);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr;
            overflow: hidden;
            border-radius: 26px;
            background: #FFFFFF;
            box-shadow: 0 24px 60px rgba(6, 28, 63, 0.18);
        }

        .nexo-access-brand {
            position: relative;
            padding: 24px 20px;
            color: #FFFFFF;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.28), transparent 32%),
                linear-gradient(180deg, #061C3F 0%, #0F3A68 100%);
            overflow: hidden;
        }

        .nexo-access-brand::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            right: -70px;
            bottom: -70px;
            background: rgba(47, 128, 237, 0.16);
        }

        .nexo-access-logo {
            position: relative;
            z-index: 1;
            margin-bottom: 22px;
        }

        .nexo-access-logo img {
            width: 100%;
            max-width: 220px;
            height: auto;
            display: block;
        }

        .nexo-access-badge {
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

        .nexo-access-brand h1 {
            position: relative;
            z-index: 1;
            color: #FFFFFF;
            font-size: 2rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.055em;
            margin: 0 0 12px;
        }

        .nexo-access-brand p {
            position: relative;
            z-index: 1;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0;
        }

        .nexo-access-steps {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }

        .nexo-access-steps div {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.92rem;
            font-weight: 750;
        }

        .nexo-access-steps div.active {
            color: rgba(255, 255, 255, 0.94);
        }

        .nexo-access-steps i {
            color: #7DB5FF;
            font-size: 1rem;
        }

        .nexo-access-content {
            position: relative;
            padding: 26px 20px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.07), transparent 26%),
                #FFFFFF;
        }

        .nexo-access-lock {
            position: relative;
            width: 74px;
            height: 74px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nexo-access-lock-glow {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: radial-gradient(
                circle,
                rgba(47, 128, 237, 0.20) 0%,
                rgba(47, 128, 237, 0.05) 60%,
                transparent 78%
            );
            animation: nexoAccessPulse 1.5s ease-in-out infinite;
        }

        .nexo-access-lock-ring {
            position: absolute;
            border-radius: 999px;
            border: 1.5px solid rgba(47, 128, 237, 0.18);
        }

        .ring-one {
            width: 74px;
            height: 74px;
            border-top-color: rgba(47, 128, 237, 0.8);
            animation: nexoAccessRotate 2.2s linear infinite;
        }

        .ring-two {
            width: 56px;
            height: 56px;
            border-style: dashed;
            border-color: rgba(47, 128, 237, 0.28);
            animation: nexoAccessRotateReverse 1.6s linear infinite;
        }

        .nexo-access-lock-core {
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
            animation: nexoAccessFloat 1.3s ease-in-out infinite;
        }

        .nexo-access-status {
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

        .nexo-access-content h2 {
            color: #061C3F;
            font-size: 2rem;
            line-height: 1.02;
            font-weight: 950;
            letter-spacing: -0.06em;
            margin: 0 0 14px;
        }

        .nexo-access-content > p {
            color: #64748B;
            font-size: 0.95rem;
            line-height: 1.55;
            margin: 0;
        }

        .nexo-access-form {
            display: grid;
            gap: 18px;
            margin-top: 22px;
        }

        .nexo-access-form .form-label {
            color: #061C3F;
            font-weight: 850;
            margin-bottom: 8px;
        }

        .nexo-access-form .form-control {
            min-height: 58px;
            border-radius: 15px;
            border: 1px solid #D8E2EF;
            color: #061C3F;
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: 0.2em;
            text-align: center;
            padding: 12px 16px;
        }

        .nexo-access-form .form-control::placeholder {
            color: #CBD5E1;
            letter-spacing: 0.2em;
        }

        .nexo-access-form .form-control:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-access-error {
            color: #E14D4D;
            font-size: 0.85rem;
            font-weight: 800;
            margin-top: 8px;
        }

        .nexo-access-hint {
            color: #64748B;
            font-size: 0.84rem;
            font-weight: 650;
            margin-top: 8px;
        }

        .nexo-access-submit {
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

        .nexo-access-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(47, 128, 237, 0.30);
        }

        .nexo-access-message {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-top: 22px;
            padding: 18px;
            border-radius: 20px;
            background: #F8FBFF;
            border: 1px solid #DCEBFF;
        }

        .nexo-access-message i {
            color: #2F80ED;
            font-size: 1.18rem;
            margin-top: 2px;
        }

        .nexo-access-message strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
            margin-bottom: 4px;
        }

        .nexo-access-message span {
            display: block;
            color: #64748B;
            line-height: 1.42;
            font-size: 0.92rem;
        }

        @keyframes nexoAccessRotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes nexoAccessRotateReverse {
            from {
                transform: rotate(360deg);
            }

            to {
                transform: rotate(0deg);
            }
        }

        @keyframes nexoAccessPulse {
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

        @keyframes nexoAccessFloat {
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
            html,
            body {
                height: 100%;
                overflow: hidden;
            }

            .nexo-access-page {
                height: 100vh;
                padding: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                background:
                    radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                    linear-gradient(135deg, #061C3F 0%, #0D2F57 48%, #F4F7FB 48%, #FFFFFF 100%);
            }

            .nexo-access-card {
                height: min(92vh, 720px);
                grid-template-columns: 0.95fr 1.05fr;
                border-radius: 30px;
            }

            .nexo-access-brand {
                padding: 42px 52px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .nexo-access-logo img {
                max-width: 250px;
            }

            .nexo-access-brand h1 {
                font-size: clamp(2.1rem, 3vw, 3.6rem);
                line-height: 0.98;
            }

            .nexo-access-brand p {
                max-width: 430px;
                font-size: 1rem;
            }

            .nexo-access-content {
                padding: 42px 52px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .nexo-access-content h2 {
                font-size: clamp(2rem, 3vw, 3rem);
            }

            .nexo-access-content > p {
                font-size: 1rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-six-digits]').forEach((input) => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '').slice(0, 6);
                });
            });
        });
    </script>
</x-layouts.public>
