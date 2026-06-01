<x-layouts.app title="Pré-cadastro | Nexo Saúde">
    @php
        $documentos = $preCadastro->documentosObrigatorios;

        $documentosObrigatorios = $documentos->where('obrigatorio', true);

        $documentosSemAlternativaOk = $documentosObrigatorios
            ->filter(fn ($documento) => empty($documento->grupo_alternativo))
            ->every(fn ($documento) => in_array($documento->status, ['aprovado', 'dispensado'], true));

        $gruposAlternativosOk = $documentosObrigatorios
            ->filter(fn ($documento) => ! empty($documento->grupo_alternativo))
            ->groupBy(fn ($documento) => $documento->vida_proposta_id.'|'.$documento->grupo_alternativo)
            ->every(fn ($grupo) => $grupo->contains(fn ($documento) => in_array($documento->status, ['aprovado', 'dispensado'], true)));

        $documentosOk = $documentos->isNotEmpty() && $documentosSemAlternativaOk && $gruposAlternativosOk;

        $todosDocumentosAprovados = $documentos->isNotEmpty()
            && $documentos->every(fn ($documento) => $documento->status === 'aprovado');

        $statusLegivel = [
            'aguardando_envio' => 'Aguardando envio',
            'documentacao_pendente' => 'Documentação pendente',
            'documentacao_em_analise' => 'Documentação em análise',
            'documentacao_aprovada' => 'Documentação aprovada',
            'correcao_solicitada' => 'Correção solicitada',
            'contrato_em_analise' => 'Contrato em análise',
            'contrato_vigente' => 'Contrato vigente',
            'convertido_em_cliente' => 'Convertido em cliente',
        ][$preCadastro->status] ?? ucfirst(str_replace('_', ' ', $preCadastro->status));
    @endphp

    <main class="nexo-main">
        <div class="nexo-pre-header mb-4">
            <div>
                <span class="nexo-page-label">
                    <i class="bi bi-clipboard2-check"></i>
                    Pré-cadastro
                </span>

                <h1>
                    {{ $indicacao->nome_cliente }}
                </h1>

                <p>
                    Revisão documental, beneficiários e gerenciamento do fluxo operacional.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a
                    class="nexo-secondary-btn"
                    href="{{ route('paginas.simples', 'pre-cadastros') }}"
                >
                    <i class="bi bi-arrow-left"></i>
                    Voltar
                </a>

                <a
                    class="nexo-primary-btn"
                    href="{{ $linkPublico }}"
                    target="_blank"
                    rel="noopener"
                >
                    <i class="bi bi-box-arrow-up-right"></i>
                    Abrir link público
                </a>
            </div>
        </div>

        <div class="nexo-pre-top-grid mb-4">
            <div class="nexo-pre-top-card">
                <span>Status</span>
                <strong>{{ $statusLegivel }}</strong>
            </div>

            <div class="nexo-pre-top-card">
                <span>Tipo da proposta</span>
                <strong>{{ ucfirst($preCadastro->tipo_proposta) }}</strong>
            </div>

            <div class="nexo-pre-top-card">
                <span>PF/PJ</span>
                <strong>{{ $preCadastro->pessoa }}</strong>
            </div>

            <div class="nexo-pre-top-card">
                <span>Beneficiários</span>
                <strong>{{ $preCadastro->vidas->count() }}</strong>
            </div>
        </div>

        <section class="nexo-pre-link-card mb-4" data-pre-link-card>
            <div class="nexo-pre-link-intro">
                <div class="nexo-pre-link-icon">
                    <i class="bi bi-send-check"></i>
                </div>

                <div>
                    <span class="nexo-section-kicker">
                        Link do cliente
                    </span>

                    <h2>Pré-cadastro pronto para envio</h2>

                    <p>
                        Compartilhe o link público e o token de acesso com o cliente. Ele usará a chave alfanumérica para acessar o formulário com segurança.
                    </p>
                </div>
            </div>

            <div class="nexo-pre-link-box">
                <div class="nexo-pre-copy-field">
                    <label>Link público</label>

                    <div class="nexo-pre-copy-input">
                        <input type="text" value="{{ $linkPublico }}" readonly data-copy-link-input>

                        <button type="button" data-copy-pre-link aria-label="Copiar link público">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    </div>
                </div>

                <div class="nexo-pre-copy-field">
                    <label>Token de acesso</label>

                    <div class="nexo-pre-copy-input">
                        <input type="text" value="{{ $preCadastro->chave_acesso }}" readonly data-copy-key-input>

                        <button type="button" data-copy-pre-token aria-label="Copiar token de acesso">
                            <i class="bi bi-key"></i>
                        </button>
                    </div>
                </div>

                <div class="nexo-pre-link-actions">
                    <button type="button" class="nexo-primary-btn" data-copy-pre-link>
                        <i class="bi bi-link-45deg"></i>
                        Copiar link
                    </button>

                    <button type="button" class="nexo-secondary-btn" data-copy-pre-token>
                        <i class="bi bi-key"></i>
                        Copiar token
                    </button>
                </div>

                <div class="nexo-pre-link-footer">
                    <span class="nexo-sms-status {{ $preCadastro->sms_status === 'sent' ? 'is-success' : 'is-warning' }}">
                        <i class="bi {{ $preCadastro->sms_status === 'sent' ? 'bi-check-circle-fill' : 'bi-clock-fill' }}"></i>
                        SMS: {{ $preCadastro->sms_status === 'sent' ? 'enviado' : 'envio pendente' }}
                    </span>

                    <small data-copy-feedback hidden>Copiado para a área de transferência.</small>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-xl-8">
                <section class="nexo-pre-panel" data-pre-cadastro-review-panel>
                    <div class="nexo-pre-panel-header">
                        <div>
                            <h2>
                                Documentos do pré-cadastro
                            </h2>

                            <p>
                                Analise os documentos enviados sem sair do fluxo operacional.
                            </p>
                        </div>

                        <div
                            class="nexo-approved-badge {{ ! $todosDocumentosAprovados ? 'd-none' : '' }}"
                            data-documentacao-aprovada-badge
                        >
                            <i class="bi bi-check-circle-fill"></i>
                            Documentação aprovada
                        </div>
                    </div>

                    @if($indicacao->etapa === 'pre_cadastros' && in_array($indicacao->status, ['documentacao_em_analise', 'correcao_solicitada', 'documentacao_pendente'], true))
                        <form
                            method="post"
                            action="{{ route('indicacoes.pre-cadastro.correcao', $indicacao) }}"
                            class="nexo-correction-box mb-4"
                        >
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Motivos da correção
                                </label>

                                <textarea
                                    class="form-control"
                                    name="motivos_correcao"
                                    rows="4"
                                    placeholder="Ex.: documento de identidade com foto ilegível; comprovante vencido; reenviar certidão."
                                >{{ old('motivos_correcao') }}</textarea>
                            </div>

                            <button class="nexo-secondary-btn">
                                Solicitar correção
                            </button>
                        </form>
                    @endif

                    <div class="nexo-beneficiarios" data-pre-cadastro-documentos>
                        @foreach($preCadastro->vidas->sortBy('ordem') as $vida)
                            <div class="nexo-beneficiario-card">
                                <div class="nexo-beneficiario-header">
                                    <div class="nexo-beneficiario-user">
                                        <div class="nexo-beneficiario-avatar">
                                            {{ strtoupper(substr($vida->nome ?: 'B', 0, 1)) }}
                                        </div>

                                        <div>
                                            <strong>
                                                {{ $vida->nome ?: 'Beneficiário '.$vida->ordem }}
                                            </strong>

                                            <span>
                                                {{ str_replace('_', ' ', $vida->tipo) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="nexo-beneficiario-tag">
                                        {{ $vida->parentesco ?: 'Sem parentesco' }}
                                    </div>
                                </div>

                                <div class="nexo-beneficiario-infos">
                                    <div class="nexo-info-item">
                                        <span>CPF/documento</span>
                                        <strong>{{ $vida->cpf ?: 'Não informado' }}</strong>
                                    </div>

                                    <div class="nexo-info-item">
                                        <span>Nascimento</span>
                                        <strong>{{ $vida->data_nascimento?->format('d/m/Y') ?: 'Não informado' }}</strong>
                                    </div>

                                    <div class="nexo-info-item">
                                        <span>Sexo</span>
                                        <strong>{{ $vida->sexo ?: 'Não informado' }}</strong>
                                    </div>

                                    <div class="nexo-info-item">
                                        <span>Gestante</span>
                                        <strong>{{ $vida->gestante ? 'Sim' : 'Não' }}</strong>
                                    </div>
                                </div>

                                <div class="nexo-documentos-wrapper">
                                    @include('interno.indicacoes.partials.documentos-revisao', [
                                        'indicacao' => $indicacao,
                                        'documentos' => $documentos->where('vida_proposta_id', $vida->id),
                                    ])
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($documentos->whereNull('vida_proposta_id')->isNotEmpty())
                        <div class="nexo-proposta-docs mt-4">
                            <div class="nexo-proposta-docs-header">
                                <i class="bi bi-folder2-open"></i>

                                <div>
                                    <strong>
                                        Documentos da proposta
                                    </strong>

                                    <span>
                                        Arquivos vinculados ao pré-cadastro geral.
                                    </span>
                                </div>
                            </div>

                            <div class="nexo-documentos-wrapper mt-3">
                                @include('interno.indicacoes.partials.documentos-revisao', [
                                    'indicacao' => $indicacao,
                                    'documentos' => $documentos->whereNull('vida_proposta_id'),
                                ])
                            </div>
                        </div>
                    @endif

                    @if($indicacao->etapa === 'pre_cadastros')
                        <form
                            method="post"
                            action="{{ route('indicacoes.implantacao.iniciar', $indicacao) }}"
                            class="mt-4"
                            data-implantacao-form
                        >
                            @csrf

                            <button
                                type="submit"
                                class="nexo-primary-btn nexo-primary-btn-disabled"
                                data-btn-implantacao
                                disabled
                            >
                                <i class="bi bi-check-circle"></i>
                                Iniciar implantação
                            </button>

                            <div
                                class="nexo-action-hint mt-2 {{ $todosDocumentosAprovados ? 'd-none' : '' }}"
                                data-implantacao-hint
                            >
                                Todos os documentos precisam estar aprovados para iniciar a implantação.
                            </div>
                        </form>
                    @endif
                </section>
            </div>

            <div class="col-xl-4">
                <div class="nexo-pre-sidebar">
                    @include('interno.indicacoes.partials.lembretes-card', ['indicacao' => $indicacao])

                    <section class="nexo-pre-panel">
                        <div class="nexo-pre-panel-header">
                            <div>
                                <h2>
                                    Timeline
                                </h2>

                                <p>
                                    Histórico de movimentações do pré-cadastro.
                                </p>
                            </div>
                        </div>

                        <div class="nexo-timeline">
                            @forelse($indicacao->timelineEventos->sortByDesc('created_at') as $evento)
                                <div class="nexo-timeline-item">
                                    <div class="nexo-timeline-dot"></div>

                                    <div class="nexo-timeline-content">
                                        <strong>
                                            {{ $evento->titulo }}
                                        </strong>

                                        <p>
                                            {{ $evento->descricao }}
                                        </p>

                                        <span>
                                            {{ $evento->created_at?->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="nexo-empty-state">
                                    <i class="bi bi-clock-history"></i>

                                    <p>
                                        Nenhum evento registrado.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const copiarValor = async (valor, mensagem) => {
                const feedback = document.querySelector('[data-copy-feedback]');

                await navigator.clipboard.writeText(valor);

                if (feedback) {
                    feedback.textContent = mensagem;
                    feedback.hidden = false;
                    window.setTimeout(() => {
                        feedback.hidden = true;
                    }, 2200);
                }
            };

            document.querySelectorAll('[data-copy-pre-link]').forEach((button) => {
                button.addEventListener('click', async () => {
                    await copiarValor(document.querySelector('[data-copy-link-input]')?.value || '', 'Link de pré-cadastro copiado.');
                });
            });

            document.querySelectorAll('[data-copy-pre-token]').forEach((button) => {
                button.addEventListener('click', async () => {
                    await copiarValor(document.querySelector('[data-copy-key-input]')?.value || '', 'Token de acesso copiado.');
                });
            });

            const reviewPanel = document.querySelector('[data-pre-cadastro-review-panel]');
            const botaoImplantacao = document.querySelector('[data-btn-implantacao]');
            const hintImplantacao = document.querySelector('[data-implantacao-hint]');
            const badgeAprovado = document.querySelector('[data-documentacao-aprovada-badge]');

            if (!reviewPanel || !botaoImplantacao) {
                return;
            }

            const statusAprovado = ['aprovado', 'dispensado'];
            const statusIgnorados = ['visualizar', 'enviado', 'enviar', 'substituir', 'baixar', 'abrir'];

            const normalizarTexto = (texto) => {
                return (texto || '')
                    .toString()
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/\s+/g, ' ');
            };

            const extrairStatusDosDocumentos = () => {
                const statusPorDataAttr = Array.from(reviewPanel.querySelectorAll('[data-documento-status]'))
                    .map((elemento) => normalizarTexto(elemento.dataset.documentoStatus))
                    .filter(Boolean);

                if (statusPorDataAttr.length) {
                    return statusPorDataAttr;
                }

                const candidatos = Array.from(reviewPanel.querySelectorAll(
                    '.nexo-document-status, .document-status, .badge, [class*="status"], [class*="documento"]'
                ));

                const textos = candidatos
                    .map((elemento) => normalizarTexto(elemento.textContent))
                    .filter((texto) => texto.length > 0)
                    .filter((texto) => !statusIgnorados.includes(texto))
                    .filter((texto) => {
                        return [
                            'pendente',
                            'corrigir',
                            'recusado',
                            'aprovado',
                            'dispensado',
                            'documentacao aprovada',
                            'documento aprovado',
                            'aprovada',
                        ].some((status) => texto.includes(status));
                    })
                    .map((texto) => {
                        if (texto.includes('dispensado')) {
                            return 'dispensado';
                        }

                        if (texto.includes('aprovado') || texto.includes('aprovada')) {
                            return 'aprovado';
                        }

                        if (texto.includes('recusado')) {
                            return 'recusado';
                        }

                        if (texto.includes('corrigir')) {
                            return 'corrigir';
                        }

                        return 'pendente';
                    });

                return textos;
            };

            const atualizarEstadoImplantacao = () => {
                const statusDocumentos = extrairStatusDosDocumentos();

                const todosAprovados = (
                    statusDocumentos.length > 0
                    && statusDocumentos.every((status) => {
                        return statusAprovado.includes(status);
                    })
                );

                botaoImplantacao.disabled = true;
                botaoImplantacao.classList.add('nexo-primary-btn-disabled');

                if (todosAprovados) {
                    botaoImplantacao.disabled = false;
                    botaoImplantacao.classList.remove('nexo-primary-btn-disabled');
                }

                if (hintImplantacao) {
                    hintImplantacao.classList.toggle(
                        'd-none',
                        todosAprovados
                    );
                }

                if (badgeAprovado) {
                    badgeAprovado.classList.toggle(
                        'd-none',
                        !todosAprovados
                    );
                }
            };

            const agendarAtualizacao = () => {
                window.requestAnimationFrame(() => {
                    atualizarEstadoImplantacao();

                    setTimeout(atualizarEstadoImplantacao, 150);
                    setTimeout(atualizarEstadoImplantacao, 400);
                    setTimeout(atualizarEstadoImplantacao, 900);
                });
            };

            atualizarEstadoImplantacao();

            const observer = new MutationObserver(() => {
                agendarAtualizacao();
            });

            observer.observe(reviewPanel, {
                subtree: true,
                childList: true,
                attributes: true,
                characterData: true,
                attributeFilter: [
                    'class',
                    'data-documento-status',
                    'disabled',
                    'aria-disabled',
                ],
            });

            reviewPanel.addEventListener('click', (event) => {
                const alvo = event.target.closest('button, a, input[type="submit"]');

                if (!alvo) {
                    return;
                }

                agendarAtualizacao();
            });

            reviewPanel.addEventListener('submit', () => {
                agendarAtualizacao();
            }, true);

            if (!window.__nexoPreCadastroFetchMonitorado) {
                window.__nexoPreCadastroFetchMonitorado = true;

                const fetchOriginal = window.fetch;

                if (typeof fetchOriginal === 'function') {
                    window.fetch = async (...args) => {
                        const resposta = await fetchOriginal(...args);

                        agendarAtualizacao();

                        return resposta;
                    };
                }

                const openOriginal = XMLHttpRequest.prototype.open;
                const sendOriginal = XMLHttpRequest.prototype.send;

                XMLHttpRequest.prototype.open = function (...args) {
                    this.__nexoPreCadastroRequest = true;

                    return openOriginal.apply(this, args);
                };

                XMLHttpRequest.prototype.send = function (...args) {
                    if (this.__nexoPreCadastroRequest) {
                        this.addEventListener('loadend', () => {
                            agendarAtualizacao();
                        });
                    }

                    return sendOriginal.apply(this, args);
                };
            }
        });
    </script>

    <style>
        .d-none {
            display: none !important;
        }

        .nexo-pre-header {
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

        .nexo-pre-header h1 {
            color: #061C3F;
            font-size: 2.35rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.05em;
            margin: 0 0 8px;
        }

        .nexo-pre-header p {
            color: #64748B;
            margin: 0;
            font-size: 1rem;
        }

        .nexo-primary-btn,
        .nexo-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 0 18px;
            border-radius: 14px;
            font-weight: 900;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .nexo-primary-btn {
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            border: none;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.22);
        }

        .nexo-primary-btn:hover {
            color: #FFFFFF;
            transform: translateY(-2px);
        }

        .nexo-primary-btn-disabled,
        .nexo-primary-btn:disabled {
            background: #D8E2EF;
            color: #64748B;
            box-shadow: none;
            cursor: not-allowed;
            opacity: 1;
            transform: none;
        }

        .nexo-primary-btn-disabled:hover,
        .nexo-primary-btn:disabled:hover {
            color: #64748B;
            transform: none;
        }

        .nexo-action-hint {
            color: #64748B;
            font-size: 0.88rem;
            font-weight: 700;
        }

        .nexo-secondary-btn {
            background: #FFFFFF;
            border: 1px solid #D7E7FF;
            color: #2F80ED;
        }

        .nexo-secondary-btn:hover {
            background: #2F80ED;
            color: #FFFFFF;
        }

        .nexo-pre-top-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .nexo-pre-top-card {
            padding: 22px;
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.05);
        }

        .nexo-pre-top-card span {
            display: block;
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .nexo-pre-top-card strong {
            color: #061C3F;
            font-size: 1.5rem;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .nexo-pre-link-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 520px);
            gap: 28px;
            align-items: stretch;
            padding: 28px;
            border: 1px solid #D7E7FF;
            border-radius: 30px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.10), transparent 30%),
                linear-gradient(135deg, #FFFFFF 0%, #F8FBFF 100%);
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.06);
        }

        .nexo-pre-link-intro {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            min-width: 0;
        }

        .nexo-pre-link-icon {
            width: 58px;
            height: 58px;
            border-radius: 20px;
            background: linear-gradient(135deg, #EAF3FF 0%, #DCEBFF 100%);
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            flex-shrink: 0;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .nexo-section-kicker {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            min-height: 30px;
            padding: 0 11px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.76rem;
            font-weight: 950;
            margin-bottom: 12px;
        }

        .nexo-pre-link-card h2 {
            color: #061C3F;
            font-size: clamp(1.55rem, 2.4vw, 2.1rem);
            line-height: 1.05;
            font-weight: 950;
            letter-spacing: -0.055em;
            margin: 0 0 10px;
        }

        .nexo-pre-link-card p {
            max-width: 720px;
            color: #64748B;
            font-size: 0.98rem;
            line-height: 1.55;
            font-weight: 750;
            margin: 0;
        }

        .nexo-pre-link-box {
            display: grid;
            gap: 14px;
            padding: 18px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid #E4EBF5;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.045);
        }

        .nexo-pre-copy-field {
            display: grid;
            gap: 7px;
        }

        .nexo-pre-copy-field label {
            color: #64748B;
            font-size: 0.74rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .nexo-pre-copy-input {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 48px;
            min-height: 52px;
            border-radius: 16px;
            border: 1px solid #D8E2EF;
            background: #FFFFFF;
            overflow: hidden;
            transition: 0.2s ease;
        }

        .nexo-pre-copy-input:focus-within {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.12);
        }

        .nexo-pre-copy-input input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            background: transparent;
            color: #061C3F;
            font-size: 0.95rem;
            font-weight: 900;
            padding: 0 16px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nexo-pre-copy-input button {
            border: 0;
            border-left: 1px solid #E4EBF5;
            background: #F8FBFF;
            color: #2F80ED;
            font-size: 1.05rem;
            transition: 0.2s ease;
        }

        .nexo-pre-copy-input button:hover {
            background: #2F80ED;
            color: #FFFFFF;
        }

        .nexo-pre-link-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .nexo-pre-link-actions .nexo-primary-btn,
        .nexo-pre-link-actions .nexo-secondary-btn {
            min-height: 46px;
            border-radius: 14px;
            padding: 0 14px;
            font-size: 0.88rem;
        }

        .nexo-pre-link-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            min-height: 30px;
        }

        .nexo-pre-link-footer small {
            color: #16834F;
            font-size: 0.8rem;
            font-weight: 850;
        }

        .nexo-sms-status {
            width: fit-content;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 30px;
            padding: 5px 10px;
            border-radius: 999px;
            background: #FFF5E8;
            color: #B45309;
            font-size: 0.8rem;
            font-weight: 900;
        }

        .nexo-sms-status.is-success {
            background: #EAFBF1;
            color: #16834F;
        }

        .nexo-pre-panel {
            border-radius: 28px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.05);
            padding: 28px;
        }

        .nexo-pre-sidebar {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .nexo-pre-sidebar .nexo-pre-panel,
        .nexo-pre-sidebar > * {
            width: 100%;
        }

        .nexo-pre-sidebar .nexo-pre-panel {
            padding: 22px;
        }

        .nexo-pre-sidebar textarea {
            min-height: 86px;
        }

        .nexo-pre-sidebar .form-control,
        .nexo-pre-sidebar .form-select {
            min-height: 44px;
        }

        .nexo-pre-sidebar .nexo-primary-btn,
        .nexo-pre-sidebar .nexo-secondary-btn {
            min-height: 42px;
            padding: 0 14px;
            border-radius: 12px;
        }

        .nexo-pre-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .nexo-pre-sidebar .nexo-pre-panel-header {
            margin-bottom: 16px;
        }

        .nexo-pre-panel-header h2 {
            color: #061C3F;
            font-size: 1.35rem;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .nexo-pre-sidebar .nexo-pre-panel-header h2 {
            font-size: 1.15rem;
        }

        .nexo-pre-panel-header p {
            color: #64748B;
            margin: 0;
        }

        .nexo-pre-sidebar .nexo-pre-panel-header p {
            font-size: 0.86rem;
        }

        .nexo-approved-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 14px;
            border-radius: 999px;
            background: #EAFBF1;
            color: #1E9E63;
            font-size: 0.82rem;
            font-weight: 900;
        }

        .nexo-warning-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            border-radius: 18px;
            background: #FFF5E8;
            border: 1px solid #FFE2B8;
            color: #D9822B;
            font-weight: 700;
        }

        .nexo-correction-box {
            padding: 22px;
            border-radius: 24px;
            background: #F8FBFF;
            border: 1px solid #DCEBFF;
        }

        .nexo-beneficiarios {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .nexo-beneficiario-card {
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #E6EDF5;
            padding: 24px;
        }

        .nexo-beneficiario-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }

        .nexo-beneficiario-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nexo-beneficiario-avatar {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            background: linear-gradient(135deg, #2F80ED 0%, #5BA7FF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1rem;
        }

        .nexo-beneficiario-user strong {
            display: block;
            color: #061C3F;
            font-size: 1rem;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .nexo-beneficiario-user span {
            color: #64748B;
            font-size: 0.9rem;
        }

        .nexo-beneficiario-tag {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 999px;
            background: #F3F8FF;
            color: #2F80ED;
            font-size: 0.82rem;
            font-weight: 900;
        }

        .nexo-beneficiario-infos {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .nexo-info-item {
            padding: 16px;
            border-radius: 18px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-info-item span {
            display: block;
            color: #64748B;
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .nexo-info-item strong {
            color: #061C3F;
            font-size: 0.92rem;
            font-weight: 900;
        }

        .nexo-documentos-wrapper {
            border-top: 1px solid #EDF2F7;
            padding-top: 22px;
        }

        .nexo-proposta-docs {
            padding: 24px;
            border-radius: 24px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-proposta-docs-header {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nexo-proposta-docs-header i {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: rgba(47, 128, 237, 0.12);
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }

        .nexo-proposta-docs-header strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .nexo-proposta-docs-header span {
            color: #64748B;
            font-size: 0.9rem;
        }

        .nexo-timeline {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .nexo-timeline-item {
            position: relative;
            display: flex;
            gap: 16px;
        }

        .nexo-timeline-dot {
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: #2F80ED;
            margin-top: 6px;
            flex-shrink: 0;
            box-shadow: 0 0 0 6px rgba(47, 128, 237, 0.10);
        }

        .nexo-timeline-content {
            position: relative;
            padding-bottom: 18px;
            flex: 1;
        }

        .nexo-timeline-content::before {
            content: "";
            position: absolute;
            left: -23px;
            top: 22px;
            width: 2px;
            height: calc(100% - 6px);
            background: #DCEBFF;
        }

        .nexo-timeline-item:last-child .nexo-timeline-content::before {
            display: none;
        }

        .nexo-timeline-content strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
            margin-bottom: 6px;
        }

        .nexo-timeline-content p {
            color: #64748B;
            margin: 0 0 8px;
            font-size: 0.92rem;
        }

        .nexo-timeline-content span {
            color: #94A3B8;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .nexo-empty-state {
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #64748B;
            text-align: center;
        }

        .nexo-empty-state i {
            color: #2F80ED;
            font-size: 2.4rem;
            margin-bottom: 12px;
        }

        .nexo-empty-state p {
            margin: 0;
            font-weight: 700;
        }

        @media (max-width: 1200px) {
            .nexo-pre-top-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .nexo-pre-link-card {
                grid-template-columns: 1fr;
            }

            .nexo-beneficiario-infos {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .nexo-pre-sidebar {
                margin-top: 0;
            }
        }

        @media (max-width: 768px) {
            .nexo-pre-link-card {
                padding: 22px;
            }

            .nexo-pre-link-intro {
                flex-direction: column;
            }

            .nexo-pre-link-actions {
                grid-template-columns: 1fr;
            }

            .nexo-pre-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-pre-top-grid {
                grid-template-columns: 1fr;
            }

            .nexo-beneficiario-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-beneficiario-infos {
                grid-template-columns: 1fr;
            }

            .nexo-pre-panel {
                padding: 22px;
            }
        }
    </style>
</x-layouts.app>