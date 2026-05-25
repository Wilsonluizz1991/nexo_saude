<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIndicacaoPublicaRequest;
use App\Models\CorretorPerfil;
use App\Models\Operadora;
use App\Services\AvaliacaoAtendimentoService;
use App\Services\IndicacaoService;

class PaginaCorretorController extends Controller
{
    public function show(string $slug, AvaliacaoAtendimentoService $avaliacoes)
    {
        $perfil = CorretorPerfil::buscarPorIdentificadorPublico($slug);
        abort_unless($perfil && $perfil->publico_ativo, 404);

        return view('publico.corretor', [
            'perfil' => $perfil,
            'operadoras' => Operadora::where('ativa', true)->orderBy('nome')->get(),
            'reputacao' => $avaliacoes->mediaDoCorretor($perfil->user_id),
        ]);
    }

    public function showAntigo(string $slug, AvaliacaoAtendimentoService $avaliacoes)
    {
        return $this->show($slug, $avaliacoes);
    }

    public function store(StoreIndicacaoPublicaRequest $request, string $slug, IndicacaoService $service)
    {
        $perfil = CorretorPerfil::buscarPorIdentificadorPublico($slug);
        abort_unless($perfil && $perfil->publico_ativo, 404);
        $service->criarPorSolicitacaoPublica($perfil->user, $request->validated());

        return view('publico.sucesso', ['perfil' => $perfil]);
    }

    public function storeAntigo(StoreIndicacaoPublicaRequest $request, string $slug, IndicacaoService $service)
    {
        return $this->store($request, $slug, $service);
    }
}
