@php
    $statusLabels = [
        'pendente' => 'Pendente',
        'enviado' => 'Enviado',
        'aprovado' => 'Aprovado',
        'aprovado_ia' => 'Aprovado pela IA',
        'corrigir' => 'Corrigir',
        'recusado' => 'Recusado',
        'dispensado' => 'Dispensado',
    ];

    $statusClasses = [
        'pendente' => 'nexo-document-status-pendente',
        'enviado' => 'nexo-document-status-enviado',
        'aprovado' => 'nexo-document-status-aprovado',
        'aprovado_ia' => 'nexo-document-status-aprovado',
        'corrigir' => 'nexo-document-status-corrigir',
        'recusado' => 'nexo-document-status-recusado',
        'dispensado' => 'nexo-document-status-dispensado',
    ];

    $confirmacoes = [
        'aprovado' => 'Aprovar documento?',
        'corrigir' => 'Solicitar correção deste documento?',
        'recusado' => 'Recusar documento?',
        'dispensado' => 'Dispensar este documento?',
    ];

    $acoes = [
        'aprovado' => [
            'label' => 'Aprovar',
            'icon' => 'bi-check-circle',
        ],
        'corrigir' => [
            'label' => 'Solicitar correção',
            'icon' => 'bi-pencil-square',
        ],
        'recusado' => [
            'label' => 'Recusar',
            'icon' => 'bi-x-circle',
        ],
        'dispensado' => [
            'label' => 'Dispensar',
            'icon' => 'bi-slash-circle',
        ],
    ];

    $revisaoBloqueada = $revisaoBloqueada
        ?? ($indicacao->etapa === 'carteira' || $indicacao->status === 'contrato_vigente');
@endphp

