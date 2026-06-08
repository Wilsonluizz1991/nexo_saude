<?php

namespace App\Services\Dashboard;

use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\CorretorMetaMensal;
use App\Models\Indicacao;
use App\Models\PreCadastro;
use App\Models\Proposta;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardMetricsService
{
    private array $statusContratoAtivo = ['ativo', 'vigente', 'contrato_vigente', 'fechado'];

    public function forUser(int $userId, ?CarbonInterface $inicio = null, ?CarbonInterface $fim = null): array
    {
        $limites = $this->periodoDisponivel($userId);
        $inicioPeriodo = ($inicio ?: now()->startOfMonth())->copy()->startOfDay();
        $fimPeriodo = ($fim ?: now())->copy()->endOfDay();

        if ($inicioPeriodo->lt($limites['min'])) {
            $inicioPeriodo = $limites['min']->copy()->startOfDay();
        }

        if ($fimPeriodo->gt($limites['max'])) {
            $fimPeriodo = $limites['max']->copy()->endOfDay();
        }

        if ($inicioPeriodo->gt($fimPeriodo)) {
            $inicioPeriodo = $fimPeriodo->copy()->startOfDay();
        }

        $inicioAnterior = $inicioPeriodo->copy()->subMonthNoOverflow()->startOfDay();
        $fimAnterior = $fimPeriodo->copy()->subMonthNoOverflow()->endOfDay();

        if ($inicioAnterior->gt($fimAnterior)) {
            $inicioAnterior = $fimAnterior->copy()->startOfDay();
        }

        $indicacoesBase = Indicacao::where('user_id', $userId);
        $contratosBase = Contrato::where('usuario_id', $userId);

        $contratosPeriodo = $this->contratosNoPeriodo($userId, $inicioPeriodo, $fimPeriodo);
        $contratosPeriodoAnterior = $this->contratosNoPeriodo($userId, $inicioAnterior, $fimAnterior);

        $comissaoRealizadaPeriodo = $this->comissaoRealizadaNoPeriodo($userId, $inicioPeriodo, $fimPeriodo);
        $comissaoRealizadaAnterior = $this->comissaoRealizadaNoPeriodo($userId, $inicioAnterior, $fimAnterior);
        $receitaPeriodo = $this->receitaNoPeriodo($userId, $inicioPeriodo, $fimPeriodo);

        $vidasPeriodo = (int) $contratosPeriodo->sum('quantidade_vidas');
        $vidasAnterior = (int) $contratosPeriodoAnterior->sum('quantidade_vidas');
        $contratosFechadosPeriodo = $contratosPeriodo->count();
        $contratosFechadosAnterior = $contratosPeriodoAnterior->count();
        $leadsCaptados = (clone $indicacoesBase)->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])->count();
        $leadsCaptadosAnterior = (clone $indicacoesBase)->whereBetween('created_at', [$inicioAnterior, $fimAnterior])->count();
        $funil = $this->funil($userId, $inicioPeriodo, $fimPeriodo, $inicioAnterior, $fimAnterior);
        $alertasDashboard = $this->alertasDashboard($userId, $inicioPeriodo, $fimPeriodo);

        $alertasPendentes = Alerta::where('user_id', $userId)
            ->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])
            ->where(function ($query) {
                $query->where('status', 'pendente')->orWhere('lido', false);
            })
            ->count();

        return [
            'periodo' => $inicioPeriodo->format('d/m/Y').' - '.$fimPeriodo->format('d/m/Y'),
            'periodoFiltro' => [
                'inicio' => $inicioPeriodo->toDateString(),
                'fim' => $fimPeriodo->toDateString(),
                'min' => $limites['min']->toDateString(),
                'max' => $limites['max']->toDateString(),
            ],
            'metricas' => [
                'comissao_prevista' => [
                    'titulo' => 'Comissão Realizada',
                    'valor' => $this->moeda($comissaoRealizadaPeriodo),
                    'valor_bruto' => $comissaoRealizadaPeriodo,
                    'variacao' => $this->variacaoPercentual($comissaoRealizadaPeriodo, $comissaoRealizadaAnterior),
                    'serie' => $this->serieComparativa($comissaoRealizadaAnterior, $this->serieComissaoRealizada($userId, $inicioPeriodo, $fimPeriodo)),
                    'cor' => 'emerald',
                    'icone' => 'dollar-sign',
                    'empty' => $comissaoRealizadaPeriodo <= 0,
                ],
                'vidas_fechadas' => [
                    'titulo' => 'Vidas Fechadas',
                    'valor' => $vidasPeriodo,
                    'variacao' => $this->variacaoPercentual($vidasPeriodo, $vidasAnterior),
                    'serie' => $this->serieComparativa($vidasAnterior, $this->serieContratos($userId, 'quantidade_vidas', true, $inicioPeriodo, $fimPeriodo)),
                    'cor' => 'blue',
                    'icone' => 'users',
                    'empty' => $vidasPeriodo <= 0,
                ],
                'contratos_fechados' => [
                    'titulo' => 'Contratos Fechados',
                    'valor' => $contratosFechadosPeriodo,
                    'variacao' => $this->variacaoPercentual($contratosFechadosPeriodo, $contratosFechadosAnterior),
                    'serie' => $this->serieComparativa($contratosFechadosAnterior, $this->serieContratos($userId, 'id', false, $inicioPeriodo, $fimPeriodo)),
                    'cor' => 'violet',
                    'icone' => 'clipboard-check',
                    'empty' => $contratosFechadosPeriodo <= 0,
                ],
                'leads_captados' => [
                    'titulo' => 'Leads Captados',
                    'valor' => $leadsCaptados,
                    'variacao' => $this->variacaoPercentual($leadsCaptados, $leadsCaptadosAnterior),
                    'serie' => $this->serieComparativa($leadsCaptadosAnterior, $this->serieLeadsCaptados($userId, $inicioPeriodo, $fimPeriodo)),
                    'cor' => 'orange',
                    'icone' => 'flame',
                    'empty' => $leadsCaptados <= 0,
                ],
            ],
            'funil' => $funil,
            'alertasDashboard' => $alertasDashboard,
            'alertasTotal' => $alertasPendentes,
            'receitaTotal' => $receitaPeriodo,
            'receitaSerie' => $this->serieReceitaMensal($userId, $inicioPeriodo, $fimPeriodo),
            'receitaPorOperadora' => $this->receitaPorOperadora($userId, $inicioPeriodo, $fimPeriodo),
            'receitaPorTipoPlano' => $this->receitaPorTipoPlano($userId, $inicioPeriodo, $fimPeriodo),
            'oportunidades' => $this->oportunidades($userId, $inicioPeriodo, $fimPeriodo),
            'resumo' => [
                'leads_mes' => (clone $indicacoesBase)->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])->count(),
                'leads_mes_anterior' => (clone $indicacoesBase)->whereBetween('created_at', [$inicioAnterior, $fimAnterior])->count(),
                'contratos_mes' => $contratosFechadosPeriodo,
                'pre_cadastros_parados' => $alertasDashboard[0]['total'],
                'cotacoes_sem_retorno' => $alertasDashboard[2]['total'],
                'documentos_pendentes' => $alertasDashboard[3]['total'],
                'comissao_prevista' => $comissaoRealizadaPeriodo,
                'receita' => $receitaPeriodo,
                'total_indicacoes' => (clone $indicacoesBase)->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])->count(),
                'total_contratos' => (clone $contratosBase)->whereIn('status', $this->statusContratoAtivo)->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])->count(),
            ],
        ];
    }

    public function periodoDisponivel(int $userId): array
    {
        $datas = collect([
            Indicacao::where('user_id', $userId)->min('created_at'),
            Proposta::whereHas('indicacao', fn ($query) => $query->where('user_id', $userId))->min('created_at'),
            PreCadastro::whereHas('indicacao', fn ($query) => $query->where('user_id', $userId))->min('created_at'),
            Cliente::where('user_id', $userId)->min('created_at'),
            Contrato::where('usuario_id', $userId)->min('created_at'),
            Alerta::where('user_id', $userId)->min('created_at'),
        ])->filter();

        $min = $datas->isNotEmpty()
            ? Carbon::parse($datas->min())->startOfDay()
            : now()->startOfMonth();

        return [
            'min' => $min,
            'max' => now()->endOfDay(),
        ];
    }

    private function contratosNoPeriodo(int $userId, CarbonInterface $inicio, CarbonInterface $fim): Collection
    {
        return Contrato::where('usuario_id', $userId)
            ->whereIn('status', $this->statusContratoAtivo)
            ->whereBetween('created_at', [$inicio, $fim])
            ->get();
    }

    private function comissaoRealizadaNoPeriodo(int $userId, CarbonInterface $inicio, CarbonInterface $fim): float
    {
        return (float) CorretorMetaMensal::where('user_id', $userId)
            ->whereBetween('mes_referencia', [
                $inicio->copy()->startOfMonth()->toDateString(),
                $fim->copy()->endOfMonth()->toDateString(),
            ])
            ->sum('comissao_realizada');
    }

    private function receitaNoPeriodo(int $userId, CarbonInterface $inicio, CarbonInterface $fim): float
    {
        return (float) Contrato::where('usuario_id', $userId)
            ->whereIn('status', $this->statusContratoAtivo)
            ->whereBetween('created_at', [$inicio, $fim])
            ->sum('valor_mensal');
    }

    private function funil(int $userId, CarbonInterface $inicio, CarbonInterface $fim, CarbonInterface $inicioAnterior, CarbonInterface $fimAnterior): array
    {
        $atual = $this->funilNoPeriodo($userId, $inicio, $fim);
        $anterior = $this->funilNoPeriodo($userId, $inicioAnterior, $fimAnterior);

        $atual['lead_cotacao'] = $this->percentual($atual['cotacoes'], $atual['leads']);
        $atual['cotacao_pre_cadastro'] = $this->percentual($atual['pre_cadastros'], $atual['cotacoes']);
        $atual['pre_cadastro_implantacao'] = $this->percentual($atual['implantacoes'], $atual['pre_cadastros']);
        $atual['pre_cadastro_contrato'] = $atual['pre_cadastro_implantacao'];
        $atual['variacoes'] = [
            'leads' => $this->variacaoPercentual($atual['leads'], $anterior['leads']),
            'cotacoes' => $this->variacaoPercentual($atual['cotacoes'], $anterior['cotacoes']),
            'pre_cadastros' => $this->variacaoPercentual($atual['pre_cadastros'], $anterior['pre_cadastros']),
            'implantacoes' => $this->variacaoPercentual($atual['implantacoes'], $anterior['implantacoes']),
            'contratos' => $this->variacaoPercentual($atual['contratos'], $anterior['contratos']),
        ];
        $atual['principal_perda'] = $this->principalPerda($atual);

        return $atual;
    }

    private function funilNoPeriodo(int $userId, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        $base = Indicacao::where('user_id', $userId)->whereBetween('created_at', [$inicio, $fim]);

        return [
            'leads' => (clone $base)->count(),
            'cotacoes' => (clone $base)->whereIn('etapa', ['propostas', 'pre_cadastros', 'implantacoes', 'clientes', 'carteira'])->count(),
            'pre_cadastros' => (clone $base)->whereIn('etapa', ['pre_cadastros', 'implantacoes', 'clientes', 'carteira'])->count(),
            'implantacoes' => (clone $base)->where('etapa', 'implantacoes')->count(),
            'contratos' => (clone $base)->whereIn('etapa', ['clientes', 'carteira'])->count(),
        ];
    }

    private function principalPerda(array $funil): ?string
    {
        $perdas = [
            'Entre Leads e Cotações' => max(0, ($funil['leads'] ?? 0) - ($funil['cotacoes'] ?? 0)),
            'Entre Cotações e Pré-cadastro' => max(0, ($funil['cotacoes'] ?? 0) - ($funil['pre_cadastros'] ?? 0)),
            'Entre Pré-cadastro e Implantações' => max(0, ($funil['pre_cadastros'] ?? 0) - ($funil['implantacoes'] ?? 0)),
        ];

        arsort($perdas);

        $maior = reset($perdas);

        return $maior > 0 ? array_key_first($perdas) : null;
    }

    private function leadsQuentes(int $userId, CarbonInterface $inicio, CarbonInterface $fim): int
    {
        return Indicacao::where('user_id', $userId)
            ->withCount('propostas')
            ->withExists('preCadastro')
            ->whereIn('etapa', ['lead', 'propostas', 'pre_cadastros'])
            ->whereBetween('created_at', [$inicio, $fim])
            ->get()
            ->filter(function (Indicacao $indicacao) use ($fim) {
                $score = 0;
                $score += $indicacao->created_at?->gte($fim->copy()->subDays(7)) ? 2 : 0;
                $score += $indicacao->updated_at?->gte($fim->copy()->subDays(3)) ? 1 : 0;
                $score += $indicacao->propostas_count > 0 ? 2 : 0;
                $score += $indicacao->pre_cadastro_exists ? 2 : 0;
                $score += in_array($indicacao->status, ['proposta_enviada', 'aguardando_envio', 'documentacao_em_analise'], true) ? 1 : 0;
                $score += (int) $indicacao->quantidade_vidas >= 3 ? 1 : 0;

                return $score >= 3;
            })
            ->count();
    }

    private function alertasDashboard(int $userId, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        $preCadastrosParados = PreCadastro::whereHas('indicacao', fn ($query) => $query->where('user_id', $userId))
            ->whereIn('status', ['aguardando_envio', 'aberto', 'em_preenchimento', 'documentacao_pendente', 'correcao_solicitada'])
            ->whereBetween('created_at', [$inicio, $fim])
            ->where('updated_at', '<=', $fim->copy()->subHours(48))
            ->count();

        $leadsAguardandoContato = Indicacao::where('user_id', $userId)
            ->where('etapa', 'lead')
            ->whereIn('status', ['nova', 'novo', 'em_contato', 'aguardando_contato'])
            ->whereBetween('created_at', [$inicio, $fim])
            ->where('updated_at', '<=', $fim->copy()->subDay())
            ->count();

        $cotacoesSemRetorno = Indicacao::where('user_id', $userId)
            ->where('etapa', 'propostas')
            ->where('status', 'proposta_enviada')
            ->whereBetween('created_at', [$inicio, $fim])
            ->where('updated_at', '<=', $fim->copy()->subDays(7))
            ->count();

        $documentosPendentes = Indicacao::where('user_id', $userId)
            ->whereBetween('created_at', [$inicio, $fim])
            ->whereHas('preCadastro.documentosObrigatorios', fn ($query) => $query->whereIn('status', ['pendente', 'recusado', 'corrigir']))
            ->count();

        return [
            [
                'titulo' => 'Pré-cadastro parado há mais de 48h',
                'total' => $preCadastrosParados,
                'descricao' => $preCadastrosParados === 1 ? '1 cliente' : $preCadastrosParados.' clientes',
                'icone' => 'file-warning',
                'cor' => 'red',
                'url' => route('paginas.simples', 'pre-cadastros'),
            ],
            [
                'titulo' => 'Leads aguardando contato',
                'total' => $leadsAguardandoContato,
                'descricao' => $leadsAguardandoContato === 1 ? '1 lead' : $leadsAguardandoContato.' leads',
                'icone' => 'flame',
                'cor' => 'orange',
                'url' => route('indicacoes.index'),
            ],
            [
                'titulo' => 'Cotações sem retorno há mais de 7 dias',
                'total' => $cotacoesSemRetorno,
                'descricao' => $cotacoesSemRetorno === 1 ? '1 cotação' : $cotacoesSemRetorno.' cotações',
                'icone' => 'clock',
                'cor' => 'amber',
                'url' => route('paginas.simples', 'propostas'),
            ],
            [
                'titulo' => 'Documentação com pendência',
                'total' => $documentosPendentes,
                'descricao' => $documentosPendentes === 1 ? '1 pré-cadastro' : $documentosPendentes.' pré-cadastros',
                'icone' => 'file-text',
                'cor' => 'blue',
                'url' => route('paginas.simples', 'pre-cadastros'),
            ],
        ];
    }

    private function oportunidades(int $userId, CarbonInterface $inicio, CarbonInterface $fim): Collection
    {
        return Indicacao::where('user_id', $userId)
            ->select(['id', 'nome_cliente', 'tipo_plano', 'quantidade_vidas', 'etapa', 'status', 'updated_at'])
            ->withMax('propostas as maior_valor_proposta', 'valor_mensal')
            ->whereIn('etapa', ['lead', 'propostas', 'pre_cadastros', 'implantacoes'])
            ->whereBetween('created_at', [$inicio, $fim])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn (Indicacao $indicacao) => [
                'id' => $indicacao->id,
                'nome' => $indicacao->nome_cliente,
                'etapa' => $this->nomeEtapa($indicacao->etapa),
                'tempo' => max(1, (int) $indicacao->updated_at->diffInHours(now())).'h',
                'valor' => (float) ($indicacao->maior_valor_proposta ?? 0),
                'criticidade' => $indicacao->updated_at->lte(now()->subHours(48)) ? 'alta' : ($indicacao->updated_at->lte(now()->subHours(24)) ? 'media' : 'baixa'),
                'url' => route('indicacoes.show', $indicacao),
            ]);
    }

    private function serieContratos(int $userId, string $campo, bool $somar, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        return $this->seriePorPeriodo($inicio, $fim, function (CarbonInterface $inicioItem, CarbonInterface $fimItem) use ($userId, $campo, $somar) {
            $query = Contrato::where('usuario_id', $userId)
                ->whereIn('status', $this->statusContratoAtivo)
                ->whereBetween('created_at', [$inicioItem, $fimItem]);

            return $somar ? (float) $query->sum($campo) : (float) $query->count();
        });
    }

    private function serieComissaoRealizada(int $userId, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        return $this->seriePorPeriodo($inicio, $fim, fn (CarbonInterface $inicioItem, CarbonInterface $fimItem) => $this->comissaoRealizadaNoPeriodo($userId, $inicioItem, $fimItem));
    }

    private function serieLeadsQuentes(int $userId, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        return $this->seriePorPeriodo($inicio, $fim, fn (CarbonInterface $inicioItem, CarbonInterface $fimItem) => (float) $this->leadsQuentes($userId, $inicioItem, $fimItem));
    }

    private function serieLeadsCaptados(int $userId, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        return $this->seriePorPeriodo($inicio, $fim, fn (CarbonInterface $inicioItem, CarbonInterface $fimItem) => (float) Indicacao::where('user_id', $userId)
            ->whereBetween('created_at', [$inicioItem, $fimItem])
            ->count());
    }

    private function serieReceitaMensal(int $userId, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        $meses = $this->mesesDoPeriodo($inicio, $fim);

        return $meses->map(function (CarbonInterface $mes) use ($userId, $inicio, $fim) {
            $inicioMes = $mes->copy()->startOfMonth()->max($inicio);
            $fimMes = $mes->copy()->endOfMonth()->min($fim);

            return [
                'label' => ucfirst($mes->translatedFormat('M')),
                'valor' => (float) Contrato::where('usuario_id', $userId)
                    ->whereIn('status', $this->statusContratoAtivo)
                    ->whereNotNull('valor_mensal')
                    ->whereBetween('created_at', [$inicioMes, $fimMes])
                    ->sum('valor_mensal'),
            ];
        })->values()->all();
    }

    private function receitaPorOperadora(int $userId, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        $contratos = Contrato::where('usuario_id', $userId)
            ->whereIn('status', $this->statusContratoAtivo)
            ->whereNotNull('valor_mensal')
            ->whereBetween('created_at', [$inicio, $fim])
            ->with('operadora:id,nome')
            ->get();

        return $this->agruparReceita($contratos, fn (Contrato $contrato) => $contrato->operadora?->nome ?: 'Outras');
    }

    private function receitaPorTipoPlano(int $userId, CarbonInterface $inicio, CarbonInterface $fim): array
    {
        $contratos = Contrato::where('usuario_id', $userId)
            ->whereIn('status', $this->statusContratoAtivo)
            ->whereNotNull('valor_mensal')
            ->whereBetween('created_at', [$inicio, $fim])
            ->get();

        return $this->agruparReceita($contratos, fn (Contrato $contrato) => $this->normalizarTipoPlano($contrato->tipo_contrato), false);
    }

    private function agruparReceita(Collection $contratos, callable $resolverNome, bool $formatarMoeda = true): array
    {
        $dados = $contratos
            ->groupBy($resolverNome)
            ->map(fn (Collection $items, string $nome) => [
                'nome' => $nome,
                'valor' => (float) $items->sum('valor_mensal'),
            ])
            ->filter(fn (array $item) => $item['valor'] > 0)
            ->sortByDesc('valor')
            ->take(5)
            ->values();

        $totalGeral = (float) $dados->sum('valor');

        if ($totalGeral <= 0) {
            return [];
        }

        return $dados->map(function (array $item) use ($totalGeral, $formatarMoeda) {
            $resultado = [
                'nome' => $item['nome'],
                'valor' => $item['valor'],
                'percentual' => (int) round(($item['valor'] / $totalGeral) * 100),
            ];

            if ($formatarMoeda) {
                $resultado['valor_formatado'] = $this->moeda($item['valor']);
            }

            return $resultado;
        })->values()->all();
    }

    private function seriePorPeriodo(CarbonInterface $inicio, CarbonInterface $fim, callable $resolver): array
    {
        $dias = max(1, (int) floor($inicio->diffInDays($fim)) + 1);
        $pontos = min(6, $dias);
        $tamanho = (int) ceil($dias / $pontos);

        return collect(range(0, $pontos - 1))->map(function (int $indice) use ($inicio, $fim, $tamanho, $resolver) {
            $inicioItem = $inicio->copy()->addDays($indice * $tamanho)->startOfDay();
            $fimItem = $inicioItem->copy()->addDays($tamanho - 1)->endOfDay()->min($fim);

            if ($inicioItem->gt($fim)) {
                return 0.0;
            }

            return (float) $resolver($inicioItem, $fimItem);
        })->values()->all();
    }

    private function serieComparativa(int|float $valorAnterior, array $valoresPeriodo): array
    {
        $acumulado = 0.0;
        $serie = collect($valoresPeriodo)
            ->map(function (int|float $valor) use (&$acumulado) {
                $acumulado += (float) $valor;

                return round($acumulado, 2);
            })
            ->values();

        return collect([(float) $valorAnterior])
            ->merge($serie)
            ->values()
            ->all();
    }

    private function mesesDoPeriodo(CarbonInterface $inicio, CarbonInterface $fim): Collection
    {
        $meses = collect();
        $cursor = $inicio->copy()->startOfMonth();

        while ($cursor->lte($fim)) {
            $meses->push($cursor->copy());
            $cursor->addMonthNoOverflow();
        }

        return $meses;
    }

    private function percentual(int|float $parte, int|float $total): int
    {
        return $total > 0 ? (int) round(($parte / $total) * 100) : 0;
    }

    private function variacaoPercentual(int|float $atual, int|float $anterior): int
    {
        if ($anterior <= 0) {
            return $atual > 0 ? 100 : 0;
        }

        return (int) round((($atual - $anterior) / $anterior) * 100);
    }

    private function moeda(int|float $valor): string
    {
        return 'R$ '.number_format((float) $valor, 0, ',', '.');
    }

    private function nomeEtapa(?string $etapa): string
    {
        return match ($etapa) {
            'lead' => 'Lead',
            'propostas' => 'Cotação',
            'pre_cadastros' => 'Pré-cadastro',
            'implantacoes' => 'Implantação',
            'clientes', 'carteira' => 'Contrato',
            default => 'Operação',
        };
    }

    private function normalizarTipoPlano(?string $tipo): string
    {
        $tipo = trim((string) $tipo);

        if ($tipo === '') {
            return 'Outros';
        }

        return match (Str::ascii(mb_strtolower($tipo))) {
            'pme', 'empresarial' => 'PME',
            'adesao' => 'Adesão',
            'individual' => 'Individual',
            'familiar' => 'Familiar',
            default => ucfirst($tipo),
        };
    }
}
