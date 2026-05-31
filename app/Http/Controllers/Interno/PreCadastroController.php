<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePreCadastroRequest;
use App\Mail\PreCadastroLinkClienteMail;
use App\Models\Indicacao;
use App\Models\PreCadastro;
use App\Models\TipoDocumento;
use App\Services\PreCadastroService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $this->enviarEmailLinkCliente($preCadastro, $link);

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
            'documentosObrigatorios.envio.iaValidacao',
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

    private function enviarEmailLinkCliente(PreCadastro $preCadastro, string $link): void
    {
        $preCadastro->loadMissing('indicacao', 'indicacao.user');
        $indicacao = $preCadastro->indicacao;
        $corretor = $indicacao?->user;

        if (! $indicacao?->email) {
            Log::info('E-mail de pré-cadastro não enviado: cliente sem e-mail.', [
                'pre_cadastro_id' => $preCadastro->id,
                'indicacao_id' => $indicacao?->id,
            ]);
            return;
        }

        if (! $corretor) {
            Log::warning('E-mail de pré-cadastro não enviado: corretor ausente.', [
                'pre_cadastro_id' => $preCadastro->id,
                'indicacao_id' => $indicacao->id,
            ]);
            return;
        }

        try {
            Mail::to($indicacao->email)->send(new PreCadastroLinkClienteMail($indicacao, $preCadastro, $corretor, $link));
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar e-mail de link de pré-cadastro.', [
                'pre_cadastro_id' => $preCadastro->id,
                'indicacao_id' => $indicacao->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