<div class="nexo-document-review-list mt-3">
    @forelse($documentos as $documento)
        <article class="nexo-document-review-card" data-document-row="{{ $documento->id }}">
            <div class="nexo-document-review-main">
                <div class="nexo-document-review-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>

                <div class="nexo-document-review-content">
                    <div class="nexo-document-review-top">
                        <div class="nexo-document-title">
                            <strong>{{ $documento->titulo }}</strong>

                            @if($documento->grupo_alternativo)
                                <span>Alternativa: {{ $documento->grupo_alternativo }}</span>
                            @endif
                        </div>

                        <span
                            class="nexo-document-status {{ $statusClasses[$documento->status] ?? 'nexo-document-status-pendente' }}"
                            data-document-status
                            data-document-status-value="{{ $documento->status }}"
                        >
                            {{ $statusLabels[$documento->status] ?? ucfirst(str_replace('_', ' ', $documento->status)) }}
                        </span>
                    </div>

                    @if($documento->dispensado_por_ia)
                        <div class="nexo-document-ia">
                            <i class="bi bi-stars"></i>
                            <span>
                                IA: {{ $documento->motivo_dispensa ?: 'Documento dispensado automaticamente pela IA.' }}
                            </span>
                        </div>
                    @endif

                    @if($documento->validado_por_documento_compartilhado)
                        <div class="nexo-document-ia">
                            <i class="bi bi-stars"></i>
                            <span>
                                IA: {{ $documento->motivo_validacao ?: 'Carta de Permanência aprovada automaticamente. Beneficiário encontrado na carta de permanência familiar anexada ao titular.' }}
                            </span>
                        </div>
                    @endif

                    @if($documento->envio)
                        <a
                            class="nexo-document-link"
                            href="{{ asset('storage/'.$documento->envio->arquivo_path) }}"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="bi bi-box-arrow-up-right"></i>
                            Visualizar documento enviado
                        </a>

                        @if($documento->envio->iaValidacao)
                            <div class="nexo-document-ia">
                                <i class="bi bi-stars"></i>
                                <span>
                                    IA: {{ $documento->envio->iaValidacao->mensagem_corretor ?: $documento->envio->iaValidacao->mensagem_cliente }}
                                </span>
                            </div>
                        @endif
                    @else
                        <div class="nexo-document-muted">
                            <i class="bi bi-hourglass-split"></i>
                            Aguardando envio do cliente.
                        </div>
                    @endif

                    @if($documento->observacoes)
                        <div class="nexo-document-observacao" data-document-observacoes>
                            <i class="bi bi-chat-left-text"></i>
                            <span>{{ $documento->observacoes }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="nexo-document-review-actions">
                @if($revisaoBloqueada)
                    <div class="nexo-review-locked">
                        <i class="bi bi-lock"></i>
                        Revisão encerrada
                    </div>
                @else
                    <div class="dropdown nexo-review-dropdown">
                        <button
                            class="nexo-review-dropdown-btn dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="bi bi-ui-checks"></i>
                            Revisar documento
                        </button>

                        <div class="dropdown-menu dropdown-menu-end nexo-review-menu">
                            @foreach($acoes as $status => $acao)
                                <form
                                    method="post"
                                    action="{{ route('indicacoes.documentos.update', [$indicacao, $documento]) }}"
                                    data-review-form
                                    data-confirm-message="{{ $confirmacoes[$status] }}"
                                    data-status-label="{{ $statusLabels[$status] }}"
                                    data-status-value="{{ $status }}"
                                >
                                    @csrf

                                    <input type="hidden" name="status" value="{{ $status }}">

                                    <button class="dropdown-item nexo-review-menu-item" type="submit">
                                        <i class="bi {{ $acao['icon'] }}"></i>
                                        {{ $acao['label'] }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="nexo-document-empty">
            <i class="bi bi-folder2-open"></i>
            <strong>Nenhum documento obrigatório para esta vida.</strong>
            <span>Quando houver documentos solicitados, eles aparecerão aqui para revisão.</span>
        </div>
    @endforelse
</div>

@once
    <div class="modal fade" id="nexoDocumentoConfirmModal" tabindex="-1" aria-labelledby="nexoDocumentoConfirmTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content nexo-document-modal">
                <div class="modal-header">
                    <div>
                        <span class="nexo-modal-label">
                            Revisão documental
                        </span>

                        <h2 class="modal-title h5" id="nexoDocumentoConfirmTitle">
                            Confirmar ação
                        </h2>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-0" data-confirm-text>
                        Confirma a ação neste documento?
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="nexo-modal-secondary-btn" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button" class="nexo-modal-primary-btn" data-confirm-action>
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nexo-document-review-list {
            display: grid;
            gap: 14px;
        }

        .nexo-document-review-card {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            padding: 18px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #E4EBF5;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.045);
        }

        .nexo-document-review-main {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            min-width: 0;
        }

        .nexo-document-review-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: linear-gradient(135deg, #EAF3FF 0%, #DCEEFF 100%);
            color: #2F80ED;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.18rem;
            flex-shrink: 0;
        }

        .nexo-document-review-content {
            flex: 1;
            min-width: 0;
        }

        .nexo-document-review-top {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
        }

        .nexo-document-title strong {
            display: block;
            color: #061C3F;
            font-size: 0.98rem;
            font-weight: 950;
            line-height: 1.25;
            margin-bottom: 4px;
        }

        .nexo-document-title span {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 0 10px;
            border-radius: 999px;
            background: #F3F8FF;
            color: #2F80ED;
            font-size: 0.74rem;
            font-weight: 850;
        }

        .nexo-document-link,
        .nexo-document-muted {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 0.88rem;
            font-weight: 800;
            margin-top: 2px;
        }

        .nexo-document-link {
            color: #2F80ED;
            text-decoration: none;
        }

        .nexo-document-link:hover {
            color: #1B6DFF;
            text-decoration: underline;
        }

        .nexo-document-muted {
            color: #64748B;
        }

        .nexo-document-observacao {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 12px;
            color: #64748B;
            font-size: 0.86rem;
            line-height: 1.42;
            padding: 12px;
            border-radius: 14px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
        }

        .nexo-document-observacao i {
            color: #2F80ED;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .nexo-document-ia {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 12px;
            color: #475569;
            font-size: 0.84rem;
            line-height: 1.42;
            padding: 12px;
            border-radius: 14px;
            background: #F3F8FF;
            border: 1px solid #D7E7FF;
        }

        .nexo-document-ia i {
            color: #2F80ED;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .nexo-document-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 950;
            white-space: nowrap;
        }

        .nexo-document-status-pendente {
            background: #F3F8FF;
            color: #2F80ED;
        }

        .nexo-document-status-enviado {
            background: #EEF7FF;
            color: #3A8DDE;
        }

        .nexo-document-status-aprovado {
            background: #EAFBF1;
            color: #1E9E63;
        }

        .nexo-document-status-corrigir {
            background: #FFF5E8;
            color: #D9822B;
        }

        .nexo-document-status-recusado {
            background: #FFECEC;
            color: #E14D4D;
        }

        .nexo-document-status-dispensado {
            background: #F1F5F9;
            color: #475569;
        }

        .nexo-document-review-actions {
            display: flex;
            justify-content: stretch;
        }

        .nexo-review-dropdown {
            width: 100%;
        }

        .nexo-review-locked {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 0 16px;
            border-radius: 14px;
            border: 1px solid #E2E8F0;
            background: #F8FBFF;
            color: #64748B;
            font-size: 0.88rem;
            font-weight: 950;
            white-space: nowrap;
        }

        .nexo-review-dropdown-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 0 16px;
            border-radius: 14px;
            border: 1px solid #D7E7FF;
            background: #FFFFFF;
            color: #2F80ED;
            font-size: 0.88rem;
            font-weight: 950;
            transition: 0.2s ease;
        }

        .nexo-review-dropdown-btn:hover,
        .nexo-review-dropdown-btn.show {
            background: #2F80ED;
            border-color: #2F80ED;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .nexo-review-menu {
            width: 100%;
            padding: 8px;
            border: 1px solid #E4EBF5;
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            min-width: 230px;
        }

        .nexo-review-menu form {
            margin: 0;
        }

        .nexo-review-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 42px;
            border-radius: 12px;
            color: #061C3F;
            font-size: 0.9rem;
            font-weight: 850;
            padding: 0 12px;
        }

        .nexo-review-menu-item i {
            color: #2F80ED;
            font-size: 1rem;
        }

        .nexo-review-menu-item:hover,
        .nexo-review-menu-item:focus {
            background: #EAF3FF;
            color: #061C3F;
        }

        .nexo-document-empty {
            min-height: 160px;
            padding: 28px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            border-radius: 22px;
            background: #F8FBFF;
            border: 1px solid #E6EEF9;
            color: #64748B;
        }

        .nexo-document-empty i {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: #EAF3FF;
            color: #2F80ED;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 14px;
        }

        .nexo-document-empty strong {
            color: #061C3F;
            font-weight: 950;
            margin-bottom: 4px;
        }

        .nexo-document-empty span {
            max-width: 360px;
            font-size: 0.9rem;
            line-height: 1.45;
        }

        .nexo-document-modal {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 28px 70px rgba(6, 28, 63, 0.18);
            overflow: hidden;
        }

        .nexo-document-modal .modal-header {
            border-bottom: 1px solid #E8EEF6;
            padding: 22px 24px;
            background: linear-gradient(180deg, #F8FBFF 0%, #FFFFFF 100%);
        }

        .nexo-modal-label {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            padding: 0 10px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 0.72rem;
            font-weight: 950;
            margin-bottom: 8px;
        }

        .nexo-document-modal .modal-title {
            color: #061C3F;
            font-weight: 950;
            margin: 0;
        }

        .nexo-document-modal .modal-body {
            color: #475569;
            padding: 24px;
            font-weight: 650;
            line-height: 1.5;
        }

        .nexo-document-modal .modal-footer {
            border-top: 1px solid #E8EEF6;
            padding: 18px 24px;
            background: #F8FBFF;
        }

        .nexo-modal-primary-btn,
        .nexo-modal-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 13px;
            font-weight: 950;
            transition: 0.2s ease;
        }

        .nexo-modal-primary-btn {
            border: 1px solid #2F80ED;
            background: #2F80ED;
            color: #FFFFFF;
        }

        .nexo-modal-secondary-btn {
            border: 1px solid #D7E7FF;
            background: #FFFFFF;
            color: #2F80ED;
        }

        .nexo-modal-secondary-btn:hover {
            background: #EAF3FF;
        }

        .nexo-review-toast {
            position: fixed;
            left: 12px;
            right: 12px;
            bottom: 18px;
            z-index: 9999;
            border-radius: 16px;
            border: 1px solid #BFD8FF;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
            font-weight: 850;
        }

        @media (min-width: 768px) {
            .nexo-document-review-card {
                grid-template-columns: 1fr auto;
                align-items: center;
                gap: 20px;
                padding: 20px;
            }

            .nexo-document-review-top {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
                gap: 16px;
            }

            .nexo-document-review-actions {
                justify-content: flex-end;
            }

            .nexo-review-dropdown {
                width: auto;
            }

            .nexo-review-dropdown-btn {
                width: auto;
                min-width: 170px;
            }

            .nexo-review-menu {
                width: auto;
            }

            .nexo-review-toast {
                left: auto;
                right: 24px;
                bottom: 24px;
                min-width: 280px;
                max-width: 420px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalElement = document.getElementById('nexoDocumentoConfirmModal');

            if (!modalElement || typeof bootstrap === 'undefined') {
                return;
            }

            const modal = new bootstrap.Modal(modalElement);
            const confirmText = modalElement.querySelector('[data-confirm-text]');
            const confirmButton = modalElement.querySelector('[data-confirm-action]');

            let pendingForm = null;

            const statusClasses = [
                'nexo-document-status-pendente',
                'nexo-document-status-enviado',
                'nexo-document-status-aprovado',
                'nexo-document-status-corrigir',
                'nexo-document-status-recusado',
                'nexo-document-status-dispensado',
            ];

            const statusClassMap = {
                pendente: 'nexo-document-status-pendente',
                enviado: 'nexo-document-status-enviado',
                aprovado: 'nexo-document-status-aprovado',
                aprovado_ia: 'nexo-document-status-aprovado',
                corrigir: 'nexo-document-status-corrigir',
                recusado: 'nexo-document-status-recusado',
                dispensado: 'nexo-document-status-dispensado',
            };

            const showToast = (message, type = 'success') => {
                document.querySelectorAll('[data-nexo-review-toast]').forEach((toast) => toast.remove());

                const wrapper = document.createElement('div');

                wrapper.setAttribute('data-nexo-review-toast', 'true');
                wrapper.setAttribute('data-nexo-page-toast', 'true');
                wrapper.className = `nexo-floating-toast ${type === 'success' ? '' : 'is-danger'}`;
                wrapper.innerHTML = `
                    <div class="nexo-floating-toast-icon">
                        <i class="bi ${type === 'success' ? 'bi-check2-circle' : 'bi-exclamation-triangle'}"></i>
                    </div>
                    <div class="nexo-floating-toast-content">
                        <strong>${type === 'success' ? 'Sucesso' : 'Revise o documento'}</strong>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="nexo-floating-toast-close" data-toast-close aria-label="Fechar notificacao">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <div class="nexo-floating-toast-progress"></div>
                `;

                document.body.appendChild(wrapper);

                const close = () => {
                    wrapper.classList.add('is-leaving');
                    wrapper.addEventListener('animationend', () => wrapper.remove(), { once: true });
                    window.setTimeout(() => wrapper.remove(), 360);
                };

                wrapper.querySelector('[data-toast-close]')?.addEventListener('click', close, { once: true });
                window.setTimeout(close, 3200);
            };

            document.querySelectorAll('[data-review-form]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();

                    pendingForm = form;

                    confirmText.textContent = form.dataset.confirmMessage || 'Confirma a ação neste documento?';

                    modal.show();
                });
            });

            confirmButton.addEventListener('click', async () => {
                if (!pendingForm) {
                    return;
                }

                const form = pendingForm;
                const row = form.closest('[data-document-row]');
                const statusBadge = row?.querySelector('[data-document-status]');
                const formData = new FormData(form);
                const requestedStatus = form.dataset.statusValue;

                confirmButton.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Não foi possível atualizar o documento.');
                    }

                    const data = await response.json();

                    if (statusBadge) {
                        statusBadge.textContent = data.status_label || form.dataset.statusLabel || 'Atualizado';
                        statusBadge.dataset.documentStatusValue = requestedStatus;

                        statusClasses.forEach((className) => statusBadge.classList.remove(className));

                        statusBadge.classList.add(statusClassMap[requestedStatus] || 'nexo-document-status-pendente');
                    }

                    showToast(data.message || 'Documento atualizado.');

                    modal.hide();
                } catch (error) {
                    showToast(error.message || 'Não foi possível atualizar o documento.', 'error');
                } finally {
                    confirmButton.disabled = false;
                    pendingForm = null;
                }
            });
        });
    </script>
@endonce
