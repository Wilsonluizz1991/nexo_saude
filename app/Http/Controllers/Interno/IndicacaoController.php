<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLembreteRequest;
use App\Http\Requests\StorePropostaRequest;
use App\Models\Indicacao;
use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\Operadora;
use App\Services\ImplantacaoService;
use App\Services\PlanoSaudeService;
use App\Services\ServicoLembrete;
use App\Services\ServicoProposta;
use Illuminate\Http\Request;

class IndicacaoController extends Controller
{
    public function index()
    {
        $etapa = request('etapa', 'leads');
        $statusPorEtapa = [
            'leads' => ['lead'],
            'propostas' => ['propostas'],
            'pre-cadastros' => ['pre_cadastros'],
            'implantacoes' => ['implantacoes'],
            'clientes' => ['clientes', 'carteira'],
            'perdidos' => ['perdida'],
        ];

        $base = Indicacao::where('user_id', auth()->id());
        $query = (clone $base)->latest();

        $query->whereIn('etapa', $statusPorEtapa[$etapa] ?? $statusPorEtapa['leads']);

        return view('interno.indicacoes.index', [
            'indicacoes' => $query->paginate(10)->withQueryString(),
            'etapaAtual' => $etapa,
            'contadoresEtapas' => [
                'todos' => (clone $base)->count(),
                'leads' => (clone $base)->whereIn('etapa', $statusPorEtapa['leads'])->count(),
                'propostas' => (clone $base)->whereIn('etapa', $statusPorEtapa['propostas'])->count(),
                'pre-cadastros' => (clone $base)->whereIn('etapa', $statusPorEtapa['pre-cadastros'])->count(),
                'implantacoes' => (clone $base)->whereIn('etapa', $statusPorEtapa['implantacoes'])->count(),
                'clientes' => (clone $base)->whereIn('etapa', $statusPorEtapa['clientes'])->count(),
                'perdidos' => (clone $base)->whereIn('etapa', $statusPorEtapa['perdidos'])->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('interno.indicacoes.create', [
            'operadoras' => Operadora::where('ativa', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'tipo_plano' => PlanoSaudeService::normalizarTipo($request->input('tipo_plano')),
            'quantidade_vidas' => PlanoSaudeService::normalizarQuantidadeVidas(
                $request->input('tipo_plano'),
                $request->input('quantidade_vidas')
            ),
        ]);

        if ($request->input('possui_preferencias') !== 'sim') {
            $request->merge([
                'operadoras' => [],
                'hospitais' => [],
                'faixa_valor_mensal' => null,
            ]);
        }

        $dados = $request->validate([
            'nome_cliente' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'tipo_plano' => ['required', 'string', 'max:80'],
            'quantidade_vidas' => ['required', 'integer', 'min:1', 'max:999'],
            'cidade' => ['required', 'string', 'max:120'],
            'estado' => ['required', 'string', 'size:2'],
            'possui_preferencias' => ['required', 'in:sim,nao,ainda_nao_sei'],
            'operadoras' => ['nullable', 'array', 'max:3'],
            'operadoras.*' => ['integer', 'distinct', 'exists:operadoras,id'],
            'hospitais' => ['nullable', 'array', 'max:3'],
            'hospitais.*' => ['nullable', 'string', 'max:120'],
            'faixa_valor_mensal' => ['nullable', 'string', 'max:80'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $possuiPreferencias = $dados['possui_preferencias'] === 'sim';
        $operadorasPreferidas = collect($dados['operadoras'] ?? [])
            ->map(fn ($operadoraId) => (int) $operadoraId)
            ->take(3)
            ->values()
            ->all();
        $hospitaisPreferidos = array_values(array_filter(array_slice($dados['hospitais'] ?? [], 0, 3)));
        unset($dados['possui_preferencias'], $dados['operadoras'], $dados['hospitais']);

        $indicacao = Indicacao::create(array_merge($dados, [
            'user_id' => auth()->id(),
            'origem' => 'cadastro_interno',
            'estado' => strtoupper($dados['estado']),
            'possui_preferencias' => $possuiPreferencias,
            'operadoras_preferidas' => $possuiPreferencias ? $operadorasPreferidas : [],
            'hospitais_preferidos' => $possuiPreferencias ? $hospitaisPreferidos : [],
            'faixa_valor_mensal' => $possuiPreferencias ? ($dados['faixa_valor_mensal'] ?? null) : null,
            'etapa' => 'lead',
            'status' => 'nova',
        ]));
        $indicacao->timelineEventos()->create(['titulo' => 'Lead criado', 'descricao' => 'Cadastro interno feito pelo corretor.']);

        return redirect()->route('indicacoes.show', $indicacao)->with('status', 'Lead criado.');
    }

    public function show(Indicacao $indicacao)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        $indicacao->loadMissing('preCadastro', 'implantacao', 'cliente');

        if ($indicacao->etapa === 'propostas') {
            return redirect()->route('propostas.show', $indicacao);
        }

        if ($indicacao->etapa === 'pre_cadastros' && $indicacao->preCadastro) {
            return redirect()->route('pre-cadastros.show', $indicacao->preCadastro);
        }

        if ($indicacao->etapa === 'implantacoes' && $indicacao->implantacao) {
            return redirect()->route('implantacoes.show', $indicacao->implantacao);
        }

        if (in_array($indicacao->etapa, ['clientes', 'carteira'], true) && $indicacao->cliente) {
            return redirect()->route('clientes.show', $indicacao->cliente);
        }

        return view('interno.indicacoes.show', [
            'indicacao' => $indicacao->load('propostas.operadora', 'preCadastro.vidas', 'preCadastro.documentosObrigatorios.tipoDocumento', 'preCadastro.documentosObrigatorios.envio', 'timelineEventos', 'tarefas', 'implantacao', 'cliente', 'user.corretorPerfil'),
            'operadoras' => Operadora::where('ativa', true)->orderBy('nome')->get(),
        ]);
    }

    public function storeProposta(StorePropostaRequest $request, Indicacao $indicacao, ServicoProposta $service)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        abort_unless(in_array($indicacao->etapa, ['lead', 'propostas', 'pre_cadastros', 'implantacoes'], true), 422);

        $service->anexar($indicacao, $request->validated(), $request->file('arquivo_pdf'));

        return redirect()
            ->route('paginas.simples', 'propostas')
            ->with('status', 'Proposta em PDF anexada. O registro foi movido para Propostas.');
    }

    public function storeLembrete(StoreLembreteRequest $request, Indicacao $indicacao, ServicoLembrete $service)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        abort_unless(in_array($indicacao->etapa, ['lead', 'propostas', 'pre_cadastros', 'implantacoes'], true), 422);

        $service->criar($indicacao, $request->validated());

        return back()->with('status', 'Lembrete criado. O sistema notificará o corretor um dia antes e no dia programado.');
    }

    public function aceitar(Indicacao $indicacao)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);

        return redirect()->route('pre-cadastros.create', $indicacao)->with('status', 'Defina a estrutura para gerar o link de pré-cadastro.');
    }

    public function iniciarImplantacao(Indicacao $indicacao, ImplantacaoService $service)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        abort_if(! $this->documentosAprovados($indicacao), 422, 'Ainda existem documentos pendentes ou em correção.');
        $service->iniciar($indicacao);

        return redirect()
            ->route('implantacoes.show', $indicacao->implantacao()->firstOrFail())
            ->with('status', 'Documentação aprovada. Implantação iniciada.');
    }

    public function aprovarImplantacao(Indicacao $indicacao, ImplantacaoService $service, Request $request)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        $dados = $request->validate([
            'operadora_id' => ['required', 'exists:operadoras,id'],
            'tipo_contrato' => ['required', 'string', 'max:80'],
            'quantidade_vidas' => ['required', 'integer', 'min:1'],
            'data_vigencia' => ['required', 'date'],
            'valor_mensal' => ['required', 'numeric', 'min:0'],
            'renovacao_em' => ['required', 'date'],
            'reajuste_em' => ['required', 'date'],
            'numero_contrato' => ['nullable', 'string', 'max:120'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'enviar_email' => ['nullable', 'boolean'],
            'enviar_sms' => ['nullable', 'boolean'],
        ]);
        $dados['quantidade_vidas'] = PlanoSaudeService::normalizarQuantidadeVidas(
            $dados['tipo_contrato'],
            $dados['quantidade_vidas']
        );
        $service->contratoVigente($indicacao, $dados);

        return redirect()
            ->route('paginas.simples', 'clientes')
            ->with('status', 'Contrato vigente confirmado. Cliente ativo criado e adicionado à carteira.');
    }

    public function atualizarStatusImplantacao(Indicacao $indicacao, Request $request)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        abort_unless($indicacao->etapa === 'implantacoes', 422);

        $dados = $request->validate([
            'status' => ['required', 'in:contrato_em_analise,pendencia_na_operadora,aguardando_vigencia,contrato_recusado'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $indicacao->implantacao()->firstOrCreate([], ['data_inicio' => now()->toDateString()])
            ->update($dados);
        $indicacao->update(['status' => $dados['status']]);
        $indicacao->timelineEventos()->create([
            'titulo' => 'Status da implantação atualizado',
            'descricao' => str_replace('_', ' ', $dados['status']),
        ]);

        return back()->with('status', 'Status da implantação atualizado.');
    }

    public function atualizarDocumento(Indicacao $indicacao, DocumentoObrigatorioPreCadastro $documento, Request $request)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        abort_unless($documento->preCadastro?->indicacao_id === $indicacao->id, 403);
        abort_if(
            $indicacao->etapa === 'carteira' || $indicacao->status === 'contrato_vigente',
            422,
            'Contrato vigente. A revisão documental está encerrada.'
        );

        $dados = $request->validate([
            'status' => ['required', 'in:aprovado,recusado,corrigir,dispensado'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        $documento->update($dados);
        $this->sincronizarStatusDocumental($indicacao);
        $indicacao->timelineEventos()->create([
            'titulo' => 'Documento revisado',
            'descricao' => "{$documento->titulo}: {$dados['status']}.",
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Documento atualizado.',
                'status' => $documento->status,
                'status_label' => $this->rotuloStatusDocumento($documento->status),
            ]);
        }

        return back()->with('status', 'Documento atualizado.');
    }

    public function solicitarCorrecao(Indicacao $indicacao, Request $request)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        abort_unless($indicacao->etapa === 'pre_cadastros' && $indicacao->preCadastro, 422);

        $dados = $request->validate([
            'motivos_correcao' => ['nullable', 'string', 'max:2000'],
        ]);

        $indicacao->preCadastro->update([
            'status' => 'documentacao_pendente',
            'formulario_bloqueado' => false,
            'motivos_correcao' => $dados['motivos_correcao'] ?? null,
            'bloqueado_em' => null,
        ]);
        $indicacao->update(['status' => 'documentacao_pendente']);
        $indicacao->timelineEventos()->create([
            'titulo' => 'Correção solicitada',
            'descricao' => 'O corretor solicitou correção de documentos do pré-cadastro.',
        ]);

        return back()->with('status', 'Pré-cadastro devolvido para correção. O mesmo link foi desbloqueado para o cliente.');
    }

    private function rotuloStatusDocumento(string $status): string
    {
        return [
            'pendente' => 'Pendente',
            'enviado' => 'Enviado',
            'aprovado' => 'Aprovado',
            'corrigir' => 'Corrigir',
            'recusado' => 'Recusado',
            'dispensado' => 'Dispensado',
        ][$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    private function sincronizarStatusDocumental(Indicacao $indicacao): void
    {
        $preCadastro = $indicacao->preCadastro?->load('documentosObrigatorios');
        if (! $preCadastro || $indicacao->etapa !== 'pre_cadastros') {
            return;
        }

        $status = $this->documentosAprovados($indicacao)
            ? 'documentacao_aprovada'
            : ($preCadastro->documentosObrigatorios->whereIn('status', ['recusado', 'corrigir'])->isNotEmpty()
                ? 'correcao_solicitada'
                : 'documentacao_pendente');

        $preCadastro->update([
            'status' => $status,
            'formulario_bloqueado' => in_array($status, ['documentacao_em_analise', 'documentacao_aprovada', 'correcao_solicitada'], true),
        ]);
        $indicacao->update(['status' => $status]);

        if ($status === 'documentacao_aprovada') {
            $indicacao->timelineEventos()->firstOrCreate([
                'titulo' => 'Documentação aprovada',
                'descricao' => 'Todos os documentos obrigatórios foram aprovados pelo corretor.',
            ]);
        }
    }

    private function documentosAprovados(Indicacao $indicacao): bool
    {
        $preCadastro = $indicacao->preCadastro;
        if (! $preCadastro) {
            return false;
        }

        $documentos = $preCadastro->documentosObrigatorios;

        if ($documentos->isEmpty()) {
            return false;
        }

        $obrigatorios = $documentos->where('obrigatorio', true);
        $documentosSemAlternativaOk = $obrigatorios
            ->filter(fn ($documento) => empty($documento->grupo_alternativo))
            ->every(fn ($documento) => in_array($documento->status, ['aprovado', 'dispensado'], true));

        $gruposAlternativosOk = $obrigatorios
            ->filter(fn ($documento) => ! empty($documento->grupo_alternativo))
            ->groupBy(fn ($documento) => $documento->vida_proposta_id.'|'.$documento->grupo_alternativo)
            ->every(fn ($grupo) => $grupo->contains(fn ($documento) => in_array($documento->status, ['aprovado', 'dispensado'], true)));

        return $documentosSemAlternativaOk && $gruposAlternativosOk;
    }
}
