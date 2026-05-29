<x-layouts.app title="Usuários | Admin Nexo">
    <div class="container-fluid py-4 admin-users-page">
        <div class="admin-users-hero">
            <div>
                <span>Administração</span>
                <h1>Usuários do sistema</h1>
                <p>Gerencie contas, perfis, administradores, bloqueios e assinaturas.</p>
            </div>

            <a href="{{ route('admin.usuarios.create') }}" class="admin-primary-btn">
                <i class="bi bi-person-plus-fill"></i>
                Novo usuário
            </a>
        </div>

        @if(session('status'))
            <div class="admin-alert is-success">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="admin-alert is-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="admin-users-toolbar">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="admin-users-search" data-admin-users-search-form>
                <i class="bi bi-search"></i>

                <input
                    type="search"
                    name="q"
                    value="{{ $busca ?? request('q') }}"
                    placeholder="Buscar por nome, e-mail, telefone, perfil ou assinatura..."
                    autocomplete="off"
                    data-admin-users-search-input
                >
            </form>
        </div>

        <div id="admin-users-table-wrapper">
            @include('admin.usuarios.partials.table', ['usuarios' => $usuarios])
        </div>
    </div>

    <style>
        .admin-users-page {
            display: grid;
            gap: 20px;
        }

        .admin-users-hero {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding: 30px;
            border-radius: 28px;
            color: #FFFFFF;
            background: linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            box-shadow: 0 18px 45px rgba(6, 28, 63, 0.16);
        }

        .admin-users-hero span {
            display: block;
            color: #BBD7FF;
            font-size: 0.78rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .admin-users-hero h1 {
            margin: 0 0 8px;
            font-size: clamp(2rem, 3vw, 3rem);
            font-weight: 950;
            letter-spacing: -0.06em;
        }

        .admin-users-hero p {
            margin: 0;
            color: rgba(255, 255, 255, 0.75);
            line-height: 1.5;
        }

        .admin-primary-btn {
            align-self: center;
            min-height: 54px;
            padding: 0 22px;
            border-radius: 16px;
            color: #FFFFFF;
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
            color: #FFFFFF;
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(47, 128, 237, 0.34);
        }

        .admin-alert {
            min-height: 52px;
            padding: 14px 16px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 850;
        }

        .admin-alert.is-success {
            background: #ECFDF5;
            color: #166534;
        }

        .admin-alert.is-danger {
            background: #FEF2F2;
            color: #B91C1C;
        }

        .admin-users-toolbar {
            padding: 18px;
            border-radius: 22px;
            background: #FFFFFF;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(6, 28, 63, 0.05);
        }

        .admin-users-search {
            width: 100%;
            min-height: 54px;
            padding: 0 18px;
            border-radius: 16px;
            background: #F8FAFC;
            border: 1px solid #D8E2EF;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-users-search i {
            color: #2F80ED;
            font-size: 1.2rem;
        }

        .admin-users-search input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #061C3F;
            font-size: 0.98rem;
            font-weight: 750;
        }

        .admin-users-search input::placeholder {
            color: #7A8BA3;
        }

        .admin-users-table-card {
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(6, 28, 63, 0.05);
            overflow: hidden;
        }

        .admin-users-table {
            margin-bottom: 0;
        }

        .admin-users-table thead th {
            padding: 16px 18px;
            color: #64748B;
            font-size: 0.76rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #E2E8F0;
            background: #F8FAFC;
            white-space: nowrap;
        }

        .admin-users-table tbody td {
            padding: 16px 18px;
            color: #061C3F;
            font-weight: 750;
            vertical-align: middle;
            border-bottom: 1px solid #EEF2F7;
        }

        .admin-users-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .admin-user-name {
            display: block;
            font-weight: 950;
            color: #061C3F;
        }

        .admin-user-email {
            display: block;
            color: #64748B;
            font-size: 0.82rem;
            font-weight: 700;
            margin-top: 2px;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 950;
            white-space: nowrap;
        }

        .admin-badge.is-success {
            background: #ECFDF5;
            color: #166534;
        }

        .admin-badge.is-danger {
            background: #FEF2F2;
            color: #B91C1C;
        }

        .admin-badge.is-info {
            background: #EAF3FF;
            color: #1D4ED8;
        }

        .admin-badge.is-warning {
            background: #FFFBEB;
            color: #B45309;
        }

        .admin-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .admin-action-btn {
            min-height: 36px;
            padding: 0 12px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 950;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid transparent;
            text-decoration: none;
            background: #FFFFFF;
            transition: 0.2s ease;
        }

        .admin-action-btn.is-primary {
            color: #2F80ED;
            border-color: #CFE2FF;
            background: #EAF3FF;
        }

        .admin-action-btn.is-warning {
            color: #B45309;
            border-color: #FDE68A;
            background: #FFFBEB;
        }

        .admin-action-btn.is-success {
            color: #166534;
            border-color: #BBF7D0;
            background: #ECFDF5;
        }

        .admin-action-btn.is-danger {
            color: #B91C1C;
            border-color: #FCA5A5;
            background: #FEF2F2;
        }

        .admin-action-btn:hover {
            transform: translateY(-1px);
        }

        .admin-pagination {
            padding: 16px 18px;
            border-top: 1px solid #EEF2F7;
            background: #FFFFFF;
        }

        .admin-empty-state {
            padding: 34px 18px;
            text-align: center;
            color: #64748B;
            font-weight: 800;
        }

        @media (max-width: 900px) {
            .admin-users-hero {
                flex-direction: column;
            }

            .admin-primary-btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('admin-users-table-wrapper');
            const form = document.querySelector('[data-admin-users-search-form]');
            const input = document.querySelector('[data-admin-users-search-input]');

            if (!wrapper || !form || !input) {
                return;
            }

            let timeout = null;
            let controller = null;

            const fetchAdminUsers = function (url) {
                if (controller) {
                    controller.abort();
                }

                controller = new AbortController();

                wrapper.style.opacity = '0.55';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                })
                    .then(function (response) {
                        return response.text();
                    })
                    .then(function (html) {
                        wrapper.innerHTML = html;
                        wrapper.style.opacity = '1';
                        window.history.pushState({}, '', url);
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            wrapper.style.opacity = '1';
                        }
                    });
            };

            const buildSearchUrl = function () {
                const url = new URL(form.getAttribute('action'), window.location.origin);
                const value = input.value.trim();

                if (value.length > 0) {
                    url.searchParams.set('q', value);
                }

                return url.toString();
            };

            input.addEventListener('input', function () {
                clearTimeout(timeout);

                timeout = setTimeout(function () {
                    fetchAdminUsers(buildSearchUrl());
                }, 450);
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                fetchAdminUsers(buildSearchUrl());
            });

            document.addEventListener('click', function (event) {
                const link = event.target.closest('#admin-users-table-wrapper .pagination a');

                if (!link) {
                    return;
                }

                event.preventDefault();
                fetchAdminUsers(link.href);
            });

            window.addEventListener('popstate', function () {
                fetchAdminUsers(window.location.href);
            });
        });
    </script>
</x-layouts.app>