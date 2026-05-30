<x-layouts.app title="Redefinir senha | Nexo Saude">
    <main class="nexo-login-page">
        <section class="nexo-login-card">
            <div class="nexo-login-brand">
                <div class="nexo-login-logo">
                    <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saude">
                </div>

                <span class="nexo-login-badge">
                    <i class="bi bi-key"></i>
                    Novo acesso
                </span>

                <h1>Crie uma nova senha</h1>

                <p>
                    Use uma senha segura para proteger seus dados comerciais, propostas e carteira de clientes.
                </p>
            </div>

            <div class="nexo-login-form-area">
                <div class="nexo-login-form-header">
                    <span>Redefinicao de senha</span>

                    <h2>Nova senha</h2>

                    <p>
                        Informe a nova senha e confirme para concluir a recuperacao do acesso.
                    </p>
                </div>

                <form method="post" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>

                        <input
                            name="email"
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $email) }}"
                            required
                        >

                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nova senha</label>

                        <input
                            name="password"
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required
                        >

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirmar nova senha</label>

                        <input
                            name="password_confirmation"
                            type="password"
                            class="form-control"
                            required
                        >
                    </div>

                    <button class="nexo-login-submit">
                        Redefinir senha
                        <i class="bi bi-arrow-right"></i>
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
            min-height: 620px;
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
        .nexo-login-form-area .form-label { color: #061C3F; font-weight: 850; margin-bottom: 7px; }
        .nexo-login-form-area .form-control {
            min-height: 52px;
            border-radius: 14px;
            border: 1px solid #D8E2EF;
            color: #162033;
            padding: 12px 15px;
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
        }
        @media (max-width: 992px) {
            .nexo-login-card { grid-template-columns: 1fr; }
            .nexo-login-brand, .nexo-login-form-area { padding: 32px 24px; }
        }
    </style>
</x-layouts.app>
