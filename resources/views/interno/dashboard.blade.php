<x-layouts.app title="Dashboard | Nexo Saúde">
    @php
        $nomeCompleto = trim($usuario?->name ?? 'Corretor');
        $partesNome = collect(preg_split('/\s+/', $nomeCompleto))->filter()->values();
        $primeiroNome = ucfirst(mb_strtolower($partesNome->first() ?: 'Corretor'));
        $totalAtencao = collect($alertasDashboard)->sum('total');
        $formatarMoeda = fn ($valor) => 'R$ '.number_format((float) $valor, 2, ',', '.');
        $formatarMoedaCurta = fn ($valor) => 'R$ '.number_format((float) $valor, 0, ',', '.');

        $metricColors = [
            'emerald' => ['soft' => 'bg-emerald-50 text-emerald-600', 'line' => '#10b981', 'fill' => '#d1fae5', 'accent' => 'from-emerald-500 to-teal-400'],
            'blue' => ['soft' => 'bg-blue-50 text-blue-700', 'line' => '#2563eb', 'fill' => '#dbeafe', 'accent' => 'from-blue-600 to-cyan-400'],
            'violet' => ['soft' => 'bg-violet-50 text-violet-700', 'line' => '#7c3aed', 'fill' => '#ede9fe', 'accent' => 'from-violet-600 to-fuchsia-400'],
            'orange' => ['soft' => 'bg-orange-50 text-orange-500', 'line' => '#f97316', 'fill' => '#ffedd5', 'accent' => 'from-orange-500 to-amber-300'],
        ];

        $operatorColors = ['#2563eb', '#06b6d4', '#7c3aed', '#10b981', '#f97316'];
        $planColors = ['#2563eb', '#2dd4bf', '#f59e0b', '#7c3aed', '#94a3b8'];
        $receitaValores = collect($receitaSerie)->pluck('valor')->map(fn ($valor) => (float) $valor)->values();
        $receitaLabels = collect($receitaSerie)->pluck('label')->values();
        $temReceita = $receitaValores->sum() > 0;
        $tiposPlanoValores = collect($receitaPorTipoPlano)->pluck('percentual')->map(fn ($valor) => (float) $valor)->values();
        $tiposPlanoLabels = collect($receitaPorTipoPlano)->pluck('nome')->values();
        $temTiposPlano = $tiposPlanoValores->sum() > 0;

        $funnelSteps = [
            ['key' => 'leads', 'label' => 'Leads', 'value' => $funil['leads'] ?? 0, 'icon' => 'users', 'classes' => 'from-blue-600 to-blue-500 text-white', 'chip' => 'bg-blue-50 text-blue-700'],
            ['key' => 'cotacoes', 'label' => 'Cotações', 'value' => $funil['cotacoes'] ?? 0, 'icon' => 'file-text', 'classes' => 'from-cyan-100 to-cyan-200 text-slate-950', 'chip' => 'bg-cyan-50 text-cyan-700'],
            ['key' => 'pre_cadastros', 'label' => 'Pré-cadastro', 'value' => $funil['pre_cadastros'] ?? 0, 'icon' => 'clipboard-check', 'classes' => 'from-violet-100 to-violet-200 text-slate-950', 'chip' => 'bg-violet-50 text-violet-700'],
            ['key' => 'implantacoes', 'label' => 'Implantações', 'value' => $funil['implantacoes'] ?? 0, 'icon' => 'badge-check', 'classes' => 'from-emerald-100 to-emerald-200 text-slate-950', 'chip' => 'bg-emerald-50 text-emerald-700'],
        ];
        $conversoes = [$funil['lead_cotacao'] ?? 0, $funil['cotacao_pre_cadastro'] ?? 0, $funil['pre_cadastro_implantacao'] ?? 0];
    @endphp

    <style>
        body {
            background:
                linear-gradient(180deg, rgba(239, 246, 255, .76) 0%, rgba(248, 250, 252, .98) 340px, #f5f7fb 100%),
                #f5f7fb;
            overflow-x: hidden;
        }
        .dash-root {
            color: #071631;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }
        .dash-main {
            max-width: 1680px;
            margin: 0 auto;
            padding: 24px 28px 32px;
        }
        .dash-panel {
            background: rgba(255, 255, 255, .90);
            border: 1px solid rgba(226, 232, 240, .98);
            border-radius: 18px;
            box-shadow: 0 20px 54px rgba(15, 23, 42, .07);
            backdrop-filter: blur(14px);
        }
        .dash-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 250, 252, .95));
            border: 1px solid #e5edf7;
            border-radius: 22px;
            box-shadow: 0 20px 48px rgba(15, 23, 42, .065);
        }
        .dash-card-hover {
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .dash-card-hover:hover {
            transform: translateY(-2px);
            border-color: rgba(37, 99, 235, .22);
            box-shadow: 0 24px 58px rgba(15, 23, 42, .09);
        }
        .dash-empty {
            display: flex;
            min-height: 124px;
            align-items: center;
            justify-content: center;
            border: 1px dashed #dbe4f0;
            border-radius: 16px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 800;
            text-align: center;
            padding: 16px;
        }
        .dash-scroll::-webkit-scrollbar { height: 7px; width: 7px; }
        .dash-scroll::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, .7); border-radius: 999px; }
        .dash-ia {
            box-shadow: 0 0 0 1px rgba(255,255,255,.14), 0 34px 92px rgba(30, 64, 175, .34);
        }
        .dash-ia-grid {
            background-image:
                linear-gradient(rgba(255,255,255,.075) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.075) 1px, transparent 1px);
            background-size: 34px 34px;
            mask-image: linear-gradient(90deg, transparent, #000 18%, #000 82%, transparent);
        }
        .dash-pulse {
            animation: dashPulse 2.8s ease-in-out infinite;
        }
        .dash-funnel-step {
            clip-path: polygon(0 0, calc(100% - 22px) 0, 100% 50%, calc(100% - 22px) 100%, 0 100%, 16px 50%);
        }
        .dash-funnel-step:first-child {
            clip-path: polygon(0 0, calc(100% - 22px) 0, 100% 50%, calc(100% - 22px) 100%, 0 100%);
        }
        .dash-ring {
            background:
                radial-gradient(circle at center, #ffffff 0 42%, transparent 43%),
                conic-gradient(from 220deg, #2563eb, #2dd4bf, #7c3aed, #2563eb);
        }
        .dash-sparkline-shell {
            isolation: isolate;
        }
        .dash-sparkline-shell::before {
            content: "";
            position: absolute;
            inset: 7px 0 0;
            z-index: -1;
            border-radius: 18px;
            background:
                linear-gradient(90deg, rgba(148, 163, 184, .08) 1px, transparent 1px),
                linear-gradient(180deg, rgba(148, 163, 184, .10) 1px, transparent 1px);
            background-size: 34px 22px;
            mask-image: linear-gradient(180deg, transparent 0%, #000 30%, #000 100%);
            opacity: .72;
        }
        .dash-sparkline-shell::after {
            content: "";
            position: absolute;
            right: 0;
            bottom: 2px;
            z-index: -1;
            width: 42%;
            height: 52px;
            border-radius: 999px;
            background: var(--sparkline-glow, rgba(37, 99, 235, .16));
            filter: blur(22px);
            opacity: .82;
        }
        .dash-action-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 42px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, .58);
            background: linear-gradient(180deg, #ffffff 0%, #eef6ff 100%);
            color: #0f2d68;
            font-size: 13px;
            font-weight: 950;
            text-decoration: none !important;
            box-shadow: 0 18px 34px rgba(0, 0, 0, .18), inset 0 1px 0 rgba(255,255,255,.85);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        }
        .dash-action-link:hover {
            transform: translateY(-1px);
            border-color: rgba(147, 197, 253, .85);
            background: linear-gradient(180deg, #ffffff 0%, #dbeafe 100%);
            color: #0b2b6d;
            box-shadow: 0 22px 42px rgba(15, 23, 42, .24), inset 0 1px 0 rgba(255,255,255,.9);
        }
        .dash-action-link:focus-visible,
        .dash-modern-link:focus-visible,
        .dash-opportunity-row:focus-visible {
            outline: 3px solid rgba(96, 165, 250, .35);
            outline-offset: 3px;
        }
        .dash-modern-link {
            color: inherit;
            text-decoration: none !important;
            transition: color .18s ease;
        }
        .dash-modern-link:hover {
            color: #2563eb;
        }
        .dash-opportunity-row {
            position: relative;
            text-decoration: none !important;
        }
        .dash-opportunity-row::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            border-radius: 16px 0 0 16px;
            background: linear-gradient(180deg, #2563eb, #60a5fa);
            opacity: 0;
            transition: opacity .18s ease;
        }
        .dash-opportunity-row:hover::before {
            opacity: 1;
        }
        .dash-access-pill {
            display: inline-flex;
            height: 34px;
            min-width: 86px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 999px;
            border: 1px solid rgba(191, 219, 254, .95);
            background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%);
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 950;
            box-shadow: 0 10px 24px rgba(37, 99, 235, .08);
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .dash-opportunity-row:hover .dash-access-pill {
            transform: translateX(2px);
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 14px 30px rgba(37, 99, 235, .26);
        }
        @keyframes dashPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(96,165,250,.40); }
            50% { transform: scale(1.04); box-shadow: 0 0 0 12px rgba(96,165,250,0); }
        }
        @media (max-width: 1279px) { .dash-main { padding: 18px; } }
        @media (max-width: 767px) {
            .dash-main { padding: 14px; }
            .dash-funnel-step,
            .dash-funnel-step:first-child { clip-path: none; }
        }
    </style>

    <div class="dash-root min-h-screen">
        <main class="dash-main">
            <header class="flex flex-col gap-[16px] pb-[20px] xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <h1
                        class="text-[34px] font-black leading-[38px] text-[#071631] sm:text-[42px] sm:leading-[46px]"
                        data-dashboard-greeting
                        data-user-name="{{ $primeiroNome }}"
                    >{{ $primeiroNome }}!</h1>
                    <p class="mt-[8px] text-[14px] font-semibold leading-[20px] text-slate-500">Aqui está o resumo da sua performance hoje.</p>
                </div>

                <div class="flex flex-wrap items-center gap-[12px]">
                    <form method="get" action="{{ route('dashboard') }}" class="dash-panel flex min-h-[50px] w-[420px] max-w-full items-center gap-[10px] rounded-[18px] px-[12px] text-[12px] font-bold text-slate-900" data-dashboard-period-form>
                        <span class="flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-[11px] bg-blue-50 text-blue-700">
                            <i data-lucide="calendar-days" class="h-[17px] w-[17px]"></i>
                        </span>
                        <label class="grid min-w-0 flex-1 gap-[2px]">
                            <span class="text-[9px] font-black uppercase text-slate-400">Início</span>
                            <input type="date" name="inicio" value="{{ $periodoFiltro['inicio'] }}" min="{{ $periodoFiltro['min'] }}" max="{{ $periodoFiltro['max'] }}" class="min-w-0 border-0 bg-transparent p-0 text-[12px] font-black text-slate-900 outline-none" aria-label="Data inicial do dashboard" data-dashboard-period-input>
                        </label>
                        <span class="text-slate-300">-</span>
                        <label class="grid min-w-0 flex-1 gap-[2px]">
                            <span class="text-[9px] font-black uppercase text-slate-400">Fim</span>
                            <input type="date" name="fim" value="{{ $periodoFiltro['fim'] }}" min="{{ $periodoFiltro['min'] }}" max="{{ $periodoFiltro['max'] }}" class="min-w-0 border-0 bg-transparent p-0 text-[12px] font-black text-slate-900 outline-none" aria-label="Data final do dashboard" data-dashboard-period-input>
                        </label>
                    </form>

                    <a href="{{ route('alertas.index') }}" class="dash-panel relative flex h-[46px] w-[46px] items-center justify-center text-slate-900 no-underline">
                        <i data-lucide="bell" class="h-[18px] w-[18px]"></i>
                        @if ($alertasTotal > 0)
                            <span class="absolute right-[-7px] top-[-7px] inline-flex h-[23px] min-w-[23px] items-center justify-center rounded-full bg-rose-500 px-[6px] text-[10px] font-black leading-none text-white ring-[3px] ring-white shadow-[0_8px_18px_rgba(244,63,94,.35)]" data-dashboard-alert-badge>
                                {{ $alertasTotal > 99 ? '99+' : $alertasTotal }}
                            </span>
                        @endif
                    </a>
                </div>
            </header>

            <section class="dash-ia relative overflow-hidden rounded-[24px] bg-[linear-gradient(130deg,#051331_0%,#0f2d68_39%,#4338ca_73%,#7c3aed_100%)] p-[16px] text-white sm:rounded-[26px] sm:p-[22px] xl:p-[28px]">
                <div class="dash-ia-grid pointer-events-none absolute inset-0 opacity-45"></div>
                <div class="pointer-events-none absolute inset-x-[24px] top-0 h-px bg-gradient-to-r from-transparent via-cyan-200/75 to-transparent"></div>
                <div class="relative grid gap-[20px] xl:grid-cols-[1fr_382px] xl:items-stretch">
                    <div class="grid gap-[14px] sm:grid-cols-[78px_1fr] sm:gap-[16px]">
                        <div class="dash-pulse flex h-[58px] w-[58px] items-center justify-center rounded-[18px] bg-white/12 text-cyan-100 ring-1 ring-white/20 backdrop-blur sm:h-[70px] sm:w-[70px] sm:rounded-[22px]">
                            <i data-lucide="bot" class="h-[29px] w-[29px] sm:h-[35px] sm:w-[35px]"></i>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-[10px]">
                                <h2 class="text-[26px] font-black leading-[30px] text-white sm:text-[40px] sm:leading-[44px]">Insights da Nexo IA</h2>
                                <span class="rounded-full border border-cyan-200/25 bg-cyan-200/12 px-[11px] py-[5px] text-[11px] font-black text-cyan-100">Gerente de vendas</span>
                            </div>
                            <p class="mt-[12px] max-w-[980px] text-[15px] font-black leading-[21px] text-white sm:mt-[15px] sm:text-[18px] sm:leading-[24px]">{{ $resumoIa['titulo'] }}</p>
                            <p class="mt-[8px] max-w-[920px] text-[13px] font-semibold leading-[20px] text-blue-100 sm:text-[14px] sm:leading-[22px]">{{ $resumoIa['descricao'] }}</p>
                        </div>
                    </div>

                    <aside class="rounded-[18px] border border-white/14 bg-white/[.11] p-[13px] shadow-[inset_0_1px_0_rgba(255,255,255,.14),0_18px_50px_rgba(0,0,0,.16)] backdrop-blur-xl sm:rounded-[22px] sm:p-[16px]">
                        <div class="flex items-center justify-between gap-[12px]">
                            <span class="text-[11px] font-black uppercase text-cyan-100">Ação recomendada</span>
                            <span class="inline-flex h-[26px] items-center rounded-full bg-white/12 px-[9px] text-[10px] font-black text-white">Hoje</span>
                        </div>
                        <p class="mt-[9px] text-[12px] font-extrabold leading-[18px] text-white sm:mt-[10px] sm:text-[13px] sm:leading-[19px]">{{ $resumoIa['recomendacao'] ?: 'Revise as oportunidades paradas e priorize os contatos com maior chance de fechamento.' }}</p>
                        <div class="mt-[12px] grid grid-cols-3 gap-[7px] sm:mt-[14px] sm:gap-[8px]">
                            <div class="rounded-[14px] bg-white/[.10] px-[10px] py-[9px]">
                                <span class="block text-[10px] font-bold text-blue-100">Pré-cad.</span>
                                <strong class="mt-[3px] block text-[18px] font-black text-white">{{ $funil['pre_cadastros'] ?? 0 }}</strong>
                            </div>
                            <div class="rounded-[14px] bg-white/[.10] px-[10px] py-[9px]">
                                <span class="block text-[10px] font-bold text-blue-100">Implantações</span>
                                <strong class="mt-[3px] block text-[18px] font-black text-white">{{ $funil['implantacoes'] ?? 0 }}</strong>
                            </div>
                            <div class="rounded-[14px] bg-white/[.10] px-[10px] py-[9px]">
                                <span class="block text-[10px] font-bold text-blue-100">Atenção</span>
                                <strong class="mt-[3px] block text-[18px] font-black text-white">{{ $totalAtencao }}</strong>
                            </div>
                        </div>
                        <a href="{{ route('paginas.simples', 'pre-cadastros') }}" class="dash-action-link mt-[14px] w-full">
                            Ver oportunidades
                            <i data-lucide="arrow-right" class="h-[15px] w-[15px]"></i>
                        </a>
                    </aside>
                </div>
            </section>

            <section class="mt-[18px] grid gap-[16px] md:grid-cols-2 2xl:grid-cols-4">
                @foreach ($metricas as $key => $metrica)
                    @php
                        $color = $metricColors[$metrica['cor']] ?? $metricColors['blue'];
                        $variacao = (int) ($metrica['variacao'] ?? 0);
                        $positivo = $variacao >= 0;
                        $serie = collect($metrica['serie'] ?? [])->map(fn ($valor) => (float) $valor)->values();
                        $temSerie = $serie->sum() > 0;
                    @endphp
                    <article class="dash-card dash-card-hover relative min-h-[196px] overflow-hidden p-[21px]">
                        <div class="absolute right-[-28px] top-[-30px] h-[124px] w-[154px] rounded-full bg-gradient-to-bl {{ $color['accent'] }} opacity-[.11] blur-[2px]"></div>
                        <div class="relative flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h2 class="text-[17px] font-black leading-[21px] text-slate-950">{{ $metrica['titulo'] }}</h2>
                                <p class="mt-[10px] text-[32px] font-black leading-[34px] text-[#071631]">{{ $metrica['valor'] }}</p>
                                <span class="mt-[12px] inline-flex items-center gap-[5px] rounded-full {{ $positivo ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} px-[10px] py-[6px] text-[11px] font-black">
                                    <i data-lucide="{{ $positivo ? 'trending-up' : 'trending-down' }}" class="h-[13px] w-[13px]"></i>
                                    {{ abs($variacao) }}% vs mês anterior
                                </span>
                            </div>
                            <div class="{{ $color['soft'] }} flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-[16px]">
                                <i data-lucide="{{ $metrica['icone'] }}" class="h-[23px] w-[23px]"></i>
                            </div>
                        </div>
                        @if($temSerie)
                            <div class="dash-sparkline-shell relative mt-[8px] h-[86px] overflow-hidden rounded-[18px]" style="--sparkline-glow: {{ $color['fill'] }};" data-sparkline data-color="{{ $color['line'] }}" data-fill="{{ $color['fill'] }}" data-label="{{ $metrica['titulo'] }}" data-values='@json($serie)'></div>
                        @else
                            <div class="mt-[16px] rounded-[14px] bg-slate-50 px-3 py-3 text-[11px] font-bold text-slate-400">Sem dados reais no período.</div>
                        @endif
                    </article>
                @endforeach
            </section>

            <section class="mt-[18px]">
                <article class="dash-card min-w-0 p-[22px]">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex items-center gap-[8px]">
                                <h2 class="text-[26px] font-black leading-[30px] text-slate-950">Funil de Vendas</h2>
                                <i data-lucide="info" class="h-[15px] w-[15px] text-slate-400"></i>
                            </div>
                            <p class="mt-[6px] text-[13px] font-semibold text-slate-500">Acompanhe onde as oportunidades avançam ou travam.</p>
                        </div>
                        <span class="inline-flex h-[35px] items-center rounded-[11px] border border-slate-200 bg-white px-[12px] text-[11px] font-black text-slate-700">{{ $periodo }}</span>
                    </div>

                    <div class="dash-scroll mt-[22px] overflow-x-auto pb-2">
                        <div class="min-w-[920px]">
                            <div class="grid grid-cols-4 gap-[10px]">
                                @foreach($funnelSteps as $index => $step)
                                    @php $delta = (int) data_get($funil, 'variacoes.'.$step['key'], 0); @endphp
                                    <div class="relative">
                                        <div class="dash-funnel-step min-h-[138px] bg-gradient-to-br {{ $step['classes'] }} p-[19px] shadow-[0_18px_36px_rgba(15,23,42,.08)]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[12px] font-black">{{ $step['label'] }}</span>
                                                <i data-lucide="{{ $step['icon'] }}" class="h-[17px] w-[17px] opacity-80"></i>
                                            </div>
                                            <strong class="mt-[14px] block text-[36px] font-black leading-[38px]">{{ $step['value'] }}</strong>
                                            <span class="mt-[12px] inline-flex items-center gap-[5px] rounded-full bg-white/64 px-[9px] py-[5px] text-[10px] font-black {{ $delta >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                                {{ $delta >= 0 ? '↑' : '↓' }} {{ abs($delta) }}% vs mês anterior
                                            </span>
                                        </div>

                                        @if($index < 3)
                                            <div class="absolute right-[-23px] top-[45px] z-10 rounded-full border border-white bg-white px-[11px] py-[7px] text-center shadow-[0_14px_30px_rgba(15,23,42,.14)]">
                                                <span class="block text-[16px] font-black text-slate-950">{{ $conversoes[$index] }}%</span>
                                                <span class="block text-[9px] font-black uppercase text-slate-400">conv.</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-[15px] flex items-center gap-[8px] rounded-[15px] bg-blue-50 px-[13px] py-[11px] text-[12px] font-bold text-blue-800">
                        <i data-lucide="target" class="h-[15px] w-[15px] shrink-0"></i>
                        <span>{{ $funil['principal_perda'] ? 'Principal perda: '.$funil['principal_perda'] : 'Sem perda relevante no funil atual.' }}</span>
                    </div>
                </article>

            </section>

            <section class="mt-[18px] grid gap-[16px] xl:grid-cols-[1.15fr_.76fr_.76fr]">
                <article class="dash-card min-h-[306px] p-[22px]">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-[7px]">
                                <h2 class="text-[19px] font-black leading-[23px] text-slate-950">Receita de Vendas</h2>
                                <span class="text-[13px] font-bold text-slate-500">(Contratos vigentes)</span>
                            </div>
                            <p class="mt-[8px] text-[31px] font-black leading-[33px] text-[#071631]">{{ $formatarMoedaCurta($receitaTotal ?? 0) }}</p>
                        </div>
                        <span class="rounded-[11px] border border-slate-200 bg-white px-[11px] py-[7px] text-[11px] font-black text-slate-700">{{ count($receitaSerie) }} pontos</span>
                    </div>
                    @if($temReceita)
                        <div id="receita-chart" class="mt-[6px] h-[206px]" data-values='@json($receitaValores)' data-labels='@json($receitaLabels)'></div>
                    @else
                        <div class="dash-empty mt-[18px]">Sem contratos vigentes no período para calcular receita.</div>
                    @endif
                </article>

                <article class="dash-card min-h-[306px] p-[22px]">
                    <h2 class="text-[19px] font-black leading-[23px] text-slate-950">Receita por Operadora</h2>
                    <div class="mt-[20px] space-y-[16px]">
                        @forelse ($receitaPorOperadora as $item)
                            @php $color = $operatorColors[$loop->index % count($operatorColors)]; @endphp
                            <div title="{{ $item['nome'] }} - {{ $item['valor_formatado'] }}">
                                <div class="mb-[8px] flex items-center justify-between gap-3">
                                    <span class="truncate text-[12px] font-black text-slate-800">{{ $item['nome'] }}</span>
                                    <span class="shrink-0 text-[11px] font-black text-slate-500">{{ $item['percentual'] }}%</span>
                                </div>
                                <div class="h-[11px] overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full" style="width: {{ min(100, $item['percentual']) }}%; background: linear-gradient(90deg, {{ $color }}, {{ $color }}99);"></div>
                                </div>
                                <p class="mt-[5px] text-[10px] font-bold text-slate-400">{{ $item['valor_formatado'] }}</p>
                            </div>
                        @empty
                            <div class="dash-empty">Sem contratos com comissão no período.</div>
                        @endforelse
                    </div>
                </article>

                <article class="dash-card min-h-[306px] p-[22px]">
                    <h2 class="text-[19px] font-black leading-[23px] text-slate-950">Receita por Tipo de Plano</h2>
                    @if($temTiposPlano)
                        <div class="mt-[18px] grid grid-cols-[150px_1fr] items-center gap-[14px]">
                            <div id="plan-chart" class="h-[164px]" data-values='@json($tiposPlanoValores)' data-labels='@json($tiposPlanoLabels)'></div>
                            <div class="space-y-[13px]">
                                @foreach ($receitaPorTipoPlano as $index => $item)
                                    <div class="grid grid-cols-[10px_1fr_38px] items-center gap-[8px]">
                                        <span class="h-[10px] w-[10px] rounded-full" style="background: {{ $planColors[$index % count($planColors)] }}"></span>
                                        <span class="truncate text-[11px] font-bold text-slate-600">{{ $item['nome'] }}</span>
                                        <span class="text-right text-[11px] font-black text-slate-950">{{ $item['percentual'] }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="dash-empty mt-[18px]">Sem distribuição real para exibir.</div>
                    @endif
                </article>
            </section>

            <section class="mt-[18px]">
                <article class="dash-card p-[22px]">
                    <h2 class="text-[19px] font-black leading-[23px] text-slate-950">Últimas oportunidades em andamento</h2>
                    <div class="mt-[15px] space-y-[10px]">
                        @forelse ($oportunidades as $oportunidade)
                            @php
                                $criticidade = [
                                    'alta' => 'bg-rose-500',
                                    'media' => 'bg-orange-400',
                                    'baixa' => 'bg-emerald-500',
                                ][$oportunidade['criticidade']] ?? 'bg-emerald-500';
                            @endphp
                            <a href="{{ $oportunidade['url'] }}" class="dash-opportunity-row group grid gap-[10px] rounded-[16px] border border-slate-100 bg-white px-[13px] py-[12px] text-slate-900 shadow-[0_10px_24px_rgba(15,23,42,.03)] transition hover:border-blue-100 hover:shadow-[0_16px_34px_rgba(37,99,235,.08)] md:grid-cols-[1fr_150px_120px_130px_96px] md:items-center">
                                <div class="flex min-w-0 items-center gap-[10px]">
                                    <span class="flex h-[36px] w-[36px] shrink-0 items-center justify-center rounded-[13px] bg-blue-600 text-[12px] font-black text-white">{{ mb_substr($oportunidade['nome'], 0, 1) }}</span>
                                    <span class="min-w-0">
                                        <span class="dash-modern-link block truncate text-[13px] font-black">{{ $oportunidade['nome'] }}</span>
                                        <span class="mt-[2px] block text-[10px] font-bold text-slate-400">Oportunidade ativa</span>
                                    </span>
                                </div>
                                <span class="w-fit rounded-full bg-violet-50 px-[10px] py-[5px] text-[11px] font-black text-violet-700">{{ $oportunidade['etapa'] }}</span>
                                <span class="inline-flex items-center gap-[7px] text-[12px] font-black text-slate-700"><span class="{{ $criticidade }} h-[7px] w-[7px] rounded-full"></span>{{ $oportunidade['tempo'] }}</span>
                                <span class="text-[13px] font-black text-slate-950">{{ $formatarMoeda($oportunidade['valor']) }}</span>
                                <span class="dash-access-pill">
                                    Acessar
                                    <i data-lucide="arrow-right" class="h-[13px] w-[13px]"></i>
                                </span>
                            </a>
                        @empty
                            <div class="dash-empty">Nenhuma oportunidade em andamento.</div>
                        @endforelse
                    </div>
                </article>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }

            const greetingElement = document.querySelector('[data-dashboard-greeting]');

            const getGreetingByCurrentTime = (date = new Date()) => {
                const hour = date.getHours();

                if (hour >= 5 && hour < 12) {
                    return 'Bom dia';
                }

                if (hour >= 12 && hour < 18) {
                    return 'Boa tarde';
                }

                return 'Boa noite';
            };

            const updateDashboardGreeting = () => {
                if (!greetingElement) {
                    return;
                }

                const userName = greetingElement.dataset.userName || 'Corretor';
                greetingElement.textContent = `${getGreetingByCurrentTime()}, ${userName}!`;
            };

            updateDashboardGreeting();
            window.setInterval(updateDashboardGreeting, 60 * 1000);

            const periodForm = document.querySelector('[data-dashboard-period-form]');
            const periodInputs = document.querySelectorAll('[data-dashboard-period-input]');

            periodInputs.forEach((input) => {
                input.addEventListener('change', () => {
                    const inicio = periodForm.querySelector('input[name="inicio"]');
                    const fim = periodForm.querySelector('input[name="fim"]');

                    if (inicio.value && fim.value && inicio.value > fim.value) {
                        if (input.name === 'inicio') {
                            fim.value = inicio.value;
                        } else {
                            inicio.value = fim.value;
                        }
                    }

                    periodForm.requestSubmit();
                });
            });

            if (!window.ApexCharts) {
                return;
            }

            document.querySelectorAll('[data-sparkline]').forEach((element) => {
                const values = JSON.parse(element.dataset.values || '[]').map((value) => Number(value) || 0);

                if (!values.some((value) => value > 0)) {
                    return;
                }

                const color = element.dataset.color || '#2563eb';
                const label = element.dataset.label || 'Indicador';

                new ApexCharts(element, {
                    chart: {
                        type: 'area',
                        height: 86,
                        sparkline: { enabled: true },
                        animations: { enabled: true, speed: 680, animateGradually: { enabled: true, delay: 70 } },
                        toolbar: { show: false },
                        dropShadow: { enabled: true, top: 8, left: 0, blur: 10, opacity: .22, color },
                    },
                    series: [{ name: label, data: values }],
                    stroke: { curve: 'smooth', width: 4, lineCap: 'round' },
                    colors: [color],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            type: 'vertical',
                            opacityFrom: .44,
                            opacityTo: .02,
                            stops: [0, 76, 100],
                        },
                    },
                    markers: {
                        size: 0,
                        strokeWidth: 3,
                        discrete: [{
                            seriesIndex: 0,
                            dataPointIndex: values.length - 1,
                            fillColor: '#ffffff',
                            strokeColor: color,
                            size: 5,
                        }],
                    },
                    tooltip: {
                        enabled: true,
                        theme: 'light',
                        marker: { show: false },
                        x: { show: false },
                        y: {
                            title: { formatter: () => '' },
                            formatter: (value) => Number(value).toLocaleString('pt-BR'),
                        },
                    },
                }).render();
            });

            const receitaChart = document.querySelector('#receita-chart');
            if (receitaChart) {
                const values = JSON.parse(receitaChart.dataset.values || '[]').map((value) => Number(value) || 0);
                const labels = JSON.parse(receitaChart.dataset.labels || '[]');

                if (values.some((value) => value > 0)) {
                    new ApexCharts(receitaChart, {
                        chart: { type: 'area', height: 206, animations: { enabled: true, speed: 560 }, toolbar: { show: false }, zoom: { enabled: false } },
                        series: [{ name: 'Receita de vendas', data: values }],
                        colors: ['#2563eb'],
                        stroke: { curve: 'smooth', width: 3.5, lineCap: 'round' },
                        fill: { type: 'gradient', gradient: { opacityFrom: .34, opacityTo: .02, stops: [0, 94] } },
                        dataLabels: { enabled: false },
                        grid: { borderColor: '#edf2f7', strokeDashArray: 4, padding: { left: 0, right: 8, top: 8, bottom: -8 } },
                        xaxis: { categories: labels, labels: { style: { colors: '#64748b', fontSize: '10px', fontWeight: 700 } }, axisTicks: { show: false }, axisBorder: { show: false } },
                        yaxis: { labels: { style: { colors: '#64748b', fontSize: '10px', fontWeight: 700 }, formatter: (value) => `R$ ${Math.round(value / 1000)}k` }, min: 0, tickAmount: 4 },
                        markers: { size: 4, colors: ['#fff'], strokeColors: '#2563eb', strokeWidth: 3, hover: { size: 7 } },
                        tooltip: { theme: 'light', y: { formatter: (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value) } },
                    }).render();
                }
            }

            const planChart = document.querySelector('#plan-chart');
            if (planChart) {
                const values = JSON.parse(planChart.dataset.values || '[]').map((value) => Number(value) || 0);
                const labels = JSON.parse(planChart.dataset.labels || '[]');

                if (values.some((value) => value > 0)) {
                    new ApexCharts(planChart, {
                        chart: { type: 'donut', height: 164, animations: { enabled: true, speed: 560 }, toolbar: { show: false } },
                        series: values,
                        labels,
                        colors: ['#2563eb', '#2dd4bf', '#f59e0b', '#7c3aed', '#94a3b8'],
                        stroke: { width: 5, colors: ['#ffffff'] },
                        legend: { show: false },
                        dataLabels: { enabled: false },
                        plotOptions: {
                            pie: {
                                expandOnClick: false,
                                donut: {
                                    size: '76%',
                                    labels: {
                                        show: true,
                                        name: { show: true, offsetY: 18, color: '#071631', fontSize: '11px', fontWeight: 800 },
                                        value: { show: true, offsetY: -8, color: '#071631', fontSize: '23px', fontWeight: 900, formatter: (value) => `${Math.round(value)}%` },
                                        total: { show: true, showAlways: true, label: labels[0] || '', fontSize: '11px', fontWeight: 800, color: '#071631', formatter: () => `${Math.round(values[0] || 0)}%` },
                                    },
                                },
                            },
                        },
                        tooltip: { y: { formatter: (value) => `${Math.round(value)}%` } },
                    }).render();
                }
            }
        });
    </script>
</x-layouts.app>
