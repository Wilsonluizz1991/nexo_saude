<?php

namespace Tests\Feature;

use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\DocumentoIaValidacao;
use App\Models\Indicacao;
use App\Models\PreCadastro;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Models\Vida;
use App\Services\OpenAI\PdfConversionResult;
use App\Services\OpenAI\PdfToImageConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DocumentoIaValidationTest extends TestCase
{
    use RefreshDatabase;

    private PreCadastro $preCadastro;
    private DocumentoObrigatorioPreCadastro $documento;
    private string $slug = 'CORRETORQA';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config([
            'services.openai.api_key' => 'test-openai-key',
            'services.openai.model' => 'gpt-4.1-mini',
            'services.openai.document_validation_enabled' => true,
        ]);

        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Documento de identidade com foto');
        $this->withSession([
            'pre_cadastro_acesso.'.$this->preCadastro->id => [
                'chave' => $this->preCadastro->chave_acesso,
                'expires_at' => now()->addMinutes(30)->timestamp,
            ],
        ]);
    }

    public function test_endpoint_desabilitado_retorna_inconclusivo_sem_chamar_openai(): void
    {
        config(['services.openai.document_validation_enabled' => false]);
        Http::fake();

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('rg.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'analise_inconclusiva')
            ->assertJsonPath('allow_upload', true)
            ->assertJsonPath('enabled', false);

        Http::assertNothingSent();
        $this->assertDatabaseHas('documento_ia_validacoes', [
            'pre_cadastro_id' => $this->preCadastro->id,
            'status' => 'analise_inconclusiva',
        ]);
    }

    public function test_retorno_aprovado_para_envio(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'RG',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'mensagem_cliente' => 'Documento validado com sucesso.',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('rg.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'aprovado_para_envio')
            ->assertJsonPath('allow_upload', true);
    }

    public function test_retorno_reenviar_remove_validade_do_upload(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'reenviar',
                'tipo_documento_identificado' => 'Certidão de Nascimento',
                'documento_corresponde_ao_tipo' => false,
                'possui_foto' => false,
                'mensagem_cliente' => 'O arquivo enviado parece ser uma certidão, mas aqui é necessário enviar um documento de identidade com foto.',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('certidao.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'reenviar')
            ->assertJsonPath('allow_upload', false);
    }

    public function test_falha_da_openai_nao_bloqueia_definitivamente(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response(['error' => ['message' => 'temporario']], 500),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('documento.jpg'),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'analise_inconclusiva')
            ->assertJsonPath('allow_upload', true);
    }

    public function test_documento_de_identidade_com_foto_aceita_rg_ou_cnh(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'mensagem_cliente' => 'Documento validado com sucesso.',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('cnh.png'),
        ])->assertJsonPath('status', 'aprovado_para_envio');

        Http::assertSent(function ($request) {
            return str_contains(json_encode($request->data()), 'aceite RG ou CNH');
        });
    }

    public function test_documento_de_identidade_com_foto_recusa_certidao(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'reenviar',
                'tipo_documento_identificado' => 'Certidão de Nascimento',
                'documento_corresponde_ao_tipo' => false,
                'possui_foto' => false,
                'mensagem_cliente' => 'O arquivo enviado parece ser uma certidão, mas aqui é necessário enviar um documento de identidade com foto.',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('certidao.png'),
        ])->assertJsonPath('status', 'reenviar');
    }

    public function test_pdf_valido_de_identidade_e_enviado_para_openai(): void
    {
        $this->fakePdfConverter(totalPages: 1, generatedPages: 1);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'mensagem_cliente' => 'Documento validado com sucesso.',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->create('cnh.pdf', 32, 'application/pdf'),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'aprovado_para_envio')
            ->assertJsonPath('analise_parcial', false);

        Http::assertSent(fn ($request) => collect($request->data()['input'][0]['content'])
            ->where('type', 'input_image')
            ->count() === 1);
    }

    public function test_pdf_valido_de_comprovante_e_enviado_para_openai(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Comprovante de Residência');
        $this->withSession([
            'pre_cadastro_acesso.'.$this->preCadastro->id => [
                'chave' => $this->preCadastro->chave_acesso,
                'expires_at' => now()->addMinutes(30)->timestamp,
            ],
        ]);
        $this->fakePdfConverter(totalPages: 1, generatedPages: 1);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Comprovante de Residência',
                'tipo_documento_identificado' => 'Conta de luz',
            'documento_corresponde_ao_tipo' => true,
                'mensagem_cliente' => 'Documento validado com sucesso.',
                'endereco_extraido' => 'Rua QA, 123',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->create('comprovante.pdf', 32, 'application/pdf'),
        ])
            ->assertOk()
            ->assertJsonPath('status', 'aprovado_para_envio');

        Http::assertSent(fn ($request) => str_contains(json_encode($request->data()), 'Comprovante de Resid'));
    }

    public function test_pdf_com_multiplas_paginas_respeita_limite_do_tipo_documental(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Certidão de Nascimento');
        $this->withSession([
            'pre_cadastro_acesso.'.$this->preCadastro->id => [
                'chave' => $this->preCadastro->chave_acesso,
                'expires_at' => now()->addMinutes(30)->timestamp,
            ],
        ]);
        $this->fakePdfConverter(totalPages: 3, generatedPages: 3);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Certidão de Nascimento',
                'tipo_documento_identificado' => 'Certidão de Nascimento',
                'documento_corresponde_ao_tipo' => true,
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->create('certidao.pdf', 32, 'application/pdf'),
        ])->assertOk();

        Http::assertSent(function ($request) {
            $images = collect($request->data()['input'][0]['content'])
                ->where('type', 'input_image')
                ->count();

            return $images === 3;
        });
    }

    public function test_pdf_acima_do_limite_marca_analise_parcial(): void
    {
        $this->fakePdfConverter(totalPages: 4, generatedPages: 2);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'RG',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
            ])),
        ]);

        $response = $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->create('rg-completo.pdf', 32, 'application/pdf'),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'aprovado_para_envio')
            ->assertJsonPath('analise_parcial', true);

        $this->assertContains('Documento possui mais paginas do que o limite analisado.', $response->json('motivos'));

        $this->assertDatabaseHas('documento_ia_validacoes', [
            'documento_obrigatorio_pre_cadastro_id' => $this->documento->id,
            'analise_parcial' => true,
            'paginas_analisadas' => 2,
            'total_paginas_pdf' => 4,
        ]);
    }

    public function test_falha_de_conversao_pdf_retorna_inconclusivo_sem_chamar_openai(): void
    {
        $this->fakePdfConverter(fails: true);
        Http::fake();

        $response = $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->create('documento.pdf', 32, 'application/pdf'),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'analise_inconclusiva')
            ->assertJsonPath('allow_upload', true);

        $this->assertContains('Falha ao converter PDF para analise automatica.', $response->json('motivos'));

        Http::assertNothingSent();
    }

    public function test_cnh_correta_aprova_somente_quando_nome_confere(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'nome_extraido' => 'Cliente QA',
                'match_nome' => true,
                'criterio_titularidade_usado' => 'nome_beneficiario',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('cnh-ok.jpg'),
        ])->assertJsonPath('status', 'aprovado_para_envio');
    }

    public function test_cnh_com_nome_divergente_retorna_reenviar(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'nome_extraido' => 'Outra Pessoa',
                'match_nome' => false,
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('cnh-outra.jpg'),
        ])->assertJsonPath('status', 'reenviar')
            ->assertJsonPath('allow_upload', false);
    }

    public function test_cnh_sem_nome_legivel_retorna_inconclusivo(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'documento_possui_nome' => false,
                'nome_extraido' => null,
                'match_nome' => null,
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('cnh-sem-nome.jpg'),
        ])->assertJsonPath('status', 'analise_inconclusiva')
            ->assertJsonPath('allow_upload', true);
    }

    public function test_cnh_enviada_como_comprovante_retorna_reenviar(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Comprovante de Residência');
        $this->autorizarPreCadastroAtual();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Comprovante de Residência',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => false,
                'mensagem_cliente' => 'Documento incorreto.',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('cnh.jpg'),
        ])->assertJsonPath('status', 'reenviar');
    }

    public function test_comprovante_com_nome_do_titular_aprova(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Comprovante de Residência');
        $this->autorizarPreCadastroAtual();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Comprovante de Residência',
                'tipo_documento_identificado' => 'Conta de luz',
                'documento_corresponde_ao_tipo' => true,
                'nome_extraido' => 'Cliente QA',
                'endereco_extraido' => 'Rua QA, 123',
                'match_nome' => true,
                'criterio_titularidade_usado' => 'nome_beneficiario',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('conta.jpg'),
        ])->assertJsonPath('status', 'aprovado_para_envio');
    }

    public function test_comprovante_sem_nome_retorna_inconclusivo(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Comprovante de Residência');
        $this->autorizarPreCadastroAtual();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Comprovante de Residência',
                'tipo_documento_identificado' => 'Conta de luz',
                'documento_corresponde_ao_tipo' => true,
                'documento_possui_nome' => false,
                'nome_extraido' => null,
                'endereco_extraido' => 'Rua QA, 123',
                'match_nome' => null,
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('conta-sem-nome.jpg'),
        ])->assertJsonPath('status', 'analise_inconclusiva');
    }

    public function test_comprovante_com_nome_de_terceiro_retorna_reenviar(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Comprovante de Residência');
        $this->autorizarPreCadastroAtual();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Comprovante de Residência',
                'tipo_documento_identificado' => 'Conta de luz',
                'documento_corresponde_ao_tipo' => true,
                'nome_extraido' => 'Terceiro Sem Vinculo',
                'endereco_extraido' => 'Rua QA, 123',
                'match_nome' => false,
                'match_titular_responsavel' => false,
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('conta-terceiro.jpg'),
        ])->assertJsonPath('status', 'reenviar');
    }

    public function test_cpf_divergente_retorna_reenviar(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('CPF');
        $this->autorizarPreCadastroAtual();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'CPF',
                'tipo_documento_identificado' => 'CPF',
                'documento_corresponde_ao_tipo' => true,
                'cpf_extraido' => '99999999999',
                'match_cpf' => false,
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('cpf.jpg'),
        ])->assertJsonPath('status', 'reenviar');
    }

    public function test_cartao_cnpj_correto_sem_contexto_empresarial_confiavel_retorna_inconclusivo(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Cartão CNPJ', pessoa: 'PJ', tipoProposta: 'empresarial');
        $this->autorizarPreCadastroAtual();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Cartão CNPJ',
                'tipo_documento_identificado' => 'Cartão CNPJ',
                'documento_corresponde_ao_tipo' => true,
                'cnpj_extraido' => '12345678000190',
                'razao_social_extraida' => 'Empresa QA LTDA',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('cnpj.jpg'),
        ])->assertJsonPath('status', 'analise_inconclusiva');
    }

    public function test_contrato_social_correto_sem_contexto_empresarial_confiavel_retorna_inconclusivo(): void
    {
        [$this->preCadastro, $this->documento] = $this->cenarioDocumento('Contrato Social', pessoa: 'PJ', tipoProposta: 'empresarial');
        $this->autorizarPreCadastroAtual();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Contrato Social',
                'tipo_documento_identificado' => 'Contrato Social',
                'documento_corresponde_ao_tipo' => true,
                'cnpj_extraido' => '12345678000190',
                'razao_social_extraida' => 'Empresa QA LTDA',
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('contrato.jpg'),
        ])->assertJsonPath('status', 'analise_inconclusiva');
    }

    public function test_inputs_preenchidos_antes_cnh_correta_usa_fase_completa_e_aprova(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'nome_extraido' => 'Maria Input',
                'cpf_extraido' => '11122233344',
                'data_nascimento_extraida' => '1988-05-10',
                'match_nome' => true,
                'match_cpf' => true,
                'match_data_nascimento' => true,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'arquivo' => UploadedFile::fake()->image('cnh.jpg'),
            'fase_validacao' => 'completa',
            'nome_beneficiario_atual' => 'Maria Input',
            'cpf_beneficiario_atual' => '111.222.333-44',
            'data_nascimento_beneficiario_atual' => '1988-05-10',
        ]))->assertJsonPath('status', 'aprovado_para_envio');

        $this->assertDatabaseHas('documento_ia_validacoes', [
            'fase_validacao' => 'completa',
            'nome_beneficiario_usado' => 'Maria Input',
            'cpf_beneficiario_usado' => '11122233344',
        ]);

        Http::assertSent(fn ($request) => str_contains(json_encode($request->data()), 'Maria Input'));
    }

    public function test_inputs_preenchidos_antes_cnh_divergente_retorna_reenviar(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'nome_extraido' => 'Outra Pessoa',
                'match_nome' => false,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'arquivo' => UploadedFile::fake()->image('cnh.jpg'),
            'fase_validacao' => 'completa',
        ]))->assertJsonPath('status', 'reenviar');
    }

    public function test_documento_antes_dos_inputs_fica_com_titularidade_pendente(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('cnh.jpg'),
            'fase_validacao' => 'documental',
        ])
            ->assertJsonPath('status', 'analise_inconclusiva')
            ->assertJsonPath('titularidade_pendente', true);
    }

    public function test_depois_de_preencher_inputs_compativeis_fase_titularidade_aprova_sem_novo_upload(): void
    {
        $validacao = $this->criarValidacaoPendente();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'nome_extraido' => 'Cliente QA',
                'cpf_extraido' => '12345678900',
                'match_nome' => true,
                'match_cpf' => true,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'fase_validacao' => 'titularidade',
            'ia_validacao_id' => $validacao->id,
        ]))->assertJsonPath('status', 'aprovado_para_envio');
    }

    public function test_depois_de_preencher_inputs_divergentes_fase_titularidade_reenviar(): void
    {
        $validacao = $this->criarValidacaoPendente();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'nome_extraido' => 'Cliente QA',
                'match_nome' => false,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'fase_validacao' => 'titularidade',
            'ia_validacao_id' => $validacao->id,
            'nome_beneficiario_atual' => 'Outra Pessoa',
        ]))->assertJsonPath('status', 'reenviar');
    }

    public function test_documento_errado_antes_dos_inputs_reenviar_sem_titularidade_pendente(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'analise_inconclusiva',
                'tipo_documento_identificado' => 'Certidão de Nascimento',
                'documento_corresponde_ao_tipo' => false,
            ])),
        ]);

        $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->image('certidao.jpg'),
            'fase_validacao' => 'documental',
        ])
            ->assertJsonPath('status', 'reenviar')
            ->assertJsonPath('titularidade_pendente', false);
    }

    public function test_pdf_funciona_na_fase_documental_e_titularidade(): void
    {
        $this->fakePdfConverter(totalPages: 1, generatedPages: 1);
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
            ])),
        ]);

        $documental = $this->postJson($this->url(), [
            'arquivo' => UploadedFile::fake()->create('cnh.pdf', 32, 'application/pdf'),
            'fase_validacao' => 'documental',
        ])->assertJsonPath('titularidade_pendente', true);

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'match_nome' => true,
                'match_cpf' => true,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'fase_validacao' => 'titularidade',
            'ia_validacao_id' => $documental->json('id'),
        ]))->assertJsonPath('status', 'aprovado_para_envio');
    }

    public function test_identidade_com_cpf_compativel_dispensa_upload_separado_de_cpf(): void
    {
        $cpf = $this->adicionarDocumentoCpf();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'cpf_extraido' => '12345678900',
                'match_cpf' => true,
                'match_nome' => true,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'arquivo' => UploadedFile::fake()->image('cnh-com-cpf.jpg'),
            'fase_validacao' => 'completa',
        ]))
            ->assertJsonPath('status', 'aprovado_para_envio')
            ->assertJsonPath('dispensas_documentais.0.documento_obrigatorio_id', $cpf->id)
            ->assertJsonPath('dispensas_documentais.0.dispensado', true);

        $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
            'id' => $cpf->id,
            'status' => 'dispensado',
            'dispensado_por_ia' => true,
            'dispensado_por_documento_id' => $this->documento->id,
        ]);
    }

    public function test_identidade_sem_cpf_extraido_nao_dispensa_cpf(): void
    {
        $cpf = $this->adicionarDocumentoCpf();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'cpf_extraido' => null,
                'match_cpf' => null,
                'match_nome' => true,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'arquivo' => UploadedFile::fake()->image('cnh-sem-cpf.jpg'),
        ]))->assertJsonPath('dispensas_documentais', []);

        $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
            'id' => $cpf->id,
            'status' => 'pendente',
            'dispensado_por_ia' => false,
        ]);
    }

    public function test_identidade_com_cpf_divergente_nao_dispensa_cpf(): void
    {
        $cpf = $this->adicionarDocumentoCpf();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'cpf_extraido' => '99999999999',
                'match_cpf' => false,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'arquivo' => UploadedFile::fake()->image('cnh-cpf-divergente.jpg'),
        ]))->assertJsonPath('status', 'reenviar')
            ->assertJsonPath('dispensas_documentais', []);

        $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
            'id' => $cpf->id,
            'status' => 'pendente',
            'dispensado_por_ia' => false,
        ]);
    }

    public function test_identidade_inconclusiva_nao_dispensa_cpf(): void
    {
        $cpf = $this->adicionarDocumentoCpf();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'analise_inconclusiva',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'cpf_extraido' => '12345678900',
                'match_cpf' => true,
                'match_nome' => true,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'arquivo' => UploadedFile::fake()->image('cnh-inconclusiva.jpg'),
        ]))->assertJsonPath('dispensas_documentais', []);

        $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
            'id' => $cpf->id,
            'status' => 'pendente',
            'dispensado_por_ia' => false,
        ]);
    }

    public function test_dispensa_de_cpf_vale_apenas_para_mesma_vida(): void
    {
        $cpfMesmaVida = $this->adicionarDocumentoCpf();
        $cpfOutraVida = $this->adicionarCpfDeOutraVida();
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_identificado' => 'RG',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'cpf_extraido' => '12345678900',
                'match_cpf' => true,
                'match_nome' => true,
            ])),
        ]);

        $this->postJson($this->url(), $this->payloadAtual([
            'arquivo' => UploadedFile::fake()->image('rg-com-cpf.jpg'),
        ]))->assertOk();

        $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
            'id' => $cpfMesmaVida->id,
            'status' => 'dispensado',
            'dispensado_por_ia' => true,
        ]);
        $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
            'id' => $cpfOutraVida->id,
            'status' => 'pendente',
            'dispensado_por_ia' => false,
        ]);
    }

    public function test_envio_final_aceita_cpf_dispensado_por_ia(): void
    {
        $cpf = $this->adicionarDocumentoCpf();
        $cpf->update([
            'status' => 'dispensado',
            'dispensado_por_ia' => true,
            'dispensado_por_documento_id' => $this->documento->id,
            'motivo_dispensa' => 'Envio separado de CPF não é necessário. O CPF já foi identificado no documento de identidade enviado.',
            'dispensado_em' => now(),
        ]);

        $validacao = DocumentoIaValidacao::create([
            'pre_cadastro_id' => $this->preCadastro->id,
            'beneficiario_id' => $this->documento->vida_proposta_id,
            'documento_obrigatorio_pre_cadastro_id' => $this->documento->id,
            'tipo_documento_id' => $this->documento->tipo_documento_id,
            'tipo_documento_esperado' => $this->documento->tipoDocumento?->nome,
            'status' => 'aprovado_para_envio',
            'tipo_documento_identificado' => 'CNH',
            'documento_corresponde_ao_tipo' => true,
            'legivel' => true,
            'possui_foto' => true,
            'cpf_extraido' => '12345678909',
            'match_cpf' => true,
            'match_nome' => true,
            'motivos' => [],
            'mensagem_cliente' => 'Documento validado com sucesso.',
            'analisado_em' => now(),
        ]);

        $this->post(route('cliente.pre-cadastro.store', [
            'slug' => $this->slug,
            'token' => $this->preCadastro->token,
        ]), [
            'vidas' => [
                $this->documento->vida_proposta_id => [
                    'nome' => 'Cliente QA',
                    'cpf' => '123.456.789-09',
                    'data_nascimento' => '1990-01-01',
                    'sexo' => 'masculino',
                ],
            ],
            'documentos' => [
                $this->documento->id => UploadedFile::fake()->image('cnh.jpg'),
            ],
            'ia_validacoes' => [
                $this->documento->id => $validacao->id,
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('pre_cadastros', [
            'id' => $this->preCadastro->id,
            'status' => 'documentacao_em_analise',
        ]);
    }

    public function test_carta_permanencia_do_titular_valida_grupo_familiar_completo(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayloadCarta([
                $this->beneficiarioExtraido($vidas['pai']),
                $this->beneficiarioExtraido($vidas['mae']),
                $this->beneficiarioExtraido($vidas['filho']),
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($documentos['pai']), [
            'arquivo' => UploadedFile::fake()->image('carta.jpg'),
            ...$this->payloadAtualCarta($vidas['pai']),
        ])->assertOk()
            ->assertJsonPath('status', 'aprovado_para_envio')
            ->assertJsonCount(2, 'validacoes_compartilhadas');

        $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
            'id' => $documentos['pai']->id,
            'status' => 'aprovado_ia',
            'validado_por_documento_compartilhado' => false,
            'tipo_regra_validacao' => 'validacao_direta',
        ]);

        foreach (['mae', 'filho'] as $chave) {
            $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
                'id' => $documentos[$chave]->id,
                'status' => 'aprovado_ia',
                'validado_por_documento_compartilhado' => true,
                'documento_origem_id' => $documentos['pai']->id,
                'beneficiario_origem_id' => $vidas['pai']->id,
                'tipo_regra_validacao' => 'documento_compartilhado_grupo_familiar',
            ]);
        }
    }

    public function test_carta_permanencia_compartilhada_mantem_pendente_quem_nao_aparece(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayloadCarta([
                $this->beneficiarioExtraido($vidas['pai']),
                $this->beneficiarioExtraido($vidas['mae']),
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($documentos['pai']), [
            'arquivo' => UploadedFile::fake()->image('carta.jpg'),
            ...$this->payloadAtualCarta($vidas['pai']),
        ])->assertOk()
            ->assertJsonCount(1, 'validacoes_compartilhadas');

        $this->assertSame('aprovado_ia', $documentos['pai']->fresh()->status);
        $this->assertSame('aprovado_ia', $documentos['mae']->fresh()->status);
        $this->assertSame('pendente', $documentos['filho']->fresh()->status);
    }

    public function test_carta_permanencia_com_nome_e_cpf_sem_data_usa_validacao_completa(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayloadCarta([
                $this->beneficiarioExtraido($vidas['pai']),
                $this->beneficiarioExtraido($vidas['mae']),
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($documentos['pai']), [
            'arquivo' => UploadedFile::fake()->image('carta.jpg'),
            ...array_merge($this->payloadAtualCarta($vidas['pai']), [
                'fase_validacao' => 'documental',
                'data_nascimento_beneficiario_atual' => '',
            ]),
        ])->assertOk()
            ->assertJsonPath('status', 'aprovado_para_envio')
            ->assertJsonCount(1, 'validacoes_compartilhadas');

        $this->assertDatabaseHas('documento_ia_validacoes', [
            'documento_obrigatorio_pre_cadastro_id' => $documentos['pai']->id,
            'fase_validacao' => 'completa',
            'status' => 'aprovado_para_envio',
        ]);
    }

    public function test_carta_permanencia_inconclusiva_aprova_quando_beneficiario_origem_aparece_com_nome_e_cpf(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayloadCarta([
                $this->beneficiarioExtraido($vidas['pai']),
                $this->beneficiarioExtraido($vidas['mae']),
            ], [
                'status' => 'analise_inconclusiva',
                'mensagem_cliente' => 'Não foi possível validar com segurança.',
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($documentos['pai']), [
            'arquivo' => UploadedFile::fake()->image('carta.jpg'),
            ...array_merge($this->payloadAtualCarta($vidas['pai']), [
                'data_nascimento_beneficiario_atual' => '',
            ]),
        ])->assertOk()
            ->assertJsonPath('status', 'aprovado_para_envio')
            ->assertJsonCount(1, 'validacoes_compartilhadas');

        $this->assertSame('aprovado_ia', $documentos['pai']->fresh()->status);
        $this->assertSame('aprovado_ia', $documentos['mae']->fresh()->status);
    }

    public function test_carta_permanencia_nao_valida_dependente_com_cpf_divergente(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayloadCarta([
                $this->beneficiarioExtraido($vidas['pai']),
                $this->beneficiarioExtraido($vidas['mae'], ['cpf' => '00000000000']),
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($documentos['pai']), [
            'arquivo' => UploadedFile::fake()->image('carta.jpg'),
            ...$this->payloadAtualCarta($vidas['pai']),
        ])->assertOk();

        $this->assertSame('aprovado_ia', $documentos['pai']->fresh()->status);
        $this->assertSame('pendente', $documentos['mae']->fresh()->status);
    }

    public function test_carta_permanencia_nao_valida_dependente_com_data_nascimento_divergente(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayloadCarta([
                $this->beneficiarioExtraido($vidas['pai']),
                $this->beneficiarioExtraido($vidas['mae'], ['data_nascimento' => '1980-01-01']),
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($documentos['pai']), [
            'arquivo' => UploadedFile::fake()->image('carta.jpg'),
            ...$this->payloadAtualCarta($vidas['pai']),
        ])->assertOk();

        $this->assertSame('aprovado_ia', $documentos['pai']->fresh()->status);
        $this->assertSame('pendente', $documentos['mae']->fresh()->status);
    }

    public function test_carta_permanencia_enviada_por_dependente_valida_demais_membros(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayloadCarta([
                $this->beneficiarioExtraido($vidas['pai']),
                $this->beneficiarioExtraido($vidas['mae']),
                $this->beneficiarioExtraido($vidas['filho']),
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($documentos['mae']), [
            'arquivo' => UploadedFile::fake()->image('carta.jpg'),
            ...$this->payloadAtualCarta($vidas['mae']),
        ])->assertOk()
            ->assertJsonCount(2, 'validacoes_compartilhadas');

        $this->assertSame('aprovado_ia', $documentos['mae']->fresh()->status);
        $this->assertTrue($documentos['pai']->fresh()->validado_por_documento_compartilhado);
        $this->assertTrue($documentos['filho']->fresh()->validado_por_documento_compartilhado);
        $this->assertSame($documentos['mae']->id, $documentos['pai']->fresh()->documento_origem_id);
    }

    public function test_documento_que_nao_e_carta_nao_executa_regra_compartilhada(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();
        $tipoIdentidade = TipoDocumento::where('nome', 'Documento de identidade com foto')->firstOrFail();
        $identidade = DocumentoObrigatorioPreCadastro::create([
            'pre_cadastro_id' => $this->preCadastro->id,
            'vida_proposta_id' => $vidas['pai']->id,
            'tipo_documento_id' => $tipoIdentidade->id,
            'titulo' => 'Documento de identidade com foto - Beneficiário 1',
            'obrigatorio' => true,
            'ordem' => 2,
            'status' => 'pendente',
        ]);

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayload([
                'status' => 'aprovado_para_envio',
                'tipo_documento_esperado' => 'Documento de identidade com foto',
                'tipo_documento_identificado' => 'CNH',
                'documento_corresponde_ao_tipo' => true,
                'possui_foto' => true,
                'beneficiarios_extraidos' => [
                    $this->beneficiarioExtraido($vidas['mae']),
                    $this->beneficiarioExtraido($vidas['filho']),
                ],
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($identidade), [
            'arquivo' => UploadedFile::fake()->image('cnh.jpg'),
            ...$this->payloadAtualCarta($vidas['pai'], 'Documento de identidade com foto'),
        ])->assertOk()
            ->assertJsonPath('validacoes_compartilhadas', []);

        $this->assertSame('pendente', $documentos['mae']->fresh()->status);
        $this->assertSame('pendente', $documentos['filho']->fresh()->status);
    }

    public function test_carta_nao_valida_documentos_de_outro_pre_cadastro(): void
    {
        [$vidas, $documentos] = $this->cenarioCartaPermanenciaFamiliar();
        $preCadastroAtual = $this->preCadastro;
        $documentoAtual = $this->documento;
        $slugAtual = $this->slug;

        [$vidasOutro, $documentosOutro] = $this->cenarioCartaPermanenciaFamiliar(autorizar: false);
        $vidasOutro['mae']->forceFill([
            'nome' => 'Ana Externa Outro Cadastro',
            'cpf' => '99999999999',
            'data_nascimento' => '1977-07-07',
        ])->save();
        $vidasOutro['mae']->refresh();
        $this->preCadastro = $preCadastroAtual;
        $this->documento = $documentoAtual;
        $this->slug = $slugAtual;

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response($this->openAiPayloadCarta([
                $this->beneficiarioExtraido($vidas['pai']),
                $this->beneficiarioExtraido($vidasOutro['mae']),
            ])),
        ]);

        $this->postJson($this->urlParaDocumento($documentos['pai']), [
            'arquivo' => UploadedFile::fake()->image('carta.jpg'),
            ...$this->payloadAtualCarta($vidas['pai']),
        ])->assertOk();

        $this->assertSame('aprovado_ia', $documentos['pai']->fresh()->status);
        $this->assertSame('pendente', $documentos['mae']->fresh()->status);
        $this->assertSame('pendente', $documentosOutro['mae']->fresh()->status);
    }

    private function cenarioDocumento(string $tipoDocumento, string $pessoa = 'PF', string $tipoProposta = 'individual'): array
    {
        $this->slug = 'CORRETORQA'.str()->random(6);
        $user = User::factory()->create(['name' => 'Corretor QA']);
        $user->corretorPerfil()->create([
            'slug' => $this->slug,
            'public_hash' => 'HASHIA'.str()->random(10),
            'nome_publico' => 'Corretor QA',
        ]);

        $indicacao = Indicacao::create([
            'user_id' => $user->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Cliente QA',
            'telefone' => '(11) 98888-0000',
            'email' => 'cliente@example.com',
            'tipo_plano' => $tipoProposta === 'empresarial' ? 'Empresarial' : 'Individual',
            'quantidade_vidas' => 1,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'pre_cadastros',
            'status' => 'aguardando_envio',
        ]);

        $preCadastro = PreCadastro::create([
            'indicacao_id' => $indicacao->id,
            'token' => 'token-ia-'.str()->random(8),
            'chave_acesso' => strtoupper(str()->random(4)).'-'.random_int(1000, 9999),
            'chave_expira_em' => now()->addHour(),
            'tipo_proposta' => $tipoProposta,
            'pessoa' => $pessoa,
            'status' => 'aguardando_envio',
        ]);

        $vida = Vida::create([
            'pre_cadastro_id' => $preCadastro->id,
            'tipo' => $pessoa === 'PJ' ? 'socio' : 'titular',
            'ordem' => 1,
            'nome' => 'Cliente QA',
            'cpf' => '12345678900',
            'data_nascimento' => '1990-01-01',
            'sexo' => 'masculino',
        ]);

        $tipo = TipoDocumento::where('nome', $tipoDocumento)->firstOrFail();
        $documento = DocumentoObrigatorioPreCadastro::create([
            'pre_cadastro_id' => $preCadastro->id,
            'vida_proposta_id' => $vida->id,
            'tipo_documento_id' => $tipo->id,
            'titulo' => $tipo->nome.' - Beneficiário 1',
            'obrigatorio' => true,
            'ordem' => 1,
            'status' => 'pendente',
        ]);

        return [$preCadastro->fresh('vidas', 'indicacao.user.corretorPerfil'), $documento];
    }

    private function autorizarPreCadastroAtual(): void
    {
        $this->withSession([
            'pre_cadastro_acesso.'.$this->preCadastro->id => [
                'chave' => $this->preCadastro->chave_acesso,
                'expires_at' => now()->addMinutes(30)->timestamp,
            ],
        ]);
    }

    private function payloadAtual(array $overrides = []): array
    {
        return array_merge([
            'fase_validacao' => 'completa',
            'nome_beneficiario_atual' => 'Cliente QA',
            'cpf_beneficiario_atual' => '123.456.789-00',
            'data_nascimento_beneficiario_atual' => '1990-01-01',
            'sexo_beneficiario_atual' => 'masculino',
            'tipo_beneficiario_atual' => 'titular',
            'tipo_documento_esperado' => $this->documento->tipoDocumento?->nome,
        ], $overrides);
    }

    private function criarValidacaoPendente(): DocumentoIaValidacao
    {
        return DocumentoIaValidacao::create([
            'pre_cadastro_id' => $this->preCadastro->id,
            'beneficiario_id' => $this->documento->vida_proposta_id,
            'documento_obrigatorio_pre_cadastro_id' => $this->documento->id,
            'tipo_documento_id' => $this->documento->tipo_documento_id,
            'tipo_documento_esperado' => $this->documento->tipoDocumento?->nome,
            'arquivo_nome' => 'cnh.jpg',
            'arquivo_path' => 'documentos/ia-validacoes/cnh.jpg',
            'status' => 'analise_inconclusiva',
            'fase_validacao' => 'documental',
            'validacao_documental_status' => 'analise_inconclusiva',
            'titularidade_pendente' => true,
            'tipo_documento_identificado' => 'CNH',
            'documento_corresponde_ao_tipo' => true,
            'legivel' => true,
            'possui_foto' => true,
            'nome_extraido' => 'Cliente QA',
            'cpf_extraido' => '12345678900',
            'dados_extraidos' => [
                'nome_extraido' => 'Cliente QA',
                'cpf_extraido' => '12345678900',
                'tipo_documento_identificado' => 'CNH',
            ],
            'motivos' => [],
            'mensagem_cliente' => 'Documento legivel e do tipo correto. Preencha os dados pessoais para concluir a validacao.',
            'analisado_em' => now(),
        ]);
    }

    private function adicionarDocumentoCpf(?Vida $vida = null): DocumentoObrigatorioPreCadastro
    {
        $tipo = TipoDocumento::where('nome', 'CPF')->firstOrFail();

        return DocumentoObrigatorioPreCadastro::create([
            'pre_cadastro_id' => $this->preCadastro->id,
            'vida_proposta_id' => $vida?->id ?? $this->documento->vida_proposta_id,
            'tipo_documento_id' => $tipo->id,
            'titulo' => 'CPF - Beneficiário',
            'obrigatorio' => true,
            'ordem' => 2,
            'status' => 'pendente',
        ]);
    }

    private function adicionarCpfDeOutraVida(): DocumentoObrigatorioPreCadastro
    {
        $vida = Vida::create([
            'pre_cadastro_id' => $this->preCadastro->id,
            'tipo' => 'dependente',
            'ordem' => 2,
            'nome' => 'Dependente QA',
            'cpf' => '98765432100',
            'data_nascimento' => '2015-01-01',
            'sexo' => 'feminino',
            'parentesco' => 'filho',
        ]);

        return $this->adicionarDocumentoCpf($vida);
    }

    private function cenarioCartaPermanenciaFamiliar(bool $autorizar = true): array
    {
        $this->slug = 'CORRETORQA'.str()->random(6);
        $user = User::factory()->create(['name' => 'Corretor Familiar QA']);
        $user->corretorPerfil()->create([
            'slug' => $this->slug,
            'public_hash' => 'HASHFAM'.str()->random(10),
            'nome_publico' => 'Corretor Familiar QA',
        ]);

        $indicacao = Indicacao::create([
            'user_id' => $user->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Familia QA',
            'telefone' => '(11) 98888-0000',
            'email' => 'familia@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 3,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'pre_cadastros',
            'status' => 'aguardando_envio',
        ]);

        $preCadastro = PreCadastro::create([
            'indicacao_id' => $indicacao->id,
            'token' => 'token-fam-'.str()->random(8),
            'chave_acesso' => strtoupper(str()->random(4)).'-'.random_int(1000, 9999),
            'chave_expira_em' => now()->addHour(),
            'tipo_proposta' => 'familiar',
            'pessoa' => 'PF',
            'status' => 'aguardando_envio',
        ]);

        $vidas = [
            'pai' => Vida::create([
                'pre_cadastro_id' => $preCadastro->id,
                'tipo' => 'titular',
                'ordem' => 1,
                'nome' => 'Joao Silva Familiar',
                'cpf' => '11111111111',
                'data_nascimento' => '1980-01-01',
                'sexo' => 'masculino',
            ]),
            'mae' => Vida::create([
                'pre_cadastro_id' => $preCadastro->id,
                'tipo' => 'dependente',
                'ordem' => 2,
                'nome' => 'Maria Silva Familiar',
                'cpf' => '22222222222',
                'data_nascimento' => '1982-02-02',
                'sexo' => 'feminino',
                'parentesco' => 'conjuge',
            ]),
            'filho' => Vida::create([
                'pre_cadastro_id' => $preCadastro->id,
                'tipo' => 'dependente',
                'ordem' => 3,
                'nome' => 'Pedro Silva Familiar',
                'cpf' => '33333333333',
                'data_nascimento' => '2012-03-03',
                'sexo' => 'masculino',
                'parentesco' => 'filho',
            ]),
        ];

        $tipo = TipoDocumento::where('nome', 'Carta de Permanência')->firstOrFail();
        $documentos = [];

        foreach ($vidas as $chave => $vida) {
            $documentos[$chave] = DocumentoObrigatorioPreCadastro::create([
                'pre_cadastro_id' => $preCadastro->id,
                'vida_proposta_id' => $vida->id,
                'tipo_documento_id' => $tipo->id,
                'titulo' => 'Carta de Permanência - Beneficiário '.$vida->ordem,
                'obrigatorio' => true,
                'ordem' => 1,
                'status' => 'pendente',
            ]);
        }

        $this->preCadastro = $preCadastro->fresh('vidas', 'documentosObrigatorios.tipoDocumento', 'indicacao.user.corretorPerfil');
        $this->documento = $documentos['pai'];

        if ($autorizar) {
            $this->autorizarPreCadastroAtual();
        }

        return [$vidas, $documentos];
    }

    private function beneficiarioExtraido(Vida $vida, array $overrides = []): array
    {
        return array_merge([
            'nome' => $vida->nome,
            'cpf' => $vida->cpf,
            'data_nascimento' => $vida->data_nascimento?->format('Y-m-d'),
            'operadora_anterior' => 'Operadora QA',
            'plano_anterior' => 'Plano Familiar QA',
        ], $overrides);
    }

    private function payloadAtualCarta(Vida $vida, string $tipoDocumento = 'Carta de Permanência'): array
    {
        return [
            'fase_validacao' => 'completa',
            'nome_beneficiario_atual' => $vida->nome,
            'cpf_beneficiario_atual' => $vida->cpf,
            'data_nascimento_beneficiario_atual' => $vida->data_nascimento?->format('Y-m-d'),
            'sexo_beneficiario_atual' => $vida->sexo,
            'tipo_beneficiario_atual' => $vida->tipo,
            'tipo_documento_esperado' => $tipoDocumento,
        ];
    }

    private function url(): string
    {
        return route('cliente.pre-cadastro.documentos.validar-ia', [
            'slug' => $this->slug,
            'token' => $this->preCadastro->token,
            'documento' => $this->documento,
        ]);
    }

    private function urlParaDocumento(DocumentoObrigatorioPreCadastro $documento): string
    {
        return route('cliente.pre-cadastro.documentos.validar-ia', [
            'slug' => $this->slug,
            'token' => $this->preCadastro->token,
            'documento' => $documento,
        ]);
    }

    private function openAiPayload(array $overrides): array
    {
        $base = array_merge([
            'status' => 'analise_inconclusiva',
            'tipo_documento_esperado' => 'Documento de identidade com foto',
            'tipo_documento_identificado' => null,
            'documento_corresponde_ao_tipo' => null,
            'legivel' => true,
            'cortado' => false,
            'desfocado' => false,
            'escuro' => false,
            'possui_foto' => null,
            'documento_possui_nome' => true,
            'documento_possui_cpf' => true,
            'documento_possui_cnpj' => null,
            'nome_extraido' => 'Cliente QA',
            'cpf_extraido' => '12345678900',
            'cnpj_extraido' => null,
            'data_nascimento_extraida' => null,
            'nome_vinculado_extraido' => null,
            'razao_social_extraida' => null,
            'endereco_extraido' => null,
            'data_documento_extraida' => null,
            'match_nome' => true,
            'match_cpf' => true,
            'match_cnpj' => null,
            'match_data_nascimento' => null,
            'match_titular_responsavel' => true,
            'criterio_titularidade_usado' => 'nome_beneficiario',
            'confianca' => 88,
            'analise_parcial' => false,
            'titularidade_pendente' => false,
            'beneficiarios_extraidos' => [],
            'motivos' => [],
            'mensagem_cliente' => 'Documento mantido para análise.',
            'mensagem_corretor' => 'Parecer de teste.',
        ], $overrides);

        return [
            'output' => [[
                'content' => [[
                    'type' => 'output_text',
                    'text' => json_encode($base, JSON_UNESCAPED_UNICODE),
                ]],
            ]],
        ];
    }

    private function openAiPayloadCarta(array $beneficiariosExtraidos, array $overrides = []): array
    {
        return $this->openAiPayload(array_merge([
            'status' => 'aprovado_para_envio',
            'tipo_documento_esperado' => 'Carta de Permanência',
            'tipo_documento_identificado' => 'Carta de Permanência',
            'documento_corresponde_ao_tipo' => true,
            'legivel' => true,
            'nome_extraido' => $beneficiariosExtraidos[0]['nome'] ?? null,
            'cpf_extraido' => $beneficiariosExtraidos[0]['cpf'] ?? null,
            'data_nascimento_extraida' => $beneficiariosExtraidos[0]['data_nascimento'] ?? null,
            'match_nome' => true,
            'match_cpf' => true,
            'match_data_nascimento' => true,
            'criterio_titularidade_usado' => 'carta_permanencia_familiar',
            'beneficiarios_extraidos' => $beneficiariosExtraidos,
            'mensagem_cliente' => 'Carta de Permanência aprovada pela IA.',
            'mensagem_corretor' => 'Carta de Permanência familiar identificada com beneficiários extraídos.',
        ], $overrides));
    }

    private function fakePdfConverter(?int $totalPages = 1, int $generatedPages = 1, bool $fails = false): void
    {
        $this->app->instance(PdfToImageConverter::class, new class($totalPages, $generatedPages, $fails) extends PdfToImageConverter
        {
            public function __construct(
                private readonly ?int $totalPages,
                private readonly int $generatedPages,
                private readonly bool $fails,
            ) {
            }

            public function convert(UploadedFile $file, int $maxPages): PdfConversionResult
            {
                if ($this->fails) {
                    throw new \RuntimeException('Falha simulada.');
                }

                $dir = storage_path('app/testing/ia-pdf/'.uniqid('pdf_', true));
                File::ensureDirectoryExists($dir);
                $paths = [];
                $pages = min($this->generatedPages, $maxPages);

                for ($index = 1; $index <= $pages; $index++) {
                    $path = $dir.DIRECTORY_SEPARATOR.'page-'.$index.'.jpg';
                    File::put($path, base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAH/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAEFAqf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/ASf/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/ASf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAY/Amf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/ISf/2gAMAwEAAgADAAAAEP/EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQMBAT8QH//EABQRAQAAAAAAAAAAAAAAAAAAABD/2gAIAQIBAT8QH//EABQQAQAAAAAAAAAAAAAAAAAAABD/2gAIAQEAAT8QH//Z'));
                    $paths[] = $path;
                }

                return new PdfConversionResult(
                    imagePaths: $paths,
                    totalPages: $this->totalPages,
                    analyzedPages: count($paths),
                    partial: $this->totalPages !== null && $this->totalPages > count($paths),
                );
            }

            public function cleanup(PdfConversionResult $result): void
            {
                foreach ($result->imagePaths as $path) {
                    File::deleteDirectory(dirname($path));
                }
            }
        });
    }
}
