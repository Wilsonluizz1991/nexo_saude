<?php

namespace App\Services\OpenAI;

use App\Models\DocumentoObrigatorioPreCadastro;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentAiValidationService
{
    public function __construct(private readonly PdfToImageConverter $pdfConverter)
    {
    }

    public function validate(DocumentoObrigatorioPreCadastro $documento, ?UploadedFile $file, array $contexto, string $faseValidacao = 'completa', ?array $validacaoAnterior = null): array
    {
        if (! config('services.openai.document_validation_enabled')) {
            return $this->inconclusivo('Validacao automatica desativada. O arquivo sera enviado para analise do corretor.');
        }

        if (! config('services.openai.api_key')) {
            return $this->inconclusivo('Validacao automatica indisponivel no momento. O arquivo sera enviado para analise do corretor.', 'OPENAI_API_KEY ausente.');
        }

        $pdfConversion = null;

        try {
            $content = [['type' => 'input_text', 'text' => $this->prompt($documento, $contexto, $faseValidacao, $validacaoAnterior)]];

            if ($file && $this->isPdf($file)) {
                $pdfConversion = $this->pdfConverter->convert($file, $this->pageLimitFor($documento->tipoDocumento?->nome));

                foreach ($pdfConversion->imagePaths as $index => $path) {
                    $content[] = [
                        'type' => 'input_text',
                        'text' => 'Pagina '.($index + 1).' do PDF convertido para imagem temporaria.',
                    ];
                    $content[] = [
                        'type' => 'input_image',
                        'image_url' => 'data:image/jpeg;base64,'.base64_encode(file_get_contents($path)),
                        'detail' => 'high',
                    ];
                }
            } elseif ($file) {
                $content[] = [
                    'type' => 'input_image',
                    'image_url' => 'data:'.$file->getMimeType().';base64,'.base64_encode(file_get_contents($file->getRealPath())),
                    'detail' => 'high',
                ];
            }

            $response = Http::timeout((int) config('services.openai.timeout', 60))
                ->withToken(config('services.openai.api_key'))
                ->acceptJson()
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.model', 'gpt-4.1-mini'),
                    'input' => [[
                        'role' => 'user',
                        'content' => $content,
                    ]],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'documento_validacao_ia',
                            'strict' => true,
                            'schema' => $this->schema(),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Falha na validacao IA do documento', [
                    'status' => $response->status(),
                    'documento_id' => $documento->id,
                ]);

                return $this->inconclusivo('Nao foi possivel validar automaticamente agora. Tente novamente ou envie para analise do corretor.', 'OpenAI retornou HTTP '.$response->status());
            }

            $json = $this->extractJson($response->json());
            $validado = $this->normalizarResultado($json);
            $this->aplicarRegrasPorFase($validado, $documento, $contexto, $faseValidacao);
            $this->aplicarMetadadosPdf($validado, $pdfConversion);
            $this->aplicarMetadadosFase($validado, $faseValidacao, $contexto);
            $validado['raw_response'] = $response->json();

            return $validado;
        } catch (\Throwable $e) {
            Log::warning('Excecao segura na validacao IA do documento', [
                'documento_id' => $documento->id,
                'message' => $e->getMessage(),
            ]);

            if ($file && $this->isPdf($file) && ! $pdfConversion) {
                return $this->inconclusivo('Falha ao converter PDF para analise automatica. O arquivo sera enviado para analise do corretor.', 'Falha ao converter PDF para analise automatica.');
            }

            return $this->inconclusivo('Nao foi possivel validar automaticamente agora. Tente novamente ou envie para analise do corretor.', $e->getMessage());
        } finally {
            if ($pdfConversion) {
                $this->pdfConverter->cleanup($pdfConversion);
            }
        }
    }

    private function prompt(DocumentoObrigatorioPreCadastro $documento, array $contexto, string $faseValidacao, ?array $validacaoAnterior): string
    {
        $dadosExtraidos = $validacaoAnterior ? $this->dadosExtraidos($validacaoAnterior) : null;

        return 'Voce e um validador documental da Nexo Saude. Nao aprove contrato, nao aprove funil e nao substitua a revisao do corretor. '.
            'Sua unica tarefa e validar se o arquivo enviado corresponde ao tipo documental solicitado e pertence a pessoa, vinculo ou empresa esperada. '.
            'Fase da validacao: '.$faseValidacao.'. '.
            'Documento esperado: '.$documento->tipoDocumento?->nome.'. '.
            'Contexto confiavel para comparacao: '.json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'. '.
            'Dados extraidos anteriormente para fase titularidade: '.json_encode($dadosExtraidos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'. '.
            'Regra critica: os dados extraidos do documento devem ser comparados obrigatoriamente com os dados digitados nos inputs atuais do contexto. Esses dados atuais prevalecem sobre dados salvos no banco. '.
            'Se fase_validacao=documental, valide somente tipo, legibilidade, corte, borrao, escuro e se ha dados extraiveis; nao valide titularidade, nao reprove por ausencia de nome/CPF/data digitados e nao aprove definitivamente. Se tipo correto e legivel, retorne analise_inconclusiva com titularidade_pendente=true. '.
            'Se fase_validacao=titularidade, compare os dados extraidos anteriormente com os dados atuais dos inputs e nao exija novo arquivo. '.
            'Se fase_validacao=completa, valide tipo, qualidade e titularidade na mesma analise. '.
            'Regra geral obrigatoria: aprovado_para_envio somente quando o tipo estiver correto, o documento estiver legivel/completo e os dados principais forem compativeis com o contexto. '.
            'Nunca aprove apenas porque o arquivo e legivel ou parece ser do tipo correto. Sempre compare nome, CPF, CNPJ, data de nascimento ou razao social quando existirem no contexto. '.
            'Use reenviar para documento errado, ilegivel, cortado, escuro, desfocado ou com nome/CPF/CNPJ/data claramente divergente. '.
            'Use analise_inconclusiva quando nao conseguir extrair dados, quando houver duvida razoavel ou quando o contexto confiavel nao existir. '.
            'Documento de identidade com foto: aceite RG ou CNH, exija foto, compare nome_beneficiario, cpf_beneficiario e data_nascimento_beneficiario quando visiveis. Se nome nao for extraivel, use analise_inconclusiva; se nome/CPF/data divergirem claramente, use reenviar. '.
            'CPF: extraia e compare o CPF com cpf_beneficiario; CPF divergente e reenviar. '.
            'Comprovante de residencia: aceite agua, luz, gas, internet, telefone, fatura bancaria/cartao, IPTU, contrato ou declaracao de residencia; exija endereco visivel; compare nome do comprovante com nome_beneficiario, nome_titular, titular_pf_nome, responsavel_legal_nome ou nome_vinculado. Terceiro sem relacao e reenviar; sem nome ou sem confirmacao de vinculo e analise_inconclusiva. '.
            'Certidao de nascimento: compare nome e data do beneficiario. Certidao de casamento ou uniao estavel: confirme as partes com beneficiario, titular ou vinculado. '.
            'Carta de permanencia: compare nome do beneficiario/titular e identifique operadora anterior. Se a carta contiver um grupo familiar, extraia todos os beneficiarios citados no campo beneficiarios_extraidos, com nome, CPF e data de nascimento quando existirem. Essa extracao sera usada pelo backend para a regra documento_compartilhado_grupo_familiar somente dentro do mesmo pre-cadastro. CPF ou data divergentes nunca devem ser tratados como compatibilidade automatica. '.
            'Cartao CNPJ: extraia CNPJ e razao social; se cnpj_empresa for null ou razao_social_empresa_confiavel for false, nao aprove automaticamente por conferencia empresarial, use analise_inconclusiva quando o tipo estiver correto. '.
            'Contrato social: extraia razao social, CNPJ e socios; se nao houver CNPJ/razao social confiaveis no contexto, nao aprove automaticamente apenas por parecer contrato social. '.
            'Relacao de vidas: compare nomes extraidos com lista_beneficiarios. Documento do responsavel legal: valide como identidade com foto e compare com responsavel_legal_nome/cpf. '.
            'Se receber varias imagens, trate-as como paginas do mesmo PDF. '.
            'A mensagem_cliente deve ser curta e objetiva. A mensagem_corretor deve detalhar tipo identificado, nome, CPF/CNPJ, campos conferidos e motivo da decisao. '.
            'Retorne somente JSON valido conforme o schema.';
    }

    private function extractJson(array $payload): array
    {
        if (isset($payload['output_text'])) {
            return json_decode($payload['output_text'], true, flags: JSON_THROW_ON_ERROR);
        }

        foreach ($payload['output'] ?? [] as $output) {
            foreach ($output['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text' && isset($content['text'])) {
                    return json_decode($content['text'], true, flags: JSON_THROW_ON_ERROR);
                }
            }
        }

        throw new \RuntimeException('Resposta da OpenAI sem output_text.');
    }

    private function normalizarResultado(array $resultado): array
    {
        $status = in_array($resultado['status'] ?? null, ['aprovado_para_envio', 'reenviar', 'analise_inconclusiva'], true)
            ? $resultado['status']
            : 'analise_inconclusiva';

        $resultado['status'] = $status;
        $resultado['confianca'] = isset($resultado['confianca']) ? max(0, min(100, (int) $resultado['confianca'])) : null;
        $resultado['motivos'] = array_values(array_filter((array) ($resultado['motivos'] ?? [])));
        $resultado['mensagem_cliente'] = $resultado['mensagem_cliente'] ?: $this->mensagemPadrao($status);
        $resultado['analise_parcial'] = (bool) ($resultado['analise_parcial'] ?? false);
        $resultado['titularidade_pendente'] = (bool) ($resultado['titularidade_pendente'] ?? false);
        $resultado['documento_possui_nome'] = $resultado['documento_possui_nome'] ?? ($resultado['nome_extraido'] ? true : null);
        $resultado['documento_possui_cpf'] = $resultado['documento_possui_cpf'] ?? ($resultado['cpf_extraido'] ? true : null);
        $resultado['documento_possui_cnpj'] = $resultado['documento_possui_cnpj'] ?? ($resultado['cnpj_extraido'] ? true : null);
        $resultado['beneficiarios_extraidos'] = array_values(array_filter((array) ($resultado['beneficiarios_extraidos'] ?? []), fn ($item) => is_array($item)));

        return $resultado;
    }

    private function aplicarRegrasPorFase(array &$resultado, DocumentoObrigatorioPreCadastro $documento, array $contexto, string $faseValidacao): void
    {
        if ($faseValidacao === 'documental') {
            $this->aplicarRegrasDocumentais($resultado);
            return;
        }

        $this->aplicarRegrasDeTitularidade($resultado, $documento, $contexto);
    }

    private function aplicarRegrasDocumentais(array &$resultado): void
    {
        if (($resultado['documento_corresponde_ao_tipo'] ?? null) === false) {
            $this->forcarReenviar($resultado, 'O arquivo enviado nao corresponde ao documento solicitado.');
            $resultado['titularidade_pendente'] = false;
            return;
        }

        if (($resultado['legivel'] ?? null) === false || ($resultado['cortado'] ?? null) === true || ($resultado['desfocado'] ?? null) === true || ($resultado['escuro'] ?? null) === true) {
            $this->forcarReenviar($resultado, 'O documento nao esta legivel ou completo para validacao automatica.');
            $resultado['titularidade_pendente'] = false;
            return;
        }

        $resultado['status'] = 'analise_inconclusiva';
        $resultado['titularidade_pendente'] = true;
        $resultado['mensagem_cliente'] = 'Documento legivel e do tipo correto. Preencha os dados pessoais para concluir a validacao.';
        $resultado['mensagem_corretor'] = trim((string) ($resultado['mensagem_corretor'] ?? '').' Validacao documental concluida; titularidade pendente pelos dados pessoais.');
    }

    private function aplicarRegrasDeTitularidade(array &$resultado, DocumentoObrigatorioPreCadastro $documento, array $contexto): void
    {
        $tipo = $this->tipoNormalizado($documento->tipoDocumento?->nome);

        if (($resultado['documento_corresponde_ao_tipo'] ?? null) === false) {
            $this->forcarReenviar($resultado, 'O arquivo enviado nao corresponde ao documento solicitado.');
            return;
        }

        if (($resultado['legivel'] ?? null) === false || ($resultado['cortado'] ?? null) === true || ($resultado['desfocado'] ?? null) === true || ($resultado['escuro'] ?? null) === true) {
            $this->forcarReenviar($resultado, 'O documento nao esta legivel ou completo para validacao automatica.');
            return;
        }

        match ($tipo) {
            'documento de identidade com foto',
            'documento do responsavel legal' => $this->validarIdentidadeComFoto($resultado, $tipo),
            'cpf' => $this->validarCpf($resultado),
            'comprovante de residencia' => $this->validarComprovanteResidencia($resultado),
            'cartao cnpj',
            'contrato social' => $this->validarDocumentoEmpresarialSemContextoForte($resultado, $contexto),
            default => null,
        };
    }

    private function validarIdentidadeComFoto(array &$resultado, string $tipo): void
    {
        if (($resultado['possui_foto'] ?? null) === false) {
            $this->forcarReenviar($resultado, 'O documento enviado nao possui foto.');
            return;
        }

        if (($resultado['match_nome'] ?? null) === false || ($resultado['match_cpf'] ?? null) === false || ($resultado['match_data_nascimento'] ?? null) === false) {
            $this->forcarReenviar($resultado, 'Os dados do documento nao correspondem ao beneficiario informado.');
            return;
        }

        if (($resultado['documento_possui_nome'] ?? null) === false || blank($resultado['nome_extraido'] ?? null)) {
            $this->forcarInconclusivo($resultado, 'Nao foi possivel confirmar automaticamente o nome no documento.');
            return;
        }

        $nomeConfere = ($resultado['match_nome'] ?? null) === true || ($tipo === 'documento do responsavel legal' && ($resultado['match_titular_responsavel'] ?? null) === true);

        if (($resultado['status'] ?? null) === 'aprovado_para_envio' && ! $nomeConfere) {
            $this->forcarInconclusivo($resultado, 'Nao foi possivel confirmar automaticamente a titularidade do documento.');
        }
    }

    private function validarCpf(array &$resultado): void
    {
        if (($resultado['match_cpf'] ?? null) === false) {
            $this->forcarReenviar($resultado, 'O CPF identificado nao corresponde ao CPF informado.');
            return;
        }

        if (($resultado['documento_possui_cpf'] ?? null) === false || blank($resultado['cpf_extraido'] ?? null)) {
            $this->forcarInconclusivo($resultado, 'Nao foi possivel confirmar automaticamente o CPF no documento.');
            return;
        }

        if (($resultado['status'] ?? null) === 'aprovado_para_envio' && ($resultado['match_cpf'] ?? null) !== true) {
            $this->forcarInconclusivo($resultado, 'Nao foi possivel confirmar automaticamente se o CPF pertence ao beneficiario.');
        }
    }

    private function validarComprovanteResidencia(array &$resultado): void
    {
        if (($resultado['match_nome'] ?? null) === false && ($resultado['match_titular_responsavel'] ?? null) === false) {
            $this->forcarReenviar($resultado, 'O nome identificado no comprovante nao possui relacao com o contexto informado.');
            return;
        }

        if (($resultado['documento_possui_nome'] ?? null) === false || blank($resultado['nome_extraido'] ?? null)) {
            $this->forcarInconclusivo($resultado, 'Nao foi possivel confirmar automaticamente o nome no comprovante.');
            return;
        }

        if (blank($resultado['endereco_extraido'] ?? null)) {
            $this->forcarInconclusivo($resultado, 'Nao foi possivel confirmar automaticamente o endereco no comprovante.');
            return;
        }

        if (($resultado['status'] ?? null) === 'aprovado_para_envio' && ($resultado['match_nome'] ?? null) !== true && ($resultado['match_titular_responsavel'] ?? null) !== true) {
            $this->forcarInconclusivo($resultado, 'Nao foi possivel confirmar automaticamente o vinculo do comprovante.');
        }
    }

    private function validarDocumentoEmpresarialSemContextoForte(array &$resultado, array $contexto): void
    {
        $temContextoConfiavel = ! blank($contexto['cnpj_empresa'] ?? null)
            || (($contexto['razao_social_empresa_confiavel'] ?? false) === true && ! blank($contexto['razao_social_empresa'] ?? null));

        if (($resultado['status'] ?? null) === 'aprovado_para_envio' && ! $temContextoConfiavel) {
            $this->forcarInconclusivo($resultado, 'Documento empresarial identificado, mas nao ha CNPJ ou razao social confiavel para conferencia automatica.');
        }
    }

    private function forcarReenviar(array &$resultado, string $motivo): void
    {
        $resultado['status'] = 'reenviar';
        $this->aplicarMotivoDecisao($resultado, $motivo, 'Nao conseguimos validar este arquivo. '.$motivo);
    }

    private function forcarInconclusivo(array &$resultado, string $motivo): void
    {
        if (($resultado['status'] ?? null) === 'reenviar') {
            return;
        }

        $resultado['status'] = 'analise_inconclusiva';
        $this->aplicarMotivoDecisao($resultado, $motivo, 'Nao foi possivel confirmar automaticamente. O documento sera enviado para analise do corretor.');
    }

    private function aplicarMotivoDecisao(array &$resultado, string $motivo, string $mensagemCliente): void
    {
        $resultado['motivos'] = array_values(array_unique(array_merge($resultado['motivos'] ?? [], [$motivo])));
        $resultado['mensagem_cliente'] = $mensagemCliente;
        $detalhe = trim((string) ($resultado['mensagem_corretor'] ?? ''));
        $resultado['mensagem_corretor'] = trim($detalhe.' Motivo da decisao: '.$motivo);
    }

    private function aplicarMetadadosPdf(array &$resultado, ?PdfConversionResult $pdfConversion): void
    {
        if (! $pdfConversion) {
            return;
        }

        $resultado['analise_parcial'] = $pdfConversion->partial || (bool) ($resultado['analise_parcial'] ?? false);
        $resultado['paginas_analisadas'] = $pdfConversion->analyzedPages;
        $resultado['total_paginas_pdf'] = $pdfConversion->totalPages;

        if (! $pdfConversion->partial) {
            return;
        }

        $motivo = 'Documento possui mais paginas do que o limite analisado.';
        $resultado['motivos'] = array_values(array_unique(array_merge($resultado['motivos'] ?? [], [$motivo])));

        foreach (['mensagem_cliente', 'mensagem_corretor'] as $campo) {
            $texto = trim((string) ($resultado[$campo] ?? ''));
            $resultado[$campo] = $texto === '' ? $motivo : $texto.' '.$motivo;
        }
    }

    private function inconclusivo(string $mensagem, ?string $erro = null): array
    {
        return [
            'status' => 'analise_inconclusiva',
            'tipo_documento_esperado' => null,
            'tipo_documento_identificado' => null,
            'documento_corresponde_ao_tipo' => null,
            'legivel' => null,
            'cortado' => null,
            'desfocado' => null,
            'escuro' => null,
            'possui_foto' => null,
            'documento_possui_nome' => null,
            'documento_possui_cpf' => null,
            'documento_possui_cnpj' => null,
            'nome_extraido' => null,
            'cpf_extraido' => null,
            'cnpj_extraido' => null,
            'data_nascimento_extraida' => null,
            'nome_vinculado_extraido' => null,
            'razao_social_extraida' => null,
            'endereco_extraido' => null,
            'data_documento_extraida' => null,
            'match_nome' => null,
            'match_cpf' => null,
            'match_cnpj' => null,
            'match_data_nascimento' => null,
            'match_titular_responsavel' => null,
            'criterio_titularidade_usado' => null,
            'confianca' => 0,
            'analise_parcial' => false,
            'titularidade_pendente' => false,
            'fase_validacao' => 'completa',
            'validacao_documental_status' => null,
            'validacao_titularidade_status' => null,
            'paginas_analisadas' => null,
            'total_paginas_pdf' => null,
            'dados_extraidos' => null,
            'beneficiarios_extraidos' => [],
            'dados_comparados' => null,
            'motivos' => $erro ? [$erro] : [],
            'mensagem_cliente' => $mensagem,
            'mensagem_corretor' => $erro,
            'raw_response' => null,
            'erro' => $erro,
        ];
    }

    private function mensagemPadrao(string $status): string
    {
        return match ($status) {
            'aprovado_para_envio' => 'Documento validado com sucesso.',
            'reenviar' => 'Nao conseguimos validar este arquivo. Envie uma nova foto com o documento inteiro e legivel.',
            default => 'Nao foi possivel validar com seguranca. O arquivo sera enviado para analise do corretor.',
        };
    }

    private function schema(): array
    {
        $nullableString = ['type' => ['string', 'null']];
        $nullableBoolean = ['type' => ['boolean', 'null']];

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'status' => ['type' => 'string', 'enum' => ['aprovado_para_envio', 'reenviar', 'analise_inconclusiva']],
                'tipo_documento_esperado' => ['type' => 'string'],
                'tipo_documento_identificado' => $nullableString,
                'documento_corresponde_ao_tipo' => $nullableBoolean,
                'legivel' => $nullableBoolean,
                'cortado' => $nullableBoolean,
                'desfocado' => $nullableBoolean,
                'escuro' => $nullableBoolean,
                'possui_foto' => $nullableBoolean,
                'documento_possui_nome' => $nullableBoolean,
                'documento_possui_cpf' => $nullableBoolean,
                'documento_possui_cnpj' => $nullableBoolean,
                'nome_extraido' => $nullableString,
                'cpf_extraido' => $nullableString,
                'cnpj_extraido' => $nullableString,
                'data_nascimento_extraida' => $nullableString,
                'nome_vinculado_extraido' => $nullableString,
                'razao_social_extraida' => $nullableString,
                'endereco_extraido' => $nullableString,
                'data_documento_extraida' => $nullableString,
                'match_nome' => $nullableBoolean,
                'match_cpf' => $nullableBoolean,
                'match_cnpj' => $nullableBoolean,
                'match_data_nascimento' => $nullableBoolean,
                'match_titular_responsavel' => $nullableBoolean,
                'criterio_titularidade_usado' => $nullableString,
                'confianca' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                'analise_parcial' => ['type' => 'boolean'],
                'titularidade_pendente' => ['type' => 'boolean'],
                'beneficiarios_extraidos' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'nome' => $nullableString,
                            'cpf' => $nullableString,
                            'data_nascimento' => $nullableString,
                            'operadora_anterior' => $nullableString,
                            'plano_anterior' => $nullableString,
                        ],
                        'required' => ['nome', 'cpf', 'data_nascimento', 'operadora_anterior', 'plano_anterior'],
                    ],
                ],
                'motivos' => ['type' => 'array', 'items' => ['type' => 'string']],
                'mensagem_cliente' => ['type' => 'string'],
                'mensagem_corretor' => ['type' => 'string'],
            ],
            'required' => [
                'status', 'tipo_documento_esperado', 'tipo_documento_identificado', 'documento_corresponde_ao_tipo',
                'legivel', 'cortado', 'desfocado', 'escuro', 'possui_foto', 'documento_possui_nome',
                'documento_possui_cpf', 'documento_possui_cnpj', 'nome_extraido', 'cpf_extraido',
                'cnpj_extraido', 'data_nascimento_extraida', 'nome_vinculado_extraido', 'razao_social_extraida', 'endereco_extraido',
                'data_documento_extraida', 'match_nome', 'match_cpf', 'match_cnpj', 'match_data_nascimento', 'match_titular_responsavel',
                'criterio_titularidade_usado',
                'confianca', 'analise_parcial', 'titularidade_pendente', 'beneficiarios_extraidos', 'motivos', 'mensagem_cliente', 'mensagem_corretor',
            ],
        ];
    }

    private function aplicarMetadadosFase(array &$resultado, string $faseValidacao, array $contexto): void
    {
        $resultado['fase_validacao'] = $faseValidacao;
        $resultado['titularidade_pendente'] = (bool) ($resultado['titularidade_pendente'] ?? false);
        $resultado['validacao_documental_status'] = in_array($faseValidacao, ['documental', 'completa'], true) ? $resultado['status'] : null;
        $resultado['validacao_titularidade_status'] = in_array($faseValidacao, ['titularidade', 'completa'], true) ? $resultado['status'] : null;
        $resultado['dados_extraidos'] = $this->dadosExtraidos($resultado);
        $resultado['beneficiarios_extraidos'] = $resultado['beneficiarios_extraidos'] ?? [];
        $resultado['dados_comparados'] = [
            'nome_beneficiario' => $contexto['nome_beneficiario'] ?? null,
            'cpf_beneficiario' => $contexto['cpf_beneficiario'] ?? null,
            'data_nascimento_beneficiario' => $contexto['data_nascimento_beneficiario'] ?? null,
            'nome_titular' => $contexto['nome_titular'] ?? null,
            'cpf_titular' => $contexto['cpf_titular'] ?? null,
            'nome_responsavel_legal' => $contexto['nome_responsavel_legal'] ?? null,
            'cpf_responsavel_legal' => $contexto['cpf_responsavel_legal'] ?? null,
            'nome_vinculado' => $contexto['nome_vinculado'] ?? null,
            'cpf_vinculado' => $contexto['cpf_vinculado'] ?? null,
        ];
    }

    private function dadosExtraidos(array $resultado): array
    {
        return [
            'tipo_documento_identificado' => $resultado['tipo_documento_identificado'] ?? null,
            'nome_extraido' => $resultado['nome_extraido'] ?? null,
            'cpf_extraido' => $resultado['cpf_extraido'] ?? null,
            'cnpj_extraido' => $resultado['cnpj_extraido'] ?? null,
            'data_nascimento_extraida' => $resultado['data_nascimento_extraida'] ?? null,
            'nome_vinculado_extraido' => $resultado['nome_vinculado_extraido'] ?? null,
            'razao_social_extraida' => $resultado['razao_social_extraida'] ?? null,
            'endereco_extraido' => $resultado['endereco_extraido'] ?? null,
            'data_documento_extraida' => $resultado['data_documento_extraida'] ?? null,
        ];
    }

    private function pageLimitFor(?string $tipoDocumento): int
    {
        return match ($this->tipoNormalizado($tipoDocumento)) {
            'documento de identidade com foto',
            'cpf',
            'comprovante de residencia',
            'cartao cnpj',
            'carta de permanencia' => 2,
            'certidao de nascimento',
            'certidao de casamento',
            'declaracao de uniao estavel',
            'documento do responsavel legal' => 3,
            'contrato social',
            'relacao de vidas' => 5,
            default => 2,
        };
    }

    private function isPdf(UploadedFile $file): bool
    {
        return $file->getMimeType() === 'application/pdf'
            || strtolower($file->getClientOriginalExtension()) === 'pdf';
    }

    private function tipoNormalizado(?string $tipoDocumento): string
    {
        return Str::ascii(mb_strtolower(trim((string) $tipoDocumento)));
    }
}
