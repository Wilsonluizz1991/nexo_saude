<x-configuracoes.layout titulo="Mensagem WhatsApp">
    <div class="nexo-whatsapp-config">
        <div class="nexo-whatsapp-config-header">
            <div>
                <span class="nexo-settings-eyebrow">
                    <i class="bi bi-whatsapp"></i>
                    Primeiro contato
                </span>

                <h2>Mensagem de WhatsApp para Leads</h2>
                <p>Configure a mensagem inicial usada quando voc&ecirc; clicar no WhatsApp de uma Lead.</p>
            </div>
        </div>

        <form method="post" action="{{ route('configuracoes.mensagem-whatsapp.update') }}" class="nexo-whatsapp-config-form">
            @csrf

            <div>
                <label class="form-label" for="mensagem_primeiro_contato_whatsapp">Mensagem padr&atilde;o</label>
                <textarea
                    id="mensagem_primeiro_contato_whatsapp"
                    name="mensagem_primeiro_contato_whatsapp"
                    class="form-control"
                    rows="7"
                    maxlength="500"
                    required
                    data-whatsapp-template
                >{{ old('mensagem_primeiro_contato_whatsapp', $mensagem) }}</textarea>

                <div class="nexo-whatsapp-config-meta">
                    <span>Limite de 500 caracteres.</span>
                    <strong><span data-whatsapp-count>{{ mb_strlen(old('mensagem_primeiro_contato_whatsapp', $mensagem)) }}</span>/500</strong>
                </div>

                @error('mensagem_primeiro_contato_whatsapp')
                    <div class="text-danger small fw-semibold mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="nexo-whatsapp-help">
                <strong>Vari&aacute;veis dispon&iacute;veis</strong>
                <p>Use as vari&aacute;veis abaixo para personalizar a mensagem automaticamente com os dados da Lead.</p>

                <div class="nexo-whatsapp-variable-list">
                    @foreach($variaveis as $variavel)
                        <button type="button" data-whatsapp-variable="{{ $variavel }}">{{ $variavel }}</button>
                    @endforeach
                </div>
            </div>

            <div class="nexo-whatsapp-preview">
                <span>Preview com dados fict&iacute;cios</span>
                <p data-whatsapp-preview>{{ $preview }}</p>
            </div>

            <button class="nexo-whatsapp-save-btn">
                <i class="bi bi-check2-circle"></i>
                Salvar mensagem
            </button>
        </form>
    </div>

    <style>
        .nexo-whatsapp-config {
            display: grid;
            gap: 22px;
        }

        .nexo-whatsapp-config-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding-bottom: 18px;
            border-bottom: 1px solid #E5EAF0;
        }

        .nexo-settings-eyebrow {
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

        .nexo-whatsapp-config h2 {
            color: #061C3F;
            font-size: 1.65rem;
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
            gap: 18px;
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
            margin-top: 8px;
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
            const textarea = document.querySelector('[data-whatsapp-template]');
            const preview = document.querySelector('[data-whatsapp-preview]');
            const counter = document.querySelector('[data-whatsapp-count]');
            const dados = {
                '{nome}': 'Fernando Diniz',
                '{telefone}': '(11) 99953-5578',
                '{tipo_plano}': 'PME',
                '{quantidade_vidas}': '11',
                '{cidade}': 'Sao Paulo',
                '{estado}': 'SP',
            };

            if (!textarea || !preview || !counter) {
                return;
            }

            const atualizarPreview = () => {
                let texto = textarea.value;

                Object.entries(dados).forEach(([variavel, valor]) => {
                    texto = texto.split(variavel).join(valor);
                });

                preview.textContent = texto;
                counter.textContent = textarea.value.length;
            };

            document.querySelectorAll('[data-whatsapp-variable]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    const variavel = botao.dataset.whatsappVariable;
                    const inicio = textarea.selectionStart ?? textarea.value.length;
                    const fim = textarea.selectionEnd ?? textarea.value.length;

                    textarea.value = `${textarea.value.slice(0, inicio)}${variavel}${textarea.value.slice(fim)}`;
                    textarea.focus();
                    textarea.setSelectionRange(inicio + variavel.length, inicio + variavel.length);
                    atualizarPreview();
                });
            });

            textarea.addEventListener('input', atualizarPreview);
            atualizarPreview();
        });
    </script>
</x-configuracoes.layout>
