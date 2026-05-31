<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $propostas->count() === 1 ? 'Sua proposta comercial de plano de saúde' : 'Suas cotações de plano de saúde' }}</title>
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
                min-height: 720px !important;
                border-radius: 24px 0 0 24px !important;
            }

            .nexo-right {
                width: 55% !important;
                min-height: 720px !important;
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
                        📄 Propostas disponíveis
                    </span>
                </div>

                <h1 style="margin:26px 0 18px;font-size:30px;line-height:1.12;font-weight:900;color:#FFFFFF;">
                    {{ $propostas->count() === 1 ? 'Sua proposta foi enviada' : 'Suas cotações estão prontas' }}
                </h1>

                <p style="margin:0;color:#D7E7FF;font-size:16px;line-height:1.65;">
                    {{ $propostas->count() === 1
                        ? 'Seu corretor preparou uma proposta personalizada para análise.'
                        : 'Seu corretor selecionou diferentes opções para que você compare e escolha a melhor alternativa.' }}
                </p>

                <div style="height:1px;background:rgba(255,255,255,0.14);margin:28px 0 22px;"></div>

                <p style="margin:0 0 13px;color:#FFFFFF;font-size:14px;font-weight:800;">
                    ✓ Acesso online
                </p>

                <p style="margin:0 0 13px;color:#FFFFFF;font-size:14px;font-weight:800;">
                    📎 Arquivos anexados
                </p>

                <p style="margin:0;color:#FFFFFF;font-size:14px;font-weight:800;">
                    🔒 Compartilhamento seguro
                </p>
            </div>

            <div class="nexo-panel nexo-right" style="display:block;width:100%;background:#FFFFFF;padding:32px 28px 34px;border-radius:0 0 24px 24px;font-size:16px;">

                <div style="display:inline-block;background:#EAF3FF;color:#2F80ED;border-radius:999px;padding:8px 14px;font-size:12px;font-weight:900;margin-bottom:22px;">
                    {{ $propostas->count() === 1 ? 'Nova proposta' : 'Novas cotações' }}
                </div>

                <h2 style="margin:0 0 18px;color:#071B3A;font-size:30px;line-height:1.12;font-weight:900;">
                    Olá, {{ $indicacao->nome_cliente }}
                </h2>

                @if($propostas->count() === 1)
                    <p style="margin:0 0 24px;color:#52657D;font-size:15px;line-height:1.7;">
                        Seu corretor <strong>{{ $corretor->name }}</strong> enviou uma proposta comercial para sua análise. O documento está anexado neste e-mail e também disponível através do botão abaixo.
                    </p>
                @else
                    <p style="margin:0 0 24px;color:#52657D;font-size:15px;line-height:1.7;">
                        Seu corretor <strong>{{ $corretor->name }}</strong> enviou algumas cotações para que você possa comparar as opções disponíveis e escolher a mais adequada para você.
                    </p>
                @endif

                <a href="{{ $linkPublico }}" style="display:block;width:100%;background:#066BFF;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:900;padding:16px 22px;border-radius:14px;text-align:center;box-shadow:0 14px 28px rgba(6,107,255,0.26);margin-bottom:24px;">
                    {{ $propostas->count() === 1 ? 'Visualizar proposta' : 'Visualizar cotações' }}
                </a>

                @if($propostas->isNotEmpty())
                    <div style="background:#F7FAFE;border:1px solid #DDEAFF;border-radius:18px;padding:18px;margin-bottom:24px;">
                        <p style="margin:0 0 14px;color:#071B3A;font-size:15px;font-weight:900;">
                            {{ $propostas->count() === 1 ? 'Proposta enviada' : 'Propostas enviadas' }}
                        </p>

                        @foreach($propostas as $proposta)
                            <div style="padding:12px 0;border-bottom:1px solid #E7EEF8;{{ $loop->last ? 'border-bottom:none;padding-bottom:0;' : '' }}">
                                <div style="font-size:15px;font-weight:800;color:#071B3A;">
                                    {{ $proposta->titulo }}
                                </div>

                                @if($proposta->operadora)
                                    <div style="margin-top:4px;font-size:13px;color:#52657D;">
                                        Operadora: {{ $proposta->operadora->nome }}
                                    </div>
                                @endif

                                @if($proposta->valor_mensal)
                                    <div style="margin-top:6px;font-size:14px;font-weight:800;color:#066BFF;">
                                        R$ {{ number_format((float) $proposta->valor_mensal, 2, ',', '.') }}/mês
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <div style="background:#F7FAFE;border:1px solid #DDEAFF;border-radius:18px;padding:18px;margin-bottom:24px;">
                    <p style="margin:0;color:#071B3A;font-size:15px;font-weight:900;">
                        💡 Próximo passo
                    </p>

                    <p style="margin:8px 0 0;color:#52657D;font-size:14px;line-height:1.6;">
                        {{ $propostas->count() === 1
                            ? 'Analise a proposta enviada e entre em contato com seu corretor para esclarecer qualquer dúvida.'
                            : 'Compare as opções com calma e converse com seu corretor para escolher a melhor alternativa.' }}
                    </p>
                </div>

                <p style="margin:0 0 8px;color:#7B8BA1;font-size:13px;line-height:1.6;">
                    Link alternativo de acesso:
                </p>

                <p style="margin:0;word-break:break-all;overflow-wrap:break-word;font-size:12px;line-height:1.6;">
                    <a href="{{ $linkPublico }}" style="color:#066BFF;text-decoration:none;font-weight:800;">
                        {{ $linkPublico }}
                    </a>
                </p>

            </div>
        </div>

        <p style="max-width:920px;margin:18px auto 0;text-align:center;color:#7B8BA1;font-size:12px;line-height:1.6;">
            Nexo Saúde · CRM inteligente para corretores de planos de saúde
        </p>
    </div>
</body>
</html>