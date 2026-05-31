<x-layouts.app title="Pré-cadastro | Nexo Saúde">
    @php
        $quantidadeInicial = max(1, (int) $indicacao->quantidade_vidas);
    @endphp

    <main class="nexo-main nexo-pre-cadastro-page">
        <section class="nexo-pre-hero">
            <div>
                <span class="nexo-page-label">
                    <i class="bi bi-link-45deg"></i>
                    Pré-cadastro
                </span>

                <h1>Gerar link de pré-cadastro</h1>

                <p>
                    Configure a estrutura da proposta, defina as vidas e selecione quais documentos o cliente deverá enviar pelo link público.
                </p>
            </div>

            <div class="nexo-hero-summary">
                <span>Estrutura atual</span>
                <strong><span data-beneficiary-total>{{ $quantidadeInicial }}</span> vida(s)</strong>
                <small>O cliente preencherá os dados pessoais depois.</small>
            </div>
        </section>

        <section class="nexo-panel-card">
            <div class="nexo-section-header">
                <div>
                    <span class="nexo-section-kicker">
                        <i class="bi bi-sliders"></i>
                        Configuração
                    </span>

                    <h2>Dados da proposta</h2>

                    <p>
                        Escolha o tipo de proposta e a natureza do cadastro antes de configurar os beneficiários.
                    </p>
                </div>
            </div>

            <form method="post" action="{{ route('pre-cadastros.store', $indicacao) }}" class="row g-4" data-beneficiarios-form>
                @csrf

                <div class="col-md-6">
                    <label class="form-label">Tipo da proposta</label>

                    <select name="tipo_proposta" class="form-select d-none" data-tipo-proposta>
                        <option value="individual">Individual</option>
                        <option value="familiar" selected>Familiar</option>
                        <option value="empresarial">Empresarial</option>
                    </select>

                    <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper>
                        <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span data-bootstrap-select-label>Familiar</span>
                        </button>

                        <ul class="dropdown-menu nexo-bootstrap-select-menu">
                            <li>
                                <button class="dropdown-item nexo-bootstrap-select-item" type="button" data-select-target="tipo_proposta" data-select-value="individual">
                                    <i class="bi bi-person"></i>
                                    <span>Individual</span>
                                </button>
                            </li>

                            <li>
                                <button class="dropdown-item nexo-bootstrap-select-item active" type="button" data-select-target="tipo_proposta" data-select-value="familiar">
                                    <i class="bi bi-people"></i>
                                    <span>Familiar</span>
                                </button>
                            </li>

                            <li>
                                <button class="dropdown-item nexo-bootstrap-select-item" type="button" data-select-target="tipo_proposta" data-select-value="empresarial">
                                    <i class="bi bi-buildings"></i>
                                    <span>Empresarial</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">PF ou PJ</label>

                    <select name="pessoa" class="form-select d-none" data-pessoa>
                        <option value="PF" selected>PF</option>
                        <option value="PJ">PJ</option>
                    </select>

                    <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper>
                        <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-pessoa-toggle>
                            <span data-bootstrap-select-label>PF</span>
                        </button>

                        <ul class="dropdown-menu nexo-bootstrap-select-menu">
                            <li>
                                <button class="dropdown-item nexo-bootstrap-select-item active" type="button" data-select-target="pessoa" data-select-value="PF">
                                    <i class="bi bi-person-vcard"></i>
                                    <span>PF</span>
                                </button>
                            </li>

                            <li>
                                <button class="dropdown-item nexo-bootstrap-select-item" type="button" data-select-target="pessoa" data-select-value="PJ">
                                    <i class="bi bi-building"></i>
                                    <span>PJ</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-12">
                    <div class="nexo-beneficiaries-heading">
                        <div>
                            <span class="nexo-section-kicker">
                                <i class="bi bi-people"></i>
                                Vidas
                            </span>

                            <h2>Beneficiários</h2>

                            <p>
                                Cada beneficiário terá seus próprios documentos solicitados no link público.
                            </p>
                        </div>

                        <button type="button" class="nexo-secondary-btn" data-add-beneficiario>
                            <i class="bi bi-plus-circle"></i>
                            Adicionar beneficiário
                        </button>
                    </div>

                    <div class="nexo-beneficiary-list" data-beneficiarios-list>
                        @for($i = 0; $i < $quantidadeInicial; $i++)
                            <section class="nexo-beneficiary-card" data-beneficiario>
                                <div class="nexo-beneficiary-card-header">
                                    <div class="nexo-beneficiary-title-wrap">
                                        <div class="nexo-beneficiary-avatar">
                                            {{ $i + 1 }}
                                        </div>

                                        <div>
                                            <h3 data-beneficiary-title>Beneficiário {{ $i + 1 }}</h3>
                                            <p>{{ $i === 0 ? 'Pessoa principal da proposta' : 'Vida adicional vinculada à proposta' }}</p>
                                        </div>
                                    </div>

                                    <button type="button" class="nexo-remove-btn" data-remove-beneficiario title="Remover beneficiário">
                                        <i class="bi bi-trash3"></i>
                                        Remover
                                    </button>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo estrutural</label>

                                        <select class="form-select d-none" name="vidas[{{ $i }}][tipo]" data-beneficiary-type>
                                            <option value="{{ $i === 0 ? 'titular' : 'dependente' }}" selected>{{ $i === 0 ? 'Titular' : 'Dependente' }}</option>
                                        </select>

                                        <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper data-beneficiary-type-wrapper>
                                            <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span data-bootstrap-select-label>{{ $i === 0 ? 'Titular' : 'Dependente' }}</span>
                                            </button>

                                            <ul class="dropdown-menu nexo-bootstrap-select-menu"></ul>
                                        </div>
                                    </div>

                                    <div class="col-md-4" data-beneficiary-link-field hidden>
                                        <label class="form-label">Vinculado a</label>
                                        <select class="form-select" name="vidas[{{ $i }}][vinculo_beneficiario_id]" data-beneficiary-link></select>
                                        <div class="nexo-field-help">Obrigatorio para dependentes de socio ou colaborador.</div>
                                    </div>

                                    <div class="col-md-8" data-beneficiary-docs-column>
                                        <label class="form-label">Solicitar documentos</label>

                                        <select class="form-select d-none" name="vidas[{{ $i }}][documentos_solicitados][]" data-documentos multiple>
                                            @foreach($tiposDocumento as $tipoDocumento)
                                                <option value="{{ $tipoDocumento->id }}">{{ $tipoDocumento->nome }}</option>
                                            @endforeach
                                        </select>

                                        <div class="nexo-field-help">
                                            O cliente preencherá dados pessoais e anexará apenas os documentos definidos aqui.
                                        </div>
                                    </div>
                                </div>
                            </section>
                        @endfor
                    </div>
                </div>

                <div class="col-12">
                    <div class="nexo-form-actions">
                        <a href="{{ url()->previous() }}" class="nexo-secondary-btn">
                            <i class="bi bi-arrow-left"></i>
                            Voltar
                        </a>

                        <button class="nexo-primary-btn">
                            <i class="bi bi-link-45deg"></i>
                            Gerar link de pré-cadastro
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <template data-beneficiario-template>
        <section class="nexo-beneficiary-card" data-beneficiario>
            <div class="nexo-beneficiary-card-header">
                <div class="nexo-beneficiary-title-wrap">
                    <div class="nexo-beneficiary-avatar" data-beneficiary-avatar>
                        1
                    </div>

                    <div>
                        <h3 data-beneficiary-title>Beneficiário</h3>
                        <p>Vida adicional vinculada à proposta</p>
                    </div>
                </div>

                <button type="button" class="nexo-remove-btn" data-remove-beneficiario title="Remover beneficiário">
                    <i class="bi bi-trash3"></i>
                    Remover
                </button>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tipo estrutural</label>

                    <select class="form-select d-none" data-beneficiary-type></select>

                    <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper data-beneficiary-type-wrapper>
                        <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span data-bootstrap-select-label>Dependente</span>
                        </button>

                        <ul class="dropdown-menu nexo-bootstrap-select-menu"></ul>
                    </div>
                </div>

                <div class="col-md-4" data-beneficiary-link-field hidden>
                    <label class="form-label">Vinculado a</label>
                    <select class="form-select" data-beneficiary-link></select>
                    <div class="nexo-field-help">Obrigatorio para dependentes de socio ou colaborador.</div>
                </div>

                <div class="col-md-8" data-beneficiary-docs-column>
                    <label class="form-label">Solicitar documentos</label>
                    <select class="form-select d-none" data-documentos multiple></select>
                    <div class="nexo-field-help">O cliente preencherá dados pessoais e anexará apenas os documentos definidos aqui.</div>
                </div>
            </div>
        </section>
    </template>

    <style>
        .nexo-pre-cadastro-page {
            display: block;
        }

        .nexo-pre-hero {
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

        .nexo-pre-hero h1 {
            color: #FFFFFF;
            font-size: clamp(1.8rem, 2.8vw, 3rem);
            line-height: 1.04;
            font-weight: 950;
            letter-spacing: -0.055em;
            margin: 0 0 10px;
        }

        .nexo-pre-hero p {
            max-width: 760px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.98rem;
            font-weight: 650;
            margin: 0;
        }

        .nexo-hero-summary {
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.13);
            backdrop-filter: blur(10px);
        }

        .nexo-hero-summary span,
        .nexo-hero-summary small {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-weight: 800;
        }

        .nexo-hero-summary span {
            font-size: 0.84rem;
        }

        .nexo-hero-summary strong {
            display: block;
            color: #FFFFFF;
            font-size: 1.35rem;
            font-weight: 950;
            margin: 3px 0;
        }

        .nexo-hero-summary small {
            font-size: 0.82rem;
        }

        .nexo-panel-card {
            padding: 24px;
            border-radius: 28px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.055);
        }

        .nexo-section-header {
            margin-bottom: 22px;
        }

        .nexo-section-header h2,
        .nexo-beneficiaries-heading h2 {
            color: #061C3F;
            font-size: 1.45rem;
            font-weight: 950;
            letter-spacing: -0.035em;
            margin: 0 0 6px;
        }

        .nexo-section-header p,
        .nexo-beneficiaries-heading p {
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

        .form-control:focus,
        .form-select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-bootstrap-select {
            position: relative;
            width: 100%;
        }

        .nexo-bootstrap-select-toggle {
            width: 100%;
            min-height: 52px;
            padding: 0 15px;
            border-radius: 14px;
            border: 1px solid #D8E2EF;
            background: #FFFFFF;
            color: #061C3F;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-weight: 850;
            text-align: left;
            transition: 0.2s ease;
        }

        .nexo-bootstrap-select-toggle:hover,
        .nexo-bootstrap-select-toggle.show,
        .nexo-bootstrap-select-toggle:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-bootstrap-select-toggle::after {
            margin-left: auto;
        }

        .nexo-bootstrap-select-menu {
            width: 100%;
            padding: 8px;
            border: 1px solid #E4EBF5;
            border-radius: 18px;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.14);
        }

        .nexo-bootstrap-select-item {
            min-height: 44px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #061C3F;
            font-weight: 850;
        }

        .nexo-bootstrap-select-item i {
            color: #2F80ED;
        }

        .nexo-bootstrap-select-item:hover,
        .nexo-bootstrap-select-item:focus {
            background: #F1F7FF;
            color: #061C3F;
        }

        .nexo-bootstrap-select-item.active,
        .nexo-bootstrap-select-item:active {
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
        }

        .nexo-bootstrap-select-item.active i,
        .nexo-bootstrap-select-item:active i {
            color: #FFFFFF;
        }

        .nexo-bootstrap-select.is-disabled {
            opacity: 0.72;
        }

        .nexo-bootstrap-select.is-disabled .nexo-bootstrap-select-toggle {
            cursor: not-allowed;
            background: #F8FBFF;
        }

        .nexo-beneficiaries-heading {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 6px;
            margin-bottom: 16px;
        }

        .nexo-beneficiary-list {
            display: grid;
            gap: 16px;
        }

        .nexo-beneficiary-card {
            padding: 20px;
            border-radius: 22px;
            background: #F8FBFF;
            border: 1px solid #E3ECF8;
        }

        .nexo-beneficiary-card-header {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 18px;
        }

        .nexo-beneficiary-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nexo-beneficiary-avatar {
            width: 52px;
            height: 52px;
            border-radius: 17px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 950;
            box-shadow: 0 12px 26px rgba(47, 128, 237, 0.22);
            flex-shrink: 0;
        }

        .nexo-beneficiary-card h3 {
            color: #061C3F;
            font-size: 1.12rem;
            font-weight: 950;
            margin: 0 0 3px;
        }

        .nexo-beneficiary-card p {
            color: #64748B;
            margin: 0;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .nexo-field-help {
            color: #64748B;
            font-size: 0.84rem;
            font-weight: 650;
            margin-top: 8px;
        }

        .nexo-multiselect {
            position: relative;
        }

        .nexo-multi-control {
            width: 100%;
            min-height: 52px;
            padding: 10px 14px;
            border-radius: 14px;
            border: 1px solid #D8E2EF;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #061C3F;
            text-align: left;
            transition: 0.2s ease;
        }

        .nexo-multi-control:hover,
        .nexo-multiselect.is-open .nexo-multi-control {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.12);
        }

        .nexo-multi-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .nexo-multi-placeholder {
            color: #94A3B8;
            font-weight: 700;
        }

        .nexo-multi-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 30px;
            padding: 0 9px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.78rem;
            font-weight: 900;
        }

        .nexo-multi-chip span {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            background: rgba(47, 128, 237, 0.14);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .nexo-multi-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            z-index: 30;
            display: none;
            max-height: 260px;
            overflow: auto;
            padding: 8px;
            border-radius: 18px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.12);
        }

        .nexo-multiselect.is-open .nexo-multi-menu {
            display: grid;
            gap: 6px;
        }

        .nexo-multi-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 11px;
            border-radius: 13px;
            color: #061C3F;
            font-weight: 750;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .nexo-multi-option:hover {
            background: #F1F7FF;
        }

        .nexo-multi-option input {
            width: 18px;
            height: 18px;
            accent-color: #2F80ED;
        }

        .nexo-primary-btn,
        .nexo-secondary-btn,
        .nexo-remove-btn {
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

        .nexo-remove-btn {
            min-height: 38px;
            padding: 0 13px;
            background: #FFF1F2;
            border-color: #FFCDD5;
            color: #BE123C;
            font-size: 0.82rem;
        }

        .nexo-primary-btn:hover,
        .nexo-secondary-btn:hover,
        .nexo-remove-btn:hover {
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

        .nexo-remove-btn:hover {
            background: #FFE4E8;
            color: #9F1239;
        }

        .nexo-form-actions {
            display: flex;
            flex-direction: column-reverse;
            gap: 12px;
            padding-top: 4px;
        }

        @media (min-width: 768px) {
            .nexo-beneficiaries-heading,
            .nexo-beneficiary-card-header,
            .nexo-form-actions {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        @media (min-width: 992px) {
            .nexo-pre-hero {
                grid-template-columns: 1fr 280px;
                align-items: center;
            }
        }

        @media (max-width: 576px) {
            .nexo-pre-hero,
            .nexo-panel-card,
            .nexo-beneficiary-card {
                padding: 20px;
                border-radius: 24px;
            }

            .nexo-primary-btn,
            .nexo-secondary-btn,
            .nexo-remove-btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-beneficiarios-form]');
            if (!form) return;

            const list = form.querySelector('[data-beneficiarios-list]');
            const template = document.querySelector('[data-beneficiario-template]');
            const tipoProposta = form.querySelector('[data-tipo-proposta]');
            const pessoa = form.querySelector('[data-pessoa]');
            const total = document.querySelector('[data-beneficiary-total]');
            const tiposDocumento = @json($tiposDocumento->map(fn ($tipo) => ['id' => $tipo->id, 'nome' => $tipo->nome])->values());

            const options = {
                PF: [['titular', 'Titular'], ['dependente', 'Dependente']],
                PJ: [['socio', 'Sócio'], ['colaborador', 'Colaborador'], ['dependente_socio', 'Dependente de sócio'], ['dependente_colaborador', 'Dependente de colaborador'], ['responsavel_legal', 'Responsável legal']],
            };

            const typeIcons = {
                titular: 'bi-person-badge',
                dependente: 'bi-person',
                socio: 'bi-person-workspace',
                colaborador: 'bi-person-gear',
                dependente_socio: 'bi-person-plus',
                dependente_colaborador: 'bi-person-plus',
                responsavel_legal: 'bi-shield-check',
            };

            const currentCards = () => Array.from(list.querySelectorAll('[data-beneficiario]'));
            const needsLink = (tipo) => ['dependente_socio', 'dependente_colaborador'].includes(tipo);

            const selectOptionLabel = (select) => {
                const selected = select.options[select.selectedIndex];

                return selected ? selected.textContent : 'Selecione';
            };

            const syncBootstrapSelect = (select) => {
                const wrapper = select.nextElementSibling?.matches('[data-bootstrap-select-wrapper]')
                    ? select.nextElementSibling
                    : select.parentElement.querySelector('[data-bootstrap-select-wrapper]');

                if (! wrapper) {
                    return;
                }

                const label = wrapper.querySelector('[data-bootstrap-select-label]');
                const items = wrapper.querySelectorAll('[data-select-value]');
                const selectedValue = select.value;

                if (label) {
                    label.textContent = selectOptionLabel(select);
                }

                items.forEach((item) => {
                    item.classList.toggle('active', item.dataset.selectValue === selectedValue);
                });
            };

            const setNativeSelectValue = (select, value, shouldDispatch = true) => {
                select.value = value;
                syncBootstrapSelect(select);

                if (shouldDispatch) {
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            const renderBootstrapSelectFromNative = (select, items) => {
                const wrapper = select.nextElementSibling?.matches('[data-bootstrap-select-wrapper]')
                    ? select.nextElementSibling
                    : select.parentElement.querySelector('[data-bootstrap-select-wrapper]');

                if (! wrapper) {
                    return;
                }

                const menu = wrapper.querySelector('.nexo-bootstrap-select-menu');
                const toggle = wrapper.querySelector('.nexo-bootstrap-select-toggle');

                menu.innerHTML = '';

                items.forEach(([value, label]) => {
                    const li = document.createElement('li');
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'dropdown-item nexo-bootstrap-select-item';
                    button.dataset.selectValue = value;
                    button.innerHTML = `
                        <i class="bi ${typeIcons[value] || 'bi-check2-circle'}"></i>
                        <span>${label}</span>
                    `;

                    button.addEventListener('click', () => {
                        setNativeSelectValue(select, value);
                    });

                    li.appendChild(button);
                    menu.appendChild(li);
                });

                if (toggle && ! toggle._nexoBootstrapSelectBound) {
                    toggle._nexoBootstrapSelectBound = true;
                    toggle.addEventListener('show.bs.dropdown', () => syncBootstrapSelect(select));
                }

                syncBootstrapSelect(select);
            };

            document.querySelectorAll('[data-select-target]').forEach((item) => {
                item.addEventListener('click', () => {
                    const target = item.dataset.selectTarget;
                    const value = item.dataset.selectValue;
                    const select = target === 'tipo_proposta' ? tipoProposta : pessoa;

                    setNativeSelectValue(select, value);
                });
            });

            const syncPessoaDropdownState = () => {
                const wrapper = pessoa.nextElementSibling?.matches('[data-bootstrap-select-wrapper]')
                    ? pessoa.nextElementSibling
                    : pessoa.parentElement.querySelector('[data-bootstrap-select-wrapper]');
                const toggle = wrapper?.querySelector('[data-pessoa-toggle]');

                if (! wrapper || ! toggle) {
                    return;
                }

                wrapper.classList.toggle('is-disabled', pessoa.disabled);
                toggle.disabled = pessoa.disabled;
                syncBootstrapSelect(pessoa);
            };

            const fillTypeOptions = (select, selected) => {
                const mode = pessoa.value;
                select.innerHTML = '';

                options[mode].forEach(([value, label]) => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    select.appendChild(option);
                });

                const fallback = mode === 'PJ' ? 'colaborador' : options[mode][0][0];
                select.value = options[mode].some(([value]) => value === selected) ? selected : fallback;
                renderBootstrapSelectFromNative(select, options[mode]);
            };

            const fillDocumentOptions = (select) => {
                if (select.options.length > 0) return;
                tiposDocumento.forEach((tipo) => {
                    const option = document.createElement('option');
                    option.value = String(tipo.id);
                    option.textContent = tipo.nome;
                    select.appendChild(option);
                });
            };

            const ensureDocumentosUi = (select) => {
                if (select._nexoMulti) return select._nexoMulti;
                const ui = document.createElement('div');
                ui.className = 'nexo-multiselect';
                ui.innerHTML = `
                    <button type="button" class="nexo-multi-control" aria-expanded="false">
                        <span class="nexo-multi-tags" data-multi-tags></span>
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nexo-multi-menu" data-multi-menu></div>
                `;
                select.insertAdjacentElement('afterend', ui);
                select._nexoMulti = ui;
                ui.querySelector('.nexo-multi-control').addEventListener('click', () => {
                    const isOpen = ui.classList.toggle('is-open');
                    ui.querySelector('.nexo-multi-control').setAttribute('aria-expanded', String(isOpen));
                });
                ui.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('[data-remove-doc]');
                    if (!removeButton) return;
                    event.stopPropagation();
                    const option = Array.from(select.options).find((item) => item.value === removeButton.dataset.removeDoc);
                    if (option) option.selected = false;
                    renderDocumentosUi(select);
                });
                return ui;
            };

            const renderDocumentosUi = (select) => {
                const ui = ensureDocumentosUi(select);
                const tags = ui.querySelector('[data-multi-tags]');
                const menu = ui.querySelector('[data-multi-menu]');
                const selecionados = Array.from(select.selectedOptions);
                tags.innerHTML = selecionados.length
                    ? selecionados.map((option) => `<span class="nexo-multi-chip">${option.textContent}<span role="button" tabindex="0" data-remove-doc="${option.value}" aria-label="Remover ${option.textContent}">×</span></span>`).join('')
                    : '<span class="nexo-multi-placeholder">Selecione os documentos</span>';
                menu.innerHTML = Array.from(select.options).map((option) => `
                    <label class="nexo-multi-option">
                        <input type="checkbox" value="${option.value}" ${option.selected ? 'checked' : ''}>
                        <span>${option.textContent}</span>
                    </label>
                `).join('');
                menu.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        const option = Array.from(select.options).find((item) => item.value === checkbox.value);
                        if (option) option.selected = checkbox.checked;
                        renderDocumentosUi(select);
                    });
                });
            };

            const selectDocumentosPadrao = (card) => {
                const select = card.querySelector('[data-documentos]');
                fillDocumentOptions(select);
                if (Array.from(select.selectedOptions).length > 0) return;
                const tipo = card.querySelector('[data-beneficiary-type]').value;
                const nomes = (() => {
                    if (pessoa.value === 'PF' && tipo === 'titular') return ['Documento de identidade com foto', 'CPF', 'Comprovante de Residência'];
                    if (pessoa.value === 'PF' && tipo === 'dependente') return ['Documento de identidade com foto', 'Certidão de Nascimento'];
                    if (['socio', 'colaborador', 'responsavel_legal'].includes(tipo)) return ['Documento de identidade com foto', 'CPF'];
                    if (['dependente_socio', 'dependente_colaborador'].includes(tipo)) return ['Documento de identidade com foto', 'Certidão de Nascimento'];
                    return ['Outro'];
                })();
                Array.from(select.options).forEach((option) => {
                    option.selected = nomes.includes(option.textContent.trim());
                });
            };

            const syncProposalPerson = () => {
                if (tipoProposta.value === 'familiar') {
                    pessoa.value = 'PF';
                    pessoa.disabled = true;
                } else if (tipoProposta.value === 'empresarial') {
                    pessoa.value = 'PJ';
                    pessoa.disabled = true;
                } else {
                    pessoa.value = 'PF';
                    pessoa.disabled = true;
                }

                syncPessoaDropdownState();
            };

            const syncDependentLinks = () => {
                const cards = currentCards();

                cards.forEach((card, index) => {
                    const type = card.querySelector('[data-beneficiary-type]');
                    const linkField = card.querySelector('[data-beneficiary-link-field]');
                    const linkSelect = card.querySelector('[data-beneficiary-link]');
                    const docsColumn = card.querySelector('[data-beneficiary-docs-column]');

                    if (!linkField || !linkSelect) {
                        return;
                    }

                    const tipoAtual = type.value;
                    const deveVincular = needsLink(tipoAtual);
                    const tipoVinculo = tipoAtual === 'dependente_colaborador' ? 'colaborador' : 'socio';
                    const elegiveis = cards
                        .map((item, indice) => ({ item, indice, tipo: item.querySelector('[data-beneficiary-type]')?.value }))
                        .filter((item) => item.indice !== index && item.tipo === tipoVinculo);

                    linkField.hidden = !deveVincular;
                    linkSelect.disabled = !deveVincular;
                    linkSelect.required = deveVincular;
                    linkSelect.name = `vidas[${index}][vinculo_beneficiario_id]`;
                    linkSelect.innerHTML = '<option value="">Selecione o titular do vinculo</option>';

                    elegiveis.forEach(({ indice, item }) => {
                        const option = document.createElement('option');
                        option.value = String(indice);
                        option.textContent = `${indice + 1} - ${item.querySelector('[data-beneficiary-title]')?.textContent || tipoVinculo}`;
                        linkSelect.appendChild(option);
                    });

                    if (deveVincular && !elegiveis.some(({ indice }) => String(indice) === linkSelect.value)) {
                        linkSelect.value = elegiveis.length ? String(elegiveis[0].indice) : '';
                    }

                    if (!deveVincular) {
                        linkSelect.value = '';
                    }

                    docsColumn?.classList.toggle('col-md-4', deveVincular);
                    docsColumn?.classList.toggle('col-md-8', !deveVincular);
                });
            };

            const syncCard = (card) => {
                const index = currentCards().indexOf(card);
                const type = card.querySelector('[data-beneficiary-type]');
                const avatar = card.querySelector('[data-beneficiary-avatar]') || card.querySelector('.nexo-beneficiary-avatar');
                card.querySelector('[data-beneficiary-title]').textContent = `Beneficiário ${index + 1}`;
                if (avatar) avatar.textContent = String(index + 1);
                card.querySelectorAll('select').forEach((field) => {
                    if (field.dataset.beneficiaryType !== undefined) field.name = `vidas[${index}][tipo]`;
                    if (field.dataset.documentos !== undefined) field.name = `vidas[${index}][documentos_solicitados][]`;
                    if (field.dataset.beneficiaryLink !== undefined) field.name = `vidas[${index}][vinculo_beneficiario_id]`;
                });
                selectDocumentosPadrao(card);
                renderDocumentosUi(card.querySelector('[data-documentos]'));
                syncBootstrapSelect(type);
                syncDependentLinks();
            };

            const ensurePfTitular = () => {
                if (pessoa.value !== 'PF') return;
                let titularFound = false;
                currentCards().forEach((card, index) => {
                    const type = card.querySelector('[data-beneficiary-type]');
                    if (index === 0 && !titularFound) {
                        type.value = 'titular';
                        titularFound = true;
                    } else if (type.value === 'titular') {
                        type.value = 'dependente';
                    }
                    syncBootstrapSelect(type);
                });
            };

            const renumber = () => {
                if (tipoProposta.value === 'individual') {
                    currentCards().slice(1).forEach((card) => card.remove());
                    const first = currentCards()[0];
                    const firstType = first?.querySelector('[data-beneficiary-type]');

                    if (firstType) {
                        fillTypeOptions(firstType, 'titular');
                        firstType.value = 'titular';
                        syncBootstrapSelect(firstType);
                    }
                }

                currentCards().forEach(syncCard);
                total.textContent = String(currentCards().length);
                form.querySelector('[data-add-beneficiario]').disabled = tipoProposta.value === 'individual';
                syncDependentLinks();
            };

            const applyPersonMode = () => {
                currentCards().forEach((card, index) => {
                    const type = card.querySelector('[data-beneficiary-type]');
                    const selected = type.value;
                    fillTypeOptions(type, selected);
                    if (pessoa.value === 'PF') {
                        type.value = index === 0 ? 'titular' : 'dependente';
                    } else if (!options.PJ.some(([value]) => value === type.value)) {
                        type.value = 'colaborador';
                    }
                    syncBootstrapSelect(type);
                });
                ensurePfTitular();
                renumber();
            };

            const attachCardEvents = (card) => {
                card.querySelector('[data-beneficiary-type]').addEventListener('change', () => {
                    ensurePfTitular();
                    syncDependentLinks();
                    renumber();
                });
                card.querySelector('[data-remove-beneficiario]').addEventListener('click', () => {
                    if (currentCards().length === 1) return;
                    if (pessoa.value === 'PF' && card.querySelector('[data-beneficiary-type]').value === 'titular') return;
                    card.remove();
                    ensurePfTitular();
                    renumber();
                });
            };

            const addBeneficiario = () => {
                const node = template.content.firstElementChild.cloneNode(true);
                list.appendChild(node);
                const type = node.querySelector('[data-beneficiary-type]');
                fillTypeOptions(type, pessoa.value === 'PF' ? 'dependente' : 'colaborador');
                fillDocumentOptions(node.querySelector('[data-documentos]'));
                renderDocumentosUi(node.querySelector('[data-documentos]'));
                attachCardEvents(node);
                renumber();
            };

            currentCards().forEach((card, index) => {
                const type = card.querySelector('[data-beneficiary-type]');
                fillTypeOptions(type, index === 0 ? 'titular' : 'dependente');
                fillDocumentOptions(card.querySelector('[data-documentos]'));
                renderDocumentosUi(card.querySelector('[data-documentos]'));
                attachCardEvents(card);
            });

            document.addEventListener('click', (event) => {
                document.querySelectorAll('.nexo-multiselect.is-open').forEach((ui) => {
                    if (ui.contains(event.target)) return;
                    ui.classList.remove('is-open');
                    ui.querySelector('.nexo-multi-control')?.setAttribute('aria-expanded', 'false');
                });
            });

            tipoProposta.addEventListener('change', () => {
                syncBootstrapSelect(tipoProposta);
                syncProposalPerson();
                applyPersonMode();
            });
            pessoa.addEventListener('change', () => {
                syncBootstrapSelect(pessoa);
                applyPersonMode();
            });
            form.querySelector('[data-add-beneficiario]').addEventListener('click', addBeneficiario);
            form.addEventListener('submit', () => {
                pessoa.disabled = false;
                renumber();
            });

            syncBootstrapSelect(tipoProposta);
            syncBootstrapSelect(pessoa);
            syncProposalPerson();
            applyPersonMode();
        });
    </script>
</x-layouts.app>
