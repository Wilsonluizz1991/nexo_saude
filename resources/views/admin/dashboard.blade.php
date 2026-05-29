<x-layouts.app title="Admin | Nexo Saúde">
    <div class="container-fluid py-4">
        <div class="admin-hero">
            <div>
                <span>Administração do Sistema</span>
                <h1>Painel geral da Nexo Saúde</h1>
                <p>Monitore usuários, assinaturas, receita prevista e operação da plataforma.</p>
            </div>

            <a href="{{ route('admin.usuarios.index') }}" class="admin-primary-btn">
                <i class="bi bi-people-fill"></i>
                Gerenciar usuários
            </a>
        </div>

        <div class="admin-grid">
            <div class="admin-card"><span>Usuários totais</span><strong>{{ $totalUsuarios }}</strong></div>
            <div class="admin-card"><span>Usuários ativos</span><strong>{{ $usuariosAtivos }}</strong></div>
            <div class="admin-card"><span>Usuários bloqueados</span><strong>{{ $usuariosBloqueados }}</strong></div>
            <div class="admin-card"><span>Administradores</span><strong>{{ $admins }}</strong></div>
            <div class="admin-card"><span>Assinaturas ativas</span><strong>{{ $assinaturasAtivas }}</strong></div>
            <div class="admin-card"><span>Em teste grátis</span><strong>{{ $assinaturasTeste }}</strong></div>
            <div class="admin-card"><span>Pendentes</span><strong>{{ $assinaturasPendentes }}</strong></div>
            <div class="admin-card highlight"><span>Receita mensal prevista</span><strong>R$ {{ number_format($receitaMensalPrevista, 2, ',', '.') }}</strong></div>
        </div>

        <div class="admin-section">
            <div class="admin-section-header">
                <div>
                    <span>Monitoramento</span>
                    <h2>Usuários recentes</h2>
                </div>

                <a href="{{ route('admin.usuarios.index') }}" class="admin-section-action">
                    Ver todos
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuariosRecentes as $usuario)
                            <tr>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>{{ $usuario->is_admin ? 'Administrador' : ($usuario->perfil ?? 'Corretor') }}</td>
                                <td>
                                    @if($usuario->blocked_at)
                                        <span class="admin-status is-danger">Bloqueado</span>
                                    @else
                                        <span class="admin-status is-success">Ativo</span>
                                    @endif
                                </td>
                                <td>{{ optional($usuario->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .admin-hero {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding: 30px;
            border-radius: 28px;
            color: #fff;
            background: linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            margin-bottom: 24px;
        }

        .admin-hero span {
            font-weight: 900;
            color: #BBD7FF;
        }

        .admin-hero h1 {
            font-weight: 950;
            letter-spacing: -0.05em;
            margin: 8px 0;
        }

        .admin-hero p {
            margin: 0;
            color: rgba(255,255,255,.75);
        }

        .admin-primary-btn {
            align-self: center;
            min-height: 54px;
            padding: 0 22px;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, #2F80ED 0%, #1B6DFF 100%);
            text-decoration: none;
            font-weight: 950;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.28);
            transition: 0.2s ease;
        }

        .admin-primary-btn:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(47, 128, 237, 0.34);
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .admin-card {
            padding: 20px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(6, 28, 63, .05);
        }

        .admin-card span {
            display: block;
            color: #64748B;
            font-size: .76rem;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .admin-card strong {
            font-size: 1.7rem;
            color: #061C3F;
            font-weight: 950;
        }

        .admin-card.highlight {
            background: #ECFDF5;
            border-color: #BBF7D0;
        }

        .admin-section {
            padding: 24px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(6, 28, 63, .05);
        }

        .admin-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        .admin-section-header span {
            display: block;
            color: #64748B;
            font-size: .76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .admin-section-header h2 {
            font-weight: 950;
            color: #061C3F;
            margin: 0;
            letter-spacing: -0.04em;
        }

        .admin-section-action {
            min-height: 44px;
            padding: 0 18px;
            border-radius: 14px;
            color: #2F80ED;
            background: #EAF3FF;
            border: 1px solid #CFE2FF;
            text-decoration: none;
            font-weight: 950;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s ease;
            white-space: nowrap;
        }

        .admin-section-action:hover {
            color: #FFFFFF;
            background: #2F80ED;
            border-color: #2F80ED;
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(47, 128, 237, 0.22);
        }

        .admin-table {
            margin-bottom: 0;
        }

        .admin-table thead th {
            color: #64748B;
            font-size: 0.78rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #E2E8F0;
        }

        .admin-table tbody td {
            color: #061C3F;
            font-weight: 700;
            border-bottom: 1px solid #EEF2F7;
        }

        .admin-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 950;
        }

        .admin-status.is-success {
            background: #ECFDF5;
            color: #166534;
        }

        .admin-status.is-danger {
            background: #FEF2F2;
            color: #B91C1C;
        }

        @media (max-width: 1100px) {
            .admin-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }

            .admin-hero,
            .admin-section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-primary-btn,
            .admin-section-action {
                width: 100%;
            }
        }
    </style>
</x-layouts.app>