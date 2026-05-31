<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Confirme seu e-mail</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        @media only screen and (min-width: 700px) {
            .nexo-card {
                max-width: 920px !important;
            }

            .nexo-panel {
                display: inline-block !important;
                vertical-align: top !important;
            }

            .nexo-left {
                width: 45% !important;
                min-height: 520px !important;
                border-radius: 24px 0 0 24px !important;
            }

            .nexo-right {
                width: 55% !important;
                min-height: 520px !important;
                border-radius: 0 24px 24px 0 !important;
            }

            .nexo-wrapper {
                padding: 42px 16px !important;
            }
        }
    </style>
</head>

<body style="margin:0;padding:0;background:#F4F8FD;font-family:Arial,Helvetica,sans-serif;color:#071B3A;">
    <div class="nexo-wrapper" style="width:100%;background:#F4F8FD;padding:16px 10px;box-sizing:border-box;">
        <div class="nexo-card" style="width:100%;max-width:520px;margin:0 auto;background:#FFFFFF;border-radius:24px;overflow:hidden;box-shadow:0 24px 70px rgba(7,27,58,0.16);font-size:0;">

            <div class="nexo-panel nexo-left" style="display:block;width:100%;background:#082B5C;background-image:linear-gradient(145deg,#06224C 0%,#082F66 58%,#06316B 100%);padding:32px 28px 34px;color:#FFFFFF;border-radius:24px 24px 0 0;font-size:16px;">
                <div style="font-size:34px;font-weight:900;letter-spacing:-1px;line-height:1;color:#FFFFFF;">
                    nexo
                </div>

                <div style="margin-top:12px;font-size:15px;letter-spacing:8px;color:#2F80ED;font-weight:900;">
                    SAÚDE
                </div>

                <div style="margin-top:32px;">
                    <span style="display:inline-block;background:rgba(255,255,255,0.10);border-radius:999px;padding:9px 15px;color:#D7E7FF;font-size:13px;font-weight:800;">
                        ✉️ Verificação segura
                    </span>
                </div>

                <h1 style="margin:26px 0 18px;font-size:30px;line-height:1.12;font-weight:900;color:#FFFFFF;">
                    Confirmação de e-mail
                </h1>

                <p style="margin:0;color:#D7E7FF;font-size:16px;line-height:1.65;">
                    Confirme seu endereço de e-mail para liberar o acesso à sua conta na Nexo Saúde.
                </p>

                <div style="height:1px;background:rgba(255,255,255,0.14);margin:28px 0 22px;"></div>

                <p style="margin:0 0 13px;color:#FFFFFF;font-size:14px;font-weight:800;">
                    ✓ Validação segura
                </p>

                <p style="margin:0 0 13px;color:#FFFFFF;font-size:14px;font-weight:800;">
                    ⏳ Expira em {{ $expiresIn }} minutos
                </p>

                <p style="margin:0;color:#FFFFFF;font-size:14px;font-weight:800;">
                    🔒 Proteção da conta
                </p>
            </div>

            <div class="nexo-panel nexo-right" style="display:block;width:100%;background:#FFFFFF;padding:32px 28px 34px;border-radius:0 0 24px 24px;font-size:16px;">
                <div style="display:inline-block;background:#EAF3FF;color:#2F80ED;border-radius:999px;padding:8px 14px;font-size:12px;font-weight:900;margin-bottom:22px;">
                    Ativação de acesso
                </div>

                <h2 style="margin:0 0 18px;color:#071B3A;font-size:30px;line-height:1.12;font-weight:900;">
                    Confirme seu e-mail
                </h2>

                <p style="margin:0 0 18px;color:#52657D;font-size:15px;line-height:1.7;">
                    Olá, <strong style="color:#071B3A;">{{ $user->name }}</strong>.
                </p>

                <p style="margin:0 0 26px;color:#52657D;font-size:15px;line-height:1.7;">
                    Para liberar o acesso à sua conta na Nexo Saúde, confirme seu e-mail clicando no botão abaixo.
                </p>

                <a href="{{ $url }}" style="display:block;width:100%;background:#066BFF;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:900;padding:16px 22px;border-radius:14px;text-align:center;box-shadow:0 14px 28px rgba(6,107,255,0.26);margin-bottom:26px;">
                    Confirmar e-mail
                </a>

                <div style="background:#F7FAFE;border:1px solid #DDEAFF;border-radius:18px;padding:17px 18px;margin:0 0 24px;">
                    <p style="margin:0;color:#071B3A;font-size:15px;font-weight:900;line-height:1.5;">
                        ⓘ Você não criou uma conta?
                    </p>

                    <p style="margin:7px 0 0;color:#52657D;font-size:14px;line-height:1.6;">
                        Ignore este e-mail. Nenhum acesso será liberado sem a confirmação.
                    </p>
                </div>

                <p style="margin:0 0 8px;color:#7B8BA1;font-size:13px;line-height:1.6;">
                    Se o botão não funcionar, copie e cole este link no navegador:
                </p>

                <p style="margin:0;word-break:break-all;overflow-wrap:break-word;font-size:12px;line-height:1.6;">
                    <a href="{{ $url }}" style="color:#066BFF;text-decoration:none;font-weight:800;">{{ $url }}</a>
                </p>
            </div>
        </div>

        <p style="max-width:920px;margin:18px auto 0;text-align:center;color:#7B8BA1;font-size:12px;line-height:1.6;">
            Nexo Saúde · CRM inteligente para corretores de planos de saúde
        </p>
    </div>
</body>
</html>