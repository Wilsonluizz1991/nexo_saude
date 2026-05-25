<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Indicacao;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Support\Collection;

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
            'compromissosHoje' => $this->limitar($compromissosHojeQuery->clone()
                ->orderBy('vencimento')
                ->orderByDesc('created_at')
                ->get()),
            'quantidadeCompromissosHoje' => $compromissosHojeQuery->count(),
            'tarefasPendentes' => $this->limitar($tarefasPendentesQuery->clone()
                ->orderByRaw("case status when 'atrasada' then 0 else 1 end")
                ->orderByRaw('vencimento is null')
                ->orderBy('vencimento')
                ->orderByDesc('created_at')
                ->get()),
            'quantidadeTarefasPendentes' => $tarefasPendentesQuery->count(),
            'alertasNaoLidos' => $this->limitar($alertasNaoLidosQuery->clone()
                ->orderByRaw("case tipo when 'erro' then 0 when 'atencao' then 1 when 'info' then 2 else 3 end")
                ->orderByDesc('created_at')
                ->get()),
            'quantidadeAlertasNaoLidos' => $alertasNaoLidosQuery->count(),
            'quantidadePreCadastrosPendentes' => Indicacao::query()
                ->where('user_id', $user->id)
                ->where('etapa', 'pre_cadastros')
                ->whereHas('preCadastro', fn ($query) => $query->whereIn('status', ['aguardando_envio', 'documentacao_pendente', 'documentacao_em_analise', 'correcao_solicitada']))
                ->count(),
        ];
    }

    private function limitar(Collection $itens): Collection
    {
        return $itens->take(6)->values();
    }
}
