<x-layouts.app title="Detalhes do cliente | Nexo Saúde">
    @php
        $contratoPrincipal = $cliente->contratos->sortByDesc('created_at')->first();
        $propostaPrincipal = $indicacao?->propostas?->sortByDesc('created_at')->first();
        $vidasContrato = $contratoPrincipal?->quantidade_vidas ?? ($cliente->dependentes->count() + 1);
        $valorMensal = $contratoPrincipal?->valor_mensal ?? $cliente->valor_mensal;

        $statusCliente = [
            'ativo' => 'Ativo',
            'em_relacionamento' => 'Em relacionamento',
            'cancelado' => 'Cancelado',
        ][$cliente->status] ?? ucfirst(str_replace('_', ' ', $cliente->status));

        $statusContrato = fn (?string $status) => [
            'vigente' => 'Vigente',
            'ativo' => 'Ativo',
            'cancelado' => 'Cancelado',
        ][$status ?? ''] ?? ucfirst(str_replace('_', ' ', $status ?? 'sem_status'));
    @endphp

    <main class="nexo-main">
        <div class="nexo-cliente-header mb-4">
            <div>
                <span class="nexo-page-label">
                    <i class="bi bi-person-lines-fill"></i>
                    Cliente
                </span>

                <h1>
                    {{ $cliente->nome }}
                </h1>

                <p>
                    Detalhes operacionais do cliente, contrato, plano, dependentes e histórico.
                </p>
            </div>

            <a class="nexo-secondary-btn" href="{{ route('paginas.simples', 'clientes') }}">
                <i class="bi bi-arrow-left"></i>
                Voltar para Clientes
            </a>
        </div>

        <div class="nexo-cliente-summary mb-4">
            <div class="nexo-cliente-summary-card">
                <span>Status</span>
                <strong>{{ $statusCliente }}</strong>
            </div>

            <div class="nexo-cliente-summary-card">
                <span>Operadora</span>
                <strong>{{ $contratoPrincipal?->operadora?->nome ?? $propostaPrincipal?->operadora?->nome ?? 'Não informada' }}</strong>
            </div>

            <div class="nexo-cliente-summary-card">
                <span>Vidas</span>
                <strong>{{ $vidasContrato }}</strong>
            </div>

            <div class="nexo-cliente-summary-card">
                <span>Mensalidade</span>
                <strong>{{ $valorMensal ? 'R$ '.number_format((float) $valorMensal, 2, ',', '.') : 'Sem valor' }}</strong>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <section class="nexo-cliente-panel mb-4">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Informações de contato</h2>
                            <p>Dados principais para relacionamento e acompanhamento comercial.</p>
                        </div>
                    </div>

                    <div class="nexo-info-grid">
                        <div class="nexo-info-item">
                            <span>Telefone</span>
                            <strong>{{ $cliente->telefone ?: 'Não informado' }}</strong>
                        </div>

                        <div class="nexo-info-item">
                            <span>E-mail</span>
                            <strong>{{ $cliente->email ?: 'Não informado' }}</strong>
                        </div>

                        <div class="nexo-info-item">
                            <span>Cidade/Estado</span>
                            <strong>{{ $indicacao?->cidade ? $indicacao->cidade.'/'.$indicacao->estado : 'Não informado' }}</strong>
                        </div>

                        <div class="nexo-info-item">
                            <span>Origem</span>
                            <strong>{{ $indicacao?->origem ? ucfirst(str_replace('_', ' ', $indicacao->origem)) : 'Não informada' }}</strong>
                        </div>
                    </div>
                </section>

                @if(($avaliacaoAtendimento ?? null) && $avaliacaoAtendimento->status !== 'respondida')
                    <section class="nexo-cliente-panel nexo-review-resend-panel mb-4">
                        <div class="nexo-section-header">
                            <div>
                                <h2>Avaliação de atendimento</h2>
                                <p>O cliente ainda não respondeu a avaliação pós-contrato. Reenvie o convite quando desejar.</p>
                            </div>
                        </div>

                        <div class="nexo-review-resend-card">
                            <div class="nexo-review-resend-icon">
                                <i class="bi bi-star-fill"></i>
                            </div>

                            <div>
                                <strong>Convite de avaliação pendente</strong>
                                <span>Use o WhatsApp para reenviar a mensagem pronta ou copie o link direto.</span>
                            </div>

                            <div class="nexo-review-resend-actions">
                                <a class="nexo-review-whatsapp-btn" href="{{ $linkWhatsappAvaliacaoAtendimento }}" target="_blank" rel="noopener">
                                    <i class="bi bi-whatsapp"></i>
                                    Enviar pelo WhatsApp
                                </a>

                                <button type="button" class="nexo-review-copy-btn" data-copy-review-link="{{ $linkAvaliacaoAtendimento }}">
                                    <i class="bi bi-copy"></i>
                                    Copiar link
                                </button>
                            </div>
                        </div>
                    </section>
                @elseif(($avaliacaoAtendimento ?? null) && $avaliacaoAtendimento->status === 'respondida')
                    <section class="nexo-cliente-panel nexo-review-resend-panel mb-4">
                        <div class="nexo-section-header">
                            <div>
                                <h2>Avaliação de atendimento</h2>
                                <p>Cliente já respondeu a avaliação pós-contrato.</p>
                            </div>
                        </div>

                        <div class="nexo-review-resend-card is-finished">
                            <div class="nexo-review-resend-icon">
                                <i class="bi bi-star-fill"></i>
                            </div>

                            <div>
                                <strong>{{ number_format($avaliacaoAtendimento->media, 1, ',', '.') }} de 5</strong>
                                <span>{{ $avaliacaoAtendimento->comentario ?: 'Avaliação registrada sem comentário adicional.' }}</span>
                            </div>
                        </div>
                    </section>
                @endif

                <section class="nexo-cliente-panel mb-4">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Plano de saúde e contratos</h2>
                            <p>Dados operacionais usados para renovação, reajuste e relacionamento.</p>
                        </div>
                    </div>

                    <div class="nexo-info-grid mb-4">
                        <div class="nexo-info-item">
                            <span>Tipo de plano solicitado</span>
                            <strong>{{ $indicacao?->tipo_plano ?: 'Não informado' }}</strong>
                        </div>

                        <div class="nexo-info-item">
                            <span>Início de vigência</span>
                            <strong>{{ $cliente->inicio_vigencia?->format('d/m/Y') ?: $contratoPrincipal?->iniciado_em?->format('d/m/Y') ?: 'Sem data' }}</strong>
                        </div>

                        <div class="nexo-info-item">
                            <span>Próxima renovação</span>
                            <strong>{{ $contratoPrincipal?->renovacao_em?->format('d/m/Y') ?: 'Sem data' }}</strong>
                        </div>

                        <div class="nexo-info-item">
                            <span>Próximo reajuste</span>
                            <strong>{{ $contratoPrincipal?->reajuste_em?->format('d/m/Y') ?: 'Sem data' }}</strong>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle nexo-cliente-table mb-0">
                            <thead>
                                <tr>
                                    <th>Contrato</th>
                                    <th>Operadora</th>
                                    <th>Tipo</th>
                                    <th>Vidas</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($cliente->contratos as $contrato)
                                    <tr>
                                        <td>
                                            <strong>{{ $contrato->numero_contrato ?: 'Sem número' }}</strong>
                                            <small>{{ $contrato->iniciado_em?->format('d/m/Y') ?: 'Sem vigência' }}</small>
                                        </td>
                                        <td>{{ $contrato->operadora?->nome ?: 'Não informada' }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $contrato->tipo_contrato)) }}</td>
                                        <td>{{ $contrato->quantidade_vidas }}</td>
                                        <td>{{ $contrato->valor_mensal ? 'R$ '.number_format((float) $contrato->valor_mensal, 2, ',', '.') : 'Sem valor' }}</td>
                                        <td>
                                            <span class="nexo-status-pill">
                                                {{ $statusContrato($contrato->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="nexo-empty-state">
                                                <i class="bi bi-file-earmark-medical"></i>
                                                <p>Nenhum contrato registrado para este cliente.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="nexo-cliente-panel mb-4">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Dependentes e vidas vinculadas</h2>
                            <p>Visão das vidas associadas ao contrato ativo.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle nexo-cliente-table mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Documento</th>
                                    <th>Nascimento</th>
                                    <th>Parentesco</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($cliente->dependentes as $dependente)
                                    <tr>
                                        <td>
                                            <strong>{{ $dependente->nome }}</strong>
                                            <small>{{ $dependente->sexo ?: 'Sexo não informado' }}{{ $dependente->gestante ? ' · Gestante' : '' }}</small>
                                        </td>
                                        <td>{{ $dependente->documento ?: 'Não informado' }}</td>
                                        <td>{{ $dependente->data_nascimento?->format('d/m/Y') ?: 'Não informado' }}</td>
                                        <td>{{ $dependente->parentesco ?: 'Não informado' }}</td>
                                        <td>
                                            <span class="nexo-status-pill">
                                                {{ ucfirst($dependente->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="nexo-empty-state">
                                                <i class="bi bi-people"></i>
                                                <p>Nenhum dependente cadastrado.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="nexo-cliente-panel">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Propostas vinculadas</h2>
                            <p>Histórico comercial que originou o cliente.</p>
                        </div>
                    </div>

                    <div class="nexo-propostas-list">
                        @forelse($indicacao?->propostas ?? collect() as $proposta)
                            <div class="nexo-proposta-item">
                                <div>
                                    <strong>{{ $proposta->titulo }}</strong>
                                    <span>
                                        {{ $proposta->operadora?->nome ?: 'Operadora não informada' }}
                                        @if($proposta->validade)
                                            · validade {{ $proposta->validade->format('d/m/Y') }}
                                        @endif
                                    </span>
                                </div>

                                <div class="nexo-proposta-actions">
                                    <span>{{ $proposta->valor_mensal ? 'R$ '.number_format((float) $proposta->valor_mensal, 2, ',', '.') : 'Sem valor' }}</span>
                                    <a href="{{ asset('storage/'.$proposta->arquivo_pdf_path) }}" target="_blank" rel="noopener">Ver PDF</a>
                                </div>
                            </div>
                        @empty
                            <div class="nexo-empty-state">
                                <i class="bi bi-file-earmark-text"></i>
                                <p>Nenhuma proposta vinculada.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="nexo-cliente-panel mb-4">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Relacionamento</h2>
                            <p>Tarefas e alertas ligados a este cliente.</p>
                        </div>
                    </div>

                    <div class="nexo-side-list">
                        <div class="nexo-side-block">
                            <h3>Tarefas</h3>
                            @forelse($tarefas as $tarefa)
                                <div class="nexo-side-item">
                                    <strong>{{ $tarefa->titulo }}</strong>
                                    <span>{{ ucfirst($tarefa->status) }}{{ $tarefa->vencimento ? ' · '.$tarefa->vencimento->format('d/m/Y') : '' }}</span>
                                </div>
                            @empty
                                <p>Nenhuma tarefa vinculada.</p>
                            @endforelse
                            {{ $tarefas->links('vendor.pagination.nexo') }}
                        </div>

                        <div class="nexo-side-block">
                            <h3>Alertas</h3>
                            @forelse($alertas as $alerta)
                                <div class="nexo-side-item">
                                    <strong>{{ $alerta->titulo }}</strong>
                                    <span>{{ $alerta->mensagem ?: ucfirst($alerta->tipo) }}</span>
                                </div>
                            @empty
                                <p>Nenhum alerta vinculado.</p>
                            @endforelse
                            {{ $alertas->links('vendor.pagination.nexo') }}
                        </div>
                    </div>
                </section>

                <section class="nexo-cliente-panel">
                    <div class="nexo-section-header">
                        <div>
                            <h2>Timeline</h2>
                            <p>Histórico operacional completo.</p>
                        </div>
                    </div>

                    <div class="nexo-timeline">
                        @forelse($indicacao?->timelineEventos?->sortByDesc('created_at') ?? collect() as $evento)
                            <div class="nexo-timeline-item">
                                <div class="nexo-timeline-dot"></div>

                                <div class="nexo-timeline-content">
                                    <strong>{{ $evento->titulo }}</strong>
                                    <p>{{ $evento->descricao }}</p>
                                    <span>{{ $evento->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="nexo-empty-state">
                                <i class="bi bi-clock-history"></i>
                                <p>Nenhum evento registrado.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </main>

    <style>
        .nexo-cliente-header {
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

        .nexo-cliente-header h1 {
            color: #061C3F;
            font-size: 2.35rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.05em;
            margin: 0 0 8px;
        }

        .nexo-cliente-header p,
        .nexo-section-header p {
            color: #64748B;
            margin: 0;
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

        .nexo-secondary-btn {
            background: #FFFFFF;
            border: 1px solid #D7E7FF;
            color: #2F80ED;
        }

        .nexo-secondary-btn:hover {
            background: #2F80ED;
            color: #FFFFFF;
        }

        .nexo-cliente-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .nexo-cliente-summary-card,
        .nexo-cliente-panel {
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.05);
        }

        .nexo-cliente-summary-card {
            min-height: 116px;
            border-radius: 24px;
            padding: 24px;
        }

        .nexo-cliente-summary-card span,
        .nexo-info-item span {
            display: block;
            color: #64748B;
            font-size: 0.84rem;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .nexo-cliente-summary-card strong {
            display: block;
            color: #061C3F;
            font-size: 1.55rem;
            font-weight: 950;
            line-height: 1.1;
            letter-spacing: -0.04em;
        }

        .nexo-cliente-panel {
            border-radius: 28px;
            padding: 28px;
        }

        .nexo-section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .nexo-section-header h2 {
            color: #061C3F;
            font-size: 1.28rem;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .nexo-info-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .nexo-info-item {
            min-height: 98px;
            border-radius: 20px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
            padding: 18px;
        }

        .nexo-info-item strong {
            color: #061C3F;
            font-size: 0.98rem;
            font-weight: 900;
            overflow-wrap: anywhere;
        }

        .nexo-cliente-table thead th {
            color: #64748B;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            border-color: #E8EEF6;
            padding-bottom: 16px;
        }

        .nexo-cliente-table tbody td {
            padding-top: 18px;
            padding-bottom: 18px;
            border-color: #EDF2F7;
            vertical-align: middle;
        }

        .nexo-cliente-table strong,
        .nexo-proposta-item strong,
        .nexo-side-item strong {
            display: block;
            color: #061C3F;
            font-weight: 900;
        }

        .nexo-cliente-table small,
        .nexo-proposta-item span,
        .nexo-side-item span {
            display: block;
            color: #64748B;
            font-size: 0.88rem;
        }

        .nexo-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.82rem;
            font-weight: 900;
        }

        .nexo-propostas-list,
        .nexo-side-list,
        .nexo-timeline {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .nexo-proposta-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px;
            border-radius: 20px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-proposta-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .nexo-proposta-actions a {
            color: #2F80ED;
            font-weight: 900;
            text-decoration: none;
        }

        .nexo-side-block {
            padding: 18px;
            border-radius: 20px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-side-block h3 {
            color: #061C3F;
            font-size: 0.98rem;
            font-weight: 900;
            margin: 0 0 14px;
        }

        .nexo-side-block p {
            color: #64748B;
            margin: 0;
            font-weight: 700;
        }

        .nexo-side-item + .nexo-side-item {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #E6EEF9;
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
            min-height: 140px;
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

        @media (max-width: 1200px) {
            .nexo-cliente-summary,
            .nexo-info-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .nexo-cliente-header,
            .nexo-proposta-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .nexo-cliente-summary,
            .nexo-info-grid {
                grid-template-columns: 1fr;
            }

            .nexo-cliente-panel {
                padding: 22px;
            }

            .nexo-proposta-actions {
                justify-content: flex-start;
            }
        }

        .nexo-review-resend-card {
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            border: 1px solid rgba(212, 175, 55, 0.32);
            border-radius: 22px;
            background: linear-gradient(135deg, #FFFFFF 0%, #FFFDF4 100%);
            padding: 18px;
        }

        .nexo-review-resend-card.is-finished {
            grid-template-columns: 56px minmax(0, 1fr);
            border-color: #BEECD3;
            background: #EAFBF1;
        }

        .nexo-review-resend-icon {
            width: 56px;
            height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            color: #061C3F;
            background: linear-gradient(135deg, #F9E7A4 0%, #D4AF37 48%, #B8860B 100%);
            box-shadow: 0 14px 28px rgba(212, 175, 55, 0.22);
            font-size: 1.35rem;
        }

        .nexo-review-resend-card strong,
        .nexo-review-resend-card span {
            display: block;
        }

        .nexo-review-resend-card strong {
            color: #061C3F;
            font-size: 1rem;
            font-weight: 950;
            margin-bottom: 4px;
        }

        .nexo-review-resend-card span {
            color: #64748B;
            font-weight: 750;
            line-height: 1.45;
        }

        .nexo-review-resend-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .nexo-review-whatsapp-btn,
        .nexo-review-copy-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            border-radius: 13px;
            border: 0;
            padding: 0 14px;
            font-weight: 900;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .nexo-review-whatsapp-btn {
            background: #22C55E;
            color: #FFFFFF;
            box-shadow: 0 14px 28px rgba(34, 197, 94, 0.18);
        }

        .nexo-review-copy-btn {
            background: #EAF3FF;
            color: #2F80ED;
        }

        .nexo-review-whatsapp-btn:hover {
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .nexo-review-copy-btn:hover {
            background: #2F80ED;
            color: #FFFFFF;
        }

        .nexo-review-vigente-modal .modal-content {
            border: 0;
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(15, 23, 42, 0.22);
        }

        .nexo-review-vigente-modal-header {
            padding: 26px;
            background: linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
        }

        .nexo-review-vigente-modal-header span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #F6E7A1;
            font-size: 0.78rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 10px;
        }

        .nexo-review-vigente-modal-header h2 {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .nexo-review-vigente-modal-body {
            padding: 24px;
        }

        .nexo-review-vigente-modal-body p {
            color: #64748B;
            font-weight: 750;
            line-height: 1.65;
            margin: 0 0 18px;
        }

        .nexo-review-modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .nexo-review-modal-actions a,
        .nexo-review-modal-actions button {
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border-radius: 15px;
            border: 0;
            font-weight: 950;
            text-decoration: none;
        }

        .nexo-review-modal-actions a {
            background: #22C55E;
            color: #FFFFFF;
        }

        .nexo-review-modal-actions button {
            background: #EAF3FF;
            color: #2F80ED;
        }

        @media (max-width: 900px) {
            .nexo-review-resend-card {
                grid-template-columns: 1fr;
            }

            .nexo-review-resend-actions,
            .nexo-review-modal-actions {
                grid-template-columns: 1fr;
                justify-content: stretch;
            }
        }
    </style>

    @if(session('avaliacao_contrato_vigente'))
        <div class="modal fade nexo-review-vigente-modal" id="avaliacaoContratoVigenteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="nexo-review-vigente-modal-header">
                        <span><i class="bi bi-patch-check-fill"></i> Contrato vigente</span>
                        <h2>Enviar avaliação para {{ session('avaliacao_contrato_vigente.cliente') }}</h2>
                    </div>

                    <div class="nexo-review-vigente-modal-body">
                        <p>
                            O contrato foi registrado como vigente. Aproveite esse momento positivo para enviar ao cliente uma mensagem de confirmação com o convite para avaliar seu atendimento.
                        </p>

                        <div class="nexo-review-modal-actions">
                            <a href="{{ session('avaliacao_contrato_vigente.whatsapp') }}" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp"></i>
                                Enviar no WhatsApp
                            </a>

                            <button type="button" data-copy-review-link="{{ session('avaliacao_contrato_vigente.link') }}">
                                <i class="bi bi-copy"></i>
                                Copiar link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-copy-review-link]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const link = button.dataset.copyReviewLink;
                    const original = button.innerHTML;

                    try {
                        await navigator.clipboard.writeText(link);
                        button.innerHTML = '<i class="bi bi-check-circle"></i> Link copiado';
                    } catch (error) {
                        button.innerHTML = '<i class="bi bi-exclamation-circle"></i> Copie manualmente';
                    }

                    setTimeout(() => {
                        button.innerHTML = original;
                    }, 2200);
                });
            });

            const avaliacaoModalElement = document.getElementById('avaliacaoContratoVigenteModal');

            if (avaliacaoModalElement && window.bootstrap) {
                window.bootstrap.Modal.getOrCreateInstance(avaliacaoModalElement).show();
            }
        });
    </script>
</x-layouts.app>