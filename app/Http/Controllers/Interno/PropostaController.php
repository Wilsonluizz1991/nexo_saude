<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropostaRequest;
use App\Models\Indicacao;
use App\Models\Operadora;
use App\Services\ServicoProposta;

class PropostaController extends Controller
{
    public function show(Indicacao $indicacao)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        abort_unless($indicacao->etapa === 'propostas', 404);

        return view('interno.propostas.show', [
            'indicacao' => $indicacao->load('propostas.operadora', 'tarefas', 'user.corretorPerfil'),
            'propostas' => $indicacao->propostas()->with('operadora')->latest()->paginate(5)->withQueryString(),
            'operadoras' => Operadora::where('ativa', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(StorePropostaRequest $request, Indicacao $indicacao, ServicoProposta $service)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        abort_unless(in_array($indicacao->etapa, ['lead', 'propostas'], true), 422);

        $propostas = $service->anexar($indicacao, $request->validated(), $request->file('arquivos_pdf') ?: $request->file('arquivo_pdf'));

        return redirect()
            ->route('paginas.simples', 'propostas')
            ->with('status', $propostas->count() > 1 ? 'Cotações em PDF anexadas. O registro foi movido para Propostas.' : 'Proposta em PDF anexada. O registro foi movido para Propostas.');
    }
}
