<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\CorretorMetaMensal;
use App\Models\Indicacao;
use App\Models\Tarefa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class PaginaController extends Controller
{
    private const ITENS_POR_PAGINA = 5;
    public function agenda()
    {
        return $this->simples('agenda');
    }

    public function tarefas()
    {
        return $this->simples('tarefas');
    }

    public function alertas()
    {
        return $this->simples('alertas');
    }

    public function simples(string $pagina)
    {
        $permitidas = ['agenda', 'propostas', 'pre-cadastros', 'implantacoes', 'clientes', 'carteira', 'tarefas', 'alertas'];
        abort_unless(in_array($pagina, $permitidas, true), 404);

        $userId = auth()->id();
        $carregarCarteira = in_array($pagina, ['carteira', 'clientes'], true);

        $clientesTodos = $pagina === 'carteira'
            ? Cliente::where('user_id', $userId)
                ->select(['id', 'indicacao_id', 'user_id', 'nome', 'email', 'telefone', 'inicio_vigencia', 'valor_mensal', 'status', 'created_at'])
                ->with([
                    'contratos:id,cliente_id,usuario_id,status,quantidade_vidas,renovacao_em,reajuste_em,created_at',
                    'dependentes:id,cliente_id',
                ])
                ->latest()
                ->get()
            : collect();

        $clientes = $carregarCarteira
            ? Cliente::where('user_id', $userId)
                ->select(['id', 'indicacao_id', 'user_id', 'nome', 'email', 'telefone', 'inicio_vigencia', 'valor_mensal', 'status', 'created_at'])
                ->with([
                    'contratos:id,cliente_id,usuario_id,status,quantidade_vidas,renovacao_em,reajuste_em,created_at',
                    'dependentes:id,cliente_id',
                ])
                ->latest()
                ->paginate(self::ITENS_POR_PAGINA)
                ->withQueryString()
            : $this->paginarCollection(collect());

        $contratos = $clientesTodos->flatMap(fn (Cliente $cliente) => $cliente->contratos);
        $dadosCarteira = $pagina === 'carteira'
            ? $this->dadosCarteiraEstrategica($clientesTodos, $contratos)
            : $this->dadosCarteiraVazia();

        return view('interno.paginas.simples', array_merge([
            'pagina' => $pagina,
            'indicacoes' => $this->indicacoesPorPagina($pagina),
            'clientes' => $clientes,
            'metricasCarteira' => [
                'renovacoes_proximas' => $contratos->filter(fn ($contrato) => $contrato->renovacao_em && $contrato->renovacao_em->between(now(), now()->addDays(60)))->count(),
                'reajustes_proximos' => $contratos->filter(fn ($contrato) => $contrato->reajuste_em && $contrato->reajuste_em->between(now(), now()->addDays(60)))->count(),
                'dependentes' => $clientesTodos->sum(fn (Cliente $cliente) => $cliente->dependentes->count()),
            ],
            'tarefas' => Tarefa::where('user_id', $userId)->latest()->paginate(self::ITENS_POR_PAGINA)->withQueryString(),
            'tarefasHoje' => Tarefa::where('user_id', $userId)
                ->whereDate('vencimento', today())
                ->latest()
                ->paginate(self::ITENS_POR_PAGINA)
                ->withQueryString(),
            'alertas' => Alerta::where('user_id', $userId)->latest()->paginate(self::ITENS_POR_PAGINA)->withQueryString(),
        ], $dadosCarteira));
    }

    public function salvarMetaMensal(Request $request): RedirectResponse
    {
        $acao = $request->input('tipo_acao', 'salvar_meta');

        $mesReferencia = now()->startOfMonth()->toDateString();

        $metaMensal = CorretorMetaMensal::where('user_id', auth()->id())
            ->whereDate('mes_referencia', $mesReferencia)
            ->first();

        if (! $metaMensal) {
            $metaMensal = new CorretorMetaMensal([
                'user_id' => auth()->id(),
                'mes_referencia' => $mesReferencia,
                'comissao_realizada' => 0,
            ]);
        }

        $comissaoAntes = (float) ($metaMensal->comissao_realizada ?? 0);
        $metaAntes = (float) ($metaMensal->meta_comissao ?? 0);
        $jaTinhaBatidoMeta = $metaAntes > 0 && $comissaoAntes >= $metaAntes;

        if ($acao === 'adicionar_comissao') {
            $dados = [
                'comissao_lancamento' => $this->normalizarValorMonetario($request->input('comissao_lancamento')),
            ];

            Validator::make($dados, [
                'comissao_lancamento' => ['required', 'numeric', 'min:0.01'],
            ], [
                'comissao_lancamento.required' => 'Informe o valor da comissao desta venda.',
                'comissao_lancamento.numeric' => 'Informe uma comissao valida.',
                'comissao_lancamento.min' => 'Informe uma comissao maior que zero.',
            ])->validate();

            $metaMensal->comissao_realizada = $comissaoAntes + (float) $dados['comissao_lancamento'];
            $mensagem = 'Comissao adicionada ao acompanhamento mensal.';
        } else {
            $dados = [
                'meta_comissao' => $this->normalizarValorMonetario($request->input('meta_comissao')),
            ];

            Validator::make($dados, [
                'meta_comissao' => ['nullable', 'numeric', 'min:0'],
            ], [
                'meta_comissao.numeric' => 'Informe uma meta de comissao valida.',
            ])->validate();

            $metaMensal->meta_comissao = $dados['meta_comissao'];
            $mensagem = 'Meta mensal salva.';
        }

        $metaMensal->save();

        $metaDepois = (float) ($metaMensal->meta_comissao ?? 0);
        $comissaoDepois = (float) ($metaMensal->comissao_realizada ?? 0);
        $metaAtingidaAgora = $metaDepois > 0 && $comissaoDepois >= $metaDepois && ! $jaTinhaBatidoMeta;

        return redirect()
            ->route('paginas.simples', 'carteira')
            ->with('status', $mensagem)
            ->with('meta_atingida', $metaAtingidaAgora);

        /*
        Validator::make($dados, [
            'meta_comissao' => ['nullable', 'numeric', 'min:0'],
            'comissao_realizada' => ['nullable', 'numeric', 'min:0'],
        ], [
            'meta_comissao.numeric' => 'Informe uma meta de comissão válida.',
            'comissao_realizada.numeric' => 'Informe uma comissão realizada válida.',
        ])->validate();

        CorretorMetaMensal::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'mes_referencia' => now()->startOfMonth()->toDateString(),
            ],
            $dados
        );

        return redirect()
            ->route('paginas.simples', 'carteira')
            ->with('status', 'Acompanhamento mensal salvo.');
        */
    }

    public function concluirTarefa(Tarefa $tarefa): RedirectResponse
    {
        abort_unless($tarefa->user_id === auth()->id(), 403);

        $tarefa->update(['status' => 'concluida']);

        return back()->with('status', 'Tarefa concluída.');
    }

    public function resolverAlerta(Alerta $alerta): RedirectResponse
    {
        abort_unless($alerta->user_id === auth()->id(), 403);

        $this->marcarAlertaComoLido($alerta);

        return back()->with('status', 'Alerta marcado como resolvido.');
    }

    public function abrirAlerta(Alerta $alerta): RedirectResponse
    {
        abort_unless($alerta->user_id === auth()->id(), 403);

        $this->marcarAlertaComoLido($alerta);

        if ($alerta->pre_cadastro_id) {
            return redirect()->route('pre-cadastros.show', $alerta->pre_cadastro_id);
        }

        if ($alerta->indicacao_id) {
            return redirect()->route('indicacoes.show', $alerta->indicacao_id);
        }

        if ($alerta->cliente_id) {
            return redirect()->route('clientes.show', $alerta->cliente_id);
        }

        return redirect()->route('alertas.index');
    }

    private function indicacoesPorPagina(string $pagina)
    {
        $query = Indicacao::where('user_id', auth()->id())->with('preCadastro', 'implantacao')->latest();

        return match ($pagina) {
            'propostas' => $query->where('etapa', 'propostas')->paginate(self::ITENS_POR_PAGINA)->withQueryString(),
            'pre-cadastros' => $query->where('etapa', 'pre_cadastros')->paginate(self::ITENS_POR_PAGINA)->withQueryString(),
            'implantacoes' => $query->where('etapa', 'implantacoes')->paginate(self::ITENS_POR_PAGINA)->withQueryString(),
            default => $query->paginate(self::ITENS_POR_PAGINA)->withQueryString(),
        };
    }

    private function paginarCollection(Collection $items, string $pageName = 'page', int $perPage = self::ITENS_POR_PAGINA): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => $pageName,
            ]
        );
    }

    private function marcarAlertaComoLido(Alerta $alerta): void
    {
        if ($alerta->lido && $alerta->status === 'lido') {
            return;
        }

        $alerta->update([
            'lido' => true,
            'status' => 'lido',
        ]);
    }

    private function dadosCarteiraEstrategica(Collection $clientes, Collection $contratos): array
    {
        $userId = auth()->id();
        $inicioMesAtual = now()->startOfMonth();
        $fimMesAtual = now()->endOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $fimMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        $leadsMesAtual = Indicacao::where('user_id', $userId)
            ->whereBetween('created_at', [$inicioMesAtual, $fimMesAtual])
            ->count();

        $leadsMesAnterior = Indicacao::where('user_id', $userId)
            ->whereBetween('created_at', [$inicioMesAnterior, $fimMesAnterior])
            ->count();

        $contratosMesAtual = $this->contratosFechadosNoPeriodo($inicioMesAtual, $fimMesAtual);
        $contratosMesAnterior = $this->contratosFechadosNoPeriodo($inicioMesAnterior, $fimMesAnterior);

        $contratosFechadosMesAtual = $contratosMesAtual->count();
        $contratosFechadosMesAnterior = $contratosMesAnterior->count();
        $vidasVendidasMesAtual = (int) $contratosMesAtual->sum('quantidade_vidas');
        $vidasVendidasMesAnterior = (int) $contratosMesAnterior->sum('quantidade_vidas');
        $taxaConversaoMesAtual = $leadsMesAtual > 0
            ? round(($contratosFechadosMesAtual / $leadsMesAtual) * 100, 1)
            : 0;
        $taxaConversaoMesAnterior = $leadsMesAnterior > 0
            ? round(($contratosFechadosMesAnterior / $leadsMesAnterior) * 100, 1)
            : 0;

        $metaAtual = CorretorMetaMensal::where('user_id', $userId)
            ->whereDate('mes_referencia', $inicioMesAtual->toDateString())
            ->first();
        $metaAnterior = CorretorMetaMensal::where('user_id', $userId)
            ->whereDate('mes_referencia', $inicioMesAnterior->toDateString())
            ->first();

        $comissaoMesAtual = (float) ($metaAtual?->comissao_realizada ?? 0);
        $metaMesAtual = $metaAtual?->meta_comissao !== null ? (float) $metaAtual->meta_comissao : null;
        $comissaoMesAnterior = (float) ($metaAnterior?->comissao_realizada ?? 0);
        $metaMesAnterior = $metaAnterior?->meta_comissao !== null ? (float) $metaAnterior->meta_comissao : null;
        $percentualMetaMesAtual = $metaMesAtual && $metaMesAtual > 0
            ? round(($comissaoMesAtual / $metaMesAtual) * 100, 1)
            : 0;

        $comparacaoComissao = $this->compararComMesAnterior($comissaoMesAtual, $comissaoMesAnterior);
        $comparacaoContratos = $this->compararComMesAnterior($contratosFechadosMesAtual, $contratosFechadosMesAnterior);
        $comparacaoVidas = $this->compararComMesAnterior($vidasVendidasMesAtual, $vidasVendidasMesAnterior);
        $comparacaoLeads = $this->compararComMesAnterior($leadsMesAtual, $leadsMesAnterior);
        $comparacaoConversao = $this->compararComMesAnterior($taxaConversaoMesAtual, $taxaConversaoMesAnterior);

        return [
            'leadsMesAtual' => $leadsMesAtual,
            'contratosFechadosMesAtual' => $contratosFechadosMesAtual,
            'vidasVendidasMesAtual' => $vidasVendidasMesAtual,
            'taxaConversaoMesAtual' => $taxaConversaoMesAtual,
            'comissaoMesAtual' => $comissaoMesAtual,
            'metaMesAtual' => $metaMesAtual,
            'percentualMetaMesAtual' => $percentualMetaMesAtual,
            'leadsMesAnterior' => $leadsMesAnterior,
            'contratosFechadosMesAnterior' => $contratosFechadosMesAnterior,
            'vidasVendidasMesAnterior' => $vidasVendidasMesAnterior,
            'taxaConversaoMesAnterior' => $taxaConversaoMesAnterior,
            'comissaoMesAnterior' => $comissaoMesAnterior,
            'metaMesAnterior' => $metaMesAnterior,
            'percentualComparacaoComissao' => $comparacaoComissao['percentual'],
            'percentualComparacaoContratos' => $comparacaoContratos['percentual'],
            'percentualComparacaoVidas' => $comparacaoVidas['percentual'],
            'comparacaoComissao' => $comparacaoComissao,
            'comparacaoContratos' => $comparacaoContratos,
            'comparacaoVidas' => $comparacaoVidas,
            'comparacaoLeads' => $comparacaoLeads,
            'comparacaoConversao' => $comparacaoConversao,
            'analiseCarteira' => $this->analiseCarteira($comissaoMesAtual, $comissaoMesAnterior, $metaMesAtual),
            'clientesAtivosCarteira' => $clientes->where('status', 'ativo')->count(),
            'contratosAtivosCarteira' => $contratos->whereIn('status', ['ativo', 'vigente'])->count(),
        ];
    }

    private function dadosCarteiraVazia(): array
    {
        $comparacao = [
            'percentual' => 0,
            'direcao' => 'neutro',
                'texto' => 'Sem variação',
        ];

        return [
            'leadsMesAtual' => 0,
            'contratosFechadosMesAtual' => 0,
            'vidasVendidasMesAtual' => 0,
            'taxaConversaoMesAtual' => 0,
            'comissaoMesAtual' => 0,
            'metaMesAtual' => null,
            'percentualMetaMesAtual' => 0,
            'leadsMesAnterior' => 0,
            'contratosFechadosMesAnterior' => 0,
            'vidasVendidasMesAnterior' => 0,
            'taxaConversaoMesAnterior' => 0,
            'comissaoMesAnterior' => 0,
            'metaMesAnterior' => null,
            'percentualComparacaoComissao' => 0,
            'percentualComparacaoContratos' => 0,
            'percentualComparacaoVidas' => 0,
            'comparacaoComissao' => $comparacao,
            'comparacaoContratos' => $comparacao,
            'comparacaoVidas' => $comparacao,
            'comparacaoLeads' => $comparacao,
            'comparacaoConversao' => $comparacao,
            'analiseCarteira' => [
                'tipo' => 'neutro',
                'icone' => 'bi-bar-chart',
                'titulo' => 'Seu resultado está estável em relação ao mês anterior.',
                'descricao' => 'Defina sua meta mensal para acompanhar sua evolução comercial.',
            ],
            'clientesAtivosCarteira' => 0,
            'contratosAtivosCarteira' => 0,
        ];
    }

    private function contratosFechadosNoPeriodo($inicio, $fim): Collection
    {
        return Contrato::where('usuario_id', auth()->id())
            ->whereIn('status', ['vigente', 'ativo'])
            ->whereBetween('created_at', [$inicio, $fim])
            ->get();
    }

    private function compararComMesAnterior(float|int $atual, float|int $anterior): array
    {
        if ((float) $anterior === 0.0) {
            if ((float) $atual === 0.0) {
                return [
                    'percentual' => 0,
                    'direcao' => 'neutro',
                    'texto' => 'Sem variação',
                ];
            }

            return [
                'percentual' => 100,
                'direcao' => 'positivo',
                'texto' => '+100% vs mês anterior',
            ];
        }

        $percentual = round((($atual - $anterior) / $anterior) * 100, 1);
        $direcao = $percentual > 0 ? 'positivo' : ($percentual < 0 ? 'negativo' : 'neutro');
        $prefixo = $percentual > 0 ? '+' : '';

        return [
            'percentual' => $percentual,
            'direcao' => $direcao,
            'texto' => $percentual === 0.0 ? 'Sem variação' : "{$prefixo}{$percentual}% vs mês anterior",
        ];
    }

    private function analiseCarteira(float $comissaoAtual, float $comissaoAnterior, ?float $metaMensal): array
    {
        if ($metaMensal && $metaMensal > 0 && $comissaoAtual >= $metaMensal) {
            return [
                'tipo' => 'positivo',
                'icone' => 'bi-trophy',
                'titulo' => 'Meta mensal atingida. Excelente desempenho!',
                'descricao' => 'Continue acompanhando propostas pendentes para manter o ritmo comercial.',
            ];
        }

        if ($metaMensal && $metaMensal > 0) {
            $faltante = max(0, $metaMensal - $comissaoAtual);

            return [
                'tipo' => $comissaoAtual >= $comissaoAnterior ? 'positivo' : 'atencao',
                'icone' => $comissaoAtual >= $comissaoAnterior ? 'bi-graph-up-arrow' : 'bi-exclamation-triangle',
                'titulo' => $comissaoAtual > $comissaoAnterior
                    ? 'Seu mês está acima do mês anterior.'
                    : ($comissaoAtual < $comissaoAnterior
                        ? 'Seu mês está abaixo do mês anterior. Revise leads paradas e propostas pendentes.'
                        : 'Seu resultado está estável em relação ao mês anterior.'),
                'descricao' => 'Você está a R$ '.number_format($faltante, 2, ',', '.').' da sua meta mensal.',
            ];
        }

        if ($comissaoAtual > $comissaoAnterior) {
            return [
                'tipo' => 'positivo',
                'icone' => 'bi-graph-up-arrow',
                'titulo' => 'Seu mês está acima do mês anterior.',
                'descricao' => 'Defina sua meta mensal para acompanhar sua evolução com mais precisão.',
            ];
        }

        if ($comissaoAtual < $comissaoAnterior) {
            return [
                'tipo' => 'atencao',
                'icone' => 'bi-exclamation-triangle',
                'titulo' => 'Seu mês está abaixo do mês anterior. Revise leads paradas e propostas pendentes.',
                'descricao' => 'Uma meta mensal ajuda a orientar o volume de follow-ups e fechamentos.',
            ];
        }

        return [
            'tipo' => 'neutro',
            'icone' => 'bi-bar-chart',
            'titulo' => 'Seu resultado está estável em relação ao mês anterior.',
            'descricao' => 'Defina sua meta mensal para acompanhar sua evolução comercial.',
        ];
    }

    private function normalizarValorMonetario(mixed $valor): ?string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        $limpo = preg_replace('/[^\d,.-]/', '', (string) $valor);

        if (str_contains($limpo, ',')) {
            $limpo = str_replace('.', '', $limpo);
            $limpo = str_replace(',', '.', $limpo);
        }

        return is_numeric($limpo)
            ? number_format((float) $limpo, 2, '.', '')
            : $limpo;
    }
}
