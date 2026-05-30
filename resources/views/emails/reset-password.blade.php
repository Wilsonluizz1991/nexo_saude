<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Redefinir senha</title>
</head>
<body style="margin:0;padding:0;background:#F4F7FB;font-family:Arial,Helvetica,sans-serif;color:#162033;">
    <div style="max-width:640px;margin:0 auto;padding:32px 18px;">
        <div style="background:#FFFFFF;border-radius:18px;overflow:hidden;border:1px solid #D8E2EF;">
            <div style="background:#061C3F;padding:28px 32px;color:#FFFFFF;">
                <div style="font-size:24px;font-weight:800;">Nexo Saude</div>
                <div style="margin-top:8px;color:#DDEBFF;font-size:14px;">Recuperacao de senha</div>
            </div>

            <div style="padding:32px;">
                <h1 style="margin:0 0 14px;color:#061C3F;font-size:26px;">Redefina sua senha</h1>

                <p style="margin:0 0 18px;line-height:1.6;">
                    Ola, {{ $user->name }}. Recebemos uma solicitacao para redefinir a senha da sua conta Nexo Saude.
                </p>

                <p style="margin:0 0 24px;line-height:1.6;">
                    Este link expira em {{ $expiresIn }} minutos e pode ser usado apenas uma vez.
                </p>

                <p style="margin:0 0 28px;">
                    <a href="{{ $url }}" style="display:inline-block;background:#2F80ED;color:#FFFFFF;text-decoration:none;font-weight:800;padding:14px 22px;border-radius:12px;">
                        Redefinir senha
                    </a>
                </p>

                <p style="margin:0 0 10px;color:#64748B;font-size:14px;line-height:1.6;">
                    Se o botao nao funcionar, copie e cole este link no navegador:
                </p>

                <p style="margin:0 0 24px;word-break:break-all;font-size:13px;line-height:1.6;">
                    <a href="{{ $url }}" style="color:#2F80ED;">{{ $url }}</a>
                </p>

                <p style="margin:0;color:#64748B;font-size:14px;line-height:1.6;">
                    Se voce nao solicitou essa redefinicao, ignore este e-mail. Sua senha atual permanecera ativa.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
