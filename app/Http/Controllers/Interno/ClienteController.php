<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\Tarefa;

class ClienteController extends Controller
{
    public function show(Cliente $cliente)
    {
        abort_unless($cliente->user_id === auth()->id(), 403);

        $cliente->load([
            'contratos.operadora',
            'contratos.proposta',
            'dependentes',
            'indicacao.propostas.operadora',
            'indicacao.preCadastro.vidas',
            'indicacao.timelineEventos',
        ]);

        $tarefas = Tarefa::where('user_id', auth()->id())
            ->where('indicacao_id', $cliente->indicacao_id)
            ->latest()
            ->get();

        $alertas = Alerta::where('user_id', auth()->id())
            ->where(function ($query) use ($cliente) {
                $query->where('cliente_id', $cliente->id)
                    ->orWhere('indicacao_id', $cliente->indicacao_id);
            })
            ->latest()
            ->get();

        return view('interno.clientes.show', [
            'cliente' => $cliente,
            'indicacao' => $cliente->indicacao,
            'tarefas' => $tarefas,
            'alertas' => $alertas,
        ]);
    }
}
