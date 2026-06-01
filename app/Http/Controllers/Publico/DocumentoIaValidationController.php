<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidarDocumentoIaRequest;
use App\Models\DocumentoIaValidacao;
use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\PreCadastro;
use App\Services\OpenAI\DocumentAiValidationService;
use Illuminate\Http\JsonResponse;

class DocumentoIaValidationController extends Controller
{
    public function __invoke(
        ValidarDocumentoIaRequest $request,
        string $slug,
        string $token,
        DocumentoObrigatorioPreCadastro $documento,
        DocumentAiValidationService $service
    ): JsonResponse {
        $preCadastro = $this->resolverPreCadastro($slug, $token);

        abort_unless($this->acessoAutorizado($preCadastro), 403);
        abort_unless((int) $documento->pre_cadastro_id === (int) $preCadastro->id, 404);

        $preCadastro->loadMissing('documentosObrigatorios.tipoDocumento');
        $documento->load('tipoDocumento', 'preCadastro.indicacao', 'preCadastro.vidas');
        $vida = $preCadastro->vidas->firstWhere('id', $documento->vida_proposta_id);
        $dados = $request->validated();
        $fase = $dados['fase_validacao'];
        $file = $request->file('arquivo');
        $validacaoAnterior = $this->validacaoAnterior($preCadastro, $documento, $dados['ia_validacao_id'] ?? null);
        $path = $file?->store('documentos/ia-validacoes', 'local');

        $contexto = $this->contexto($preCadastro, $documento, $vida, $dados);
        $resultado = $service->validate($documento, $file, $contexto, $fase, $validacaoAnterior?->toArray());
        $resultado['tipo_documento_esperado'] = $resultado['tipo_documento_esperado'] ?: $documento->tipoDocumento?->nome;

        $validacao = DocumentoIaValidacao::create(array_merge(
            $this->camposPersistencia($resultado),
            [
                'pre_cadastro_id' => $preCadastro->id,
                'beneficiario_id' => $documento->vida_proposta_id,
                'documento_obrigatorio_pre_cadastro_id' => $documento->id,
                'tipo_documento_id' => $documento->tipo_documento_id,
                'tipo_documento_esperado' => $documento->tipoDocumento?->nome,
                'arquivo_nome' => $file?->getClientOriginalName() ?? $validacaoAnterior?->arquivo_nome,
                'arquivo_path' => $path ?? $validacaoAnterior?->arquivo_path,
                'nome_beneficiario_usado' => $contexto['nome_beneficiario'] ?? null,
                'cpf_beneficiario_usado' => $contexto['cpf_beneficiario'] ?? null,
                'data_nascimento_usada' => $contexto['data_nascimento_beneficiario'] ?? null,
                'analisado_em' => now(),
            ]
        ));
        $dispensas = $this->atualizarDispensasPorIdentidade($preCadastro, $documento, $validacao);

        return response()->json([
            'id' => $validacao->id,
            'status' => $validacao->status,
            'mensagem_cliente' => $validacao->mensagem_cliente,
            'motivos' => $validacao->motivos ?? [],
            'analise_parcial' => $validacao->analise_parcial,
            'titularidade_pendente' => $validacao->titularidade_pendente,
            'dispensas_documentais' => $dispensas,
            'allow_upload' => $validacao->status !== 'reenviar',
            'enabled' => (bool) config('services.openai.document_validation_enabled'),
        ]);
    }

    private function resolverPreCadastro(string $slug, string $token): PreCadastro
    {
        $preCadastro = PreCadastro::where('token', $token)
            ->with(['indicacao.user.corretorPerfil', 'vidas'])
            ->firstOrFail();

        $slugCorretor = $preCadastro->indicacao?->user?->corretorPerfil?->slug
            ?? str($preCadastro->indicacao?->user?->name ?? 'corretor')->slug()->toString();

        abort_unless($slugCorretor === $slug, 404);

        return $preCadastro;
    }

    private function acessoAutorizado(PreCadastro $preCadastro): bool
    {
        $sessao = session('pre_cadastro_acesso.'.$preCadastro->id);

        return is_array($sessao)
            && ($sessao['expires_at'] ?? 0) >= now()->timestamp
            && hash_equals(strtoupper($preCadastro->chave_acesso ?? ''), strtoupper((string) ($sessao['chave'] ?? '')));
    }

