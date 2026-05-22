<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Indicacao;
use App\Models\Tarefa;
use App\Models\User;

class ServicoLembrete
{
    public function criar(Indicacao $indicacao, array $dados): Tarefa
    {
        $lembrete = Tarefa::create([
            'user_id' => $indicacao->user_id,
            'indicacao_id' => $indicacao->id,
            'tipo' => 'lembrete',
            'titulo' => $dados['descricao'],
            'descricao' => $dados['descricao'],
            'vencimento' => $dados['data_ocorrencia'],
            'status' => 'pendente',
        ]);

        $indicacao->timelineEventos()->create([
            'titulo' => 'Lembrete criado',
            'descricao' => $dados['descricao'].' · '.$lembrete->vencimento?->format('d/m/Y'),
        ]);

        return $lembrete;
    }

    public function sincronizarAlertas(User $user): void
    {
        Tarefa::query()
            ->where('user_id', $user->id)
            ->where('tipo', 'lembrete')
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->where(function ($query) {
                $query->whereDate('vencimento', today())
                    ->orWhereDate('vencimento', today()->addDay());
            })
            ->with('indicacao')
            ->get()
            ->each(fn (Tarefa $lembrete) => $this->criarAlertaDoLembrete($lembrete));
    }

    private function criarAlertaDoLembrete(Tarefa $lembrete): void
    {
        $ehHoje = $lembrete->vencimento?->isToday();

        $tipo = $ehHoje ? 'lembrete_hoje' : 'lembrete_amanha';
        $titulo = $ehHoje ? 'Lembrete programado para hoje' : 'Lembrete programado para amanhã';
        $data = $lembrete->vencimento?->format('d/m/Y');
        $cliente = $lembrete->indicacao?->nome_cliente;
        $mensagem = $ehHoje
            ? "Você tem um lembrete para hoje{$this->sufixoCliente($cliente)}: {$lembrete->titulo}"
            : "Você tem um lembrete com data programada para amanhã ({$data}){$this->sufixoCliente($cliente)}: {$lembrete->titulo}";

        Alerta::firstOrCreate([
            'user_id' => $lembrete->user_id,
            'tarefa_id' => $lembrete->id,
            'tipo' => $tipo,
        ], [
            'indicacao_id' => $lembrete->indicacao_id,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'status' => 'nao_lido',
            'lido' => false,
        ]);
    }

    private function sufixoCliente(?string $cliente): string
    {
        return $cliente ? " de {$cliente}" : '';
    }
}
