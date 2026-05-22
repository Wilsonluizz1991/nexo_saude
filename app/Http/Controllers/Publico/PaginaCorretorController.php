<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIndicacaoPublicaRequest;
use App\Models\CorretorPerfil;
use App\Models\Operadora;
use App\Services\IndicacaoService;

class PaginaCorretorController extends Controller
{
    public function show(string $slug)
    {
        return view('publico.corretor', [
            'perfil' => CorretorPerfil::where('slug', $slug)->where('publico_ativo', true)->firstOrFail(),
            'operadoras' => Operadora::where('ativa', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(StoreIndicacaoPublicaRequest $request, string $slug, IndicacaoService $service)
    {
        $perfil = CorretorPerfil::where('slug', $slug)->where('publico_ativo', true)->firstOrFail();
        $service->criarPorSolicitacaoPublica($perfil->user, $request->validated());

        return view('publico.sucesso', ['perfil' => $perfil]);
    }
}
