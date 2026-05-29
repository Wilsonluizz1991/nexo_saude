<x-layouts.app title="Auditoria | Admin Nexo">
    <div class="container-fluid py-4 admin-audit-page">
        <div class="admin-audit-hero">
            <div>
                <span>Rastreabilidade</span>
                <h1>Auditoria administrativa</h1>
                <p>Acompanhe ações executadas por administradores dentro da plataforma.</p>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="admin-primary-btn">
                <i class="bi bi-arrow-left"></i>
                Voltar ao painel
            </a>
        </div>
        <div id="admin-audit-table-wrapper">
            @include('admin.auditoria.partials.table', ['logs' => $logs])
        </div>
    </div>

    <style>
        .admin-audit-page {
            display: grid;
            gap: 20px;
        }

        .admin-audit-hero {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding: 30px;
            border-radius: 28px;
            color: #FFFFFF;
            background: linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            box-shadow: 0 18px 45px rgba(6, 28, 63, 0.16);
        }

        .admin-audit-hero span {
            display: block;
            color: #BBD7FF;
            font-size: 0.78rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .admin-audit-hero h1 {
            margin: 0 0 8px;
            font-size: clamp(2rem, 3vw, 3rem);
            font-weight: 950;
            letter-spacing: -0.06em;
        }

        .admin-audit-hero p {
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
        }

        .admin-audit-table-card {
            border-radius: 24px;
            background: #FFFFFF;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(6, 28, 63, 0.05);
            overflow: hidden;
        }

        .admin-audit-table {
            margin-bottom: 0;
        }

        .admin-audit-table thead th {
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

        .admin-audit-table tbody td {
            padding: 16px 18px;
            color: #061C3F;
            font-weight: 750;
            vertical-align: middle;
            border-bottom: 1px solid #EEF2F7;
        }

        .admin-audit-action {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 950;
            color: #1D4ED8;
            background: #EAF3FF;
        }

        .admin-audit-user strong {
            display: block;
            color: #061C3F;
            font-weight: 950;
        }

        .admin-audit-user span {
            display: block;
            color: #64748B;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .admin-audit-description {
            color: #475569;
            font-weight: 750;
            line-height: 1.45;
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
            .admin-audit-hero {
                flex-direction: column;
            }

            .admin-primary-btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('admin-audit-table-wrapper');

            if (!wrapper) {
                return;
            }

            let controller = null;

            const fetchAuditPage = function (url) {
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

            document.addEventListener('click', function (event) {
                const link = event.target.closest('#admin-audit-table-wrapper .pagination a');

                if (!link) {
                    return;
                }

                event.preventDefault();
                fetchAuditPage(link.href);
            });

            window.addEventListener('popstate', function () {
                fetchAuditPage(window.location.href);
            });
        });
    </script>
</x-layouts.app>