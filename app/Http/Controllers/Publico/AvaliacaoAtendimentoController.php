<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\AvaliacaoAtendimento;
use Illuminate\Http\Request;

class AvaliacaoAtendimentoController extends Controller
{
    public function show(string $token)
    {
        $avaliacao = AvaliacaoAtendimento::where('token', $token)
            ->with('cliente', 'corretor.corretorPerfil')
            ->firstOrFail();

        return view('publico.avaliacao-atendimento', [
            'avaliacao' => $avaliacao,
            'cliente' => $avaliacao->cliente,
            'perfil' => $avaliacao->corretor?->corretorPerfil,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $avaliacao = AvaliacaoAtendimento::where('token', $token)->firstOrFail();

        if ($avaliacao->status === 'respondida') {
            return redirect()->route('publico.avaliacoes.show', $avaliacao->token);
        }

        $dados = $request->validate([
            'nota_atendimento' => ['required', 'integer', 'min:1', 'max:5'],
            'nota_clareza' => ['required', 'integer', 'min:1', 'max:5'],
            'nota_agilidade' => ['required', 'integer', 'min:1', 'max:5'],
            'nota_confianca' => ['required', 'integer', 'min:1', 'max:5'],
            'nota_recomendacao' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ]);

        $avaliacao->update(array_merge($dados, [
            'status' => 'respondida',
            'respondida_em' => now(),
        ]));

        return redirect()
            ->route('publico.avaliacoes.show', $avaliacao->token)
            ->with('status', 'Obrigado! Sua avaliação foi registrada com sucesso.');
    }
}
