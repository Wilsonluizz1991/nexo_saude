<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Indicacao;
use App\Models\Tarefa;
use App\Models\User;

class CabecalhoService
{
    public function dadosPara(User $user): array
    {
        app(ServicoLembrete::class)->sincronizarAlertas($user);
        app(ServicoAlerta::class)->gerarAlertasAniversarioTitular($user);

        $compromissosHojeQuery = Tarefa::query()
            ->where('user_id', $user->id)
            ->whereDate('vencimento', today())
            ->whereNotIn('status', ['concluida', 'cancelada']);

        $tarefasPendentesQuery = Tarefa::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pendente', 'atrasada']);

        $alertasNaoLidosQuery = Alerta::query()
            ->where('user_id', $user->id)
            ->where('lido', false);

        return [
            'compromissosHoje' => $compromissosHojeQuery->clone()
                ->select(['id', 'titulo', 'vencimento', 'status'])
                ->orderBy('vencimento')
                ->orderByDesc('created_at')
                ->limit(6)
                ->get(),
            'quantidadeCompromissosHoje' => $compromissosHojeQuery->count(),
            'tarefasPendentes' => $tarefasPendentesQuery->clone()
                ->select(['id', 'titulo', 'vencimento', 'status'])
                ->orderByRaw("case status when 'atrasada' then 0 else 1 end")
                ->orderByRaw('vencimento is null')
                ->orderBy('vencimento')
                ->orderByDesc('created_at')
                ->limit(6)
                ->get(),
            'quantidadeTarefasPendentes' => $tarefasPendentesQuery->count(),
            'alertasNaoLidos' => $alertasNaoLidosQuery->clone()
                ->select(['id', 'titulo', 'mensagem', 'tipo', 'indicacao_id', 'cliente_id', 'pre_cadastro_id', 'lido', 'status', 'created_at'])
                ->orderByRaw("case tipo when 'erro' then 0 when 'atencao' then 1 when 'info' then 2 else 3 end")
                ->orderByDesc('created_at')
                ->limit(6)
                ->get(),
            'quantidadeAlertasNaoLidos' => $alertasNaoLidosQuery->count(),
            'quantidadePreCadastrosPendentes' => Indicacao::query()
                ->where('user_id', $user->id)
                ->where('etapa', 'pre_cadastros')
                ->whereHas('preCadastro', fn ($query) => $query->whereIn('status', ['aguardando_envio', 'documentacao_pendente', 'documentacao_em_analise', 'correcao_solicitada']))
                ->count(),
        ];
    }
}
