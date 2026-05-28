<x-layouts.public title="Avaliar atendimento | Nexo Saúde">
    <main class="nexo-review-page">
        <section class="nexo-review-card">
            <div class="nexo-review-brand">
                <img src="{{ asset('assets/nexo-logo-claro.png') }}" alt="Nexo Saúde">
            </div>

            @if(session('status'))
                <div class="nexo-review-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <strong>Avaliação enviada</strong>
                        <p>{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            <div class="nexo-review-heading">
                <span>Avaliação de atendimento</span>
                <h1>{{ $avaliacao->status === 'respondida' ? 'Obrigado pela sua avaliação' : 'Como foi sua experiência?' }}</h1>
                <p>
                    Sua opinião ajuda o corretor {{ $perfil?->nome_publico ?? $avaliacao->corretor?->name }} a manter um atendimento cada vez melhor.
                </p>
            </div>

            @if($avaliacao->status === 'respondida')
                <div class="nexo-review-finished">
                    <div class="nexo-review-big-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= round($avaliacao->media) ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </div>

                    <strong>{{ number_format($avaliacao->media, 1, ',', '.') }} de 5</strong>
                    <p>Sua resposta já foi registrada com segurança.</p>
                </div>
            @else
                <form method="post" action="{{ route('publico.avaliacoes.store', $avaliacao->token) }}" class="nexo-review-form">
                    @csrf

                    @foreach([
                        'nota_atendimento' => 'Como você avalia o atendimento do corretor?',
                        'nota_clareza' => 'O corretor explicou as opções e etapas de forma clara?',
                        'nota_agilidade' => 'Como você avalia o tempo de resposta e acompanhamento?',
                        'nota_confianca' => 'Você se sentiu seguro(a) durante o processo de contratação?',
                        'nota_recomendacao' => 'Você recomendaria este corretor para amigos ou familiares?',
                    ] as $campo => $pergunta)
                        <div class="nexo-review-question">
                            <label>{{ $pergunta }}</label>

                            <div class="nexo-star-options">
                                @for($nota = 5; $nota >= 1; $nota--)
                                    <input id="{{ $campo }}_{{ $nota }}" type="radio" name="{{ $campo }}" value="{{ $nota }}" @checked(old($campo) == $nota) required>
                                    <label for="{{ $campo }}_{{ $nota }}" title="{{ $nota }} estrela(s)">
                                        <i class="bi bi-star-fill"></i>
                                    </label>
                                @endfor
                            </div>

                            @error($campo)
                                <small class="text-danger fw-bold">{{ $message }}</small>
                            @enderror
                        </div>
                    @endforeach

                    <div class="nexo-review-question">
                        <label for="comentario">Deseja deixar um comentário sobre sua experiência?</label>
                        <textarea id="comentario" name="comentario" rows="4" maxlength="1000" placeholder="Seu comentário é opcional.">{{ old('comentario') }}</textarea>

                        @error('comentario')
                            <small class="text-danger fw-bold">{{ $message }}</small>
                        @enderror
                    </div>

                    <button class="nexo-review-submit">
                        Enviar avaliação
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            @endif
        </section>
    </main>

    <style>
        .nexo-review-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 16px;
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 34%),
                linear-gradient(180deg, #F4F7FB 0%, #FFFFFF 100%);
        }

        .nexo-review-card {
            width: min(760px, 100%);
            border: 1px solid #E2EAF5;
            border-radius: 30px;
            background: #FFFFFF;
            padding: 34px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
        }

        .nexo-review-brand img {
            max-height: 76px;
            margin-bottom: 22px;
        }

        .nexo-review-heading span {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 11px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.78rem;
            font-weight: 950;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .nexo-review-heading h1 {
            color: #061C3F;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 950;
            letter-spacing: -0.05em;
            margin: 14px 0 8px;
        }

        .nexo-review-heading p {
            color: #64748B;
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 24px;
        }

        .nexo-review-form {
            display: grid;
            gap: 18px;
        }

        .nexo-review-question {
            display: grid;
            gap: 10px;
            padding: 18px;
            border: 1px solid #E2EAF5;
            border-radius: 20px;
            background: #F8FBFF;
        }

        .nexo-review-question > label {
            color: #061C3F;
            font-size: 1rem;
            font-weight: 950;
        }

        .nexo-star-options {
            direction: rtl;
            display: inline-flex;
            justify-content: flex-end;
            width: fit-content;
            gap: 4px;
        }

        .nexo-star-options input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .nexo-star-options label {
            cursor: pointer;
            color: #CBD5E1;
            font-size: 1.75rem;
            transition: 0.15s ease;
        }

        .nexo-star-options label:hover,
        .nexo-star-options label:hover ~ label,
        .nexo-star-options input:checked ~ label {
            color: #D4AF37;
            transform: translateY(-1px);
        }

        .nexo-review-question textarea {
            width: 100%;
            border: 1px solid #D8E2EF;
            border-radius: 16px;
            color: #061C3F;
            font-weight: 700;
            padding: 14px;
            resize: vertical;
        }

        .nexo-review-question textarea:focus {
            outline: 0;
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-review-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: fit-content;
            min-height: 52px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            font-weight: 950;
            padding: 0 22px;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.24);
        }

        .nexo-review-success,
        .nexo-review-finished {
            border: 1px solid #BEECD3;
            border-radius: 20px;
            background: #EAFBF1;
            color: #145C39;
            padding: 18px;
            margin-bottom: 22px;
        }

        .nexo-review-success {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .nexo-review-success i {
            font-size: 1.5rem;
        }

        .nexo-review-success strong,
        .nexo-review-finished strong {
            display: block;
            font-weight: 950;
        }

        .nexo-review-success p,
        .nexo-review-finished p {
            margin: 2px 0 0;
            font-weight: 750;
        }

        .nexo-review-finished {
            text-align: center;
        }

        .nexo-review-big-stars {
            color: #D4AF37;
            font-size: 2rem;
            margin-bottom: 8px;
        }
    </style>
</x-layouts.public>
