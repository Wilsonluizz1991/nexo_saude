<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\Indicacao;
use App\Models\Tarefa;
use App\Services\ServicoAlerta;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(ServicoAlerta $alertasAutomaticos)
    {
        $userId = auth()->id();
        $alertasAutomaticos->gerarAutomaticos(auth()->user());

        $totaisIndicacoes = Indicacao::where('user_id', $userId)
            ->select('etapa', DB::raw('count(*) as total'))
            ->whereIn('etapa', ['lead', 'pre_cadastros', 'implantacoes'])
            ->groupBy('etapa')
            ->pluck('total', 'etapa');

        return view('interno.dashboard', [
            'indicacoes' => Indicacao::where('user_id', $userId)
                ->select(['id', 'user_id', 'nome_cliente', 'telefone', 'email', 'tipo_plano', 'quantidade_vidas', 'cidade', 'estado', 'etapa', 'status', 'created_at'])
                ->where('etapa', 'lead')
                ->where('created_at', '>=', now()->subDay())
                ->latest()
                ->paginate(5, ['*'], 'leads_page'),
            'totais' => [
                'leads' => (int) ($totaisIndicacoes['lead'] ?? 0),
                'pré-cadastros' => (int) ($totaisIndicacoes['pre_cadastros'] ?? 0),
                'clientes ativos' => Cliente::where('user_id', $userId)->where('status', 'ativo')->count(),
                'tarefas' => Tarefa::where('user_id', $userId)->where('status', 'pendente')->count(),
            ],
            'operacaoHoje' => [
                'retornos hoje' => Tarefa::where('user_id', $userId)->whereDate('vencimento', today())->whereIn('status', ['pendente', 'atrasada'])->count(),
                'propostas sem resposta' => Indicacao::where('user_id', $userId)->where('etapa', 'propostas')->where('status', 'proposta_enviada')->count(),
                'documentos pendentes' => Indicacao::where('user_id', $userId)->whereHas('preCadastro.documentosObrigatorios', fn ($q) => $q->whereIn('status', ['pendente', 'recusado', 'corrigir']))->count(),
                'implantações' => (int) ($totaisIndicacoes['implantacoes'] ?? 0),
            ],
            'alertas' => Alerta::where('user_id', $userId)
                ->select(['id', 'user_id', 'titulo', 'mensagem', 'tipo', 'status', 'lido', 'created_at'])
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }
}
