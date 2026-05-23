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
        $link = $this->linkPublico($preCadastro);
        $preCadastro = $service->enviarSmsComLink($preCadastro, $link);

        return redirect()->route('pre-cadastros.show', $preCadastro)
            ->with('status', 'Pré-cadastro criado e link preparado.')
            ->with('pre_cadastro_link', $link)
            ->with('pre_cadastro_chave', $preCadastro->chave_acesso)
            ->with('pre_cadastro_sms_status', $preCadastro->sms_status)
            ->with('pre_cadastro_cliente', $preCadastro->indicacao?->nome_cliente);
    }

    public function show(PreCadastro $preCadastro, PreCadastroService $service)
    {
        $preCadastro = $service->garantirTokenAcesso($preCadastro);
        $preCadastro->load([
            'indicacao.user.corretorPerfil',
            'indicacao.timelineEventos',
            'indicacao.tarefas',
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
