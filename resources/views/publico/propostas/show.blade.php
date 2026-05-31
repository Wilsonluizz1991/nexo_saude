<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Propostas comerciais | Nexo Saúde</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #F3F7FC;
            color: #061C3F;
            font-family: Inter, Arial, sans-serif;
        }

        .nexo-public-shell {
            width: min(920px, calc(100% - 32px));
            margin: 0 auto;
            padding: 48px 0;
        }

        .nexo-public-panel {
            background: #FFFFFF;
            border: 1px solid #E3ECF7;
            border-radius: 24px;
            box-shadow: 0 24px 52px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .nexo-public-header {
            padding: 32px;
            border-bottom: 1px solid #E8EEF6;
        }

        .nexo-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #2F80ED;
            font-size: 0.88rem;
            font-weight: 900;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(1.8rem, 4vw, 2.7rem);
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        p {
            margin: 0;
            color: #64748B;
            line-height: 1.6;
        }

        .nexo-proposal-list {
            display: grid;
            gap: 14px;
            padding: 32px;
        }

        .nexo-proposal-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            border: 1px solid #E4EBF5;
            border-radius: 18px;
            padding: 18px;
            background: #F8FBFF;
        }

        .nexo-proposal-item strong {
            display: block;
            font-size: 1rem;
            font-weight: 900;
        }

        .nexo-proposal-item span {
            display: block;
            margin-top: 4px;
            color: #64748B;
            font-size: 0.9rem;
        }

        .nexo-download-btn {
            min-height: 42px;
            padding: 0 16px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #2F80ED;
            color: #FFFFFF;
            font-weight: 900;
            text-decoration: none;
            white-space: nowrap;
        }

        @media (max-width: 640px) {
            .nexo-public-header,
            .nexo-proposal-list {
                padding: 24px;
            }

            .nexo-proposal-item {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <main class="nexo-public-shell">
        <section class="nexo-public-panel">
            <div class="nexo-public-header">
                <div class="nexo-brand">
                    <i class="bi bi-heart-pulse"></i>
                    Nexo Saúde
                </div>

                <h1>{{ $propostas->count() === 1 ? 'Proposta comercial' : 'Propostas comerciais' }}</h1>
                <p>
                    {{ $propostas->count() === 1
                        ? 'A proposta está disponível para visualização e download seguro.'
                        : 'As cotações estão disponíveis para visualização e download seguro.' }}
                </p>
            </div>

            <div class="nexo-proposal-list">
                @foreach($propostas as $proposta)
                    <article class="nexo-proposal-item">
                        <div>
                            <strong>{{ $proposta->titulo }}</strong>
                            <span>
                                {{ $proposta->operadora?->nome ?: 'Operadora não informada' }}
                                @if($proposta->valor_mensal)
                                    · R$ {{ number_format((float) $proposta->valor_mensal, 2, ',', '.') }}
                                @endif
                                @if($proposta->validade)
                                    · validade {{ $proposta->validade->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>

                        <a class="nexo-download-btn" href="{{ route('publico.propostas.download', ['token' => $token, 'proposta' => $proposta]) }}" target="_blank" rel="noopener">
                            <i class="bi bi-file-earmark-pdf"></i>
                            Visualizar
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    </main>
</body>
</html>
