<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePreCadastroRequest;
use App\Models\Indicacao;
use App\Models\PreCadastro;
use App\Models\TipoDocumento;
use App\Services\PreCadastroService;

class PreCadastroController extends Controller
{
    public function create(Indicacao $indicacao)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);

        return view('interno.pre-cadastros.create', [
            'indicacao' => $indicacao,
            'tiposDocumento' => TipoDocumento::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(StorePreCadastroRequest $request, Indicacao $indicacao, PreCadastroService $service)
    {
        abort_unless($indicacao->user_id === auth()->id(), 403);
        $preCadastro = $service->iniciar($indicacao, $request->validated());

        return redirect()->route('pre-cadastros.show', $preCadastro)
            ->with('status', 'Pré-cadastro criado. Link do cliente: '.$this->linkPublico($preCadastro));
    }

    public function show(PreCadastro $preCadastro)
    {
        $preCadastro->load([
            'indicacao.user.corretorPerfil',
            'indicacao.timelineEventos',
            'vidas',
            'documentosObrigatorios.tipoDocumento',
            'documentosObrigatorios.envio',
        ]);

        abort_unless($preCadastro->indicacao?->user_id === auth()->id(), 403);

        return view('interno.pre-cadastros.show', [
            'preCadastro' => $preCadastro,
            'indicacao' => $preCadastro->indicacao,
            'linkPublico' => $this->linkPublico($preCadastro),
        ]);
    }

    private function linkPublico(PreCadastro $preCadastro): string
    {
        $slug = $preCadastro->indicacao?->user?->corretorPerfil?->slug
            ?? str($preCadastro->indicacao?->user?->name ?? 'corretor')->slug()->toString();

        return route('cliente.pre-cadastro.show', [
            'slug' => $slug,
            'token' => $preCadastro->token,
        ]);
    }
}
