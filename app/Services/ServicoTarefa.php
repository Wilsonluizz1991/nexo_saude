<?php

namespace App\Services;

use App\Models\Tarefa;
use App\Models\User;

class ServicoTarefa
{
    public function criar(User $user, string $titulo, ?int $indicacaoId = null, ?string $vencimento = null): Tarefa
    {
        return Tarefa::create([
            'user_id' => $user->id,
            'indicacao_id' => $indicacaoId,
            'titulo' => $titulo,
            'vencimento' => $vencimento,
            'status' => 'pendente',
        ]);
    }
}
