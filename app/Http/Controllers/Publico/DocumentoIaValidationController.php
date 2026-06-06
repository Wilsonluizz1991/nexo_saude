<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidarDocumentoIaRequest;
use App\Models\DocumentoIaValidacao;
use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\PreCadastro;
use App\Models\Vida;
use App\Services\OpenAI\DocumentAiValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        $fase = $this->faseValidacaoEfetiva($documento, $dados);
        $file = $request->file('arquivo');
        $validacaoAnterior = $this->validacaoAnterior($preCadastro, $documento, $dados['ia_validacao_id'] ?? null);
        $path = $file?->store('documentos/ia-validacoes', 'local');

        $vidasAtuais = $this->vidasComDadosAtuais($preCadastro, $documento, $dados);
        $vidaAtual = $vidasAtuais->firstWhere('id', $documento->vida_proposta_id) ?? $vida;

        $contexto = $this->contexto($preCadastro, $documento, $vidaAtual, $dados, $vidasAtuais);
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
        $validacoesCompartilhadas = $this->atualizarCartasPermanenciaCompartilhadas($preCadastro, $documento, $validacao, $vidasAtuais);

        return response()->json([
            'id' => $validacao->id,
            'status' => $validacao->status,
            'mensagem_cliente' => $validacao->mensagem_cliente,
            'motivos' => $validacao->motivos ?? [],
            'analise_parcial' => $validacao->analise_parcial,
            'titularidade_pendente' => $validacao->titularidade_pendente,
            'dispensas_documentais' => $dispensas,
            'validacoes_compartilhadas' => $validacoesCompartilhadas,
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

    private function contexto(PreCadastro $preCadastro, DocumentoObrigatorioPreCadastro $documento, $vida, array $dados, ?Collection $vidasAtuais = null): array
    {
        $vidas = $vidasAtuais ?? $preCadastro->vidas;
        $titular = $vidas->first(fn ($item) => in_array($item->tipo, ['titular', 'socio', 'responsavel_legal'], true));
        $titularPf = $vidas->firstWhere('tipo', 'titular');
        $responsavelLegal = $vidas->firstWhere('tipo', 'responsavel_legal');
        $vinculado = $vida?->vinculo_beneficiario_id
            ? $vidas->firstWhere('id', $vida->vinculo_beneficiario_id)
            : null;

        return array_filter([
            'tipo_documento_esperado' => $dados['tipo_documento_esperado'] ?? $documento->tipoDocumento?->nome,
            'tipo_beneficiario' => $vida?->tipo,
            'nome_beneficiario' => $vida?->nome,
            'cpf_beneficiario' => $vida?->cpf,
            'data_nascimento_beneficiario' => $this->formatarDataVida($vida),
            'sexo_beneficiario' => $vida?->sexo,
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
            'lista_beneficiarios' => $vidas->map(fn ($item) => [
                'nome' => $item->nome,
                'cpf' => $item->cpf,
                'data_nascimento' => $this->formatarDataVida($item),
                'tipo' => $item->tipo,
            ])->values()->all(),
        ], fn ($value) => $value !== '');
    }

    private function vidasComDadosAtuais(PreCadastro $preCadastro, DocumentoObrigatorioPreCadastro $documento, array $dados): Collection
    {
        $vidasAtuais = collect($dados['vidas_atuais'] ?? []);

        return $preCadastro->vidas->map(function (Vida $vida) use ($vidasAtuais, $documento, $dados) {
            $dadosVida = collect($vidasAtuais->get($vida->id) ?? $vidasAtuais->get((string) $vida->id) ?? []);
            $clone = clone $vida;

            if ((int) $vida->id === (int) $documento->vida_proposta_id) {
                $dadosVida = $dadosVida->merge([
                    'nome' => $dados['nome_beneficiario_atual'] ?? $dadosVida->get('nome'),
                    'cpf' => $dados['cpf_beneficiario_atual'] ?? $dadosVida->get('cpf'),
                    'data_nascimento' => $dados['data_nascimento_beneficiario_atual'] ?? $dadosVida->get('data_nascimento'),
                    'sexo' => $dados['sexo_beneficiario_atual'] ?? $dadosVida->get('sexo'),
                    'tipo' => $dados['tipo_beneficiario_atual'] ?? $dadosVida->get('tipo'),
                ]);
            }

            if ($dadosVida->has('nome')) {
                $clone->nome = $this->valorAtualOuBanco($dadosVida->get('nome'), $vida->nome);
            }

            if ($dadosVida->has('cpf')) {
                $clone->cpf = $this->normalizarDocumento($dadosVida->get('cpf')) ?? $vida->cpf;
            }

            if ($dadosVida->has('data_nascimento')) {
                $clone->data_nascimento = $this->normalizarData($dadosVida->get('data_nascimento')) ?? $vida->data_nascimento;
            }

            if ($dadosVida->has('sexo')) {
                $clone->sexo = $this->valorAtualOuBanco($dadosVida->get('sexo'), $vida->sexo);
            }

            if ($dadosVida->has('tipo')) {
                $clone->tipo = $this->valorAtualOuBanco($dadosVida->get('tipo'), $vida->tipo);
            }

            return $clone;
        });
    }

    private function valorAtualOuBanco(mixed $atual, mixed $banco): mixed
    {
        $atual = is_string($atual) ? trim($atual) : $atual;

        return blank($atual) ? $banco : $atual;
    }

    private function formatarDataVida($vida): ?string
    {
        $data = $vida?->data_nascimento;

        if ($data instanceof \Carbon\CarbonInterface) {
            return $data->format('Y-m-d');
        }

        return $this->normalizarData($data);
    }

    private function faseValidacaoEfetiva(DocumentoObrigatorioPreCadastro $documento, array $dados): string
    {
        if (($dados['fase_validacao'] ?? 'completa') !== 'documental') {
            return $dados['fase_validacao'] ?? 'completa';
        }

        if ($this->tipoDocumentoNormalizado($documento->tipoDocumento?->nome) !== 'carta de permanencia') {
            return 'documental';
        }

        $nome = trim((string) ($dados['nome_beneficiario_atual'] ?? ''));
        $cpf = $this->normalizarDocumento($dados['cpf_beneficiario_atual'] ?? null);

        return ($nome !== '' && $cpf) ? 'completa' : 'documental';
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
                'beneficiarios_extraidos',
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

    private function atualizarCartasPermanenciaCompartilhadas(PreCadastro $preCadastro, DocumentoObrigatorioPreCadastro $documento, DocumentoIaValidacao $validacao, ?Collection $vidasAtuais = null): array
    {
        if ($this->tipoDocumentoNormalizado($documento->tipoDocumento?->nome) !== 'carta de permanencia') {
            return [];
        }

        $beneficiariosExtraidos = collect($validacao->beneficiarios_extraidos ?? [])
            ->filter(fn ($item) => is_array($item) && filled($item['nome'] ?? null))
            ->values();

        $this->limparValidacoesCompartilhadasDaCarta($preCadastro, $documento);

        if ($beneficiariosExtraidos->isEmpty()) {
            return [];
        }

        $preCadastro->loadMissing('vidas', 'documentosObrigatorios.tipoDocumento');
        $vidas = $vidasAtuais ?? $preCadastro->vidas;
        $vidaOrigem = $vidas->firstWhere('id', $documento->vida_proposta_id);
        $matchOrigem = $vidaOrigem ? $this->beneficiarioEncontradoNaCarta($vidaOrigem, $beneficiariosExtraidos, $vidas) : null;

        if (! $this->cartaPermanenciaAprovada($validacao) && ! $this->cartaPermanenciaConfirmadaPorBeneficiarioOrigem($validacao, $matchOrigem)) {
            return [];
        }

        if ($validacao->status !== 'aprovado_para_envio') {
            $validacao->forceFill([
                'status' => 'aprovado_para_envio',
                'mensagem_cliente' => 'Carta de Permanência aprovada pela IA.',
                'mensagem_corretor' => trim(($validacao->mensagem_corretor ?: '').' Carta confirmada por nome e CPF do beneficiário no documento familiar.'),
            ])->save();
        }

        $documento->update([
            'status' => 'aprovado_ia',
            'validado_por_documento_compartilhado' => false,
            'documento_origem_id' => null,
            'beneficiario_origem_id' => null,
            'tipo_regra_validacao' => 'validacao_direta',
            'motivo_validacao' => 'Carta de Permanência aprovada pela IA.',
        ]);

        return $preCadastro->documentosObrigatorios
            ->filter(fn ($item) => (int) $item->id !== (int) $documento->id
                && (int) $item->pre_cadastro_id === (int) $preCadastro->id
                && $this->tipoDocumentoNormalizado($item->tipoDocumento?->nome) === 'carta de permanencia')
            ->map(function (DocumentoObrigatorioPreCadastro $item) use ($vidas, $documento, $beneficiariosExtraidos) {
                $vida = $vidas->firstWhere('id', $item->vida_proposta_id);
                $match = $vida ? $this->beneficiarioEncontradoNaCarta($vida, $beneficiariosExtraidos, $vidas) : null;

                if (! $match) {
                    if ($item->status === 'aprovado_ia'
                        && $item->tipo_regra_validacao === 'documento_compartilhado_grupo_familiar'
                        && (int) $item->documento_origem_id === (int) $documento->id) {
                        $item->update([
                            'status' => 'pendente',
                            'validado_por_documento_compartilhado' => false,
                            'documento_origem_id' => null,
                            'beneficiario_origem_id' => null,
                            'tipo_regra_validacao' => null,
                            'motivo_validacao' => 'Carta de Permanência pendente. Beneficiário não localizado no documento enviado.',
                        ]);
                    }

                    return null;
                }

                $motivo = 'Carta de Permanência aprovada automaticamente. Beneficiário encontrado na carta de permanência familiar anexada ao titular.';
                $item->update([
                    'status' => 'aprovado_ia',
                    'validado_por_documento_compartilhado' => true,
                    'documento_origem_id' => $documento->id,
                    'beneficiario_origem_id' => $documento->vida_proposta_id,
                    'tipo_regra_validacao' => 'documento_compartilhado_grupo_familiar',
                    'motivo_validacao' => $motivo,
                    'observacoes' => null,
                ]);

                return [
                    'documento_obrigatorio_id' => $item->id,
                    'beneficiario_id' => $item->vida_proposta_id,
                    'status' => 'aprovado_ia',
                    'validado_por_documento_compartilhado' => true,
                    'documento_origem_id' => $documento->id,
                    'beneficiario_origem_id' => $documento->vida_proposta_id,
                    'motivo' => $motivo,
                    'criterio' => $match['criterio'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function limparValidacoesCompartilhadasDaCarta(PreCadastro $preCadastro, DocumentoObrigatorioPreCadastro $documento): void
    {
        $preCadastro->documentosObrigatorios
            ->filter(fn ($item) => $item->validado_por_documento_compartilhado
                && $item->tipo_regra_validacao === 'documento_compartilhado_grupo_familiar'
                && (int) $item->documento_origem_id === (int) $documento->id)
            ->each(fn ($item) => $item->update([
                'status' => 'pendente',
                'validado_por_documento_compartilhado' => false,
                'documento_origem_id' => null,
                'beneficiario_origem_id' => null,
                'tipo_regra_validacao' => null,
                'motivo_validacao' => null,
            ]));
    }

    private function cartaPermanenciaAprovada(DocumentoIaValidacao $validacao): bool
    {
        return $validacao->status === 'aprovado_para_envio'
            && ($validacao->documento_corresponde_ao_tipo === true
                || $this->tipoDocumentoNormalizado($validacao->tipo_documento_identificado) === 'carta de permanencia');
    }

    private function cartaPermanenciaConfirmadaPorBeneficiarioOrigem(DocumentoIaValidacao $validacao, ?array $matchOrigem): bool
    {
        if (! $matchOrigem) {
            return false;
        }

        if ($validacao->documento_corresponde_ao_tipo === false) {
            return false;
        }

        if ($validacao->legivel === false || $validacao->cortado === true || $validacao->desfocado === true || $validacao->escuro === true) {
            return false;
        }

        if ($validacao->tipo_documento_identificado && $this->tipoDocumentoNormalizado($validacao->tipo_documento_identificado) !== 'carta de permanencia') {
            return false;
        }

        return in_array($matchOrigem['criterio'] ?? null, ['nome_cpf', 'nome_data_nascimento', 'nome_alta_similaridade'], true);
    }

    private function beneficiarioEncontradoNaCarta(Vida $vida, Collection $beneficiariosExtraidos, Collection $vidasDoPreCadastro): ?array
    {
        $cpfVida = $this->normalizarDocumento($vida->cpf);
        $nascimentoVida = $this->formatarDataVida($vida);
        $nomeVida = $this->normalizarNome($vida->nome);

        foreach ($beneficiariosExtraidos as $extraido) {
            $cpfExtraido = $this->normalizarDocumento($extraido['cpf'] ?? null);
            $nascimentoExtraido = $this->normalizarData($extraido['data_nascimento'] ?? null);
            $nomeExtraido = $this->normalizarNome($extraido['nome'] ?? null);
            $similaridadeNome = $this->similaridadeNome($nomeVida, $nomeExtraido);

            if ($cpfVida && $cpfExtraido && ! hash_equals($cpfVida, $cpfExtraido)) {
                continue;
            }

            if ($nascimentoVida && $nascimentoExtraido && ! hash_equals($nascimentoVida, $nascimentoExtraido)) {
                continue;
            }

            if ($cpfVida && $cpfExtraido && hash_equals($cpfVida, $cpfExtraido) && $similaridadeNome >= 70) {
                return ['criterio' => 'nome_cpf', 'similaridade_nome' => $similaridadeNome];
            }

            if ($nascimentoVida && $nascimentoExtraido && hash_equals($nascimentoVida, $nascimentoExtraido) && $similaridadeNome >= 80) {
                return ['criterio' => 'nome_data_nascimento', 'similaridade_nome' => $similaridadeNome];
            }

            if ($similaridadeNome >= 92 && ! $this->nomeAmbiguoNoPreCadastro($nomeExtraido, $vida, $vidasDoPreCadastro)) {
                return ['criterio' => 'nome_alta_similaridade', 'similaridade_nome' => $similaridadeNome];
            }
        }

        return null;
    }

    private function nomeAmbiguoNoPreCadastro(string $nomeExtraido, Vida $vidaEsperada, Collection $vidasDoPreCadastro): bool
    {
        return $vidasDoPreCadastro
            ->filter(fn ($vida) => (int) $vida->id !== (int) $vidaEsperada->id
                && $this->similaridadeNome($this->normalizarNome($vida->nome), $nomeExtraido) >= 92)
            ->isNotEmpty();
    }

    private function normalizarDocumento(?string $valor): ?string
    {
        $normalizado = preg_replace('/\D/', '', (string) $valor);

        return $normalizado !== '' ? $normalizado : null;
    }

    private function normalizarData(?string $valor): ?string
    {
        if (blank($valor)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizarNome(?string $valor): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::ascii(mb_strtolower((string) $valor))));
    }

    private function similaridadeNome(string $esperado, string $extraido): int
    {
        if ($esperado === '' || $extraido === '') {
            return 0;
        }

        similar_text($esperado, $extraido, $percentual);

        return (int) round($percentual);
    }

    private function tipoDocumentoNormalizado(?string $tipoDocumento): string
    {
        return Str::ascii(mb_strtolower(trim((string) $tipoDocumento)));
    }
}
