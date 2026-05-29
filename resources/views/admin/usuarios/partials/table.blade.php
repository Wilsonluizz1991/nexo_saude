<div class="admin-users-table-card">
    <div class="table-responsive">
        <table class="table align-middle admin-users-table">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Perfil</th>
                    <th>Assinatura</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>

            <tbody>
                @forelse($usuarios as $usuario)
                    @php
                        $assinaturaStatus = $usuario->assinatura?->status ?? $usuario->assinatura?->status_assinatura;

                        $assinaturaLabel = match ($assinaturaStatus) {
                            'trialing', 'teste_gratis' => 'Teste gratuito',
                            'active', 'ativa', 'paid' => 'Ativa',
                            'overdue', 'inadimplente', 'past_due', 'dunning' => 'Pendente',
                            'canceled', 'cancelled', 'cancelada' => 'Cancelada',
                            default => 'Sem assinatura',
                        };

                        $assinaturaClass = match ($assinaturaStatus) {
                            'trialing', 'teste_gratis' => 'is-warning',
                            'active', 'ativa', 'paid' => 'is-success',
                            'overdue', 'inadimplente', 'past_due', 'dunning' => 'is-danger',
                            'canceled', 'cancelled', 'cancelada' => 'is-danger',
                            default => 'is-info',
                        };
                    @endphp

                    <tr>
                        <td>
                            <span class="admin-user-name">{{ $usuario->name }}</span>
                            <span class="admin-user-email">{{ $usuario->email }}</span>
                        </td>

                        <td>
                            @if($usuario->is_admin || $usuario->perfil === 'admin')
                                <span class="admin-badge is-info">Administrador</span>
                            @else
                                <span class="admin-badge is-info">Corretor</span>
                            @endif
                        </td>

                        <td>
                            <span class="admin-badge {{ $assinaturaClass }}">
                                {{ $assinaturaLabel }}
                            </span>
                        </td>

                        <td>
                            @if($usuario->blocked_at)
                                <span class="admin-badge is-danger">Bloqueado</span>
                            @else
                                <span class="admin-badge is-success">Ativo</span>
                            @endif
                        </td>

                        <td>{{ optional($usuario->created_at)->format('d/m/Y H:i') }}</td>

                        <td>
                            <div class="admin-actions">
                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="admin-action-btn is-primary">
                                    <i class="bi bi-pencil"></i>
                                    Editar
                                </a>

                                @if($usuario->blocked_at)
                                    <form method="post" action="{{ route('admin.usuarios.desbloquear', $usuario) }}">
                                        @csrf
                                        <button class="admin-action-btn is-success">
                                            <i class="bi bi-unlock"></i>
                                            Desbloquear
                                        </button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('admin.usuarios.bloquear', $usuario) }}">
                                        @csrf
                                        <button class="admin-action-btn is-warning">
                                            <i class="bi bi-lock"></i>
                                            Bloquear
                                        </button>
                                    </form>
                                @endif

                                <form method="post" action="{{ route('admin.usuarios.destroy', $usuario) }}" onsubmit="return confirm('Deseja excluir este usuário?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-action-btn is-danger">
                                        <i class="bi bi-trash"></i>
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty-state">
                                Nenhum usuário encontrado.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($usuarios->hasPages())
        <div class="admin-pagination">
            {{ $usuarios->links() }}
        </div>
    @endif
</div>