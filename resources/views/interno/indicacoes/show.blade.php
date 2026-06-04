<x-layouts.app title="Lead | Nexo Saúde">
    @php
        $preCadastro = $indicacao->preCadastro;
        $documentos = $preCadastro?->documentosObrigatorios ?? collect();
        $documentosObrigatorios = $documentos->where('obrigatorio', true);

        $documentosSemAlternativaOk = $documentosObrigatorios
            ->filter(fn ($documento) => empty($documento->grupo_alternativo))
            ->every(fn ($documento) => in_array($documento->status, ['aprovado', 'aprovado_ia', 'dispensado'], true));

        $gruposAlternativosOk = $documentosObrigatorios
            ->filter(fn ($documento) => ! empty($documento->grupo_alternativo))
            ->groupBy(fn ($documento) => $documento->vida_proposta_id.'|'.$documento->grupo_alternativo)
            ->every(fn ($grupo) => $grupo->contains(fn ($documento) => in_array($documento->status, ['aprovado', 'aprovado_ia', 'dispensado'], true)));

        $documentosOk = $documentos->isNotEmpty() && $documentosSemAlternativaOk && $gruposAlternativosOk;
        $podeProposta = in_array($indicacao->etapa, ['lead', 'propostas'], true);
        $podePreCadastro = $indicacao->etapa === 'propostas';
        $podeLembrete = in_array($indicacao->etapa, ['lead', 'propostas'], true);
        $slugCorretor = $indicacao->user?->corretorPerfil?->slug ?? \Illuminate\Support\Str::slug($indicacao->user?->name ?? 'corretor');
        $operadorasPorId = $operadoras->keyBy('id');
        $operadorasPreferidas = collect($indicacao->operadoras_preferidas ?? [])
            ->map(fn ($operadoraId) => $operadorasPorId->get((int) $operadoraId)?->nome)
            ->filter()
            ->values();
        $hospitaisPreferidos = collect($indicacao->hospitais_preferidos ?? [])->filter()->values();
        $temPreferencias = $indicacao->possui_preferencias
            || $operadorasPreferidas->isNotEmpty()
            || $hospitaisPreferidos->isNotEmpty()
            || filled($indicacao->faixa_valor_mensal);

        $statusLegivel = [
            'nova' => 'Nova',
            'proposta_enviada' => 'Proposta enviada',
            'aguardando_envio' => 'Aguardando envio',
            'documentacao_pendente' => 'Documentação pendente',
            'documentacao_em_analise' => 'Documentação em análise',
            'documentacao_aprovada' => 'Documentação aprovada',
            'correcao_solicitada' => 'Correção solicitada',
            'contrato_em_analise' => 'Contrato em análise',
            'pendencia_na_operadora' => 'Pendência na operadora',
            'aguardando_vigencia' => 'Aguardando vigência',
            'contrato_vigente' => 'Contrato vigente',
            'contrato_recusado' => 'Contrato recusado',
        ][$indicacao->status] ?? str_replace('_', ' ', $indicacao->status);
    @endphp

    <main class="nexo-main nexo-lead-show-page">
        <section class="nexo-lead-hero">
            <div>
                <span class="nexo-page-label">
                    <i class="bi bi-person-lines-fill"></i>
                    Lead
                </span>

                <h1>{{ $indicacao->nome_cliente }}</h1>

                <div class="nexo-lead-meta">
                    <span>
                        <i class="bi bi-telephone"></i>
                        {{ $indicacao->telefone }}
                    </span>

                    <span>
                        <i class="bi bi-envelope"></i>
                        {{ $indicacao->email ?: 'E-mail não informado' }}
                    </span>

                    <span>
                        <i class="bi bi-geo-alt"></i>
                        {{ $indicacao->cidade }}/{{ $indicacao->estado }}
                    </span>
                </div>
            </div>

            <div class="nexo-lead-status-card">
                <span>Status atual</span>
                <strong>{{ $statusLegivel }}</strong>
                <small>{{ $indicacao->tipo_plano }} · {{ $indicacao->quantidade_vidas }} vida(s)</small>
            </div>
        </section>

        <section class="nexo-panel-card nexo-preferences-summary">
            <div class="nexo-section-header">
                <div>
                    <span class="nexo-section-kicker">
                        <i class="bi bi-sliders"></i>
                        Preferências
                    </span>

                    <h2>Preferências da lead</h2>

                    <p>
                        Informações recebidas no formulário público para orientar a proposta.
                    </p>
                </div>
            </div>

            @if($temPreferencias)
                <div class="nexo-preferences-grid">
                    <div class="nexo-preference-block">
                        <span>Operadoras preferidas</span>

                        <div class="nexo-preference-tags">
                            @forelse($operadorasPreferidas as $operadoraPreferida)
                                <strong>{{ $operadoraPreferida }}</strong>
                            @empty
                                <em>Não informadas</em>
                            @endforelse
                        </div>
                    </div>

                    <div class="nexo-preference-block">
                        <span>Hospitais preferidos</span>

                        <div class="nexo-preference-tags">
                            @forelse($hospitaisPreferidos as $hospitalPreferido)
                                <strong>{{ $hospitalPreferido }}</strong>
                            @empty
                                <em>Não informados</em>
                            @endforelse
                        </div>
                    </div>

                    <div class="nexo-preference-block">
                        <span>Faixa de valor mensal</span>
                        <strong>{{ $indicacao->faixa_valor_mensal ?: 'Não informada' }}</strong>
                    </div>
                </div>
            @else
                <div class="nexo-empty-state">
                    A lead não informou preferências no formulário público.
                </div>
            @endif
        </section>

        <div class="row g-4">
            <div class="col-lg-7">
                @if(filled($indicacao->observacoes))
                    <section class="nexo-panel-card">
                        <div class="nexo-section-header">
                            <div>
                                <span class="nexo-section-kicker">
                                    <i class="bi bi-chat-left-text"></i>
                                    Observação
                                </span>

                                <h2>Observações da lead</h2>

                                <p>{{ $indicacao->observacoes }}</p>
                            </div>
                        </div>
                    </section>
                @endif

                <section class="nexo-panel-card">
                    <div class="nexo-section-header">
                        <div>
                            <span class="nexo-section-kicker">
                                <i class="bi bi-file-earmark-pdf"></i>
                                Comercial
                            </span>

                            <h2>Propostas</h2>

                            <p>
                                Anexe uma ou mais propostas em PDF obtidas externamente e registre os dados comerciais.
                            </p>
                        </div>
                    </div>

                    @if($podeProposta)
                        <form method="post" action="{{ route('indicacoes.propostas.store', $indicacao) }}" enctype="multipart/form-data" class="row g-3 mb-4">
                            @csrf

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
                                <input class="form-control nexo-money-input" name="valor_mensal" type="text" inputmode="numeric" placeholder="R$ 0,00" value="{{ old('valor_mensal') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Quantidade de vidas</label>
                                <input type="hidden" value="{{ $indicacao->tipo_plano }}" data-plan-type>
                                <input class="form-control" name="quantidade_vidas" type="number" min="1" value="{{ old('quantidade_vidas', $indicacao->quantidade_vidas) }}" data-lives-count>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Validade</label>

                                <div class="nexo-date-field" data-min-date="">
                                    <i class="bi bi-calendar2-check"></i>
                                    <input class="form-control nexo-date-display" type="text" inputmode="numeric" placeholder="dd/mm/aaaa" value="{{ old('validade') ? \Carbon\Carbon::parse(old('validade'))->format('d/m/Y') : '' }}">
                                    <input class="nexo-date-hidden" name="validade" type="hidden" value="{{ old('validade') }}">
                                    <button class="nexo-date-button" type="button" aria-label="Abrir calendário">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                    <div class="nexo-calendar"></div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">PDF da proposta</label>
                                <x-file-input
                                    name="arquivos_pdf[]"
                                    accept="application/pdf"
                                    :required="true"
                                    :multiple="true"
                                    button="Selecionar proposta em PDF"
                                    placeholder="Selecione uma ou mais propostas em PDF"
                                />
                            </div>

                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes') }}</textarea>
                            </div>

                            <div class="col-12">
                                <button class="nexo-primary-btn">
                                    <i class="bi bi-upload"></i>
                                    {{ $indicacao->propostas->isNotEmpty() ? 'Anexar nova proposta' : 'Anexar proposta' }}
                                </button>
                            </div>
                        </form>
                    @endif

                    <div class="nexo-proposal-list">
                        @forelse($indicacao->propostas as $proposta)
                            <div class="nexo-proposal-item">
                                <div>
                                    <strong>{{ $proposta->titulo }}</strong>

                                    <p>
                                        {{ $proposta->operadora?->nome ?: 'Operadora não informada' }}

                                        @if($proposta->valor_mensal)
                                            · R$ {{ number_format((float) $proposta->valor_mensal, 2, ',', '.') }}
                                        @endif
                                        @if($proposta->quantidade_vidas)
                                            · {{ $proposta->quantidade_vidas }} vida(s)
                                        @endif

                                        @if($proposta->validade)
                                            · validade {{ $proposta->validade->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>

                                <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">
                                    <span>{{ $proposta->status }}</span>

                                    <a href="{{ $proposta->public_group_token ? route('publico.propostas.download', ['token' => $proposta->public_group_token, 'proposta' => $proposta]) : asset('storage/'.$proposta->arquivo_pdf_path) }}" target="_blank" rel="noopener">
                                        Visualizar
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="nexo-empty-state">
                                Nenhuma proposta anexada ainda.
                            </div>
                        @endforelse
                    </div>

                    @if($indicacao->propostas->isNotEmpty() && $podePreCadastro)
                        <form method="post" action="{{ route('indicacoes.aceitar', $indicacao) }}" class="mt-4">
                            @csrf

                            <button class="nexo-secondary-btn">
                                <i class="bi bi-link-45deg"></i>
                                Gerar link de pré-cadastro
                            </button>
                        </form>
                    @endif
                </section>

                @if($preCadastro)
                    <section class="nexo-panel-card">
                        <div class="nexo-section-header nexo-section-header-action">
                            <div>
                                <span class="nexo-section-kicker">
                                    <i class="bi bi-clipboard2-check"></i>
                                    Documentação
                                </span>

                                <h2>Pré-cadastro</h2>

                                <p>
                                    Link tokenizado para envio de dados e documentos pelo cliente.
                                </p>
                            </div>

                            <a class="nexo-secondary-btn" href="{{ route('cliente.pre-cadastro.show', ['slug' => $slugCorretor, 'token' => $preCadastro->token]) }}" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Abrir link público
                            </a>
                        </div>

                        @if(! $documentosOk && $indicacao->etapa === 'pre_cadastros')
                            <div class="nexo-warning-box">
                                Ainda existem documentos pendentes ou em correção.
                            </div>
                        @endif

                        @if($indicacao->etapa === 'pre_cadastros' && in_array($indicacao->status, ['documentacao_em_analise', 'correcao_solicitada', 'documentacao_pendente'], true))
                            <form method="post" action="{{ route('indicacoes.pre-cadastro.correcao', $indicacao) }}" class="nexo-correction-form">
                                @csrf

                                <label class="form-label">Motivos da correção</label>

                                <textarea class="form-control mb-3" name="motivos_correcao" rows="3" placeholder="Ex.: documento de identidade com foto ilegível; comprovante de residência vencido.">{{ old('motivos_correcao', $preCadastro->motivos_correcao) }}</textarea>

                                <button class="nexo-warning-btn">
                                    <i class="bi bi-pencil-square"></i>
                                    Solicitar correção
                                </button>
                            </form>
                        @endif

                        <div class="nexo-beneficiary-list">
                            @foreach($preCadastro->vidas as $vida)
                                <article class="nexo-beneficiary-review-card">
                                    <div class="nexo-beneficiary-review-header">
                                        <div class="nexo-beneficiary-avatar">
                                            {{ $vida->ordem }}
                                        </div>

                                        <div>
                                            <h3>
                                                {{ $vida->nome ?: 'Beneficiário '.$vida->ordem }}
                                            </h3>

                                            <p>
                                                {{ ucfirst(str_replace('_', ' ', $vida->tipo)) }}
                                            </p>
                                        </div>
                                    </div>

                                    @include('interno.indicacoes.partials.documentos-revisao', [
                                        'indicacao' => $indicacao,
                                        'documentos' => $documentos->where('vida_proposta_id', $vida->id),
                                    ])
                                </article>
                            @endforeach
                        </div>

                        @if($documentos->whereNull('vida_proposta_id')->isNotEmpty())
                            <article class="nexo-beneficiary-review-card">
                                <div class="nexo-beneficiary-review-header">
                                    <div class="nexo-beneficiary-avatar">
                                        <i class="bi bi-files"></i>
                                    </div>

                                    <div>
                                        <h3>Documentos da proposta</h3>
                                        <p>Arquivos gerais vinculados ao pré-cadastro.</p>
                                    </div>
                                </div>

                                @include('interno.indicacoes.partials.documentos-revisao', [
                                    'indicacao' => $indicacao,
                                    'documentos' => $documentos->whereNull('vida_proposta_id'),
                                ])
                            </article>
                        @endif

                        @if($indicacao->etapa === 'pre_cadastros' && $documentosOk)
                            <form method="post" action="{{ route('indicacoes.implantacao.iniciar', $indicacao) }}" class="mt-3">
                                @csrf

                                <button class="nexo-primary-btn">
                                    <i class="bi bi-check-circle"></i>
                                    Aprovar documentação
                                </button>
                            </form>
                        @endif

                        @if($indicacao->etapa === 'implantacoes')
                            <form method="post" action="{{ route('indicacoes.implantacao.status', $indicacao) }}" class="row g-3 mb-3 mt-4">
                                @csrf

                                <div class="col-md-6">
                                    <label class="form-label">Status da implantação</label>

                                    <select class="form-select" name="status">
                                        @foreach([
                                            'contrato_em_analise' => 'Contrato em análise',
                                            'pendencia_na_operadora' => 'Pendência na operadora',
                                            'aguardando_vigencia' => 'Aguardando vigência',
                                            'contrato_recusado' => 'Contrato recusado',
                                        ] as $valor => $label)
                                            <option value="{{ $valor }}" @selected($indicacao->status === $valor)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <button class="nexo-secondary-btn w-100">
                                        <i class="bi bi-arrow-repeat"></i>
                                        Atualizar status
                                    </button>
                                </div>
                            </form>

                            <button class="nexo-primary-btn" data-bs-toggle="modal" data-bs-target="#contratoVigenteModal">
                                <i class="bi bi-check2-circle"></i>
                                Contrato vigente
                            </button>
                        @endif
                    </section>
                @endif
            </div>

            <div class="col-lg-5">
                <section class="nexo-panel-card">
                    <div class="nexo-section-header">
                        <div>
                            <span class="nexo-section-kicker">
                                <i class="bi bi-bell"></i>
                                Acompanhamento
                            </span>

                            <h2>Lembretes</h2>

                            <p>
                                Programe retornos, cobranças de documentos e acompanhamentos deste registro.
                            </p>
                        </div>
                    </div>

                    @if($podeLembrete)
                        <form method="post" action="{{ route('indicacoes.lembretes.store', $indicacao) }}" class="row g-3 mb-4">
                            @csrf

                            <div class="col-12">
                                <label class="form-label">Data do lembrete</label>

                                <div class="nexo-date-field" data-min-date="{{ now()->toDateString() }}">
                                    <i class="bi bi-calendar2-check"></i>
                                    <input
                                        class="form-control nexo-date-display @error('data_ocorrencia') is-invalid @enderror"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="dd/mm/aaaa"
                                        value="{{ old('data_ocorrencia') ? \Carbon\Carbon::parse(old('data_ocorrencia'))->format('d/m/Y') : '' }}"
                                        required
                                    >
                                    <input
                                        class="nexo-date-hidden"
                                        name="data_ocorrencia"
                                        type="hidden"
                                        value="{{ old('data_ocorrencia') }}"
                                        required
                                    >
                                    <button class="nexo-date-button" type="button" aria-label="Abrir calendário">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                    <div class="nexo-calendar"></div>
                                </div>

                                @error('data_ocorrencia')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Informações do lembrete</label>

                                <textarea
                                    class="form-control @error('descricao') is-invalid @enderror"
                                    name="descricao"
                                    rows="4"
                                    maxlength="255"
                                    placeholder="Ex.: Ligar daqui 20 dias; carta de permanência sai daqui a 60 dias, retornar contato."
                                    required
                                >{{ old('descricao') }}</textarea>

                                @error('descricao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button class="nexo-primary-btn w-100">
                                    <i class="bi bi-plus-circle"></i>
                                    Criar lembrete
                                </button>
                            </div>
                        </form>
                    @endif

                    <div class="nexo-reminder-list">
                        @forelse($indicacao->tarefas->where('tipo', 'lembrete')->sortBy('vencimento') as $lembrete)
                            <div class="nexo-reminder-item">
                                <div>
                                    <strong>{{ $lembrete->titulo }}</strong>
                                    <span>Programado para {{ $lembrete->vencimento?->format('d/m/Y') }}</span>
                                </div>

                                <small>{{ ucfirst($lembrete->status) }}</small>
                            </div>
                        @empty
                            <div class="nexo-empty-state">
                                Nenhum lembrete criado para este registro.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="nexo-panel-card">
                    <div class="nexo-section-header">
                        <div>
                            <span class="nexo-section-kicker">
                                <i class="bi bi-clock-history"></i>
                                Histórico
                            </span>

                            <h2>Timeline</h2>

                            <p>
                                Registro das movimentações deste atendimento.
                            </p>
                        </div>
                    </div>

                    <div class="nexo-timeline-list">
                        @forelse($indicacao->timelineEventos->sortByDesc('created_at') as $evento)
                            <div class="nexo-timeline-item">
                                <strong>{{ $evento->titulo }}</strong>
                                <p>{{ $evento->descricao }}</p>
                                <small>{{ $evento->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                        @empty
                            <div class="nexo-empty-state">
                                Nenhum evento registrado.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </main>

    @if($indicacao->etapa === 'implantacoes')
        <div class="modal fade" id="contratoVigenteModal" tabindex="-1" aria-labelledby="contratoVigenteLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form method="post" action="{{ route('indicacoes.implantacao.aprovar', $indicacao) }}" class="modal-content nexo-contract-modal">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <span class="nexo-modal-label">Implantação</span>
                            <h2 class="modal-title h5" id="contratoVigenteLabel">Contrato vigente</h2>
                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Operadora</label>

                                <select class="form-select" name="operadora_id" required>
                                    <option value="">Selecione</option>

                                    @foreach($operadoras as $operadora)
                                        <option value="{{ $operadora->id }}">{{ $operadora->nome }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tipo de contrato</label>

                            <select class="form-select" name="tipo_contrato" data-plan-type required>
                                    <option value="individual">Individual</option>
                                    <option value="familiar">Familiar</option>
                                    <option value="pme">PME</option>
                                    <option value="empresarial">Empresarial</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Quantidade de vidas</label>
                                <input class="form-control" name="quantidade_vidas" type="number" min="1" value="{{ $indicacao->quantidade_vidas }}" data-lives-count required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Data de vigência</label>

                                <div class="nexo-date-field" data-min-date="">
                                    <i class="bi bi-calendar2-check"></i>
                                    <input class="form-control nexo-date-display" type="text" inputmode="numeric" placeholder="dd/mm/aaaa" required>
                                    <input class="nexo-date-hidden" name="data_vigencia" type="hidden" required>
                                    <button class="nexo-date-button" type="button" aria-label="Abrir calendário">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                    <div class="nexo-calendar"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Valor mensal</label>
                                <input class="form-control nexo-money-input" name="valor_mensal" type="text" inputmode="numeric" placeholder="R$ 0,00" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Renovação</label>

                                <div class="nexo-date-field" data-min-date="">
                                    <i class="bi bi-calendar2-check"></i>
                                    <input class="form-control nexo-date-display" type="text" inputmode="numeric" placeholder="dd/mm/aaaa" required>
                                    <input class="nexo-date-hidden" name="renovacao_em" type="hidden" required>
                                    <button class="nexo-date-button" type="button" aria-label="Abrir calendário">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                    <div class="nexo-calendar"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Reajuste</label>

                                <div class="nexo-date-field" data-min-date="">
                                    <i class="bi bi-calendar2-check"></i>
                                    <input class="form-control nexo-date-display" type="text" inputmode="numeric" placeholder="dd/mm/aaaa" required>
                                    <input class="nexo-date-hidden" name="reajuste_em" type="hidden" required>
                                    <button class="nexo-date-button" type="button" aria-label="Abrir calendário">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                    <div class="nexo-calendar"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Número do contrato</label>
                                <input class="form-control" name="numero_contrato">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea class="form-control" name="observacoes" rows="3"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="nexo-check-card">
                                    <input class="form-check-input" type="checkbox" name="enviar_email" value="1">
                                    <span>
                                        <strong>Enviar e-mail automático</strong>
                                        <small>Notificar o cliente sobre o contrato vigente.</small>
                                    </span>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="nexo-check-card">
                                    <input class="form-check-input" type="checkbox" name="enviar_sms" value="1">
                                    <span>
                                        <strong>Enviar SMS automático</strong>
                                        <small>Enviar confirmação resumida ao cliente.</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="nexo-secondary-btn" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button class="nexo-primary-btn">
                            <i class="bi bi-check-circle"></i>
                            Confirmar contrato vigente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <style>
        .nexo-lead-show-page {
            display: block;
        }

        .nexo-lead-hero {
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
        .nexo-section-kicker,
        .nexo-modal-label {
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

        .nexo-lead-hero h1 {
            color: #FFFFFF;
            font-size: clamp(1.75rem, 2.6vw, 2.85rem);
            line-height: 1.04;
            font-weight: 950;
            letter-spacing: -0.055em;
            margin: 0 0 12px;
        }

        .nexo-lead-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .nexo-lead-meta span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.09);
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.82rem;
            font-weight: 750;
        }

        .nexo-lead-status-card {
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.13);
            backdrop-filter: blur(10px);
        }

        .nexo-lead-status-card span,
        .nexo-lead-status-card small {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-weight: 800;
        }

        .nexo-lead-status-card span {
            font-size: 0.84rem;
        }

        .nexo-lead-status-card small {
            font-size: 0.82rem;
        }

        .nexo-lead-status-card strong {
            display: block;
            color: #FFFFFF;
            font-size: 1.16rem;
            font-weight: 950;
            margin: 3px 0;
        }

        .nexo-panel-card {
            padding: 24px;
            border-radius: 28px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.055);
            margin-bottom: 24px;
        }

        .nexo-preferences-summary {
            border-color: #D7E7FF;
            background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFF 100%);
        }

        .nexo-preferences-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .nexo-preference-block {
            display: grid;
            gap: 10px;
            min-height: 104px;
            padding: 16px;
            border: 1px solid #E4EBF5;
            border-radius: 18px;
            background: #FFFFFF;
        }

        .nexo-preference-block > span {
            color: #64748B;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .nexo-preference-block > strong {
            color: #061C3F;
            font-size: 1rem;
            font-weight: 950;
        }

        .nexo-preference-tags {
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 8px;
        }

        .nexo-preference-tags strong {
            min-height: 28px;
            padding: 5px 10px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #145FC5;
            font-size: 0.82rem;
            font-weight: 900;
        }

        .nexo-preference-tags em {
            color: #94A3B8;
            font-style: normal;
            font-weight: 800;
        }

        .nexo-section-header {
            margin-bottom: 22px;
        }

        .nexo-section-header-action {
            display: flex;
            flex-direction: column;
            gap: 16px;
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
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.14);
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
        }

        .nexo-date-hidden {
            display: none;
        }

        .nexo-date-button {
            width: 48px;
            height: 52px;
            border: 1px solid #D8E2EF;
            border-radius: 14px;
            background: #F8FBFF;
            color: #2F80ED;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .nexo-date-button:hover {
            background: #EAF3FF;
            border-color: #2F80ED;
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

        .nexo-file-upload {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 74px;
            padding: 16px;
            border-radius: 18px;
            background: #F8FBFF;
            border: 1px dashed #BFD7F8;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .nexo-file-upload:hover {
            background: #F1F7FF;
            border-color: #2F80ED;
        }

        .nexo-file-input {
            display: none;
        }

        .nexo-file-icon {
            width: 44px;
            height: 44px;
            border-radius: 15px;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: 0 12px 26px rgba(47, 128, 237, 0.20);
        }

        .nexo-file-content {
            display: grid;
            gap: 2px;
        }

        .nexo-file-title {
            color: #061C3F;
            font-weight: 950;
        }

        .nexo-file-name {
            color: #64748B;
            font-weight: 700;
        }

        .nexo-primary-btn,
        .nexo-secondary-btn,
        .nexo-warning-btn {
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

        .nexo-warning-btn {
            background: #FFF8EC;
            border-color: #FFE2B8;
            color: #C76A12;
        }

        .nexo-primary-btn:hover,
        .nexo-secondary-btn:hover,
        .nexo-warning-btn:hover {
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

        .nexo-proposal-list,
        .nexo-reminder-list,
        .nexo-timeline-list,
        .nexo-beneficiary-list {
            display: grid;
            gap: 14px;
        }

        .nexo-proposal-item,
        .nexo-reminder-item {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 16px;
            border-radius: 18px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-proposal-item strong,
        .nexo-reminder-item strong {
            display: block;
            color: #061C3F;
            font-weight: 950;
            margin-bottom: 4px;
        }

        .nexo-proposal-item p,
        .nexo-reminder-item span {
            color: #64748B;
            margin: 0;
            font-size: 0.9rem;
            font-weight: 650;
        }

        .nexo-proposal-item span,
        .nexo-reminder-item small {
            height: fit-content;
            padding: 6px 11px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.76rem;
            font-weight: 950;
            white-space: nowrap;
        }

        .nexo-empty-state {
            padding: 18px;
            border-radius: 18px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
            color: #64748B;
            font-weight: 750;
        }

        .nexo-warning-box,
        .nexo-correction-form {
            padding: 18px;
            border-radius: 20px;
            background: #FFF8EC;
            border: 1px solid #FFE2B8;
            color: #8A4B12;
            margin-bottom: 18px;
            font-weight: 750;
        }

        .nexo-beneficiary-review-card {
            padding: 20px;
            border-radius: 22px;
            background: #F8FBFF;
            border: 1px solid #E3ECF8;
        }

        .nexo-beneficiary-review-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
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

        .nexo-beneficiary-review-header h3 {
            color: #061C3F;
            font-size: 1.15rem;
            font-weight: 950;
            margin: 0 0 3px;
        }

        .nexo-beneficiary-review-header p {
            color: #64748B;
            margin: 0;
            font-weight: 750;
            text-transform: capitalize;
        }

        .nexo-timeline-item {
            position: relative;
            padding-left: 20px;
            border-left: 2px solid #2F80ED;
        }

        .nexo-timeline-item strong {
            display: block;
            color: #061C3F;
            font-weight: 950;
            margin-bottom: 4px;
        }

        .nexo-timeline-item p {
            color: #64748B;
            margin: 0 0 4px;
            font-size: 0.92rem;
        }

        .nexo-timeline-item small {
            color: #94A3B8;
            font-weight: 750;
        }

        .nexo-contract-modal {
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 28px 70px rgba(6, 28, 63, 0.18);
        }

        .nexo-contract-modal .modal-header,
        .nexo-contract-modal .modal-footer {
            border-color: #E8EEF6;
            background: #F8FBFF;
            padding: 22px 24px;
        }

        .nexo-contract-modal .modal-title {
            color: #061C3F;
            font-weight: 950;
        }

        .nexo-contract-modal .modal-body {
            padding: 24px;
        }

        .nexo-check-card {
            min-height: 72px;
            padding: 14px;
            border-radius: 16px;
            background: #F8FBFF;
            border: 1px solid #E3ECF8;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
        }

        .nexo-check-card strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .nexo-check-card small {
            display: block;
            color: #64748B;
            font-weight: 650;
        }

        @media (min-width: 768px) {
            .nexo-section-header-action {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
            }
        }

        @media (max-width: 991px) {
            .nexo-preferences-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 992px) {
            .nexo-lead-hero {
                grid-template-columns: 1fr 250px;
                align-items: center;
                padding: 24px 28px;
            }

            .nexo-lead-status-card {
                min-width: 250px;
            }
        }

        @media (max-width: 576px) {
            .nexo-lead-hero,
            .nexo-panel-card {
                padding: 20px;
                border-radius: 24px;
            }

            .nexo-lead-hero h1 {
                font-size: clamp(1.65rem, 8vw, 2.2rem);
            }

            .nexo-lead-meta {
                flex-direction: column;
            }

            .nexo-calendar {
                right: 0;
                left: auto;
            }

            .nexo-proposal-item,
            .nexo-reminder-item {
                flex-direction: column;
            }

            .nexo-primary-btn,
            .nexo-secondary-btn,
            .nexo-warning-btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const formatMoney = (value) => {
                const onlyNumbers = value.replace(/\D/g, '');
                const amount = Number(onlyNumbers || 0) / 100;

                return amount.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                });
            };

            document.querySelectorAll('.nexo-money-input').forEach((input) => {
                input.addEventListener('input', () => {
                    input.value = formatMoney(input.value);
                });

                input.form?.addEventListener('submit', () => {
                    input.value = input.value
                        .replace(/\s/g, '')
                        .replace('R$', '')
                        .replace(/\./g, '')
                        .replace(',', '.');
                });
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
                const minDate = parseDate(field.dataset.minDate || '');
                const selectedDate = parseDate(hiddenInput.value);
                const today = new Date();
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

                for (let optionYear = year - 8; optionYear <= year + 12; optionYear++) {
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

                    if (sameDay(date, today)) {
                        button.classList.add('is-today');
                    }

                    if (sameDay(date, selectedDate)) {
                        button.classList.add('is-selected');
                    }

                    if (minDate && date < new Date(minDate.getFullYear(), minDate.getMonth(), minDate.getDate())) {
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

                monthSelect.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

                yearSelect.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

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

                field._nexoCalendarDate = parseDate(hiddenInput.value) || new Date();

                field.addEventListener('click', (event) => {
                    event.stopPropagation();
                });

                displayInput.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    openCalendar(field);
                });

                displayInput.addEventListener('focus', () => {
                    openCalendar(field);
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

                    openCalendar(field);
                });

                renderCalendar(field, field._nexoCalendarDate);
            });

            document.addEventListener('click', () => {
                closeCalendars();
            });

            document.querySelectorAll('.nexo-file-input').forEach((input) => {
                input.addEventListener('change', () => {
                    const upload = input.closest('.nexo-file-upload');
                    const fileName = upload?.querySelector('.nexo-file-name');
                    const fileTitle = upload?.querySelector('.nexo-file-title');

                    if (! fileName || ! fileTitle) {
                        return;
                    }

                    if (input.files && input.files.length > 0) {
                        fileTitle.textContent = input.files.length === 1 ? 'Proposta selecionada' : 'Propostas selecionadas';
                        fileName.textContent = Array.from(input.files).map((file) => file.name).join(', ');
                        return;
                    }

                    fileTitle.textContent = 'Selecionar proposta em PDF';
                    fileName.textContent = 'Selecione uma ou mais propostas em PDF';
                });
            });
        });
    </script>
</x-layouts.app>
