<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Contrato;

class ServicoContrato
{
    public function criarParaCliente(Cliente $cliente, array $dados): Contrato
    {
        return Contrato::create(array_merge($dados, [
            'usuario_id' => $cliente->user_id,
            'cliente_id' => $cliente->id,
            'status' => $dados['status'] ?? 'ativo',
        ]));
    }
}
