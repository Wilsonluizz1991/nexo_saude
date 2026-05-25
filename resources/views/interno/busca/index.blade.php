<x-layouts.app title="Busca global | Nexo Saude">
    <main class="nexo-main nexo-search-page">
        <section class="nexo-search-hero">
            <div>
                <span class="nexo-page-label">
                    <i class="bi bi-search"></i>
                    Busca global
                </span>

                <h1>Resultados da pesquisa</h1>
                <p>Encontre registros em leads, propostas, pre-cadastros, implantacoes, clientes e carteira.</p>
            </div>
        </section>

        <section class="nexo-search-results">
            @if($termo === '')
                <div class="nexo-search-empty">
                    <i class="bi bi-search-heart"></i>
                    <strong>Digite algo para pesquisar.</strong>
                    <span>A busca consulta todo o funil do corretor e abre cada resultado na etapa correta.</span>
                </div>
            @elseif($resultados->isEmpty())
                <div class="nexo-search-empty">
                    <i class="bi bi-inbox"></i>
                    <strong>Nenhum resultado encontrado.</strong>
                    <span>Revise o termo ou tente buscar por telefone, e-mail, operadora, plano ou status.</span>
                </div>
            @else
                <div class="nexo-search-count">
                    <span>{{ $resultados->total() }} resultado(s)</span>
                    <strong>"{{ $termo }}"</strong>
                </div>

                <div class="nexo-search-list">
                    @foreach($resultados as $resultado)
                        <a class="nexo-search-result-card" href="{{ $resultado['url'] }}">
                            <div class="nexo-search-result-icon">
                                <i class="bi bi-arrow-right-circle"></i>
                            </div>

                            <div class="nexo-search-result-main">
                                <div class="nexo-search-result-title">
                                    <span>{{ $resultado['etapa'] }}</span>
                                    <strong>{{ $resultado['titulo'] }}</strong>
                                </div>

                                <p>{{ $resultado['subtitulo'] }}</p>

                                <div class="nexo-search-result-meta">
                                    <span><i class="bi bi-shield-check"></i>{{ $resultado['plano'] }}</span>
                                    <span><i class="bi bi-people"></i>{{ $resultado['vidas'] }} vida(s)</span>
                                    <span><i class="bi bi-geo-alt"></i>{{ $resultado['local'] ?: 'Sem cidade' }}</span>
                                    <span><i class="bi bi-activity"></i>{{ $resultado['status'] }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{ $resultados->links('vendor.pagination.nexo') }}
            @endif
        </section>
    </main>

    <style>
        .nexo-search-page {
            display: grid;
            gap: 24px;
        }

        .nexo-search-hero {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            align-items: start;
            padding: 28px;
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(91, 167, 255, 0.20), transparent 36%),
                linear-gradient(135deg, #061C3F 0%, #0F3A68 100%);
            color: #FFFFFF;
            box-shadow: 0 24px 60px rgba(6, 28, 63, 0.18);
        }

        .nexo-search-hero .nexo-page-label {
            color: #9CC8FF;
        }

        .nexo-search-hero h1 {
            margin: 8px 0 6px;
            font-size: clamp(2rem, 3vw, 3rem);
            font-weight: 950;
            letter-spacing: -0.03em;
        }

        .nexo-search-hero p {
            max-width: 720px;
            margin: 0;
            color: rgba(255, 255, 255, 0.72);
            font-weight: 700;
        }

        .nexo-search-results {
            padding: 26px;
            border: 1px solid #E4EBF5;
            border-radius: 28px;
            background: #FFFFFF;
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.055);
        }

        .nexo-search-count {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
            color: #64748B;
            font-weight: 850;
        }

        .nexo-search-count strong {
            color: #061C3F;
        }

        .nexo-search-list {
            display: grid;
            gap: 14px;
        }

        .nexo-search-result-card {
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr);
            gap: 16px;
            padding: 18px;
            border: 1px solid #E4EBF5;
            border-radius: 22px;
            background: linear-gradient(135deg, #FFFFFF 0%, #F8FBFF 100%);
            color: inherit;
            text-decoration: none;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .nexo-search-result-card:hover {
            transform: translateY(-2px);
            border-color: #BBD7FF;
            box-shadow: 0 18px 36px rgba(47, 128, 237, 0.12);
        }

        .nexo-search-result-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 1.3rem;
        }

        .nexo-search-result-title {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nexo-search-result-title span {
            min-height: 26px;
            display: inline-flex;
            align-items: center;
            padding: 0 10px;
            border-radius: 999px;
            background: #EAF3FF;
            color: #0F3A68;
            font-size: 0.78rem;
            font-weight: 950;
        }

        .nexo-search-result-title strong {
            color: #061C3F;
            font-size: 1.1rem;
            font-weight: 950;
        }

        .nexo-search-result-main p {
            margin: 6px 0 12px;
            color: #64748B;
            font-weight: 750;
        }

        .nexo-search-result-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .nexo-search-result-meta span {
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0 10px;
            border-radius: 999px;
            background: #F1F5F9;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 850;
        }

        .nexo-search-empty {
            min-height: 260px;
            display: grid;
            place-items: center;
            align-content: center;
            gap: 8px;
            text-align: center;
            color: #64748B;
            font-weight: 750;
        }

        .nexo-search-empty i {
            width: 68px;
            height: 68px;
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #EAF3FF;
            color: #2F80ED;
            font-size: 1.8rem;
            margin-bottom: 6px;
        }

        .nexo-search-empty strong {
            color: #061C3F;
            font-size: 1.25rem;
            font-weight: 950;
        }

        @media (max-width: 900px) {
            .nexo-search-hero {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .nexo-search-hero,
            .nexo-search-results {
                padding: 20px;
                border-radius: 22px;
            }

            .nexo-search-result-card {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-layouts.app>
