<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentoClienteRequest;
use App\Models\Alerta;
use App\Models\DocumentoIaValidacao;
use App\Models\PreCadastro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class DocumentoClienteController extends Controller
{
    private const STATUS_BLOQUEADOS = [
        'documentacao_em_analise',
        'documentacao_aprovada',
        'contrato_em_analise',
        'contrato_vigente',
        'convertido_em_cliente',
    ];

    public function show(string $slug, string $token)
    {
        $preCadastro = $this->resolverPreCadastro($slug, $token);

        if (! $this->acessoAutorizado($preCadastro)) {
            return view('cliente.validar-acesso', [
                'preCadastro' => $preCadastro,
                'slug' => $slug,
            ]);
        }

        if ($this->formularioBloqueado($preCadastro)) {
            return view('cliente.pre-cadastro-bloqueado', [
                'preCadastro' => $preCadastro,
                'slug' => $slug,
            ]);
        }

        return view('cliente.documentos', [
            'preCadastro' => $preCadastro,
            'slug' => $slug,
            'motivosCorrecao' => $this->motivosCorrecao($preCadastro),
        ]);
    }

    public function validarAcesso(Request $request, string $slug, string $token): RedirectResponse
    {
        $preCadastro = $this->resolverPreCadastro($slug, $token);
        $limiteKey = 'pre-cadastro-acesso:'.$token.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($limiteKey, 5)) {
            return back()->withErrors([
                'chave_acesso' => 'Muitas tentativas inválidas. Aguarde alguns minutos e tente novamente.',
            ]);
        }

        $dados = $request->validate([
            'chave_acesso' => ['required', 'string', 'max:16', 'regex:/^[A-Za-z0-9-]+$/'],
        ], [
            'chave_acesso.required' => 'Informe a chave recebida por SMS.',
            'chave_acesso.regex' => 'Informe apenas letras, números e hífen.',
        ]);

        if (! $this->validarChaveAcesso($preCadastro, $dados['chave_acesso'])) {
            RateLimiter::hit($limiteKey, 300);
            usleep(250000);

            return back()->withErrors([
                'chave_acesso' => 'A chave informada não confere ou expirou.',
            ]);
        }

        RateLimiter::clear($limiteKey);
        session()->put($this->chaveSessaoAcesso($preCadastro), [
            'chave' => strtoupper($dados['chave_acesso']),
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);

        return redirect()->route('cliente.pre-cadastro.show', [
            'slug' => $slug,
            'token' => $preCadastro->token,
        ]);
    }

    public function store(StoreDocumentoClienteRequest $request, string $slug, string $token)
    {
        $preCadastro = $this->resolverPreCadastro($slug, $token);

        if ($this->formularioBloqueado($preCadastro)) {
            abort(423, 'Este pre-cadastro esta em analise e nao aceita novas edicoes no momento.');
        }

        abort_unless($this->acessoAutorizado($preCadastro), 403);

        $emCorrecao = $this->emModoCorrecao($preCadastro);
        $arquivos = $request->file('documentos', []);
        $validacoesIa = $request->validated('ia_validacoes', []);
        if (! $emCorrecao) {
            $this->atualizarDadosDasVidas($preCadastro, $request->validated('vidas', []));
        }

        $erroDocumentos = $this->validarDocumentosObrigatorios($preCadastro, array_keys($arquivos), $emCorrecao);
        if ($erroDocumentos) {
            return back()->withErrors(['documentos' => $erroDocumentos])->withInput();
        }

        foreach ($arquivos as $documentoId => $file) {
            $documento = $preCadastro->documentosObrigatorios->firstWhere('id', (int) $documentoId);
            if (! $documento) {
                continue;
            }

            $documento->update([
                'status' => 'enviado',
                'observacoes' => null,
            ]);
            $validacaoIa = $this->validacaoIaPermitida($preCadastro, $documento, $validacoesIa[$documentoId] ?? null);

            $envio = $documento->envios()->create([
                'pre_cadastro_id' => $preCadastro->id,
                'beneficiario_id' => $documento->vida_proposta_id,
                'documento_solicitado_id' => null,
                'documento_obrigatorio_pre_cadastro_id' => $documento->id,
                'tipo_documento_solicitado_id' => $documento->tipo_documento_id,
                'tipo_documento_detectado_id' => null,
                'arquivo_path' => $file->store('documentos', 'public'),
                'observacao_cliente' => $request->observacao_cliente,
                'status_ia' => $this->statusIaDocumentoEnviado($validacaoIa),
                'analise_ia' => $validacaoIa?->raw_response,
                'documento_compativel' => $validacaoIa?->documento_corresponde_ao_tipo,
                'legivel' => $validacaoIa?->legivel,
                'cortado' => $validacaoIa?->cortado,
                'tremido' => $validacaoIa?->desfocado,
                'nome_detectado' => $validacaoIa?->nome_extraido,
                'analisado_em' => $validacaoIa?->analisado_em,
                'motivo_recusa' => $validacaoIa?->mensagem_corretor,
            ]);

            $validacaoIa?->update(['documento_enviado_id' => $envio->id]);
        }

        $reenviado = in_array($preCadastro->status, ['documentacao_pendente', 'correcao_solicitada'], true);

        $preCadastro->update([
            'status' => 'documentacao_em_analise',
            'formulario_bloqueado' => true,
            'motivos_correcao' => null,
            'enviado_em' => now(),
            'bloqueado_em' => now(),
        ]);
        $preCadastro->indicacao?->update(['status' => 'documentacao_em_analise']);
        $this->notificarCorretor($preCadastro);
        $preCadastro->indicacao?->timelineEventos()->create([
            'titulo' => $reenviado ? 'Cliente reenviou documentacao' : 'Pré-cadastro enviado pelo cliente',
            'descricao' => $reenviado
                ? 'Cliente corrigiu as informacoes solicitadas e reenviou a documentacao para analise.'
                : 'O cliente preencheu o formulario publico e enviou os documentos para analise.',
        ]);
        $preCadastro->indicacao?->timelineEventos()->create([
            'titulo' => 'Documentacao em analise',
            'descricao' => 'O formulario publico foi bloqueado enquanto a documentacao e revisada pelo corretor.',
        ]);

        return redirect()
            ->route('cliente.pre-cadastro.show', ['slug' => $slug, 'token' => $preCadastro->token])
            ->with('status', 'Pré-cadastro enviado para análise.');
    }

    public function showAntigo(string $token): RedirectResponse
    {
        $preCadastro = $this->resolverPreCadastroPorToken($token);

        return redirect()->route('cliente.pre-cadastro.show', [
            'slug' => $this->slugDoCorretor($preCadastro),
            'token' => $preCadastro->token,
        ]);
    }

    public function storeAntigo(StoreDocumentoClienteRequest $request, string $token)
    {
        $preCadastro = $this->resolverPreCadastroPorToken($token);

        return $this->store($request, $this->slugDoCorretor($preCadastro), $token);
    }

    private function resolverPreCadastro(string $slug, string $token): PreCadastro
    {
        $preCadastro = $this->resolverPreCadastroPorToken($token);

        abort_unless($this->slugDoCorretor($preCadastro) === $slug, 404);

        return $preCadastro;
    }

    private function resolverPreCadastroPorToken(string $token): PreCadastro
    {
        return PreCadastro::where('token', $token)
            ->with([
                'indicacao.user.corretorPerfil',
                'indicacao.propostas',
                'indicacao.cliente',
                'vidas',
                'documentosObrigatorios.tipoDocumento',
                'documentosObrigatorios.envio.iaValidacao',
            ])
            ->firstOrFail();
    }

    private function slugDoCorretor(PreCadastro $preCadastro): string
    {
        return $preCadastro->indicacao?->user?->corretorPerfil?->slug
            ?? str($preCadastro->indicacao?->user?->name ?? 'corretor')->slug()->toString();
    }

    private function formularioBloqueado(PreCadastro $preCadastro): bool
    {
        return (bool) $preCadastro->formulario_bloqueado
            || in_array($preCadastro->status, self::STATUS_BLOQUEADOS, true)
            || in_array($preCadastro->indicacao?->status, self::STATUS_BLOQUEADOS, true);
    }

    private function validarDocumentosObrigatorios(PreCadastro $preCadastro, array $documentosEnviados, bool $emCorrecao): ?string
    {
        $idsEnviados = array_map('intval', $documentosEnviados);
        $documentos = $preCadastro->documentosObrigatorios;
        $obrigatorios = $documentos->where('obrigatorio', true);

        if ($emCorrecao) {
            $editaveis = $this->documentosEditaveis($preCadastro);
            $idsEditaveis = $editaveis->pluck('id')->all();

            if (collect($idsEnviados)->diff($idsEditaveis)->isNotEmpty()) {
                return 'Envie apenas os documentos liberados para correcao.';
            }

            $faltandoCorrecao = $editaveis
                ->where('obrigatorio', true)
                ->contains(fn ($documento) => ! in_array($documento->id, $idsEnviados, true));

            return $faltandoCorrecao
                ? 'Envie todos os documentos solicitados para correcao antes de reenviar.'
                : null;
        }

        $semAlternativaFaltando = $obrigatorios
            ->filter(fn ($documento) => empty($documento->grupo_alternativo))
            ->contains(fn ($documento) => ! in_array($documento->status, ['enviado', 'aprovado', 'dispensado'], true)
                && ! in_array($documento->id, $idsEnviados, true));

        if ($semAlternativaFaltando) {
            return 'Envie todos os documentos obrigatorios antes de finalizar o pre-cadastro.';
        }

        $grupoFaltando = $obrigatorios
            ->filter(fn ($documento) => ! empty($documento->grupo_alternativo))
            ->groupBy(fn ($documento) => $documento->vida_proposta_id.'|'.$documento->grupo_alternativo)
            ->contains(fn ($grupo) => ! $grupo->contains(fn ($documento) => in_array($documento->status, ['enviado', 'aprovado', 'dispensado'], true)
                || in_array($documento->id, $idsEnviados, true)));

        return $grupoFaltando
            ? 'Escolha pelo menos uma opcao valida em cada grupo documental alternativo.'
            : null;
    }

    private function validacaoIaPermitida(PreCadastro $preCadastro, $documento, mixed $validacaoId): ?DocumentoIaValidacao
    {
        if (! $validacaoId) {
            return null;
        }

        $validacao = DocumentoIaValidacao::where('id', (int) $validacaoId)
            ->where('pre_cadastro_id', $preCadastro->id)
            ->where('documento_obrigatorio_pre_cadastro_id', $documento->id)
            ->first();

        if ($validacao?->status === 'reenviar') {
            abort(422, 'Um dos documentos anexados precisa ser reenviado antes de finalizar.');
        }

        return $validacao;
    }

    private function statusIaDocumentoEnviado(?DocumentoIaValidacao $validacao): string
    {
        return match ($validacao?->status) {
            'aprovado_para_envio' => 'aprovado',
            'reenviar' => 'recusado',
            'analise_inconclusiva' => 'alerta',
            default => 'aguardando_analise',
        };
    }

    private function documentosEditaveis(PreCadastro $preCadastro)
    {
        return $preCadastro->documentosObrigatorios
            ->filter(fn ($documento) => in_array($documento->status, ['pendente', 'corrigir', 'recusado'], true))
            ->values();
    }

    private function motivosCorrecao(PreCadastro $preCadastro): array
    {
        $motivos = [];

        if ($preCadastro->motivos_correcao) {
            $motivos[] = $preCadastro->motivos_correcao;
        }

        foreach ($preCadastro->documentosObrigatorios as $documento) {
            if (! in_array($documento->status, ['recusado', 'corrigir'], true)) {
                continue;
            }

            $motivos[] = $documento->observacoes ?: "{$documento->titulo} precisa ser reenviado.";
        }

        return array_values(array_unique(array_filter($motivos)));
    }

    private function atualizarDadosDasVidas(PreCadastro $preCadastro, array $vidas): void
    {
        $vidasPorId = $preCadastro->vidas->keyBy('id');

        foreach ($vidas as $vidaId => $dados) {
            $vida = $vidasPorId->get((int) $vidaId);
            if (! $vida) {
                continue;
            }

            $vida->update([
                'nome' => $dados['nome'],
                'cpf' => preg_replace('/\D/', '', $dados['cpf']),
                'data_nascimento' => $dados['data_nascimento'],
                'sexo' => $dados['sexo'],
                'parentesco' => $dados['parentesco'] ?? null,
                'gestante' => ($dados['sexo'] ?? null) === 'feminino' && ! empty($dados['gestante']),
                'vinculo_beneficiario_id' => $this->resolverVinculo($preCadastro, $dados['vinculo_beneficiario_id'] ?? null),
            ]);
        }
    }

    private function resolverVinculo(PreCadastro $preCadastro, mixed $valor): ?int
    {
        if (! is_numeric($valor)) {
            return null;
        }

        $vida = $preCadastro->vidas->firstWhere('id', (int) $valor);

        return $vida?->id;
    }

    private function emModoCorrecao(PreCadastro $preCadastro): bool
    {
        return (bool) $preCadastro->enviado_em
            && in_array($preCadastro->status, ['documentacao_pendente', 'correcao_solicitada'], true);
    }

    private function chaveSessaoAcesso(PreCadastro $preCadastro): string
    {
        return 'pre_cadastro_acesso.'.$preCadastro->id;
    }

    private function acessoAutorizado(PreCadastro $preCadastro): bool
    {
        $sessao = session($this->chaveSessaoAcesso($preCadastro));
        if (! is_array($sessao) || ($sessao['expires_at'] ?? 0) < now()->timestamp) {
            session()->forget($this->chaveSessaoAcesso($preCadastro));
            return false;
        }

        return $this->validarChaveAcesso($preCadastro, (string) ($sessao['chave'] ?? ''));
    }

    private function validarChaveAcesso(PreCadastro $preCadastro, string $chave): bool
    {
        if (! $preCadastro->chave_acesso) {
            return false;
        }

        if ($preCadastro->chave_expira_em && $preCadastro->chave_expira_em->isPast()) {
            return false;
        }

        return hash_equals(strtoupper($preCadastro->chave_acesso), strtoupper(trim($chave)));
    }

    private function notificarCorretor(PreCadastro $preCadastro): void
    {
        $indicacao = $preCadastro->indicacao;
        if (! $indicacao) {
            return;
        }

        Alerta::create([
            'user_id' => $indicacao->user_id,
            'indicacao_id' => $indicacao->id,
            'pre_cadastro_id' => $preCadastro->id,
            'proposta_id' => $indicacao->propostas->sortByDesc('created_at')->first()?->id,
            'cliente_id' => $indicacao->cliente?->id,
            'titulo' => 'Pré-cadastro enviado',
            'mensagem' => 'O cliente enviou o pré-cadastro preenchido e os documentos anexados para análise.',
            'tipo' => 'pre_cadastro_enviado',
            'status' => 'nao_lido',
            'lido' => false,
        ]);
    }
}
