<x-layouts.app title="Nova Lead | Nexo Saúde">
    <main class="nexo-main nexo-lead-create-page">
        <section class="nexo-lead-create-hero">
            <div>
                <span class="nexo-page-label">
                    <i class="bi bi-person-plus"></i>
                    Cadastro interno
                </span>

                <h1>Nova Lead</h1>

                <p>
                    Registre uma oportunidade manualmente no funil comercial. Os contatos vindos do cliente continuam entrando normalmente pelo link público do corretor.
                </p>
            </div>

            <div class="nexo-hero-info-card">
                <i class="bi bi-kanban"></i>

                <div>
                    <strong>Entrada no funil</strong>
                    <span>A lead será criada na etapa inicial para acompanhamento comercial.</span>
                </div>
            </div>
        </section>

        <section class="nexo-panel-card">
            <div class="nexo-section-header">
                <div>
                    <span class="nexo-section-kicker">
                        <i class="bi bi-clipboard2-plus"></i>
                        Dados da lead
                    </span>

                    <h2>Informações principais</h2>

                    <p>
                        Preencha os dados básicos para iniciar o acompanhamento deste contato.
                    </p>
                </div>
            </div>

            @php
                $operadorasSelecionadas = collect(old('operadoras', []))
                    ->map(fn ($id) => (string) $id)
                    ->toArray();
                $possuiPreferencias = old('possui_preferencias', 'ainda_nao_sei');
            @endphp

            <form method="post" action="{{ route('indicacoes.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Nome da lead</label>
                    <input name="nome_cliente" class="form-control" value="{{ old('nome_cliente') }}" placeholder="Ex.: Maria Oliveira" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Telefone</label>
                    <input id="nexo-input-telefone" name="telefone" class="form-control" value="{{ old('telefone') }}" placeholder="(11) 99999-9999" maxlength="15" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">E-mail</label>
                    <input name="email" type="email" class="form-control" value="{{ old('email') }}" placeholder="cliente@email.com" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tipo de plano</label>
                    <select name="tipo_plano" class="form-select d-none" data-plan-type required>
                        @foreach(['Individual', 'Familiar', 'PME', 'Empresarial'] as $tipoPlano)
                            <option value="{{ $tipoPlano }}" @selected(old('tipo_plano') === $tipoPlano)>{{ $tipoPlano }}</option>
                        @endforeach
                    </select>

                    <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper>
                        <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span data-bootstrap-select-label>{{ old('tipo_plano', 'Individual') }}</span>
                        </button>

                        <ul class="dropdown-menu nexo-bootstrap-select-menu">
                            @foreach(['Individual' => 'bi-person', 'Familiar' => 'bi-people', 'PME' => 'bi-shop', 'Empresarial' => 'bi-buildings'] as $tipoPlano => $iconePlano)
                                <li>
                                    <button class="dropdown-item nexo-bootstrap-select-item" type="button" data-select-value="{{ $tipoPlano }}">
                                        <i class="bi {{ $iconePlano }}"></i>
                                        <span>{{ $tipoPlano }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Quantidade de vidas</label>
                    <input name="quantidade_vidas" type="number" min="1" max="999" class="form-control" value="{{ old('quantidade_vidas') }}" data-lives-count placeholder="1" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cidade</label>
                    <input name="cidade" class="form-control" value="{{ old('cidade') }}" placeholder="Ex.: São Paulo" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select text-uppercase" required>
                        <option value="" disabled @selected(! old('estado'))>Selecione</option>
                        @foreach(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $estado)
                            <option value="{{ $estado }}" @selected(old('estado') === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Possui alguma preferência?</label>
                    <select name="possui_preferencias" class="form-select" data-preferences-toggle required>
                        <option value="ainda_nao_sei" @selected($possuiPreferencias === 'ainda_nao_sei')>Ainda não sei</option>
                        <option value="nao" @selected($possuiPreferencias === 'nao')>Não</option>
                        <option value="sim" @selected($possuiPreferencias === 'sim')>Sim</option>
                    </select>
                </div>

                <div class="col-12 nexo-preferences-panel" data-preferences-panel hidden>
                    <div class="nexo-preferences-card">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Selecione até 3 operadoras de sua preferência.</label>

                                <div class="nexo-check-multiselect" data-operadoras-multiselect>
                                    <button
                                        type="button"
                                        class="nexo-check-multiselect-control"
                                        data-operadoras-toggle
                                        aria-expanded="false"
                                    >
                                        <span class="nexo-check-multiselect-tags" data-operadoras-tags></span>

                                        <span class="nexo-check-multiselect-placeholder" data-operadoras-placeholder>
                                            Selecione as operadoras
                                        </span>

                                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                    </button>

                                    <div class="nexo-check-multiselect-dropdown" data-operadoras-dropdown hidden>
                                        @foreach($operadoras as $operadora)
                                            @php
                                                $operadoraId = (string) $operadora->id;
                                                $selecionada = in_array($operadoraId, $operadorasSelecionadas, true);
                                            @endphp

                                            <label class="nexo-check-multiselect-option">
                                                <input
                                                    type="checkbox"
                                                    name="operadoras[]"
                                                    value="{{ $operadora->id }}"
                                                    data-operadora-checkbox
                                                    data-label="{{ $operadora->nome }}"
                                                    @checked($selecionada)
                                                >

                                                <span class="nexo-checkmark" aria-hidden="true"></span>
                                                <span>{{ $operadora->nome }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="nexo-form-hint" data-operadoras-warning hidden>
                                    Selecione no máximo 3 operadoras.
                                </div>

                                @error('operadoras')
                                    <div class="text-danger small fw-semibold mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Selecione até 3 hospitais de sua preferência.</label>
                            </div>

                            @for($i = 0; $i < 3; $i++)
                                <div class="col-md-4">
                                    <label class="form-label">Hospital {{ $i + 1 }}</label>
                                    <input name="hospitais[]" class="form-control" value="{{ old('hospitais.'.$i) }}" placeholder="Nome do hospital">
                                </div>
                            @endfor

                            <div class="col-12">
                                <label class="form-label">Faixa de valor mensal desejada.</label>
                                <input
                                    name="faixa_valor_mensal"
                                    class="form-control"
                                    value="{{ old('faixa_valor_mensal') }}"
                                    inputmode="numeric"
                                    data-money-mask
                                    placeholder="R$ 0,00"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="4" placeholder="Adicione informações importantes sobre o interesse, urgência, preferências ou próximos passos.">{{ old('observacoes') }}</textarea>
                </div>

                <div class="col-12">
                    <div class="nexo-form-actions">
                        <a href="{{ url()->previous() }}" class="nexo-secondary-btn">
                            <i class="bi bi-arrow-left"></i>
                            Voltar
                        </a>

                        <button class="nexo-primary-btn">
                            <i class="bi bi-check-circle"></i>
                            Criar lead
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <style>
        .nexo-lead-create-page {
            display: block;
        }

        .nexo-lead-create-hero {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
            margin-bottom: 24px;
            padding: 24px 28px;
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.22), transparent 32%),
                linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
            box-shadow: 0 18px 44px rgba(6, 28, 63, 0.16);
        }

        .nexo-page-label,
        .nexo-section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            min-height: 30px;
            padding: 0 11px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.76rem;
            font-weight: 950;
            margin-bottom: 10px;
        }

        .nexo-page-label {
            background: rgba(255, 255, 255, 0.10);
            color: #DDEBFF;
        }

        .nexo-lead-create-hero h1 {
            color: #FFFFFF;
            font-size: clamp(1.8rem, 2.8vw, 3rem);
            line-height: 1.04;
            font-weight: 950;
            letter-spacing: -0.055em;
            margin: 0 0 10px;
        }

        .nexo-lead-create-hero p {
            max-width: 760px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.98rem;
            font-weight: 650;
            margin: 0;
        }

        .nexo-hero-info-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.13);
            backdrop-filter: blur(10px);
        }

        .nexo-hero-info-card i {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .nexo-hero-info-card strong {
            display: block;
            color: #FFFFFF;
            font-size: 1rem;
            font-weight: 950;
            margin-bottom: 3px;
        }

        .nexo-hero-info-card span {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .nexo-panel-card {
            padding: 24px;
            border-radius: 28px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.055);
        }

        .nexo-preferences-card {
            border: 1px solid #CFE2FF;
            border-radius: 18px;
            background: linear-gradient(180deg, #F7FBFF 0%, #FFFFFF 100%);
            padding: 22px;
        }

        .nexo-section-header {
            margin-bottom: 22px;
        }

        .nexo-section-header h2 {
            color: #061C3F;
            font-size: 1.45rem;
            font-weight: 950;
            letter-spacing: -0.035em;
            margin: 0 0 6px;
        }

        .nexo-section-header p {
            color: #64748B;
            margin: 0;
            font-weight: 650;
        }

        .form-label {
            color: #061C3F;
            font-weight: 850;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            min-height: 52px;
            border-radius: 14px;
            border: 1px solid #D8E2EF;
            color: #061C3F;
            padding: 12px 15px;
            font-weight: 650;
        }

        .form-control::placeholder {
            color: #94A3B8;
            font-weight: 600;
        }

        textarea.form-control {
            resize: vertical;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-check-multiselect {
            position: relative;
        }

        .nexo-check-multiselect-control {
            width: 100%;
            min-height: 52px;
            border: 1px solid #D8E2EF;
            border-radius: 14px;
            background: #FFFFFF;
            color: #061C3F;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            text-align: left;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .nexo-check-multiselect-control:focus,
        .nexo-check-multiselect-control[aria-expanded="true"] {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
            outline: none;
        }

        .nexo-check-multiselect-placeholder {
            color: #64748B;
            flex: 1;
            font-weight: 650;
        }

        .nexo-check-multiselect-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .nexo-check-multiselect-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #0F3A68;
            font-size: 0.85rem;
            font-weight: 850;
            padding: 5px 9px;
        }

        .nexo-check-multiselect-tag button {
            border: 0;
            background: transparent;
            color: #2F80ED;
            font-weight: 950;
            line-height: 1;
            padding: 0;
        }

        .nexo-check-multiselect-control > .bi {
            color: #2F80ED;
            margin-left: auto;
        }

        .nexo-check-multiselect-dropdown {
            position: absolute;
            z-index: 50;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            max-height: 280px;
            overflow-y: auto;
            border: 1px solid #D8E2EF;
            border-radius: 16px;
            background: #FFFFFF;
            box-shadow: 0 18px 40px rgba(6, 28, 63, 0.16);
            padding: 8px;
        }

        .nexo-check-multiselect-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 11px;
            cursor: pointer;
            color: #061C3F;
            font-size: 0.95rem;
            font-weight: 700;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .nexo-check-multiselect-option:hover {
            background: #EAF3FF;
            color: #0F3A68;
        }

        .nexo-check-multiselect-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .nexo-checkmark {
            width: 20px;
            height: 20px;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            background: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .nexo-checkmark::after {
            content: "✓";
            display: none;
            color: #FFFFFF;
            font-size: 0.8rem;
            font-weight: 950;
            line-height: 1;
        }

        .nexo-check-multiselect-option input:checked + .nexo-checkmark {
            border-color: #2F80ED;
            background: #2F80ED;
        }

        .nexo-check-multiselect-option input:checked + .nexo-checkmark::after {
            display: block;
        }

        .nexo-form-hint {
            color: #DC2626;
            font-size: 0.85rem;
            font-weight: 800;
            margin-top: 8px;
        }

        .nexo-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 8px;
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
            border: 1px solid transparent;
        }

        .nexo-primary-btn {
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            box-shadow: 0 14px 30px rgba(47, 128, 237, 0.22);
        }

        .nexo-secondary-btn {
            background: #FFFFFF;
            border-color: #D7E7FF;
            color: #2F80ED;
        }

        .nexo-primary-btn:hover,
        .nexo-secondary-btn:hover {
            transform: translateY(-1px);
        }

        .nexo-primary-btn:hover {
            color: #FFFFFF;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.28);
        }

        .nexo-secondary-btn:hover {
            background: #EAF3FF;
            color: #2F80ED;
        }

        @media (min-width: 992px) {
            .nexo-lead-create-hero {
                grid-template-columns: 1fr 280px;
                align-items: center;
            }
        }

        @media (max-width: 576px) {
            .nexo-lead-create-hero,
            .nexo-panel-card {
                padding: 20px;
                border-radius: 24px;
            }

            .nexo-form-actions {
                flex-direction: column-reverse;
            }

            .nexo-primary-btn,
            .nexo-secondary-btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputTelefone = document.getElementById('nexo-input-telefone');
            const preferencesToggle = document.querySelector('[data-preferences-toggle]');
            const preferencesPanel = document.querySelector('[data-preferences-panel]');
            const multiselect = document.querySelector('[data-operadoras-multiselect]');

            function renderTags() {}

            function togglePreferencesPanel() {
                if (!preferencesToggle || !preferencesPanel) {
                    return;
                }

                const shouldShow = preferencesToggle.value === 'sim';

                preferencesPanel.hidden = !shouldShow;

                if (!shouldShow && multiselect) {
                    multiselect.querySelectorAll('[data-operadora-checkbox]').forEach((checkbox) => {
                        checkbox.checked = false;
                    });

                    renderTags();
                }
            }

            if (preferencesToggle) {
                preferencesToggle.addEventListener('change', togglePreferencesPanel);
                togglePreferencesPanel();
            }

            if (multiselect) {
                const toggle = multiselect.querySelector('[data-operadoras-toggle]');
                const dropdown = multiselect.querySelector('[data-operadoras-dropdown]');
                const tagsContainer = multiselect.querySelector('[data-operadoras-tags]');
                const placeholder = multiselect.querySelector('[data-operadoras-placeholder]');
                const warning = document.querySelector('[data-operadoras-warning]');
                const checkboxes = Array.from(multiselect.querySelectorAll('[data-operadora-checkbox]'));
                const maxOperadoras = 3;

                const selectedValues = () => checkboxes
                    .filter((checkbox) => checkbox.checked)
                    .map((checkbox) => ({
                        value: checkbox.value,
                        label: checkbox.dataset.label || checkbox.value,
                    }));

                renderTags = () => {
                    const selected = selectedValues();

                    tagsContainer.innerHTML = '';
                    placeholder.hidden = selected.length > 0;

                    selected.forEach((item) => {
                        const tag = document.createElement('span');
                        tag.className = 'nexo-check-multiselect-tag';
                        tag.textContent = item.label;

                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.setAttribute('aria-label', `Remover ${item.label}`);
                        removeButton.textContent = 'x';
                        removeButton.addEventListener('click', (event) => {
                            event.stopPropagation();

                            const checkbox = checkboxes.find((option) => option.value === item.value);

                            if (checkbox) {
                                checkbox.checked = false;
                            }

                            if (warning) {
                                warning.hidden = true;
                            }

                            renderTags();
                        });

                        tag.appendChild(removeButton);
                        tagsContainer.appendChild(tag);
                    });
                };

                const closeDropdown = () => {
                    dropdown.hidden = true;
                    toggle.setAttribute('aria-expanded', 'false');
                };

                toggle.addEventListener('click', () => {
                    const isOpen = !dropdown.hidden;

                    dropdown.hidden = isOpen;
                    toggle.setAttribute('aria-expanded', String(!isOpen));
                });

                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        const quantidadeSelecionada = selectedValues().length;

                        if (quantidadeSelecionada > maxOperadoras) {
                            checkbox.checked = false;

                            if (warning) {
                                warning.hidden = false;
                            }
                        } else if (warning) {
                            warning.hidden = true;
                        }

                        renderTags();
                    });
                });

                document.addEventListener('click', (event) => {
                    if (!multiselect.contains(event.target)) {
                        closeDropdown();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeDropdown();
                    }
                });

                renderTags();
            }

            document.querySelectorAll('[data-money-mask]').forEach((input) => {
                input.addEventListener('input', (event) => {
                    let value = event.target.value.replace(/\D/g, '');

                    if (!value) {
                        event.target.value = '';

                        return;
                    }

                    value = (Number(value) / 100).toFixed(2);
                    value = value.replace('.', ',');
                    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                    event.target.value = `R$ ${value}`;
                });
            });

            if (inputTelefone) {
                inputTelefone.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
                    
                    if (value.length > 11) {
                        value = value.slice(0, 11); // Limita em 11 dígitos (DDD + 9 dígitos)
                    }

                    // Aplica a máscara dinamicamente de acordo com a quantidade de números
                    if (value.length > 10) {
                        // Celular: (XX) XXXXX-XXXX
                        value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
                    } else if (value.length > 6) {
                        // Telefone Fixo ou digitando celular: (XX) XXXX-XXXX
                        value = value.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
                    } else if (value.length > 2) {
                        value = value.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
                    } else if (value.length > 0) {
                        value = value.replace(/^(\d{0,2})$/, '($1');
                    }

                    e.target.value = value;
                });
            }
        });
    </script>
</x-layouts.app>
