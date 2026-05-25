<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\Indicacao;
use App\Models\Tarefa;
use App\Services\ServicoAlerta;

class DashboardController extends Controller
{
    public function __invoke(ServicoAlerta $alertasAutomaticos)
    {
        $userId = auth()->id();
        $alertasAutomaticos->gerarAutomaticos(auth()->user());

        return view('interno.dashboard', [
            'indicacoes' => Indicacao::where('user_id', $userId)
                ->where('etapa', 'lead')
                ->where('created_at', '>=', now()->subDay())
                ->latest()
                ->paginate(5, ['*'], 'leads_page'),
            'totais' => [
                'leads' => Indicacao::where('user_id', $userId)->where('etapa', 'lead')->count(),
                'pré-cadastros' => Indicacao::where('user_id', $userId)->where('etapa', 'pre_cadastros')->count(),
                'clientes ativos' => Cliente::where('user_id', $userId)->where('status', 'ativo')->count(),
                'tarefas' => Tarefa::where('user_id', $userId)->where('status', 'pendente')->count(),
            ],
            'operacaoHoje' => [
                'retornos hoje' => Tarefa::where('user_id', $userId)->whereDate('vencimento', today())->whereIn('status', ['pendente', 'atrasada'])->count(),
                'propostas sem resposta' => Indicacao::where('user_id', $userId)->where('etapa', 'propostas')->where('status', 'proposta_enviada')->count(),
                'documentos pendentes' => Indicacao::where('user_id', $userId)->whereHas('preCadastro.documentosObrigatorios', fn ($q) => $q->whereIn('status', ['pendente', 'recusado', 'corrigir']))->count(),
                'implantações' => Indicacao::where('user_id', $userId)->where('etapa', 'implantacoes')->count(),
            ],
            'alertas' => Alerta::where('user_id', $userId)->latest()->take(4)->get(),
        ]);
    }
}
