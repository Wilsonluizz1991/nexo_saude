<div class="admin-audit-table-card">
    <div class="table-responsive">
        <table class="table align-middle admin-audit-table">
            <thead>
                <tr>
                    <th>Ação</th>
                    <th>Administrador</th>
                    <th>Usuário afetado</th>
                    <th>Descrição</th>
                    <th>Data</th>
                    <th>IP</th>
                </tr>
            </thead>

            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <span class="admin-audit-action">
                                {{ str_replace('_', ' ', $log->acao) }}
                            </span>
                        </td>

                        <td>
                            <div class="admin-audit-user">
                                <strong>{{ $log->administrador?->name ?? 'Sistema' }}</strong>
                                <span>{{ $log->administrador?->email ?? '-' }}</span>
                            </div>
                        </td>

                        <td>
                            <div class="admin-audit-user">
                                <strong>{{ $log->usuarioAlvo?->name ?? '-' }}</strong>
                                <span>{{ $log->usuarioAlvo?->email ?? '-' }}</span>
                            </div>
                        </td>

                        <td>
                            <div class="admin-audit-description">
                                {{ $log->descricao ?? '-' }}
                            </div>
                        </td>

                        <td>
                            {{ optional($log->created_at)->format('d/m/Y H:i') }}
                        </td>

                        <td>
                            {{ $log->ip ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty-state">
                                Nenhum registro de auditoria encontrado.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="admin-pagination">
            {{ $logs->links() }}
        </div>
    @endif
</div>