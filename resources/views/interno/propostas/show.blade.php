<x-layouts.app title="Propostas | Nexo Saúde">
    <main class="nexo-main">
        <div class="nexo-proposta-header mb-4">
            <div>
                <span class="nexo-page-label">
                    <i class="bi bi-file-earmark-text"></i>
                    Propostas
                </span>

                <h1>{{ $indicacao->nome_cliente }}</h1>

                <p>
                    Anexe novas propostas, compare alternativas e avance para o pré-cadastro quando o cliente aprovar.
                </p>
            </div>

            <a class="nexo-secondary-btn" href="{{ route('paginas.simples', 'propostas') }}">
                <i class="bi bi-arrow-left"></i>
                Voltar para Propostas
            </a>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <section class="nexo-proposta-panel mb-4">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Anexar nova proposta</h2>
                            <p>O registro permanece em Propostas e pode receber quantas alternativas forem necessárias.</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('propostas.store', $indicacao) }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <input type="hidden" value="{{ $indicacao->tipo_plano }}" data-plan-type>

                        <div class="col-md-6">
                            <label class="form-label">Título da proposta</label>
                            <input class="form-control" name="titulo" value="{{ old('titulo', 'Proposta comercial') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Operadora</label>
                            <select class="form-select" name="operadora_id">
                                <option value="">Selecione</option>

                                @foreach($operadoras as $operadora)
                                    <option value="{{ $operadora->id }}" @selected(old('operadora_id') == $operadora->id)>
                                        {{ $operadora->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Valor mensal</label>
                            <input class="form-control" name="valor_mensal" type="number" min="0" step="0.01" value="{{ old('valor_mensal') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Quantidade de vidas</label>
                            <input class="form-control" name="quantidade_vidas" type="number" min="1" value="{{ old('quantidade_vidas', $indicacao->quantidade_vidas) }}" data-lives-count>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Validade</label>
                            <input class="form-control" name="validade" type="date" value="{{ old('validade') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">PDF da proposta</label>
                            <x-file-input name="arquivo_pdf" accept="application/pdf" :required="true" />
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes') }}</textarea>
                        </div>

                        <div class="col-12">
                            <button class="nexo-primary-btn">
                                <i class="bi bi-upload"></i>
                                Anexar nova proposta
                            </button>
                        </div>
                    </form>
                </section>

                <section class="nexo-proposta-panel">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Propostas anexadas</h2>
                            <p>Histórico de PDFs enviados para negociação.</p>
                        </div>
                    </div>

                    <div class="nexo-proposta-list">
                        @forelse($propostas as $proposta)
                            <article class="nexo-proposta-item">
                                <div>
                                    <strong>{{ $proposta->titulo }}</strong>
                                    <span>
                                        {{ $proposta->operadora?->nome ?: 'Operadora não informada' }}
                                        @if($proposta->validade)
                                            · validade {{ $proposta->validade->format('d/m/Y') }}
                                        @endif
                                    </span>
                                </div>

                                <div class="nexo-proposta-meta">
                                    <span>{{ $proposta->valor_mensal ? 'R$ '.number_format((float) $proposta->valor_mensal, 2, ',', '.') : 'Sem valor' }}</span>
                                    <a href="{{ asset('storage/'.$proposta->arquivo_pdf_path) }}" target="_blank" rel="noopener">
                                        Ver PDF
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="nexo-empty-state">
                                <i class="bi bi-file-earmark-text"></i>
                                <p>Nenhuma proposta anexada ainda.</p>
                            </div>
                        @endforelse
                    </div>
                    {{ $propostas->links('vendor.pagination.nexo') }}
                </section>
            </div>

            <div class="col-xl-4">
                <section class="nexo-proposta-panel">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Resumo do contato</h2>
                            <p>Dados usados para negociação e próximos passos.</p>
                        </div>
                    </div>

                    <div class="nexo-info-list">
                        <div>
                            <span>Telefone</span>
                            <strong>{{ $indicacao->telefone }}</strong>
                        </div>

                        <div>
                            <span>E-mail</span>
                            <strong>{{ $indicacao->email ?: 'Não informado' }}</strong>
                        </div>

                        <div>
                            <span>Plano</span>
                            <strong>{{ $indicacao->tipo_plano }}</strong>
                        </div>

                        <div>
                            <span>Vidas</span>
                            <strong>{{ $indicacao->quantidade_vidas }}</strong>
                        </div>
                    </div>

                    <form method="post" action="{{ route('indicacoes.aceitar', $indicacao) }}" class="mt-4">
                        @csrf

                        <button class="nexo-secondary-btn w-100">
                            <i class="bi bi-link-45deg"></i>
                            Gerar link de pré-cadastro
                        </button>
                    </form>
                </section>

                <div class="mt-4">
                    @include('interno.indicacoes.partials.lembretes-card', ['indicacao' => $indicacao])
                </div>
            </div>
        </div>
    </main>

    <style>
        .nexo-proposta-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .nexo-page-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.78rem;
            font-weight: 900;
            padding: 6px 11px;
            margin-bottom: 10px;
        }

        .nexo-proposta-header h1 {
            color: #061C3F;
            font-size: 2.35rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.05em;
            margin: 0 0 8px;
        }

        .nexo-proposta-header p,
        .nexo-section-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-proposta-panel {
            border-radius: 28px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.05);
            padding: 28px;
        }

        .nexo-section-header {
            margin-bottom: 24px;
        }

        .nexo-section-header h2 {
            color: #061C3F;
            font-size: 1.35rem;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .form-label {
            color: #061C3F;
            font-weight: 850;
        }

        .form-control,
        .form-select {
            min-height: 50px;
            border-radius: 13px;
            border: 1px solid #D8E2EF;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-primary-btn,
        .nexo-secondary-btn {
            min-height: 46px;
            padding: 0 18px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-weight: 950;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .nexo-primary-btn {
            border: 1px solid #2F80ED;
            background: #2F80ED;
            color: #FFFFFF;
            box-shadow: 0 14px 30px rgba(47, 128, 237, 0.22);
        }

        .nexo-secondary-btn {
            background: #FFFFFF;
            border: 1px solid #D7E7FF;
            color: #2F80ED;
        }

        .nexo-primary-btn:hover,
        .nexo-secondary-btn:hover {
            transform: translateY(-1px);
        }

        .nexo-primary-btn:hover {
            color: #FFFFFF;
        }

        .nexo-secondary-btn:hover {
            background: #EAF3FF;
            color: #2F80ED;
        }

        .nexo-proposta-list,
        .nexo-info-list {
            display: grid;
            gap: 14px;
        }

        .nexo-proposta-item,
        .nexo-info-list > div {
            border-radius: 20px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
            padding: 18px;
        }

        .nexo-proposta-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .nexo-proposta-item strong,
        .nexo-info-list strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
        }

        .nexo-proposta-item span,
        .nexo-info-list span {
            display: block;
            color: #64748B;
            font-size: 0.9rem;
        }

        .nexo-proposta-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .nexo-proposta-meta a {
            color: #2F80ED;
            font-weight: 900;
            text-decoration: none;
        }

        .nexo-empty-state {
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #64748B;
            text-align: center;
        }

        .nexo-empty-state i {
            color: #2F80ED;
            font-size: 2.2rem;
            margin-bottom: 12px;
        }

        .nexo-empty-state p {
            margin: 0;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .nexo-proposta-header,
            .nexo-proposta-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-proposta-panel {
                padding: 22px;
            }
        }
    </style>
</x-layouts.app>
