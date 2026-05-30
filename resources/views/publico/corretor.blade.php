<x-layouts.public title="{{ $perfil->nome_publico }} | Nexo Saúde">
    <header class="nexo-public-header nexo-public-header-premium">
        <div class="nexo-public-container">
            <a class="nexo-logo" href="{{ route('publico.corretor', $perfil->slug) }}" aria-label="Nexo Saúde">
                <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
            </a>
        </div>
    </header>

    <main class="nexo-public-page nexo-public-page-premium">
        <section class="nexo-public-container">
            <div class="nexo-public-card nexo-public-card-premium">
                <aside class="nexo-broker-profile nexo-broker-profile-premium">
                    <div class="nexo-broker-photo-wrap">
                        <img
                            class="nexo-broker-photo"
                            src="{{ $perfil->foto_path ? asset('storage/'.$perfil->foto_path) : asset('assets/nexo-logo-topo.png') }}"
                            alt="Foto do corretor {{ $perfil->nome_publico }}"
                        >
                    </div>

                    <span class="nexo-broker-label">Corretor responsável</span>

                    <div class="nexo-broker-name-line">
                        <h2>{{ $perfil->nome_publico }}</h2>
                    </div>

                    @if(($reputacao['total'] ?? 0) > 0)
                        <div class="nexo-public-rating-summary">
                            <div class="nexo-public-stars" aria-label="Nota média {{ number_format($reputacao['media'], 1, ',', '.') }} de 5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= round($reputacao['media']) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>

                            <a
                                href="#avaliacoes-clientes"
                                class="nexo-public-rating-link"
                                data-bs-toggle="modal"
                                data-bs-target="#avaliacoesClientesModal"
                            >{{ number_format($reputacao['media'], 1, ',', '.') }} ({{ $reputacao['total'] }} avaliações)</a>
                        </div>
                    @endif

                    @if(($reputacao['premium'] ?? false))
                        <div class="nexo-premium-floating-badge">
                            <img
                                src="{{ asset('assets/logopremium.png') }}"
                                alt="Corretor Premium - Excelência em Atendimento"
                            >
                        </div>
                    @endif

                    <p class="muted">{{ $perfil->bio }}</p>

                    <p class="nexo-broker-region">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        {{ $perfil->cidade_regiao }}
                    </p>

                    <div class="nexo-specialties">
                        @foreach($perfil->especialidades ?? [] as $especialidade)
                            <span class="status-pill">{{ $especialidade }}</span>
                        @endforeach
                    </div>

                    <div class="nexo-public-trust-box">
                        <div>
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <span>Dados enviados com segurança</span>
                        </div>

                        <div>
                            <i class="bi bi-person-check" aria-hidden="true"></i>
                            <span>Atendimento personalizado</span>
                        </div>

                        <div>
                            <i class="bi bi-heart-pulse" aria-hidden="true"></i>
                            <span>Planos conforme seu perfil</span>
                        </div>
                    </div>

                </aside>

                <section class="nexo-public-form-panel nexo-public-form-panel-premium">
                    <div class="nexo-form-heading">
                        <span>Solicitação</span>
                        <h2>Solicitar plano de saúde</h2>
                        <p>
                            Informe seus dados para que o corretor possa entender sua necessidade e retornar com uma orientação adequada.
                        </p>
                    </div>

                    <form method="post" action="{{ route('publico.indicacoes.store', $perfil->slug) }}" class="row g-3">
                        @csrf

                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input
                                name="nome"
                                class="form-control"
                                value="{{ old('nome') }}"
                                placeholder="Seu nome completo"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input
                                name="telefone"
                                class="form-control"
                                value="{{ old('telefone') }}"
                                placeholder="(00) 00000-0000"
                                data-phone-mask
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input
                                name="email"
                                type="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                placeholder="voce@email.com"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tipo de plano</label>

                            <select name="tipo_plano" class="form-select d-none" data-plan-type required>
                                @foreach(['Individual', 'Familiar', 'PME', 'Empresarial'] as $tipo)
                                    <option value="{{ $tipo }}" @selected(old('tipo_plano') === $tipo)>
                                        {{ $tipo }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper>
                                <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span data-bootstrap-select-label>{{ old('tipo_plano', 'Individual') }}</span>
                                </button>

                                <ul class="dropdown-menu nexo-bootstrap-select-menu">
                                    @foreach(['Individual' => 'bi-person', 'Familiar' => 'bi-people', 'PME' => 'bi-shop', 'Empresarial' => 'bi-buildings'] as $tipo => $icone)
                                        <li>
                                            <button class="dropdown-item nexo-bootstrap-select-item" type="button" data-select-value="{{ $tipo }}">
                                                <i class="bi {{ $icone }}"></i>
                                                <span>{{ $tipo }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Quantidade de vidas</label>

                            <input
                                name="quantidade_vidas"
                                type="number"
                                min="1"
                                class="form-control"
                                data-lives-count
                                value="{{ old('quantidade_vidas') }}"
                                placeholder="Ex: 3"
                                required
                            >
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Cidade</label>

                            <input
                                name="cidade"
                                class="form-control"
                                value="{{ old('cidade') }}"
                                placeholder="Sua cidade"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Estado</label>

                            <select name="estado" class="form-select" required>
                                <option value="" disabled @selected(! old('estado'))>Selecione</option>

                                @foreach([
                                    'AC','AL','AP','AM','BA','CE','DF','ES','GO',
                                    'MA','MT','MS','MG','PA','PB','PR','PE','PI',
                                    'RJ','RN','RS','RO','RR','SC','SP','SE','TO'
                                ] as $uf)
                                    <option value="{{ $uf }}" @selected(old('estado') === $uf)>
                                        {{ $uf }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Possui alguma preferência?</label>

                            <select
                                name="possui_preferencias"
                                class="form-select"
                                data-preferences-toggle
                                required
                            >
                                <option
                                    value="ainda_nao_sei"
                                    @selected(old('possui_preferencias', 'ainda_nao_sei') === 'ainda_nao_sei')
                                >
                                    Ainda não sei
                                </option>

                                <option
                                    value="nao"
                                    @selected(old('possui_preferencias') === 'nao')
                                >
                                    Não
                                </option>

                                <option
                                    value="sim"
                                    @selected(old('possui_preferencias') === 'sim')
                                >
                                    Sim
                                </option>
                            </select>
                        </div>

                        <div class="col-12 nexo-preferences-panel" data-preferences-panel hidden>
                            <div class="nexo-preferences-card">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">
                                            Selecione até 3 operadoras de sua preferência.
                                        </label>

                                        @php
                                            $operadorasSelecionadas = collect(old('operadoras', []))
                                                ->map(fn ($id) => (string) $id)
                                                ->toArray();
                                        @endphp

                                        <div class="nexo-check-multiselect" data-operadoras-multiselect>
                                            <button
                                                type="button"
                                                class="nexo-check-multiselect-control"
                                                data-operadoras-toggle
                                                aria-expanded="false"
                                            >
                                                <span
                                                    class="nexo-check-multiselect-tags"
                                                    data-operadoras-tags
                                                ></span>

                                                <span
                                                    class="nexo-check-multiselect-placeholder"
                                                    data-operadoras-placeholder
                                                >
                                                    Selecione as operadoras
                                                </span>

                                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                            </button>

                                            <div
                                                class="nexo-check-multiselect-dropdown"
                                                data-operadoras-dropdown
                                                hidden
                                            >
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

                                            <div data-operadoras-hidden-inputs></div>
                                        </div>

                                        <div
                                            class="nexo-form-hint"
                                            data-operadoras-warning
                                            hidden
                                        >
                                            Selecione no máximo 3 operadoras.
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">
                                            Selecione até 3 hospitais de sua preferência.
                                        </label>
                                    </div>

                                    @for($i = 0; $i < 3; $i++)
                                        <div class="col-md-4">
                                            <label class="form-label">
                                                Hospital {{ $i + 1 }}
                                            </label>

                                            <input
                                                name="hospitais[]"
                                                class="form-control"
                                                value="{{ old('hospitais.'.$i) }}"
                                                placeholder="Nome do hospital"
                                            >
                                        </div>
                                    @endfor

                                    <div class="col-12">
                                        <label class="form-label">
                                            Faixa de valor mensal desejada.
                                        </label>

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
                            <button class="btn btn-primary nexo-submit-button">
                                Enviar solicitação
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </main>

    <style>
        .nexo-public-header-premium {
            background: linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 26px 0;
        }

        .nexo-public-header-premium .nexo-logo img {
            max-height: 86px;
        }

        .nexo-public-page-premium {
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.12), transparent 34%),
                linear-gradient(180deg, #F4F7FB 0%, #FFFFFF 100%);
            min-height: calc(100vh - 120px);
            padding: 24px 0 64px;
        }

        .nexo-public-card-premium {
            display: grid;
            grid-template-columns: 390px 1fr;
            gap: 0;
            overflow: hidden;
            border: 1px solid #E5EAF0;
            border-radius: 26px;
            background: #FFFFFF;
            box-shadow: 0 28px 70px rgba(6, 28, 63, 0.10);
        }

        .nexo-broker-profile-premium {
            position: relative;
            padding: 42px 36px;
            background:
                linear-gradient(180deg, rgba(6, 28, 63, 0.96), rgba(15, 58, 104, 0.96)),
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.35), transparent 38%);
            color: #FFFFFF;
        }

        .nexo-broker-profile-premium::after {
            content: "";
            position: absolute;
            inset: auto 34px 34px auto;
            width: 120px;
            height: 120px;
            border-radius: 999px;
            background: rgba(47, 128, 237, 0.18);
            filter: blur(4px);
        }

        .nexo-broker-photo-wrap {
            width: 132px;
            height: 132px;
            border-radius: 999px;
            padding: 2px;
            background: rgba(125, 181, 255, 0.65);
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(6, 28, 63, 0.22);
        }

        .nexo-premium-floating-badge {
            position: absolute;
            top: -18px;
            right: -12px;
            z-index: 5;
            width: 245px;
            pointer-events: none;
        }

        .nexo-premium-floating-badge img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            filter:
                drop-shadow(0 22px 38px rgba(0, 0, 0, 0.50))
                drop-shadow(0 0 24px rgba(212, 175, 55, 0.22));
        }

        .nexo-broker-profile-premium .nexo-broker-photo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: #0B2448;
            display: block;
        }

        .nexo-broker-label {
            display: inline-flex;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            color: #BFD8FF;
            font-size: 0.8rem;
            font-weight: 800;
            padding: 6px 11px;
            margin-bottom: 12px;
        }

        .nexo-broker-profile-premium h2 {
            position: relative;
            z-index: 1;
            color: #FFFFFF;
            font-size: 2rem;
            line-height: 1.08;
            font-weight: 900;
            margin-bottom: 16px;
            letter-spacing: -0.03em;
        }

        .nexo-broker-profile-premium .muted {
            position: relative;
            z-index: 1;
            color: rgba(255, 255, 255, 0.78);
            font-size: 1rem;
            line-height: 1.55;
        }

        .nexo-broker-profile-premium .nexo-broker-region {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #FFFFFF;
            font-weight: 800;
            margin: 24px 0 18px;
        }

        .nexo-broker-profile-premium .nexo-specialties {
            position: relative;
            z-index: 1;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nexo-broker-profile-premium .status-pill {
            background: rgba(47, 128, 237, 0.18);
            color: #D7E8FF;
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .nexo-public-trust-box {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 12px;
            margin-top: 34px;
            padding-top: 26px;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
        }

        .nexo-public-trust-box div {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.86);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .nexo-public-trust-box i {
            color: #7DB5FF;
            font-size: 1.15rem;
        }

        .nexo-public-form-panel-premium {
            padding: 42px;
        }

        .nexo-form-heading {
            margin-bottom: 28px;
        }

        .nexo-form-heading span {
            color: #2F80ED;
            font-size: 0.8rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .nexo-form-heading h2 {
            color: #162033;
            font-size: 2rem;
            font-weight: 900;
            margin: 6px 0 8px;
            letter-spacing: -0.03em;
        }

        .nexo-form-heading p {
            color: #64748B;
            margin: 0;
        }

        .nexo-public-form-panel-premium .form-label {
            color: #162033;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .nexo-public-form-panel-premium .form-control,
        .nexo-public-form-panel-premium .form-select {
            min-height: 52px;
            border: 1px solid #D8E2EF;
            border-radius: 12px;
            color: #162033;
            padding: 11px 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .nexo-public-form-panel-premium .form-control:focus,
        .nexo-public-form-panel-premium .form-select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-preferences-card {
            border: 1px solid #CFE2FF;
            border-radius: 18px;
            background: linear-gradient(180deg, #F7FBFF 0%, #FFFFFF 100%);
            padding: 22px;
        }

        .nexo-submit-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 54px;
            border-radius: 12px;
            padding: 0 24px;
            font-weight: 900;
            box-shadow: 0 16px 34px rgba(47, 128, 237, 0.22);
        }

        .nexo-check-multiselect {
            position: relative;
        }

        .nexo-check-multiselect-control {
            width: 100%;
            min-height: 52px;
            border: 1px solid #d8e2ef;
            border-radius: 12px;
            background: #ffffff;
            color: #162033;
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
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.16);
            outline: none;
        }

        .nexo-check-multiselect-placeholder {
            color: #64748B;
            flex: 1;
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
            font-size: 0.875rem;
            font-weight: 800;
            padding: 5px 9px;
        }

        .nexo-check-multiselect-tag button {
            border: 0;
            background: transparent;
            color: #2F80ED;
            font-weight: 900;
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
            border: 1px solid #d8e2ef;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 18px 40px rgba(6, 28, 63, 0.16);
            padding: 8px;
        }

        .nexo-check-multiselect-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
            color: #162033;
            font-size: 0.95rem;
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
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .nexo-checkmark::after {
            content: "✓";
            display: none;
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 900;
            line-height: 1;
        }

        .nexo-check-multiselect-option input:checked + .nexo-checkmark {
            border-color: #2F80ED;
            background: #2F80ED;
        }

        .nexo-check-multiselect-option input:checked + .nexo-checkmark::after {
            display: block;
        }

        @media (max-width: 992px) {
            .nexo-public-card-premium {
                grid-template-columns: 1fr;
            }

            .nexo-broker-profile-premium,
            .nexo-public-form-panel-premium {
                padding: 30px 24px;
            }
        }

        @media (max-width: 576px) {
            .nexo-premium-floating-badge {
                top: -2px;
                right: -2px;
                width: 180px;
            }

            .nexo-broker-photo-wrap {
                margin-bottom: 26px;
            }
        }

        .nexo-broker-name-line {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nexo-broker-name-line h2 {
            margin-bottom: 0;
        }

        .nexo-public-rating-summary {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 12px 0 14px;
            color: rgba(255, 255, 255, 0.86);
            font-size: 0.92rem;
            font-weight: 850;
        }

        .nexo-public-stars {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            color: #D4AF37;
            text-shadow: 0 6px 16px rgba(212, 175, 55, 0.25);
        }

        .nexo-public-rating-link {
            color: rgba(255, 255, 255, 0.86);
            font-size: 0.92rem;
            font-weight: 850;
            text-decoration: none;
        }

        .nexo-public-rating-link:hover {
            color: #FFFFFF;
            text-decoration: none;
        }

        .nexo-public-reviews-modal .modal-content {
            border: 0;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(15, 23, 42, 0.24);
        }

        .nexo-public-reviews-modal-header {
            padding: 28px;
            background:
                radial-gradient(circle at top right, rgba(212, 175, 55, 0.24), transparent 34%),
                linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
        }

        .nexo-public-reviews-modal-header span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #F6E7A1;
            font-size: 0.78rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .nexo-public-reviews-modal-header h2 {
            color: #FFFFFF;
            font-size: 1.7rem;
            font-weight: 950;
            letter-spacing: -0.04em;
            margin: 0 0 6px;
        }

        .nexo-public-reviews-modal-header p {
            color: rgba(255, 255, 255, 0.76);
            margin: 0;
            font-weight: 700;
        }

        .nexo-public-reviews-modal-body {
            max-height: 62vh;
            overflow-y: auto;
            padding: 22px;
            background: #F8FBFF;
        }

        .nexo-public-reviews-modal-list {
            display: grid;
            gap: 12px;
        }

        .nexo-public-reviews-modal-list article {
            border: 1px solid #E6EEF9;
            border-radius: 18px;
            background: #FFFFFF;
            padding: 16px;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.045);
        }

        .nexo-public-reviews-modal-list article div {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #D4AF37;
            font-size: 0.92rem;
            font-weight: 950;
            margin-bottom: 8px;
        }

        .nexo-public-reviews-modal-list article p {
            color: #334155;
            font-size: 0.92rem;
            line-height: 1.5;
            margin: 0;
            font-weight: 700;
        }
    </style>

    @if(($reputacao['total'] ?? 0) > 0)
        <div class="modal fade nexo-public-reviews-modal" id="avaliacoesClientesModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="nexo-public-reviews-modal-header">
                        <span><i class="bi bi-star-fill"></i> Avaliações verificadas</span>
                        <h2>Avaliações de clientes</h2>
                        <p>{{ number_format($reputacao['media'], 1, ',', '.') }} de 5 com base em {{ $reputacao['total'] }} avaliações.</p>
                    </div>

                    <div class="nexo-public-reviews-modal-body">
                        <div class="nexo-public-reviews-modal-list">
                            @foreach(($reputacao['avaliacoes'] ?? collect()) as $avaliacao)
                                <article>
                                    <div>
                                        <span>{{ number_format($avaliacao->media, 1, ',', '.') }}</span>
                                        <i class="bi bi-star-fill"></i>
                                    </div>

                                    <p>{{ $avaliacao->comentario ?: 'Cliente avaliou positivamente o atendimento.' }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const preferencesToggle = document.querySelector('[data-preferences-toggle]');
            const preferencesPanel = document.querySelector('[data-preferences-panel]');
            const multiselect = document.querySelector('[data-operadoras-multiselect]');
            const warning = document.querySelector('[data-operadoras-warning]');

            function togglePreferencesPanel() {
                if (!preferencesToggle || !preferencesPanel) {
                    return;
                }

                const shouldShow = preferencesToggle.value === 'sim';

                preferencesPanel.hidden = !shouldShow;

                if (!shouldShow && multiselect) {
                    multiselect.querySelectorAll('[data-operadora-checkbox]').forEach(function (checkbox) {
                        checkbox.checked = false;
                    });

                    renderOperadoras();
                }
            }

            function renderOperadoras() {
                if (!multiselect) {
                    return;
                }

                const tagsContainer = multiselect.querySelector('[data-operadoras-tags]');
                const placeholder = multiselect.querySelector('[data-operadoras-placeholder]');
                const checked = Array.from(
                    multiselect.querySelectorAll('[data-operadora-checkbox]:checked')
                );

                tagsContainer.innerHTML = '';

                placeholder.hidden = checked.length > 0;

                checked.forEach(function (checkbox) {
                    const tag = document.createElement('span');

                    tag.className = 'nexo-check-multiselect-tag';
                    tag.textContent = checkbox.dataset.label;

                    const removeButton = document.createElement('button');

                    removeButton.type = 'button';
                    removeButton.textContent = '×';

                    removeButton.addEventListener('click', function (event) {
                        event.stopPropagation();

                        checkbox.checked = false;

                        renderOperadoras();

                        if (warning) {
                            warning.hidden = true;
                        }
                    });

                    tag.appendChild(removeButton);

                    tagsContainer.appendChild(tag);
                });
            }

            if (preferencesToggle) {
                preferencesToggle.addEventListener('change', togglePreferencesPanel);

                togglePreferencesPanel();
            }

            if (multiselect) {
                const toggle = multiselect.querySelector('[data-operadoras-toggle]');
                const dropdown = multiselect.querySelector('[data-operadoras-dropdown]');
                const checkboxes = multiselect.querySelectorAll('[data-operadora-checkbox]');

                toggle.addEventListener('click', function () {
                    const isOpen = !dropdown.hidden;

                    dropdown.hidden = isOpen;

                    toggle.setAttribute('aria-expanded', String(!isOpen));
                });

                checkboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        const selected = multiselect.querySelectorAll(
                            '[data-operadora-checkbox]:checked'
                        );

                        if (selected.length > 3) {
                            checkbox.checked = false;

                            if (warning) {
                                warning.hidden = false;
                            }

                            return;
                        }

                        if (warning) {
                            warning.hidden = true;
                        }

                        renderOperadoras();
                    });
                });

                document.addEventListener('click', function (event) {
                    if (!multiselect.contains(event.target)) {
                        dropdown.hidden = true;

                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });

                renderOperadoras();
            }

            const moneyInputs = document.querySelectorAll('[data-money-mask]');

            moneyInputs.forEach(function (input) {
                input.addEventListener('input', function (event) {
                    let value = event.target.value.replace(/\D/g, '');

                    if (!value) {
                        event.target.value = '';

                        return;
                    }

                    value = (Number(value) / 100).toFixed(2) + '';

                    value = value.replace('.', ',');
                    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                    event.target.value = 'R$ ' + value;
                });
            });

            const phoneInputs = document.querySelectorAll('[data-phone-mask]');

            phoneInputs.forEach(function (input) {
                input.addEventListener('input', function (event) {
                    let value = event.target.value.replace(/\D/g, '');

                    value = value.substring(0, 11);

                    if (value.length > 10) {
                        value = value.replace(
                            /^(\d{2})(\d{5})(\d{4}).*/,
                            '($1) $2-$3'
                        );
                    } else if (value.length > 6) {
                        value = value.replace(
                            /^(\d{2})(\d{4})(\d+).*/,
                            '($1) $2-$3'
                        );
                    } else if (value.length > 2) {
                        value = value.replace(
                            /^(\d{2})(\d+).*/,
                            '($1) $2'
                        );
                    } else if (value.length > 0) {
                        value = value.replace(
                            /^(\d*)/,
                            '($1'
                        );
                    }

                    event.target.value = value;
                });
            });
        });
    </script>
</x-layouts.public>