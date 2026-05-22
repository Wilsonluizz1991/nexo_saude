<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\Implantacao;
use App\Models\Operadora;

class ImplantacaoController extends Controller
{
    public function show(Implantacao $implantacao)
    {
        $implantacao->load([
            'indicacao.propostas.operadora',
            'indicacao.preCadastro.vidas',
            'indicacao.timelineEventos',
            'indicacao.user.corretorPerfil',
        ]);

        abort_unless($implantacao->indicacao?->user_id === auth()->id(), 403);

        return view('interno.implantacoes.show', [
            'implantacao' => $implantacao,
            'indicacao' => $implantacao->indicacao,
            'operadoras' => Operadora::where('ativa', true)->orderBy('nome')->get(),
        ]);
    }
}
