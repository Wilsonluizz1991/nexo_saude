<?php

namespace App\Services;

use App\Models\Interacao;

class ServicoTimeline
{
    public function registrar(array $dados): Interacao
    {
        return Interacao::create(array_merge([
            'usuario_id' => auth()->id(),
            'interacao_em' => now(),
        ], $dados));
    }
}
