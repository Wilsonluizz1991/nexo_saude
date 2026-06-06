<x-layouts.public title="Pré-cadastro | Nexo Saúde">
    @php
        $corretor = $preCadastro->indicacao?->user;
        $perfil = $corretor?->corretorPerfil;
        $documentosPorVida = $preCadastro->documentosObrigatorios->groupBy('vida_proposta_id');
        $vidasVinculaveis = $preCadastro->vidas->filter(fn ($vida) => in_array($vida->tipo, ['socio', 'colaborador'], true));
        $modoCorrecao = (bool) $preCadastro->enviado_em && in_array($preCadastro->status, ['documentacao_pendente', 'correcao_solicitada'], true);
        $dataMaxima = now()->toDateString();
        $statusDocumentos = [
            'pendente' => 'Pendente',
            'enviado' => 'Enviado',
            'aprovado' => 'Aprovado',
            'aprovado_ia' => 'Aprovado pela IA',
            'corrigir' => 'Corrigir',
            'recusado' => 'Recusado',
            'dispensado' => 'Dispensado',
        ];
    @endphp

    <main class="nexo-client-form-page">
        <section class="nexo-client-form-shell">
            <header class="nexo-client-form-header">
                <div>
                    <div class="nexo-client-logo">
                        <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
                    </div>

                    <span class="nexo-client-badge">
                        <i class="bi bi-link-45deg"></i>
                        Link único
                    </span>

                    <h1>
                        {{ $modoCorrecao ? 'Correção do pré-cadastro' : 'Enviar pré-cadastro' }}
                    </h1>

                    <p>
                        {{ $modoCorrecao
                            ? 'Revise apenas os documentos solicitados pelo corretor e envie novamente para análise.'
                            : 'Complete seus dados pessoais e anexe os documentos solicitados para análise do corretor.' }}
                    </p>
                </div>

                <div class="nexo-client-broker-card">
                    <span>Acompanhado por</span>
                    <strong>{{ $perfil?->nome_publico ?? $corretor?->name ?? 'seu corretor' }}</strong>
                </div>
            </header>

            @if($modoCorrecao)
                <div class="nexo-correction-box">
                    <div class="nexo-correction-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>

                    <div>
                        <h2>Seu pré-cadastro precisa de correções</h2>
                        <p>Revise os documentos indicados abaixo e envie novamente para análise.</p>

                        @if(! empty($motivosCorrecao))
                            <ul>
                                @foreach($motivosCorrecao as $motivo)
                                    <li>{{ $motivo }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif

            <div class="nexo-client-summary-grid">
                <div class="nexo-client-summary-card">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Tipo da proposta</span>
                    <strong>{{ ucfirst($preCadastro->tipo_proposta) }}</strong>
                </div>

                <div class="nexo-client-summary-card">
                    <i class="bi bi-person-badge"></i>
                    <span>PF/PJ</span>
                    <strong>{{ $preCadastro->pessoa }}</strong>
                </div>

                <div class="nexo-client-summary-card">
                    <i class="bi bi-people"></i>
                    <span>Número de vidas</span>
                    <strong>{{ $preCadastro->vidas->count() }}</strong>
                </div>
            </div>

            <form method="post" enctype="multipart/form-data" action="{{ route('cliente.pre-cadastro.store', ['slug' => $slug, 'token' => $preCadastro->token]) }}" data-public-pre-cadastro>
                @csrf

                @foreach($preCadastro->vidas->sortBy('ordem') as $vida)
                    @php
                        $documentos = $documentosPorVida->get($vida->id, collect());
                    @endphp

                    <section class="nexo-beneficiary-card" data-public-beneficiario data-beneficiario-id="{{ $vida->id }}" data-beneficiario-tipo="{{ $vida->tipo }}">
                        <div class="nexo-beneficiary-header">
                            <div class="nexo-beneficiary-title">
                                <div class="nexo-beneficiary-avatar">
                                    {{ $vida->ordem }}
                                </div>

                                <div>
                                    <h2>Beneficiário {{ $vida->ordem }}</h2>
                                    <p>{{ ucfirst(str_replace('_', ' ', $vida->tipo)) }}</p>
                                </div>
                            </div>

                            @if($modoCorrecao)
                                <span class="nexo-locked-pill">
                                    <i class="bi bi-lock"></i>
                                    Dados pessoais bloqueados
                                </span>
                            @endif
                        </div>

                        <div class="nexo-section-divider">
                            <span>Dados pessoais</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Nome</label>
                                <input class="form-control" name="vidas[{{ $vida->id }}][nome]" value="{{ old("vidas.{$vida->id}.nome", $vida->nome) }}" required @disabled($modoCorrecao)>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">CPF/documento</label>
                                <input class="form-control" name="vidas[{{ $vida->id }}][cpf]" value="{{ old("vidas.{$vida->id}.cpf", $vida->cpf) }}" inputmode="numeric" maxlength="14" autocomplete="off" data-cpf-mask required @disabled($modoCorrecao)>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Data de nascimento</label>

                                <div class="nexo-date-field" data-max-date="{{ $dataMaxima }}">
                                    <i class="bi bi-calendar2-check"></i>

                                    <input
                                        class="form-control nexo-date-display"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="dd/mm/aaaa"
                                        autocomplete="off"
                                        value="{{ old("vidas.{$vida->id}.data_nascimento", $vida->data_nascimento?->format('d/m/Y')) }}"
                                        required
                                        @disabled($modoCorrecao)
                                    >

                                    <input
                                        class="nexo-date-hidden"
                                        type="hidden"
                                        name="vidas[{{ $vida->id }}][data_nascimento]"
                                        value="{{ old("vidas.{$vida->id}.data_nascimento", $vida->data_nascimento?->format('Y-m-d')) }}"
                                        data-public-date-hidden
                                    >

                                    <button class="nexo-date-button" type="button" aria-label="Abrir calendário" @disabled($modoCorrecao)>
                                        <i class="bi bi-calendar3"></i>
                                    </button>

                                    <div class="nexo-calendar"></div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Sexo</label>

                                <select class="form-select d-none" name="vidas[{{ $vida->id }}][sexo]" data-public-sexo data-bootstrap-select required @disabled($modoCorrecao)>
                                    <option value="">Selecione</option>
                                    <option value="masculino" @selected(old("vidas.{$vida->id}.sexo", $vida->sexo) === 'masculino')>Masculino</option>
                                    <option value="feminino" @selected(old("vidas.{$vida->id}.sexo", $vida->sexo) === 'feminino')>Feminino</option>
                                    <option value="outro" @selected(old("vidas.{$vida->id}.sexo", $vida->sexo) === 'outro')>Outro</option>
                                </select>

                                <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper>
                                    <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" @disabled($modoCorrecao)>
                                        <span data-bootstrap-select-label>Selecione</span>
                                    </button>

                                    <ul class="dropdown-menu nexo-bootstrap-select-menu"></ul>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 d-none nexo-gestante-column" data-public-gestante-wrap>
                                <label class="nexo-check-field">
                                    <input class="form-check-input" type="checkbox" name="vidas[{{ $vida->id }}][gestante]" value="1" data-public-gestante @checked(old("vidas.{$vida->id}.gestante", $vida->gestante)) @disabled($modoCorrecao)>
                                    <span>Informar gestação</span>
                                </label>
                            </div>

                            @if(in_array($vida->tipo, ['dependente', 'dependente_socio', 'dependente_colaborador'], true))
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Parentesco</label>

                                    <select class="form-select d-none" name="vidas[{{ $vida->id }}][parentesco]" data-bootstrap-select required @disabled($modoCorrecao)>
                                        <option value="">Selecione</option>
                                        @foreach(['conjuge' => 'Cônjuge', 'companheiro' => 'Companheiro(a)', 'filho' => 'Filho(a)', 'enteado' => 'Enteado(a)', 'pai' => 'Pai', 'mae' => 'Mãe', 'outro' => 'Outro'] as $valor => $label)
                                            <option value="{{ $valor }}" @selected(old("vidas.{$vida->id}.parentesco", $vida->parentesco) === $valor)>{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper>
                                        <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" @disabled($modoCorrecao)>
                                            <span data-bootstrap-select-label>Selecione</span>
                                        </button>

                                        <ul class="dropdown-menu nexo-bootstrap-select-menu"></ul>
                                    </div>
                                </div>
                            @endif

                            @if(in_array($vida->tipo, ['dependente_socio', 'dependente_colaborador'], true))
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Vinculado a</label>

                                    <select class="form-select d-none" name="vidas[{{ $vida->id }}][vinculo_beneficiario_id]" data-bootstrap-select required @disabled($modoCorrecao)>
                                        <option value="">Selecione</option>
                                        @foreach($vidasVinculaveis as $vidaVinculavel)
                                            <option value="{{ $vidaVinculavel->id }}" @selected(old("vidas.{$vida->id}.vinculo_beneficiario_id", $vida->vinculo_beneficiario_id) == $vidaVinculavel->id)>
                                                Beneficiário {{ $vidaVinculavel->ordem }} - {{ ucfirst(str_replace('_', ' ', $vidaVinculavel->tipo)) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="dropdown nexo-bootstrap-select" data-bootstrap-select-wrapper>
                                        <button class="nexo-bootstrap-select-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" @disabled($modoCorrecao)>
                                            <span data-bootstrap-select-label>Selecione</span>
                                        </button>

                                        <ul class="dropdown-menu nexo-bootstrap-select-menu"></ul>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="nexo-section-divider">
                            <span>Documentos solicitados</span>
                        </div>

                        <div class="row g-3">
                            @foreach($documentos->sortBy('ordem') as $documento)
                                @php
                                    $documentoLiberado = (! $modoCorrecao || in_array($documento->status, ['pendente', 'corrigir', 'recusado'], true)) && ! $documento->dispensado_por_ia && ! $documento->validado_por_documento_compartilhado;
                                    $documentoObrigatorioInput = $documentoLiberado && $documento->obrigatorio && (! $documento->envio || $modoCorrecao);
                                    $statusLabel = $statusDocumentos[$documento->status] ?? ucfirst(str_replace('_', ' ', $documento->status));
                                @endphp

                                <div class="col-12 col-md-6">
                                    <div class="nexo-upload-box @if(! $documentoLiberado) is-locked @endif">
                                        <div class="nexo-upload-header">
                                            <div>
                                                <label class="form-label mb-0">
                                                    {{ $documento->tipoDocumento?->nome ?? $documento->titulo }}
                                                    @if($documento->obrigatorio)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                            </div>

                                            <span class="nexo-document-status" data-document-status>{{ $statusLabel }}</span>
                                        </div>

                                        @if($documento->envio)
                                            <div class="nexo-file-current">
                                                <i class="bi bi-file-earmark-check"></i>
                                                <span>Arquivo enviado:</span>
                                                <a href="{{ asset('storage/'.$documento->envio->arquivo_path) }}" target="_blank" rel="noopener">visualizar</a>
                                            </div>
                                        @endif

                                        @if(in_array($documento->status, ['recusado', 'corrigir'], true))
                                            <div class="nexo-document-warning">
                                                {{ $documento->observacoes ?: 'Este documento precisa ser substituído.' }}
                                            </div>
                                        @elseif($modoCorrecao && ! $documentoLiberado)
                                            <div class="nexo-document-muted">
                                                Este documento não precisa de alteração.
                                            </div>
                                        @endif

                                        <div
                                            class="nexo-file-upload"
                                            data-documento-id="{{ $documento->id }}"
                                            data-tipo-documento="{{ $documento->tipoDocumento?->nome }}"
                                            data-dispensado-por="{{ $documento->dispensado_por_documento_id }}"
                                        >
                                            <input
                                                id="documento-{{ $documento->id }}"
                                                name="documentos[{{ $documento->id }}]"
                                                type="file"
                                                class="nexo-file-input"
                                                accept=".pdf,.jpg,.jpeg,.png"
                                                data-ia-validation-url="{{ route('cliente.pre-cadastro.documentos.validar-ia', ['slug' => $slug, 'token' => $preCadastro->token, 'documento' => $documento]) }}"
                                                data-tipo-documento-esperado="{{ $documento->tipoDocumento?->nome }}"
                                                @disabled(! $documentoLiberado)
                                                @required($documentoObrigatorioInput)
                                            >

                                            <input type="hidden" name="ia_validacoes[{{ $documento->id }}]" data-ia-validation-id>

                                            <label
                                                for="documento-{{ $documento->id }}"
                                                class="nexo-file-label @if(! $documentoLiberado) is-disabled @endif"
                                            >
                                                <div class="nexo-file-icon">
                                                    <i class="bi bi-cloud-arrow-up"></i>
                                                </div>

                                                <div class="nexo-file-text">
                                                    <strong data-file-title>Selecionar documento</strong>
                                                    <span data-file-name>PDF, JPG ou PNG</span>
                                                </div>

                                                <div class="nexo-file-action">
                                                    Enviar
                                                </div>
                                            </label>

                                            <div class="nexo-ia-feedback" data-ia-feedback @if(! $documento->dispensado_por_ia) hidden @endif>
                                                @if($documento->dispensado_por_ia)
                                                    {{ $documento->motivo_dispensa ?: 'Envio separado de CPF não é necessário. O CPF já foi identificado no documento de identidade enviado.' }}
                                                @endif
                                            </div>

                                            @if($documento->validado_por_documento_compartilhado)
                                                <div class="nexo-ia-feedback">
                                                    {{ $documento->motivo_validacao ?: 'Carta de Permanência aprovada automaticamente. Beneficiário encontrado na carta de permanência familiar anexada ao titular.' }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="nexo-final-card">
                    <label class="form-label">Observação para o corretor</label>

                    <textarea name="observacao_cliente" class="form-control" rows="3" placeholder="Use este campo se precisar explicar algum documento.">{{ old('observacao_cliente') }}</textarea>

                    <div class="nexo-submit-row">
                        <p>
                            Após enviar, o formulário ficará bloqueado enquanto a equipe analisa as informações.
                        </p>

                        <button class="nexo-submit-btn">
                            {{ $modoCorrecao ? 'Reenviar pré-cadastro' : 'Enviar pré-cadastro' }}
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>
            </form>
        </section>
    </main>

    <style>
        .nexo-client-form-page {
            min-height: 100vh;
            padding: 16px 12px;
            background:
                radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                linear-gradient(135deg, #061C3F 0%, #0D2F57 28%, #F4F7FB 28%, #FFFFFF 100%);
        }

        .nexo-client-form-shell {
            width: min(1180px, 100%);
            margin: 0 auto;
        }

        .nexo-client-form-header {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 18px;
            padding: 24px 20px;
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.28), transparent 30%),
                linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            box-shadow: 0 24px 60px rgba(6, 28, 63, 0.18);
            color: #FFFFFF;
        }

        .nexo-client-logo {
            margin-bottom: 22px;
        }

        .nexo-client-logo img {
            width: 100%;
            max-width: 220px;
            height: auto;
            display: block;
        }

        .nexo-client-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            color: #DDEBFF;
            font-size: 0.76rem;
            font-weight: 900;
            margin-bottom: 14px;
        }

        .nexo-client-form-header h1 {
            color: #FFFFFF;
            font-size: 2rem;
            line-height: 1;
            font-weight: 950;
            letter-spacing: -0.055em;
            margin: 0 0 12px;
        }

        .nexo-client-form-header p {
            color: rgba(255, 255, 255, 0.78);
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .nexo-client-broker-card {
            width: 100%;
            padding: 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.13);
            backdrop-filter: blur(10px);
        }

        .nexo-client-broker-card span {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.8rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .nexo-client-broker-card strong {
            color: #FFFFFF;
            font-size: 1rem;
            font-weight: 950;
        }

        .nexo-correction-box {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px;
            border-radius: 22px;
            background: #FFF8EC;
            border: 1px solid #FFE2B8;
            color: #8A4B12;
            margin-bottom: 18px;
        }

        .nexo-correction-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: #FFF0D6;
            color: #D9822B;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .nexo-correction-box h2 {
            color: #7A3E0A;
            font-size: 1.05rem;
            font-weight: 950;
            margin: 0 0 6px;
        }

        .nexo-correction-box p {
            margin: 0;
            font-weight: 700;
        }

        .nexo-correction-box ul {
            margin: 10px 0 0;
            padding-left: 20px;
        }

        .nexo-client-summary-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }

        .nexo-client-summary-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.045);
        }

        .nexo-client-summary-card i {
            width: 44px;
            height: 44px;
            border-radius: 15px;
            background: #EAF3FF;
            color: #2F80ED;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .nexo-client-summary-card span {
            display: block;
            color: #64748B;
            font-size: 0.82rem;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .nexo-client-summary-card strong {
            color: #061C3F;
            font-size: 1.15rem;
            font-weight: 950;
        }

        .nexo-beneficiary-card,
        .nexo-final-card {
            padding: 20px;
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.05);
            margin-bottom: 18px;
        }

        .nexo-beneficiary-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
        }

        .nexo-beneficiary-title {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .nexo-beneficiary-avatar {
            width: 50px;
            height: 50px;
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

        .nexo-beneficiary-title h2 {
            color: #061C3F;
            font-size: 1.15rem;
            font-weight: 950;
            margin: 0 0 2px;
        }

        .nexo-beneficiary-title p {
            color: #64748B;
            margin: 0;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .nexo-locked-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            padding: 0 13px;
            border-radius: 999px;
            background: #F1F5F9;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 900;
        }

        .nexo-section-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 16px;
        }

        .nexo-section-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: #E8EEF6;
        }

        .nexo-section-divider span {
            color: #2F80ED;
            font-size: 0.78rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .form-label {
            color: #061C3F;
            font-weight: 850;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            min-height: 54px;
            border-radius: 15px;
            border: 1px solid #D8E2EF;
            color: #061C3F;
            padding: 12px 15px;
            font-size: 1rem;
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
            min-height: 54px;
            padding: 0 15px;
            border-radius: 15px;
            border: 1px solid #D8E2EF;
            background: #FFFFFF;
            color: #061C3F;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 1rem;
            font-weight: 650;
            text-align: left;
            transition: 0.2s ease;
        }

        .nexo-bootstrap-select-toggle:hover,
        .nexo-bootstrap-select-toggle.show,
        .nexo-bootstrap-select-toggle:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-bootstrap-select-toggle:disabled {
            cursor: not-allowed;
            background: #F8FBFF;
            opacity: 0.75;
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


        .nexo-date-field {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 48px;
            gap: 10px;
        }

        .nexo-date-field > .bi-calendar2-check {
            position: absolute;
            top: 50%;
            left: 16px;
            color: #2F80ED;
            z-index: 2;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .nexo-date-display {
            padding-left: 44px !important;
            cursor: text;
            background: #FFFFFF !important;
        }

        .nexo-date-hidden {
            display: none;
        }

        .nexo-date-button {
            width: 48px;
            min-height: 54px;
            border: 1px solid #D8E2EF;
            border-radius: 15px;
            background: #F8FBFF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-date-button:hover,
        .nexo-date-button:focus {
            background: #EAF3FF;
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-date-button:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .nexo-calendar {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            z-index: 3000;
            width: min(350px, 92vw);
            padding: 16px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 24px 60px rgba(6, 28, 63, 0.18);
            display: none;
        }

        .nexo-calendar.is-open {
            display: block;
        }

        .nexo-calendar-header {
            display: grid;
            grid-template-columns: 38px 1fr 38px;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .nexo-calendar-title {
            display: grid;
            grid-template-columns: 1fr 86px;
            gap: 8px;
        }

        .nexo-calendar-title select {
            min-height: 38px;
            border-radius: 12px;
            border: 1px solid #D8E2EF;
            background: #F8FBFF;
            color: #061C3F;
            font-size: 0.82rem;
            font-weight: 850;
            padding: 0 10px;
            outline: none;
        }

        .nexo-calendar-title select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 3px rgba(47, 128, 237, 0.12);
        }

        .nexo-calendar-nav-button {
            width: 38px;
            height: 38px;
            border: 1px solid #D8E2EF;
            border-radius: 13px;
            background: #F8FBFF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-calendar-nav-button:hover {
            background: #EAF3FF;
            border-color: #2F80ED;
        }

        .nexo-calendar-weekdays,
        .nexo-calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }

        .nexo-calendar-weekdays {
            margin-bottom: 8px;
        }

        .nexo-calendar-weekdays span {
            color: #64748B;
            font-size: 0.72rem;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .nexo-calendar-day {
            height: 36px;
            border: 0;
            border-radius: 12px;
            background: #F8FBFF;
            color: #061C3F;
            font-size: 0.86rem;
            font-weight: 850;
            transition: 0.2s ease;
        }

        .nexo-calendar-day:hover {
            background: #EAF3FF;
            color: #2F80ED;
        }

        .nexo-calendar-day.is-selected {
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            box-shadow: 0 10px 22px rgba(47, 128, 237, 0.22);
        }

        .nexo-calendar-day.is-today {
            outline: 2px solid rgba(47, 128, 237, 0.18);
        }

        .nexo-calendar-day.is-disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .nexo-calendar-empty {
            height: 36px;
        }


        .nexo-gestante-column {
            display: flex;
            align-items: flex-end;
        }

        .nexo-check-field {
            width: 100%;
            min-height: 54px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 14px;
            border-radius: 15px;
            background: #F8FBFF;
            border: 1px solid #DCEBFF;
            color: #061C3F;
            font-weight: 850;
            margin-bottom: 0;
        }

        .nexo-check-field .form-check-input {
            margin-top: 0;
            flex-shrink: 0;
        }

        .nexo-upload-box {
            height: 100%;
            padding: 16px;
            border-radius: 20px;
            background: #F8FBFF;
            border: 1px solid #E3ECF8;
        }

        .nexo-upload-box.is-locked {
            opacity: 0.72;
        }

        .nexo-upload-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 12px;
        }

        .nexo-document-status {
            min-height: 30px;
            padding: 0 11px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            font-size: 0.76rem;
            font-weight: 900;
            white-space: nowrap;
        }

        .nexo-file-current,
        .nexo-document-muted,
        .nexo-document-warning {
            font-size: 0.85rem;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .nexo-file-current {
            color: #64748B;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .nexo-file-current a {
            color: #2F80ED;
            font-weight: 900;
            text-decoration: none;
        }

        .nexo-document-muted {
            color: #64748B;
        }

        .nexo-document-warning {
            color: #E14D4D;
        }

        .nexo-file-upload {
            position: relative;
        }

        .nexo-file-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            pointer-events: none;
        }

        .nexo-file-label {
            width: 100%;
            min-height: 120px;
            border: 1.5px dashed #C9D8EA;
            border-radius: 18px;
            background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFF 100%);
            padding: 18px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .nexo-file-label:hover {
            border-color: #2F80ED;
            background: #F4F9FF;
            transform: translateY(-1px);
        }

        .nexo-file-label.is-disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .nexo-file-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.18rem;
            flex-shrink: 0;
            box-shadow: 0 10px 22px rgba(47, 128, 237, 0.20);
        }

        .nexo-file-text {
            width: 100%;
            min-width: 0;
        }

        .nexo-file-text strong {
            display: block;
            color: #061C3F;
            font-size: 0.96rem;
            font-weight: 900;
            margin-bottom: 3px;
        }

        .nexo-file-text span {
            display: block;
            color: #64748B;
            font-size: 0.84rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nexo-file-action {
            width: 100%;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 13px;
            background: #EAF3FF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.86rem;
            font-weight: 900;
            white-space: nowrap;
            transition: 0.2s ease;
        }

        .nexo-file-label:hover .nexo-file-action {
            background: #2F80ED;
            color: #FFFFFF;
        }

        .nexo-ia-feedback {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 14px;
            font-size: 0.86rem;
            font-weight: 750;
            line-height: 1.35;
        }

        .nexo-ia-feedback.is-loading,
        .nexo-ia-feedback.is-warning {
            background: #FFF5E8;
            color: #A45A13;
        }

        .nexo-ia-feedback.is-success {
            background: #EAFBF1;
            color: #16794E;
        }

        .nexo-ia-feedback.is-danger {
            background: #FFECEC;
            color: #B42318;
        }

        .nexo-final-card textarea {
            min-height: 120px;
            resize: vertical;
        }

        .nexo-submit-row {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 16px;
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid #E8EEF6;
        }

        .nexo-submit-row p {
            color: #64748B;
            margin: 0;
            font-weight: 700;
            font-size: 0.92rem;
            line-height: 1.45;
        }

        .nexo-submit-btn {
            width: 100%;
            min-height: 56px;
            padding: 0 22px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 950;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.24);
            transition: 0.2s ease;
            white-space: nowrap;
        }

        .nexo-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(47, 128, 237, 0.30);
        }

        @media (min-width: 768px) {
            .nexo-client-form-page {
                padding: 32px 18px;
                background:
                    radial-gradient(circle at top left, rgba(47, 128, 237, 0.16), transparent 30%),
                    linear-gradient(135deg, #061C3F 0%, #0D2F57 34%, #F4F7FB 34%, #FFFFFF 100%);
            }

            .nexo-client-form-header {
                grid-template-columns: 1fr auto;
                align-items: end;
                gap: 24px;
                margin-bottom: 24px;
                padding: 32px;
                border-radius: 30px;
            }

            .nexo-client-logo img {
                max-width: 250px;
            }

            .nexo-client-form-header h1 {
                font-size: clamp(2.2rem, 4vw, 3.7rem);
            }

            .nexo-client-form-header p {
                max-width: 760px;
                font-size: 1rem;
            }

            .nexo-client-broker-card {
                width: auto;
                min-width: 260px;
                padding: 20px;
                border-radius: 22px;
            }

            .nexo-correction-box {
                flex-direction: row;
                padding: 22px;
                border-radius: 24px;
                margin-bottom: 24px;
            }

            .nexo-client-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
                margin-bottom: 24px;
            }

            .nexo-client-summary-card {
                display: block;
                padding: 22px;
                border-radius: 24px;
            }

            .nexo-client-summary-card i {
                width: 48px;
                height: 48px;
                border-radius: 16px;
                font-size: 1.2rem;
                margin-bottom: 14px;
            }

            .nexo-client-summary-card span {
                font-size: 0.86rem;
                margin-bottom: 4px;
            }

            .nexo-client-summary-card strong {
                font-size: 1.35rem;
            }

            .nexo-beneficiary-card,
            .nexo-final-card {
                padding: 28px;
                border-radius: 28px;
                margin-bottom: 24px;
            }

            .nexo-beneficiary-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                margin-bottom: 24px;
            }

            .nexo-beneficiary-title h2 {
                font-size: 1.25rem;
            }

            .nexo-upload-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
                gap: 12px;
            }

            .nexo-file-label {
                min-height: 84px;
                padding: 16px 18px;
                flex-direction: row;
                align-items: center;
                gap: 16px;
            }

            .nexo-file-icon {
                width: 52px;
                height: 52px;
                font-size: 1.25rem;
            }

            .nexo-file-text {
                flex: 1;
            }

            .nexo-file-action {
                width: auto;
                min-height: 40px;
                font-size: 0.84rem;
            }

            .nexo-submit-row {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
            }

            .nexo-submit-btn {
                width: auto;
            }
        }


        @media (max-width: 767px) {
    
        .nexo-date-field {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 48px;
            gap: 10px;
        }

        .nexo-date-field > .bi-calendar2-check {
            position: absolute;
            top: 50%;
            left: 16px;
            color: #2F80ED;
            z-index: 2;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .nexo-date-display {
            padding-left: 44px !important;
            cursor: text;
            background: #FFFFFF !important;
        }

        .nexo-date-hidden {
            display: none;
        }

        .nexo-date-button {
            width: 48px;
            min-height: 54px;
            border: 1px solid #D8E2EF;
            border-radius: 15px;
            background: #F8FBFF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-date-button:hover,
        .nexo-date-button:focus {
            background: #EAF3FF;
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
        }

        .nexo-date-button:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .nexo-calendar {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            z-index: 3000;
            width: min(350px, 92vw);
            padding: 16px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 24px 60px rgba(6, 28, 63, 0.18);
            display: none;
        }

        .nexo-calendar.is-open {
            display: block;
        }

        .nexo-calendar-header {
            display: grid;
            grid-template-columns: 38px 1fr 38px;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }

        .nexo-calendar-title {
            display: grid;
            grid-template-columns: 1fr 86px;
            gap: 8px;
        }

        .nexo-calendar-title select {
            min-height: 38px;
            border-radius: 12px;
            border: 1px solid #D8E2EF;
            background: #F8FBFF;
            color: #061C3F;
            font-size: 0.82rem;
            font-weight: 850;
            padding: 0 10px;
            outline: none;
        }

        .nexo-calendar-title select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 3px rgba(47, 128, 237, 0.12);
        }

        .nexo-calendar-nav-button {
            width: 38px;
            height: 38px;
            border: 1px solid #D8E2EF;
            border-radius: 13px;
            background: #F8FBFF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-calendar-nav-button:hover {
            background: #EAF3FF;
            border-color: #2F80ED;
        }

        .nexo-calendar-weekdays,
        .nexo-calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }

        .nexo-calendar-weekdays {
            margin-bottom: 8px;
        }

        .nexo-calendar-weekdays span {
            color: #64748B;
            font-size: 0.72rem;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .nexo-calendar-day {
            height: 36px;
            border: 0;
            border-radius: 12px;
            background: #F8FBFF;
            color: #061C3F;
            font-size: 0.86rem;
            font-weight: 850;
            transition: 0.2s ease;
        }

        .nexo-calendar-day:hover {
            background: #EAF3FF;
            color: #2F80ED;
        }

        .nexo-calendar-day.is-selected {
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            box-shadow: 0 10px 22px rgba(47, 128, 237, 0.22);
        }

        .nexo-calendar-day.is-today {
            outline: 2px solid rgba(47, 128, 237, 0.18);
        }

        .nexo-calendar-day.is-disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .nexo-calendar-empty {
            height: 36px;
        }


        .nexo-gestante-column {
                align-items: stretch;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toISOString().slice(0, 10);

            const selectIcons = {
                masculino: 'bi-gender-male',
                feminino: 'bi-gender-female',
                outro: 'bi-gender-ambiguous',
                conjuge: 'bi-heart',
                companheiro: 'bi-heart',
                filho: 'bi-person',
                enteado: 'bi-person',
                pai: 'bi-person',
                mae: 'bi-person',
                selecione: 'bi-check2-circle',
                outro_parentesco: 'bi-three-dots',
            };

            const getOptionIcon = (value, label) => {
                if (selectIcons[value]) {
                    return selectIcons[value];
                }

                if (label.toLowerCase().includes('beneficiário')) {
                    return 'bi-person-badge';
                }

                return 'bi-check2-circle';
            };

            const syncBootstrapSelect = (select) => {
                const wrapper = select.nextElementSibling?.matches('[data-bootstrap-select-wrapper]')
                    ? select.nextElementSibling
                    : select.parentElement.querySelector('[data-bootstrap-select-wrapper]');

                if (!wrapper) {
                    return;
                }

                const label = wrapper.querySelector('[data-bootstrap-select-label]');
                const items = wrapper.querySelectorAll('[data-select-value]');
                const selected = select.options[select.selectedIndex];
                const selectedLabel = selected ? selected.textContent.trim() : 'Selecione';

                if (label) {
                    label.textContent = selectedLabel || 'Selecione';
                }

                items.forEach((item) => {
                    item.classList.toggle('active', item.dataset.selectValue === select.value);
                });
            };

            const initializeBootstrapSelect = (select) => {
                const wrapper = select.nextElementSibling?.matches('[data-bootstrap-select-wrapper]')
                    ? select.nextElementSibling
                    : select.parentElement.querySelector('[data-bootstrap-select-wrapper]');

                if (!wrapper || wrapper.dataset.initialized === 'true') {
                    syncBootstrapSelect(select);
                    return;
                }

                const menu = wrapper.querySelector('.nexo-bootstrap-select-menu');

                menu.innerHTML = '';

                Array.from(select.options).forEach((option) => {
                    const value = option.value;
                    const label = option.textContent.trim();
                    const icon = getOptionIcon(value || 'selecione', label);
                    const li = document.createElement('li');
                    const button = document.createElement('button');

                    button.type = 'button';
                    button.className = 'dropdown-item nexo-bootstrap-select-item';
                    button.dataset.selectValue = value;
                    button.innerHTML = `
                        <i class="bi ${icon}"></i>
                        <span>${label}</span>
                    `;

                    button.addEventListener('click', () => {
                        select.value = value;
                        syncBootstrapSelect(select);
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    li.appendChild(button);
                    menu.appendChild(li);
                });

                wrapper.dataset.initialized = 'true';
                syncBootstrapSelect(select);
            };

            document.querySelectorAll('[data-bootstrap-select]').forEach((select) => {
                initializeBootstrapSelect(select);
            });

            document.querySelectorAll('[data-cpf-mask]').forEach((input) => {
                const applyCpfMask = () => {
                    let value = input.value.replace(/\D/g, '').slice(0, 11);
                    if (value.length > 9) {
                        value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2}).*/, '$1.$2.$3-$4');
                    } else if (value.length > 6) {
                        value = value.replace(/^(\d{3})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
                    } else if (value.length > 3) {
                        value = value.replace(/^(\d{3})(\d{0,3}).*/, '$1.$2');
                    }
                    input.value = value;
                };

                input.addEventListener('input', applyCpfMask);
                applyCpfMask();
            });

            const months = [
                'Janeiro',
                'Fevereiro',
                'Março',
                'Abril',
                'Maio',
                'Junho',
                'Julho',
                'Agosto',
                'Setembro',
                'Outubro',
                'Novembro',
                'Dezembro',
            ];

            const weekdays = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];
            const pad = (value) => String(value).padStart(2, '0');

            const formatDate = (value) => {
                const onlyNumbers = value.replace(/\D/g, '').slice(0, 8);
                const day = onlyNumbers.slice(0, 2);
                const month = onlyNumbers.slice(2, 4);
                const year = onlyNumbers.slice(4, 8);

                if (onlyNumbers.length > 4) {
                    return `${day}/${month}/${year}`;
                }

                if (onlyNumbers.length > 2) {
                    return `${day}/${month}`;
                }

                return day;
            };

            const toDisplayDate = (value) => {
                if (! value) {
                    return '';
                }

                const [year, month, day] = value.split('-');

                if (! year || ! month || ! day) {
                    return '';
                }

                return `${day}/${month}/${year}`;
            };

            const toHiddenDate = (value) => {
                const parts = value.split('/');

                if (parts.length !== 3) {
                    return '';
                }

                const [day, month, year] = parts;

                if (day.length !== 2 || month.length !== 2 || year.length !== 4) {
                    return '';
                }

                const date = new Date(Number(year), Number(month) - 1, Number(day));

                if (
                    date.getFullYear() !== Number(year) ||
                    date.getMonth() !== Number(month) - 1 ||
                    date.getDate() !== Number(day)
                ) {
                    return '';
                }

                return `${year}-${month}-${day}`;
            };

            const parseDate = (value) => {
                if (! value) {
                    return null;
                }

                const [year, month, day] = value.split('-').map(Number);

                if (! year || ! month || ! day) {
                    return null;
                }

                return new Date(year, month - 1, day);
            };

            const sameDay = (first, second) => {
                return first &&
                    second &&
                    first.getFullYear() === second.getFullYear() &&
                    first.getMonth() === second.getMonth() &&
                    first.getDate() === second.getDate();
            };

            const setDateValue = (field, date) => {
                const displayInput = field.querySelector('.nexo-date-display');
                const hiddenInput = field.querySelector('.nexo-date-hidden');

                hiddenInput.value = `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
                displayInput.value = `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()}`;
            };

            const closeCalendars = (except = null) => {
                document.querySelectorAll('.nexo-calendar.is-open').forEach((calendar) => {
                    if (calendar !== except) {
                        calendar.classList.remove('is-open');
                    }
                });
            };

            const openCalendar = (field) => {
                const calendar = field.querySelector('.nexo-calendar');

                closeCalendars(calendar);
                renderCalendar(field, field._nexoCalendarDate);
                calendar.classList.add('is-open');
            };

            const renderCalendar = (field, referenceDate) => {
                const calendar = field.querySelector('.nexo-calendar');
                const hiddenInput = field.querySelector('.nexo-date-hidden');
                const maxDate = parseDate(field.dataset.maxDate || '');
                const selectedDate = parseDate(hiddenInput.value);
                const currentToday = new Date();
                const year = referenceDate.getFullYear();
                const month = referenceDate.getMonth();
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);

                calendar.innerHTML = '';

                const header = document.createElement('div');
                header.className = 'nexo-calendar-header';

                const previous = document.createElement('button');
                previous.type = 'button';
                previous.className = 'nexo-calendar-nav-button';
                previous.innerHTML = '<i class="bi bi-chevron-left"></i>';

                const next = document.createElement('button');
                next.type = 'button';
                next.className = 'nexo-calendar-nav-button';
                next.innerHTML = '<i class="bi bi-chevron-right"></i>';

                const title = document.createElement('div');
                title.className = 'nexo-calendar-title';

                const monthSelect = document.createElement('select');
                const yearSelect = document.createElement('select');

                months.forEach((monthName, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = monthName;
                    option.selected = index === month;
                    monthSelect.appendChild(option);
                });

                const baseYear = maxDate ? maxDate.getFullYear() : currentToday.getFullYear();

                for (let optionYear = baseYear - 120; optionYear <= baseYear; optionYear++) {
                    const option = document.createElement('option');
                    option.value = optionYear;
                    option.textContent = optionYear;
                    option.selected = optionYear === year;
                    yearSelect.appendChild(option);
                }

                title.append(monthSelect, yearSelect);
                header.append(previous, title, next);

                const weekdaysWrapper = document.createElement('div');
                weekdaysWrapper.className = 'nexo-calendar-weekdays';

                weekdays.forEach((weekday) => {
                    const item = document.createElement('span');
                    item.textContent = weekday;
                    weekdaysWrapper.appendChild(item);
                });

                const daysWrapper = document.createElement('div');
                daysWrapper.className = 'nexo-calendar-days';

                for (let index = 0; index < firstDay.getDay(); index++) {
                    const empty = document.createElement('div');
                    empty.className = 'nexo-calendar-empty';
                    daysWrapper.appendChild(empty);
                }

                for (let day = 1; day <= lastDay.getDate(); day++) {
                    const date = new Date(year, month, day);
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'nexo-calendar-day';
                    button.textContent = day;

                    if (sameDay(date, currentToday)) {
                        button.classList.add('is-today');
                    }

                    if (sameDay(date, selectedDate)) {
                        button.classList.add('is-selected');
                    }

                    if (maxDate && date > new Date(maxDate.getFullYear(), maxDate.getMonth(), maxDate.getDate())) {
                        button.classList.add('is-disabled');
                        button.disabled = true;
                    }

                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();

                        setDateValue(field, date);
                        calendar.classList.remove('is-open');
                    });

                    daysWrapper.appendChild(button);
                }

                previous.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    field._nexoCalendarDate = new Date(year, month - 1, 1);
                    renderCalendar(field, field._nexoCalendarDate);
                    calendar.classList.add('is-open');
                });

                next.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    field._nexoCalendarDate = new Date(year, month + 1, 1);
                    renderCalendar(field, field._nexoCalendarDate);
                    calendar.classList.add('is-open');
                });

                monthSelect.addEventListener('click', (event) => event.stopPropagation());
                yearSelect.addEventListener('click', (event) => event.stopPropagation());

                monthSelect.addEventListener('change', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    field._nexoCalendarDate = new Date(Number(yearSelect.value), Number(monthSelect.value), 1);
                    renderCalendar(field, field._nexoCalendarDate);
                    calendar.classList.add('is-open');
                });

                yearSelect.addEventListener('change', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    field._nexoCalendarDate = new Date(Number(yearSelect.value), Number(monthSelect.value), 1);
                    renderCalendar(field, field._nexoCalendarDate);
                    calendar.classList.add('is-open');
                });

                calendar.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

                calendar.append(header, weekdaysWrapper, daysWrapper);
            };

            document.querySelectorAll('.nexo-date-field').forEach((field) => {
                const displayInput = field.querySelector('.nexo-date-display');
                const hiddenInput = field.querySelector('.nexo-date-hidden');
                const button = field.querySelector('.nexo-date-button');
                const calendar = field.querySelector('.nexo-calendar');

                if (! displayInput || ! hiddenInput || ! button || ! calendar) {
                    return;
                }

                if (hiddenInput.value && ! displayInput.value) {
                    displayInput.value = toDisplayDate(hiddenInput.value);
                }

                field._nexoCalendarDate = parseDate(hiddenInput.value) || parseDate(field.dataset.maxDate || '') || new Date();

                field.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

                displayInput.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (! displayInput.disabled) {
                        openCalendar(field);
                    }
                });

                displayInput.addEventListener('focus', () => {
                    if (! displayInput.disabled) {
                        openCalendar(field);
                    }
                });

                displayInput.addEventListener('input', () => {
                    displayInput.value = formatDate(displayInput.value);
                    hiddenInput.value = toHiddenDate(displayInput.value);

                    const selectedDate = parseDate(hiddenInput.value);

                    if (selectedDate) {
                        field._nexoCalendarDate = selectedDate;
                        renderCalendar(field, field._nexoCalendarDate);
                        calendar.classList.add('is-open');
                    }
                });

                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (! button.disabled) {
                        openCalendar(field);
                    }
                });

                renderCalendar(field, field._nexoCalendarDate);
            });

            document.addEventListener('click', () => {
                closeCalendars();
            });

            document.querySelectorAll('[data-public-beneficiario]').forEach((card) => {
                const sexo = card.querySelector('[data-public-sexo]');
                const wrap = card.querySelector('[data-public-gestante-wrap]');
                const gestante = card.querySelector('[data-public-gestante]');
                const sync = () => {
                    const feminino = sexo?.value === 'feminino';
                    wrap?.classList.toggle('d-none', !feminino);
                    if (!feminino && gestante) gestante.checked = false;
                };
                sexo?.addEventListener('change', sync);
                sync();
            });
            const beneficiaryDataFor = (input) => {
                const card = input.closest('[data-public-beneficiario]');
                const name = card?.querySelector('input[name$="[nome]"]')?.value?.trim() || '';
                const cpf = card?.querySelector('input[name$="[cpf]"]')?.value?.trim() || '';
                const birth = card?.querySelector('input[name$="[data_nascimento]"]')?.value?.trim() || '';
                const sexo = card?.querySelector('[data-public-sexo]')?.value || '';

                return {
                    card,
                    complete: Boolean(name && cpf && birth),
                    nome_beneficiario_atual: name,
                    cpf_beneficiario_atual: cpf,
                    data_nascimento_beneficiario_atual: birth,
                    sexo_beneficiario_atual: sexo,
                    tipo_beneficiario_atual: card?.dataset.beneficiarioTipo || '',
                    tipo_documento_esperado: input.dataset.tipoDocumentoEsperado || '',
                };
            };

            const beneficiarySnapshot = () => {
                const snapshot = {};

                document.querySelectorAll('[data-public-beneficiario]').forEach((card) => {
                    const id = card.dataset.beneficiarioId;

                    if (!id) {
                        return;
                    }

                    snapshot[id] = {
                        nome: card.querySelector('input[name$="[nome]"]')?.value?.trim() || '',
                        cpf: card.querySelector('input[name$="[cpf]"]')?.value?.trim() || '',
                        data_nascimento: card.querySelector('input[name$="[data_nascimento]"]')?.value?.trim() || '',
                        sexo: card.querySelector('[data-public-sexo]')?.value || '',
                        tipo: card.dataset.beneficiarioTipo || '',
                    };
                });

                return snapshot;
            };

            const appendBeneficiaryData = (formData, data) => {
                Object.entries(data).forEach(([key, value]) => {
                    if (key !== 'card' && key !== 'complete') {
                        formData.append(key, value || '');
                    }
                });
            };

            const appendBeneficiarySnapshot = (formData) => {
                Object.entries(beneficiarySnapshot()).forEach(([id, data]) => {
                    Object.entries(data).forEach(([key, value]) => {
                        formData.append(`vidas_atuais[${id}][${key}]`, value || '');
                    });
                });
            };

            const normalizeDocumentType = (value) => (value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();

            const shouldUseCompleteValidation = (data) => {
                if (data.complete) {
                    return true;
                }

                return normalizeDocumentType(data.tipo_documento_esperado) === 'carta de permanencia'
                    && Boolean(data.nome_beneficiario_atual && data.cpf_beneficiario_atual);
            };

            const setCpfDispensaState = (documentoId, motivo, dispensado, identityInput = null) => {
                const upload = document.querySelector(`.nexo-file-upload[data-documento-id="${documentoId}"]`);

                if (!upload) return;

                const fileInput = upload.querySelector('.nexo-file-input');
                const label = upload.querySelector('.nexo-file-label');
                const feedback = upload.querySelector('[data-ia-feedback]');
                const validationId = upload.querySelector('[data-ia-validation-id]');
                const status = upload.closest('.nexo-upload-box')?.querySelector('[data-document-status]');
                const fileName = upload.querySelector('[data-file-name]');
                const title = upload.querySelector('[data-file-title]');

                if (dispensado) {
                    if (fileInput) {
                        fileInput.value = '';
                        fileInput.disabled = true;
                        fileInput.required = false;
                    }
                    if (validationId) validationId.value = '';
                    if (label) label.classList.add('is-disabled');
                    if (status) status.textContent = 'Dispensado';
                    if (title) title.textContent = 'CPF dispensado';
                    if (fileName) fileName.textContent = 'Atendido pela identidade';
                    if (feedback) {
                        feedback.hidden = false;
                        feedback.className = 'nexo-ia-feedback is-success';
                        feedback.textContent = motivo;
                    }
                    if (identityInput) upload.dataset.dispensadoPor = identityInput.closest('.nexo-file-upload')?.dataset.documentoId || '';
                    return;
                }

                if (fileInput) {
                    fileInput.disabled = false;
                }
                if (label) label.classList.remove('is-disabled');
                if (status) status.textContent = 'Pendente';
                if (title) title.textContent = 'Selecionar documento';
                if (fileName) fileName.textContent = 'PDF, JPG ou PNG';
                if (feedback) {
                    feedback.hidden = true;
                    feedback.textContent = '';
                }
                upload.dataset.dispensadoPor = '';
            };

            const clearDispensasFromIdentity = (input) => {
                const identityDocumentId = input.closest('.nexo-file-upload')?.dataset.documentoId;
                if (!identityDocumentId) return;

                document.querySelectorAll(`.nexo-file-upload[data-dispensado-por="${identityDocumentId}"]`).forEach((upload) => {
                    setCpfDispensaState(upload.dataset.documentoId, '', false);
                });
            };

            const applyDispensas = (input, dispensas) => {
                (dispensas || []).forEach((dispensa) => {
                    if (dispensa.dispensado && dispensa.documento_obrigatorio_id) {
                        setCpfDispensaState(String(dispensa.documento_obrigatorio_id), dispensa.motivo, true, input);
                    }
                });
            };

            const setCartaCompartilhadaState = (validacao) => {
                const upload = document.querySelector(`.nexo-file-upload[data-documento-id="${validacao.documento_obrigatorio_id}"]`);

                if (!upload) return;

                const fileInput = upload.querySelector('.nexo-file-input');
                const label = upload.querySelector('.nexo-file-label');
                const feedback = upload.querySelector('[data-ia-feedback]');
                const validationId = upload.querySelector('[data-ia-validation-id]');
                const status = upload.closest('.nexo-upload-box')?.querySelector('[data-document-status]');
                const fileName = upload.querySelector('[data-file-name]');
                const title = upload.querySelector('[data-file-title]');

                if (fileInput) {
                    fileInput.value = '';
                    fileInput.disabled = true;
                    fileInput.required = false;
                }

                if (validationId) validationId.value = '';
                if (label) label.classList.add('is-disabled');
                if (status) status.textContent = 'Aprovado pela IA';
                if (title) title.textContent = 'Carta aprovada pela IA';
                if (fileName) fileName.textContent = 'Atendido por carta familiar';

                if (feedback) {
                    feedback.hidden = false;
                    feedback.className = 'nexo-ia-feedback is-success';
                    feedback.textContent = validacao.motivo || 'Carta de Permanência aprovada automaticamente. Beneficiário encontrado na carta de permanência familiar anexada ao titular.';
                }
            };

            const applyValidacoesCompartilhadas = (validacoes) => {
                (validacoes || []).forEach(setCartaCompartilhadaState);
            };

            const validateDocumentInput = async (input, phase, file = null) => {
                    const label = input.closest('.nexo-file-upload')?.querySelector('.nexo-file-label');
                    const title = label?.querySelector('[data-file-title]');
                    const fileName = label?.querySelector('[data-file-name]');
                    const upload = input.closest('.nexo-file-upload');
                    const feedback = upload?.querySelector('[data-ia-feedback]');
                    const validationId = upload?.querySelector('[data-ia-validation-id]');

                    if (phase !== 'titularidade' && (!input.files || !input.files.length)) {
                        if (title) title.textContent = 'Selecionar documento';
                        if (fileName) fileName.textContent = 'PDF, JPG ou PNG';
                        if (validationId) validationId.value = '';
                        if (feedback) feedback.hidden = true;
                        if (upload) upload.dataset.titularidadePendente = 'false';
                        return;
                    }

                    if (phase !== 'titularidade') {
                        if (title) title.textContent = 'Documento selecionado';
                        if (fileName) fileName.textContent = input.files[0].name;
                    }

                    if (!input.dataset.iaValidationUrl || !feedback) {
                        return;
                    }

                    feedback.hidden = false;
                    feedback.className = 'nexo-ia-feedback is-loading';
                    feedback.textContent = phase === 'titularidade' ? 'Conferindo dados pessoais...' : 'Analisando documento...';
                    if (validationId && phase !== 'titularidade') validationId.value = '';
                    if (phase !== 'titularidade' && input.dataset.tipoDocumentoEsperado === 'Documento de identidade com foto') {
                        clearDispensasFromIdentity(input);
                    }

                    const formData = new FormData();
                    formData.append('fase_validacao', phase);
                    if (file) formData.append('arquivo', file);
                    if (phase === 'titularidade' && validationId?.value) {
                        formData.append('ia_validacao_id', validationId.value);
                    }
                    appendBeneficiaryData(formData, beneficiaryDataFor(input));
                    appendBeneficiarySnapshot(formData);
                    formData.append('_token', document.querySelector('input[name="_token"]')?.value || '');

                    try {
                        const response = await fetch(input.dataset.iaValidationUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Falha temporaria na validacao.');
                        }

                        const data = await response.json();
                        if (validationId) validationId.value = data.id || '';
                        if (upload) upload.dataset.titularidadePendente = data.titularidade_pendente ? 'true' : 'false';
                        applyDispensas(input, data.dispensas_documentais);
                        applyValidacoesCompartilhadas(data.validacoes_compartilhadas);

                        if (data.status === 'reenviar') {
                            input.value = '';
                            if (validationId) validationId.value = '';
                            if (upload) upload.dataset.titularidadePendente = 'false';
                            if (title) title.textContent = 'Selecionar documento';
                            if (fileName) fileName.textContent = 'PDF, JPG ou PNG';
                            feedback.className = 'nexo-ia-feedback is-danger';
                            feedback.textContent = data.mensagem_cliente || 'Envie novamente com o documento inteiro e legivel.';
                            return;
                        }

                        feedback.className = data.status === 'aprovado_para_envio'
                            ? 'nexo-ia-feedback is-success'
                            : 'nexo-ia-feedback is-warning';
                        feedback.textContent = data.mensagem_cliente || 'Documento mantido para analise do corretor.';
                    } catch (error) {
                        feedback.className = 'nexo-ia-feedback is-warning';
                        feedback.textContent = 'Nao foi possivel validar automaticamente agora. Voce pode tentar novamente selecionando o arquivo outra vez.';
                    }
            };

            document.querySelectorAll('.nexo-file-input').forEach((input) => {
                input.addEventListener('change', async () => {
                    const data = beneficiaryDataFor(input);
                    await validateDocumentInput(input, shouldUseCompleteValidation(data) ? 'completa' : 'documental', input.files?.[0] || null);
                });
            });

            document.querySelectorAll('[data-public-beneficiario]').forEach((card) => {
                let timeout = null;
                const triggerTitularidade = () => {
                    window.clearTimeout(timeout);
                    timeout = window.setTimeout(() => {
                        card.querySelectorAll('.nexo-file-input').forEach((input) => {
                            const upload = input.closest('.nexo-file-upload');
                            const validationId = upload?.querySelector('[data-ia-validation-id]');
                            const data = beneficiaryDataFor(input);

                            if (upload?.dataset.titularidadePendente === 'true' && validationId?.value && data.complete) {
                                validateDocumentInput(input, 'titularidade');
                            }
                        });
                    }, 650);
                };

                card.querySelectorAll('input[name$="[nome]"], input[name$="[cpf]"], input[name$="[data_nascimento]"]').forEach((field) => {
                    field.addEventListener('input', triggerTitularidade);
                    field.addEventListener('change', triggerTitularidade);
                });
            });
        });
    </script>
</x-layouts.public>
