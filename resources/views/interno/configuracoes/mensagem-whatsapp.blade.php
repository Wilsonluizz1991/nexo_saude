<x-configuracoes.layout titulo="Mensagem WhatsApp">
            <section class="nexo-whatsapp-config">
                <div class="nexo-whatsapp-config-header">
                    <span class="nexo-page-label">
                        <i class="bi bi-whatsapp"></i>
                        WhatsApp
                    </span>

                    <h1>Mensagens automáticas</h1>
                    <p>Configure os textos usados nos atalhos de WhatsApp da Nexo.</p>
                </div>

                <form method="post" action="{{ route('configuracoes.mensagem-whatsapp.update') }}" class="nexo-whatsapp-config-form">
                    @csrf

                    <div class="nexo-whatsapp-block">
                        <div class="nexo-whatsapp-block-heading">
                            <h2>Primeiro contato com Lead</h2>
                            <p>Mensagem usada ao clicar no WhatsApp de uma Lead.</p>
                        </div>

                        <label class="form-label" for="mensagem_primeiro_contato_whatsapp">Mensagem padrão</label>
                        <textarea
                            id="mensagem_primeiro_contato_whatsapp"
                            name="mensagem_primeiro_contato_whatsapp"
                            class="form-control"
                            rows="5"
                            maxlength="500"
                            required
                            data-whatsapp-template="lead"
                        >{{ old('mensagem_primeiro_contato_whatsapp', $mensagem) }}</textarea>

                        <div class="nexo-whatsapp-config-meta">
                            <span>Personalize usando as variáveis disponíveis.</span>
                            <strong><span data-whatsapp-count="lead">{{ mb_strlen(old('mensagem_primeiro_contato_whatsapp', $mensagem)) }}</span>/500</strong>
                        </div>

                        @error('mensagem_primeiro_contato_whatsapp')
                            <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                        @enderror

                        <div class="nexo-whatsapp-help">
                            <strong>Variáveis disponíveis</strong>
                            <p>Clique em uma variável para inserir no texto.</p>

                            <div class="nexo-whatsapp-variable-list" data-variable-list="lead">
                                @foreach($variaveis as $variavel)
                                    <button type="button" data-variable="{{ $variavel }}">{{ $variavel }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="nexo-whatsapp-preview">
                            <span>Prévia</span>
                            <p data-whatsapp-preview="lead">{{ $preview }}</p>
                        </div>
                    </div>

                    <div class="nexo-whatsapp-block nexo-whatsapp-block-premium">
                        <div class="nexo-whatsapp-block-heading">
                            <h2>Contrato vigente + convite de avaliação</h2>
                            <p>Mensagem enviada quando o contrato entra em vigência e o corretor deseja solicitar a avaliação do atendimento.</p>
                        </div>

                        <label class="form-label" for="mensagem_contrato_vigente_whatsapp">Mensagem padrão</label>
                        <textarea
                            id="mensagem_contrato_vigente_whatsapp"
                            name="mensagem_contrato_vigente_whatsapp"
                            class="form-control"
                            rows="8"
                            maxlength="900"
                            required
                            data-whatsapp-template="contract"
                        >{{ old('mensagem_contrato_vigente_whatsapp', $mensagemContrato) }}</textarea>

                        <div class="nexo-whatsapp-config-meta">
                            <span>Use obrigatoriamente <strong>{link_avaliacao}</strong> para inserir o link da avaliação.</span>
                            <strong><span data-whatsapp-count="contract">{{ mb_strlen(old('mensagem_contrato_vigente_whatsapp', $mensagemContrato)) }}</span>/900</strong>
                        </div>

                        @error('mensagem_contrato_vigente_whatsapp')
                            <div class="text-danger small fw-bold mt-2">{{ $message }}</div>
                        @enderror

                        <div class="nexo-whatsapp-help">
                            <strong>Variáveis disponíveis</strong>
                            <p>Clique em uma variável para inserir no texto.</p>

                            <div class="nexo-whatsapp-variable-list" data-variable-list="contract">
                                @foreach($variaveisContrato as $variavel)
                                    <button type="button" data-variable="{{ $variavel }}">{{ $variavel }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="nexo-whatsapp-preview">
                            <span>Prévia</span>
                            <p data-whatsapp-preview="contract">{{ $previewContrato }}</p>
                        </div>
                    </div>

                    <button class="nexo-whatsapp-save-btn">
                        <i class="bi bi-check-circle"></i>
                        Salvar mensagens
                    </button>
                </form>
            </section>
    <style>
        .nexo-whatsapp-config {
            display: grid;
            gap: 24px;
        }

        .nexo-whatsapp-config-header h1 {
            color: #061C3F;
            font-size: 2.2rem;
            font-weight: 950;
            letter-spacing: -0.04em;
            margin: 0 0 8px;
        }

        .nexo-whatsapp-config p {
            color: #64748B;
            margin: 0;
        }

        .nexo-whatsapp-config-form {
            display: grid;
            gap: 22px;
        }

        .nexo-whatsapp-block {
            display: grid;
            gap: 16px;
            border: 1px solid #E5EAF0;
            border-radius: 24px;
            background: #FFFFFF;
            padding: 22px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.045);
        }

        .nexo-whatsapp-block-premium {
            border-color: rgba(212, 175, 55, 0.35);
            background: linear-gradient(135deg, #FFFFFF 0%, #FFFDF4 100%);
        }

        .nexo-whatsapp-block-heading h2 {
            color: #061C3F;
            font-size: 1.25rem;
            font-weight: 950;
            margin: 0 0 6px;
        }

        .nexo-whatsapp-config-form .form-label {
            color: #061C3F;
            font-weight: 900;
        }

        .nexo-whatsapp-config-form .form-control {
            border-color: #D8E2EF;
            border-radius: 16px;
            color: #061C3F;
            font-weight: 650;
            line-height: 1.65;
            padding: 16px;
        }

        .nexo-whatsapp-config-form .form-control:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-whatsapp-config-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: #64748B;
            font-size: 0.85rem;
            font-weight: 700;
            margin-top: -6px;
        }

        .nexo-whatsapp-help,
        .nexo-whatsapp-preview {
            border: 1px solid #E5EAF0;
            border-radius: 18px;
            background: #F8FBFF;
            padding: 18px;
        }

        .nexo-whatsapp-help strong,
        .nexo-whatsapp-preview span {
            display: block;
            color: #061C3F;
            font-weight: 950;
            margin-bottom: 6px;
        }

        .nexo-whatsapp-variable-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .nexo-whatsapp-variable-list button {
            border: 1px solid #BFD7FF;
            border-radius: 999px;
            background: #FFFFFF;
            color: #2F80ED;
            font-size: 0.86rem;
            font-weight: 900;
            padding: 8px 12px;
            transition: 0.2s ease;
        }

        .nexo-whatsapp-variable-list button:hover {
            background: #EAF3FF;
            transform: translateY(-1px);
        }

        .nexo-whatsapp-preview p {
            color: #162033;
            font-weight: 700;
            line-height: 1.7;
            margin: 0;
            white-space: pre-wrap;
        }

        .nexo-whatsapp-save-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: fit-content;
            min-height: 48px;
            border: 0;
            border-radius: 14px;
            background: #2F80ED;
            color: #FFFFFF;
            font-weight: 900;
            padding: 0 20px;
            box-shadow: 0 14px 28px rgba(47, 128, 237, 0.2);
            transition: 0.2s ease;
        }

        .nexo-whatsapp-save-btn:hover {
            background: #1B6DFF;
            transform: translateY(-1px);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dataSets = {
                lead: {
                    '{nome}': 'Fernando Diniz',
                    '{telefone}': '(11) 99953-5578',
                    '{tipo_plano}': 'PME',
                    '{quantidade_vidas}': '11',
                    '{cidade}': 'Sao Paulo',
                    '{estado}': 'SP',
                },
                contract: {
                    '{nome}': 'Fernando Diniz',
                    '{telefone}': '(11) 99953-5578',
                    '{email}': 'fernando@email.com',
                    '{data_vigencia}': new Date().toLocaleDateString('pt-BR'),
                    '{tipo_plano}': 'PME',
                    '{quantidade_vidas}': '11',
                    '{link_avaliacao}': 'https://nexosaude.com.br/avaliacao/exemplo',
                },
            };

            const updatePreview = (type) => {
                const textarea = document.querySelector(`[data-whatsapp-template="${type}"]`);
                const preview = document.querySelector(`[data-whatsapp-preview="${type}"]`);
                const counter = document.querySelector(`[data-whatsapp-count="${type}"]`);

                if (! textarea || ! preview) {
                    return;
                }

                let text = textarea.value;
                Object.entries(dataSets[type] || {}).forEach(([key, value]) => {
                    text = text.split(key).join(value);
                });

                preview.textContent = text;

                if (counter) {
                    counter.textContent = textarea.value.length;
                }
            };

            document.querySelectorAll('[data-whatsapp-template]').forEach((textarea) => {
                const type = textarea.dataset.whatsappTemplate;
                textarea.addEventListener('input', () => updatePreview(type));
                updatePreview(type);
            });

            document.querySelectorAll('[data-variable-list] button').forEach((button) => {
                button.addEventListener('click', () => {
                    const type = button.closest('[data-variable-list]').dataset.variableList;
                    const textarea = document.querySelector(`[data-whatsapp-template="${type}"]`);

                    if (! textarea) {
                        return;
                    }

                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const value = button.dataset.variable;
                    textarea.value = textarea.value.substring(0, start) + value + textarea.value.substring(end);
                    textarea.focus();
                    textarea.selectionStart = textarea.selectionEnd = start + value.length;
                    updatePreview(type);
                });
            });
        });
    </script>
</x-configuracoes.layout>
