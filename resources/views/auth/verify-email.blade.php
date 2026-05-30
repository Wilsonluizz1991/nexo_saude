<x-layouts.app title="Confirmar e-mail | Nexo Saude">
    <main class="nexo-login-page">
        <section class="nexo-login-card">
            <div class="nexo-login-brand">
                <div class="nexo-login-logo">
                    <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saude">
                </div>

                <span class="nexo-login-badge">
                    <i class="bi bi-envelope-check"></i>
                    Verificacao de seguranca
                </span>

                <h1>Confirme seu e-mail</h1>

                <p>
                    Enviamos um link seguro para {{ auth()->user()->email }}. Confirme o e-mail para acessar sua conta Nexo Saude.
                </p>
            </div>

            <div class="nexo-login-form-area">
                <div class="nexo-login-form-header">
                    <span>Quase pronto</span>

                    <h2>Verifique sua caixa de entrada</h2>

                    <p>
                        O link expira por seguranca. Caso nao encontre a mensagem, confira a pasta de spam ou solicite um novo envio.
                    </p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="post" action="{{ route('verification.send') }}">
                    @csrf

                    <button class="nexo-login-submit">
                        Reenviar e-mail de confirmacao
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </form>

                <form method="post" action="{{ route('logout') }}" class="mt-3">
                    @csrf

                    <button class="nexo-login-secondary">
                        Sair da conta
                    </button>
                </form>
            </div>
        </section>
    </main>

    <style>
        html, body { margin: 0; padding: 0; min-height: 100%; }
        .nexo-login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                linear-gradient(135deg, #061C3F 0%, #0D2F57 48%, #F4F7FB 48%, #FFFFFF 100%);
        }
        .nexo-login-card {
            width: min(980px, 100%);
            min-height: 560px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            border-radius: 30px;
            background: #FFFFFF;
            box-shadow: 0 28px 80px rgba(6, 28, 63, 0.20);
        }
        .nexo-login-brand {
            padding: 42px 52px;
            color: #FFFFFF;
            background: linear-gradient(180deg, #061C3F 0%, #0F3A68 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .nexo-login-logo { margin-bottom: 28px; }
        .nexo-login-logo img { width: 100%; max-width: 250px; display: block; }
        .nexo-login-badge {
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
            color: #FFFFFF;
            font-size: clamp(2.1rem, 3vw, 3.4rem);
            line-height: 1;
            font-weight: 950;
            margin: 0 0 16px;
        }
        .nexo-login-brand p {
            max-width: 430px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 1rem;
            line-height: 1.5;
            margin: 0;
        }
        .nexo-login-form-area {
            padding: 42px 52px;
            background: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .nexo-login-form-header { margin-bottom: 24px; }
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
            margin: 0 0 6px;
        }
        .nexo-login-form-header p { color: #64748B; margin: 0; }
        .nexo-login-submit, .nexo-login-secondary {
            width: 100%;
            min-height: 56px;
            border: 0;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 950;
        }
        .nexo-login-submit {
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.24);
        }
        .nexo-login-secondary { background: #EEF4FB; color: #0F3A68; }
        @media (max-width: 992px) {
            .nexo-login-card { grid-template-columns: 1fr; }
            .nexo-login-brand, .nexo-login-form-area { padding: 32px 24px; }
        }
    </style>
</x-layouts.app>