    private function contexto(PreCadastro $preCadastro, DocumentoObrigatorioPreCadastro $documento, $vida, array $dados): array
    {
        $titular = $preCadastro->vidas->first(fn ($item) => in_array($item->tipo, ['titular', 'socio', 'responsavel_legal'], true));
        $titularPf = $preCadastro->vidas->firstWhere('tipo', 'titular');
        $responsavelLegal = $preCadastro->vidas->firstWhere('tipo', 'responsavel_legal');
        $vinculado = $vida?->vinculo_beneficiario_id
            ? $preCadastro->vidas->firstWhere('id', $vida->vinculo_beneficiario_id)
            : null;

        return array_filter([
            'tipo_documento_esperado' => $dados['tipo_documento_esperado'] ?? $documento->tipoDocumento?->nome,
            'tipo_beneficiario' => $dados['tipo_beneficiario_atual'] ?? $vida?->tipo,
            'nome_beneficiario' => $dados['nome_beneficiario_atual'] ?? $vida?->nome,
            'cpf_beneficiario' => isset($dados['cpf_beneficiario_atual']) ? preg_replace('/\D/', '', $dados['cpf_beneficiario_atual']) : $vida?->cpf,
            'data_nascimento_beneficiario' => $dados['data_nascimento_beneficiario_atual'] ?? $vida?->data_nascimento?->format('Y-m-d'),
            'sexo_beneficiario' => $dados['sexo_beneficiario_atual'] ?? $vida?->sexo,
            'nome_vinculado' => $vinculado?->nome,
            'cpf_vinculado' => $vinculado?->cpf,
            'tipo_vinculado' => $vinculado?->tipo,
            'nome_titular' => $titular?->nome,
            'cpf_titular' => $titular?->cpf,
            'titular_pf_nome' => $titularPf?->nome,
            'titular_pf_cpf' => $titularPf?->cpf,
            'nome_responsavel_legal' => $responsavelLegal?->nome,
            'cpf_responsavel_legal' => $responsavelLegal?->cpf,
            'responsavel_legal_nome' => $responsavelLegal?->nome,
            'responsavel_legal_cpf' => $responsavelLegal?->cpf,
            'contexto_pj_disponivel' => $preCadastro->pessoa === 'PJ',
            'razao_social_empresa' => $preCadastro->pessoa === 'PJ' ? $preCadastro->indicacao?->nome_cliente : null,
            'razao_social_empresa_confiavel' => false,
            'cnpj_empresa' => null,
            'lista_beneficiarios' => $preCadastro->vidas->map(fn ($item) => [
                'nome' => $item->nome,
                'cpf' => $item->cpf,
                'data_nascimento' => $item->data_nascimento?->format('Y-m-d'),
                'tipo' => $item->tipo,
            ])->values()->all(),
        ], fn ($value) => $value !== '');
    }

    private function camposPersistencia(array $resultado): array
    {
        return collect($resultado)
            ->only([
                'status',
                'tipo_documento_identificado',
                'documento_corresponde_ao_tipo',
                'legivel',
                'cortado',
                'desfocado',
                'escuro',
                'possui_foto',
                'documento_possui_nome',
                'documento_possui_cpf',
                'documento_possui_cnpj',
                'nome_extraido',
                'cpf_extraido',
                'cnpj_extraido',
                'data_nascimento_extraida',
                'nome_vinculado_extraido',
                'razao_social_extraida',
                'endereco_extraido',
                'data_documento_extraida',
                'match_nome',
                'match_cpf',
                'match_cnpj',
                'match_data_nascimento',
                'match_titular_responsavel',
                'criterio_titularidade_usado',
                'confianca',
                'analise_parcial',
                'paginas_analisadas',
                'total_paginas_pdf',
                'motivos',
                'mensagem_cliente',
                'mensagem_corretor',
                'raw_response',
                'erro',
                'fase_validacao',
                'validacao_documental_status',
                'validacao_titularidade_status',
                'titularidade_pendente',
                'dados_extraidos',
                'dados_comparados',
            ])
            ->all();
    }

    private function validacaoAnterior(PreCadastro $preCadastro, DocumentoObrigatorioPreCadastro $documento, mixed $validacaoId): ?DocumentoIaValidacao
    {
        if (! $validacaoId) {
            return null;
        }

        return DocumentoIaValidacao::where('id', (int) $validacaoId)
            ->where('pre_cadastro_id', $preCadastro->id)
            ->where('documento_obrigatorio_pre_cadastro_id', $documento->id)
            ->first();
    }

    private function atualizarDispensasPorIdentidade(PreCadastro $preCadastro, DocumentoObrigatorioPreCadastro $documento, DocumentoIaValidacao $validacao): array
    {
        if ($documento->tipoDocumento?->nome !== 'Documento de identidade com foto') {
            return [];
        }

        $this->limparDispensasGeradasPelaIdentidade($preCadastro, $documento);

        if (! $this->identidadeDispensaCpf($validacao)) {
            return [];
        }

        $cpf = $preCadastro->documentosObrigatorios
            ->first(fn ($item) => (int) $item->vida_proposta_id === (int) $documento->vida_proposta_id
                && $item->tipoDocumento?->nome === 'CPF');

        if (! $cpf) {
            return [];
        }

        $motivo = 'Envio separado de CPF não é necessário. O CPF já foi identificado no documento de identidade enviado.';
        $cpf->update([
            'status' => 'dispensado',
            'dispensado_por_ia' => true,
            'dispensado_por_documento_id' => $documento->id,
            'motivo_dispensa' => $motivo,
            'dispensado_em' => now(),
        ]);

        return [[
            'tipo_documento' => 'CPF',
            'documento_obrigatorio_id' => $cpf->id,
            'motivo' => $motivo,
            'dispensado' => true,
        ]];
    }

    private function limparDispensasGeradasPelaIdentidade(PreCadastro $preCadastro, DocumentoObrigatorioPreCadastro $documento): void
    {
        $preCadastro->documentosObrigatorios
            ->filter(fn ($item) => $item->dispensado_por_ia && (int) $item->dispensado_por_documento_id === (int) $documento->id)
            ->each(fn ($item) => $item->update([
                'status' => 'pendente',
                'dispensado_por_ia' => false,
                'dispensado_por_documento_id' => null,
                'motivo_dispensa' => null,
                'dispensado_em' => null,
                'observacoes' => null,
            ]));
    }

    private function identidadeDispensaCpf(DocumentoIaValidacao $validacao): bool
    {
        return $validacao->status === 'aprovado_para_envio'
            && in_array(mb_strtoupper((string) $validacao->tipo_documento_identificado), ['RG', 'CNH'], true)
            && filled($validacao->cpf_extraido)
            && $validacao->match_cpf === true;
    }
}
