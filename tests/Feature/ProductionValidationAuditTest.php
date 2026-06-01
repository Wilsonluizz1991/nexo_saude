<?php

namespace Tests\Feature;

use App\Models\AvaliacaoAtendimento;
use App\Models\Cliente;
use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\Indicacao;
use App\Models\Operadora;
use App\Models\PreCadastro;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Models\Vida;
use App\Services\AvaliacaoAtendimentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionValidationAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $corretor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
        Mail::fake();

        $this->corretor = User::where('email', 'carlos@nexosaude.local')->firstOrFail();
    }

    public function test_cadastro_de_corretor_exige_senha_forte(): void
    {
        Http::fake();

        $this->from(route('register'))
            ->post(route('register.store'), $this->dadosCadastro([
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'auditoria@example.com']);
        Http::assertNothingSent();
    }

    public function test_cadastro_rejeita_cpf_cnpj_invalido_e_duplicado(): void
    {
        Http::fake();

        $this->from(route('register'))
            ->post(route('register.store'), $this->dadosCadastro([
                'billing_cpf_cnpj' => '11111111111',
            ]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['billing_cpf_cnpj']);

        User::factory()->create([
            'email' => 'documento-usado@example.com',
            'billing_cpf_cnpj' => '12345678909',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), $this->dadosCadastro())
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['billing_cpf_cnpj']);

        Http::assertNothingSent();
    }

    public function test_cpf_do_pre_cadastro_precisa_ser_valido_e_unico_entre_vidas(): void
    {
        [$preCadastro, $vidas, $documentos] = $this->preCadastroPublicoComDuasVidas();
        $rota = route('cliente.pre-cadastro.store', ['slug' => 'CARLOSOLIVEIRA', 'token' => $preCadastro->token]);

        $this->withSession($this->sessaoAcesso($preCadastro))
            ->from(route('cliente.pre-cadastro.show', ['slug' => 'CARLOSOLIVEIRA', 'token' => $preCadastro->token]))
            ->post($rota, [
                'vidas' => [
                    $vidas[0]->id => $this->dadosVida('Titular Auditoria', '11111111111'),
                    $vidas[1]->id => $this->dadosVida('Dependente Auditoria', '52998224725'),
                ],
                'documentos' => $this->arquivosDocumentos($documentos),
            ])
            ->assertSessionHasErrors(["vidas.{$vidas[0]->id}.cpf"]);

        $this->withSession($this->sessaoAcesso($preCadastro))
            ->from(route('cliente.pre-cadastro.show', ['slug' => 'CARLOSOLIVEIRA', 'token' => $preCadastro->token]))
            ->post($rota, [
                'vidas' => [
                    $vidas[0]->id => $this->dadosVida('Titular Auditoria', '12345678909'),
                    $vidas[1]->id => $this->dadosVida('Dependente Auditoria', '12345678909'),
                ],
                'documentos' => $this->arquivosDocumentos($documentos),
            ])
            ->assertSessionHasErrors(["vidas.{$vidas[1]->id}.cpf"]);
    }

    public function test_upload_de_proposta_rejeita_pdf_com_mime_incorreto(): void
    {
        $lead = $this->criarLead();

        $this->actingAs($this->corretor)
            ->from(route('indicacoes.show', $lead))
            ->post(route('indicacoes.propostas.store', $lead), [
                'titulo' => 'Proposta segura',
                'operadora_id' => Operadora::firstOrFail()->id,
                'valor_mensal' => 450.90,
                'quantidade_vidas' => 1,
                'validade' => now()->addDays(10)->toDateString(),
                'arquivo_pdf' => UploadedFile::fake()->create('proposta.pdf', 32, 'text/plain'),
            ])
            ->assertRedirect(route('indicacoes.show', $lead))
            ->assertSessionHasErrors(['arquivo_pdf']);

        $this->assertSame(0, $lead->propostas()->count());
    }

    public function test_selo_premium_exige_media_e_quantidade_minimas_do_sistema(): void
    {
        $service = app(AvaliacaoAtendimentoService::class);
        $corretor = User::factory()->create();

        $this->assertFalse($service->mediaDoCorretor($corretor->id)['premium']);

        $this->registrarAvaliacao($corretor, 4);
        $this->assertFalse($service->mediaDoCorretor($corretor->id)['premium']);

        $corretorPremium = User::factory()->create();

        for ($indice = 0; $indice < AvaliacaoAtendimentoService::QUANTIDADE_MINIMA_PREMIUM; $indice++) {
            $this->registrarAvaliacao($corretorPremium, 5);
        }

        $resultado = $service->mediaDoCorretor($corretorPremium->id);

        $this->assertSame(AvaliacaoAtendimentoService::QUANTIDADE_MINIMA_PREMIUM, $resultado['total']);
        $this->assertTrue($resultado['premium']);
    }

    public function test_falha_do_asaas_no_cadastro_nao_deixa_usuario_parcial(): void
    {
        Http::fake([
            '*/customers' => Http::response(['errors' => [['description' => 'indisponivel']]], 500),
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), $this->dadosCadastro([
                'email' => 'asaas-falha@example.com',
                'billing_cpf_cnpj' => '52998224725',
            ]))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['billing']);

        $this->assertDatabaseMissing('users', ['email' => 'asaas-falha@example.com']);
    }

    private function dadosCadastro(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Auditoria Producao',
            'email' => 'auditoria@example.com',
            'telefone' => '(11) 98888-0000',
            'billing_cpf_cnpj' => '12345678909',
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaForte@123',
            'card_holder_name' => 'Auditoria Producao',
            'card_number' => '4111111111111111',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2030',
            'card_ccv' => '123',
            'accepted_terms' => '1',
        ], $overrides);
    }

    private function preCadastroPublicoComDuasVidas(): array
    {
        $lead = $this->criarLead();
        $preCadastro = PreCadastro::create([
            'indicacao_id' => $lead->id,
            'token' => 'auditoria-validacao',
            'chave_acesso' => 'ABCD-1234',
            'chave_expira_em' => now()->addDay(),
            'tipo_proposta' => 'familiar',
            'pessoa' => 'PF',
            'status' => 'aguardando_envio',
        ]);

        $vidas = collect([
            Vida::create([
                'pre_cadastro_id' => $preCadastro->id,
                'tipo' => 'titular',
                'ordem' => 1,
                'nome' => 'Titular Auditoria',
                'data_nascimento' => '1990-01-01',
                'sexo' => 'masculino',
            ]),
            Vida::create([
                'pre_cadastro_id' => $preCadastro->id,
                'tipo' => 'dependente',
                'ordem' => 2,
                'nome' => 'Dependente Auditoria',
                'data_nascimento' => '2010-01-01',
                'sexo' => 'feminino',
                'parentesco' => 'filho',
            ]),
        ]);

        $tipoDocumento = TipoDocumento::where('slug', 'documento-de-identidade-com-foto')->firstOrFail();
        $documentos = $vidas->map(fn (Vida $vida) => DocumentoObrigatorioPreCadastro::create([
            'pre_cadastro_id' => $preCadastro->id,
            'vida_proposta_id' => $vida->id,
            'tipo_documento_id' => $tipoDocumento->id,
            'titulo' => 'Documento de identidade com foto - Beneficiario '.$vida->ordem,
            'obrigatorio' => true,
            'ordem' => $vida->ordem,
            'status' => 'pendente',
        ]));

        return [$preCadastro->fresh('vidas', 'documentosObrigatorios'), $vidas->all(), $documentos->all()];
    }

    private function dadosVida(string $nome, string $cpf): array
    {
        return [
            'nome' => $nome,
            'cpf' => $cpf,
            'data_nascimento' => '1990-01-01',
            'sexo' => 'masculino',
        ];
    }

    private function arquivosDocumentos(array $documentos): array
    {
        return collect($documentos)
            ->mapWithKeys(fn (DocumentoObrigatorioPreCadastro $documento) => [
                $documento->id => UploadedFile::fake()->create('documento-'.$documento->id.'.pdf', 20, 'application/pdf'),
            ])
            ->all();
    }

    private function sessaoAcesso(PreCadastro $preCadastro): array
    {
        return [
            'pre_cadastro_acesso.'.$preCadastro->id => [
                'chave' => $preCadastro->chave_acesso,
                'expires_at' => now()->addMinutes(30)->timestamp,
            ],
        ];
    }

    private function criarLead(): Indicacao
    {
        return Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Cliente Auditoria',
            'telefone' => '(11) 98888-1111',
            'email' => 'cliente-auditoria@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 2,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);
    }

    private function registrarAvaliacao(User $corretor, int $nota): void
    {
        $indicacao = Indicacao::create([
            'user_id' => $corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Cliente Avaliacao '.$nota,
            'telefone' => '(11) 90000-0000',
            'email' => 'avaliacao-'.$nota.'-lead-'.str()->random(4).'@example.com',
            'tipo_plano' => 'Individual',
            'quantidade_vidas' => 1,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'clientes',
            'status' => 'contrato_vigente',
        ]);

        $cliente = Cliente::create([
            'indicacao_id' => $indicacao->id,
            'user_id' => $corretor->id,
            'nome' => 'Cliente Avaliacao '.$nota,
            'email' => 'avaliacao-'.$nota.'-'.str()->random(4).'@example.com',
            'telefone' => '(11) 90000-0000',
            'inicio_vigencia' => now()->toDateString(),
            'valor_mensal' => 100,
            'status' => 'ativo',
        ]);

        AvaliacaoAtendimento::create([
            'user_id' => $corretor->id,
            'cliente_id' => $cliente->id,
            'token' => str()->random(40),
            'status' => 'respondida',
            'nota_atendimento' => $nota,
            'nota_clareza' => $nota,
            'nota_agilidade' => $nota,
            'nota_confianca' => $nota,
            'nota_recomendacao' => $nota,
            'respondida_em' => now(),
        ]);
    }
}
