<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\CorretorMetaMensal;
use App\Models\DocumentoEnviado;
use App\Models\Indicacao;
use App\Models\Operadora;
use App\Models\Tarefa;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Services\CabecalhoService;
use App\Services\WhatsAppLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FluxoOperacionalNexoTest extends TestCase
{
    use RefreshDatabase;

    private User $corretor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->corretor = User::where('email', 'carlos@nexosaude.local')->firstOrFail();
    }

    public function test_header_mantem_nomenclatura_aprovada_e_badges_dinamicos(): void
    {
        $this->actingAs($this->corretor)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Leads')
            ->assertSee('Nova Lead')
            ->assertSee('Pré-cadastros')
            ->assertDontSee('Nova indicação')
            ->assertDontSee('Oportunidades')
            ->assertDontSee('⌘ K')
            ->assertDontSee('Ctrl K');
    }

    public function test_busca_global_encontra_registros_em_todo_funil(): void
    {
        $operadora = Operadora::firstOrFail();

        $lead = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Busca Lead Global',
            'telefone' => '(11) 90000-0001',
            'email' => 'busca-lead@example.com',
            'tipo_plano' => 'Individual',
            'quantidade_vidas' => 1,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);

        $proposta = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Busca Proposta Global',
            'telefone' => '(11) 90000-0002',
            'email' => 'busca-proposta@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 3,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'propostas',
            'status' => 'proposta_enviada',
        ]);
        $proposta->propostas()->create([
            'operadora_id' => $operadora->id,
            'titulo' => 'Plano Diamante Pesquisavel',
            'arquivo_pdf_path' => 'propostas/busca-global.pdf',
            'quantidade_vidas' => 3,
            'valor_mensal' => 1200,
            'status' => 'enviada',
        ]);

        $preCadastro = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Busca Pre Cadastro Global',
            'telefone' => '(11) 90000-0003',
            'email' => 'busca-pre@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 2,
            'cidade' => 'Campinas',
            'estado' => 'SP',
            'etapa' => 'pre_cadastros',
            'status' => 'aguardando_envio',
        ]);
        $preCadastro->preCadastro()->create([
            'token' => 'token-busca-global-pre',
            'chave_acesso' => 'ABCD-1234',
            'chave_expira_em' => now()->addDays(14),
            'tipo_proposta' => 'familiar',
            'pessoa' => 'PF',
            'status' => 'aguardando_envio',
        ]);

        $implantacao = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Busca Implantacao Global',
            'telefone' => '(11) 90000-0004',
            'email' => 'busca-implantacao@example.com',
            'tipo_plano' => 'Empresarial',
            'quantidade_vidas' => 12,
            'cidade' => 'Osasco',
            'estado' => 'SP',
            'etapa' => 'implantacoes',
            'status' => 'pendencia_na_operadora',
        ]);
        $implantacao->implantacao()->create([
            'status' => 'pendencia_na_operadora',
            'data_inicio' => now()->toDateString(),
            'observacoes' => 'Documento pendente pesquisavel',
        ]);

        $cliente = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Busca Cliente Global',
            'telefone' => '(11) 90000-0005',
            'email' => 'busca-cliente@example.com',
            'tipo_plano' => 'Empresarial',
            'quantidade_vidas' => 20,
            'cidade' => 'Barueri',
            'estado' => 'SP',
            'etapa' => 'clientes',
            'status' => 'contrato_vigente',
        ]);
        $clienteAtivo = $cliente->cliente()->create([
            'user_id' => $this->corretor->id,
            'nome' => 'Busca Cliente Global',
            'email' => 'busca-cliente@example.com',
            'telefone' => '(11) 90000-0005',
            'inicio_vigencia' => now()->toDateString(),
            'valor_mensal' => 5000,
            'status' => 'ativo',
        ]);
        $clienteAtivo->dependentes()->create([
            'nome' => 'Dependente Pesquisavel',
            'data_nascimento' => now()->subYears(8)->toDateString(),
            'parentesco' => 'filho',
        ]);

        $this->actingAs($this->corretor)
            ->get(route('busca.index', ['q' => 'Busca']))
            ->assertOk()
            ->assertSee('Busca Lead Global')
            ->assertSee('Busca Proposta Global')
            ->assertSee('Busca Pre Cadastro Global')
            ->assertSee('Busca Implantacao Global')
            ->assertSee('Busca Cliente Global')
            ->assertSee(route('indicacoes.show', $lead), false)
            ->assertSee(route('propostas.show', $proposta), false)
            ->assertSee(route('pre-cadastros.show', $preCadastro->preCadastro), false)
            ->assertSee(route('implantacoes.show', $implantacao->implantacao), false)
            ->assertSee(route('clientes.show', $clienteAtivo), false);

        $this->actingAs($this->corretor)
            ->get(route('busca.index', ['q' => 'Dependente Pesquisavel']))
            ->assertOk()
            ->assertSee('Busca Cliente Global');
    }

    public function test_perfil_publico_cria_lead_com_origem_alerta_e_preferencias_controladas(): void
    {
        $operadoras = Operadora::take(3)->pluck('id')->all();
        $nomesOperadoras = Operadora::take(3)->pluck('nome')->all();

        $this->post('/perfil-corretor/CARLOSOLIVEIRA/solicitacao', [
            'nome' => 'Mariana Silva',
            'telefone' => '(11) 97777-1111',
            'email' => 'mariana@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 4,
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'possui_preferencias' => 'sim',
            'operadoras' => $operadoras,
            'hospitais' => ['Hospital São Luiz', 'Hospital Albert Einstein', 'Hospital Nove de Julho'],
            'faixa_valor_mensal' => 'R$ 2.000 a R$ 3.000',
        ])->assertOk()->assertSee('Recebemos seu pedido.');

        $this->assertDatabaseHas('indicacoes', [
            'email' => 'mariana@example.com',
            'origem' => 'link_publico',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);

        $lead = Indicacao::where('email', 'mariana@example.com')->firstOrFail();
        $this->assertSame($operadoras, $lead->operadoras_preferidas);
        $this->assertCount(3, $lead->hospitais_preferidos);

        $this->post('/perfil-corretor/CARLOSOLIVEIRA/solicitacao', [
            'nome' => 'Lead Preferencias Nomeadas',
            'telefone' => '(11) 95555-8899',
            'email' => 'preferencias-nomeadas@example.com',
            'tipo_plano' => 'Individual',
            'quantidade_vidas' => 1,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'possui_preferencias' => 'sim',
            'operadoras' => $nomesOperadoras,
            'hospitais' => ['Albert Einsten', 'Sirio libanes', 'Sao Luiz'],
            'faixa_valor_mensal' => 'R$ 3.900,00',
        ])->assertOk()->assertSee('Recebemos seu pedido.');

        $leadComPreferencias = Indicacao::where('email', 'preferencias-nomeadas@example.com')->firstOrFail();

        $this->assertSame($operadoras, $leadComPreferencias->operadoras_preferidas);
        $this->assertSame(['Albert Einsten', 'Sirio libanes', 'Sao Luiz'], $leadComPreferencias->hospitais_preferidos);
        $this->assertSame('R$ 3.900,00', $leadComPreferencias->faixa_valor_mensal);

        $this->actingAs($this->corretor)
            ->get(route('indicacoes.show', $leadComPreferencias))
            ->assertOk()
            ->assertSee('Preferências da lead')
            ->assertSee($nomesOperadoras[0])
            ->assertSee('Albert Einsten')
            ->assertSee('R$ 3.900,00');

        $this->assertDatabaseHas('alertas', [
            'user_id' => $this->corretor->id,
            'indicacao_id' => $lead->id,
            'titulo' => 'Nova Lead recebida',
            'lido' => false,
        ]);

        $this->post('/perfil-corretor/CARLOSOLIVEIRA/solicitacao', [
            'nome' => 'Sem Preferência',
            'telefone' => '(11) 96666-2222',
            'email' => 'sempreferencia@example.com',
            'tipo_plano' => 'Individual',
            'quantidade_vidas' => 1,
            'cidade' => 'Santos',
            'estado' => 'SP',
            'possui_preferencias' => 'nao',
            'operadoras' => $operadoras,
            'hospitais' => ['Hospital indevido'],
            'faixa_valor_mensal' => 'Valor indevido',
        ])->assertOk();

        $semPreferencia = Indicacao::where('email', 'sempreferencia@example.com')->firstOrFail();
        $this->assertSame([], $semPreferencia->operadoras_preferidas);
        $this->assertSame([], $semPreferencia->hospitais_preferidos);
        $this->assertNull($semPreferencia->faixa_valor_mensal);
    }

    public function test_formulario_interno_de_nova_lead_salva_ate_tres_operadoras(): void
    {
        $operadoras = Operadora::take(3)->pluck('id')->all();

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.store'), [
                'nome_cliente' => 'Lead com Operadoras',
                'telefone' => '(11) 98888-3333',
                'email' => 'lead-operadoras@example.com',
                'tipo_plano' => 'Familiar',
                'quantidade_vidas' => 3,
                'cidade' => 'Sao Paulo',
                'estado' => 'SP',
                'possui_preferencias' => 'sim',
                'operadoras' => $operadoras,
                'hospitais' => ['Albert Einsten', 'Sirio libanes', 'Sao Luiz'],
                'faixa_valor_mensal' => 'R$ 3.900,00',
                'observacoes' => 'Preferencia registrada no cadastro interno.',
            ])
            ->assertRedirect();

        $lead = Indicacao::where('email', 'lead-operadoras@example.com')->firstOrFail();

        $this->assertTrue($lead->possui_preferencias);
        $this->assertSame($operadoras, $lead->operadoras_preferidas);
        $this->assertSame(['Albert Einsten', 'Sirio libanes', 'Sao Luiz'], $lead->hospitais_preferidos);
        $this->assertSame('R$ 3.900,00', $lead->faixa_valor_mensal);

        $this->actingAs($this->corretor)
            ->from(route('indicacoes.create'))
            ->post(route('indicacoes.store'), [
                'nome_cliente' => 'Lead com Operadoras demais',
                'telefone' => '(11) 98888-4444',
                'email' => 'lead-operadoras-demais@example.com',
                'tipo_plano' => 'Familiar',
                'quantidade_vidas' => 3,
                'cidade' => 'Sao Paulo',
                'estado' => 'SP',
                'possui_preferencias' => 'sim',
                'operadoras' => Operadora::take(4)->pluck('id')->all(),
            ])
            ->assertRedirect(route('indicacoes.create'))
            ->assertSessionHasErrors(['operadoras']);

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.store'), [
                'nome_cliente' => 'Lead interna sem preferencias',
                'telefone' => '(11) 98888-5555',
                'email' => 'lead-interna-sem-preferencias@example.com',
                'tipo_plano' => 'Individual',
                'quantidade_vidas' => 1,
                'cidade' => 'Sao Paulo',
                'estado' => 'SP',
                'possui_preferencias' => 'nao',
                'operadoras' => $operadoras,
                'hospitais' => ['Hospital indevido'],
                'faixa_valor_mensal' => 'Valor indevido',
            ])
            ->assertRedirect();

        $semPreferencias = Indicacao::where('email', 'lead-interna-sem-preferencias@example.com')->firstOrFail();
        $this->assertFalse($semPreferencias->possui_preferencias);
        $this->assertSame([], $semPreferencias->operadoras_preferidas);
        $this->assertSame([], $semPreferencias->hospitais_preferidos);
        $this->assertNull($semPreferencias->faixa_valor_mensal);
    }

    public function test_fluxo_completo_lead_proposta_pre_cadastro_implantacao_cliente_carteira(): void
    {
        Storage::fake('public');

        $lead = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Família Teste',
            'telefone' => '(11) 95555-3333',
            'email' => 'familia@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 4,
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.propostas.store', $lead), [
                'titulo' => 'Proposta Familiar',
                'operadora_id' => Operadora::first()->id,
                'valor_mensal' => 2450.90,
                'quantidade_vidas' => 4,
                'validade' => now()->addDays(15)->toDateString(),
                'observacoes' => 'Plano com coparticipação.',
                'arquivo_pdf' => UploadedFile::fake()->create('proposta.pdf', 32, 'application/pdf'),
            ])
            ->assertRedirect(route('paginas.simples', 'propostas'));

        $lead->refresh();
        $this->assertSame('propostas', $lead->etapa);
        $this->assertSame('proposta_enviada', $lead->status);
        $this->assertDatabaseHas('timeline_eventos', ['indicacao_id' => $lead->id, 'titulo' => 'Proposta enviada']);

        $this->actingAs($this->corretor)
            ->get(route('indicacoes.show', $lead))
            ->assertRedirect(route('propostas.show', $lead));

        $this->actingAs($this->corretor)
            ->get(route('paginas.simples', 'propostas'))
            ->assertOk()
            ->assertSee('Adicionar proposta')
            ->assertSee('href="'.route('propostas.show', $lead).'"', false)
            ->assertDontSee('href="'.route('indicacoes.show', $lead).'"', false);

        $this->actingAs($this->corretor)
            ->get(route('propostas.show', $lead))
            ->assertOk()
            ->assertSee('Anexar nova proposta')
            ->assertSee('action="'.route('propostas.store', $lead).'"', false);

        $this->actingAs($this->corretor)
            ->post(route('propostas.store', $lead), [
                'titulo' => 'Proposta Familiar Alternativa',
                'operadora_id' => Operadora::skip(1)->first()->id,
                'valor_mensal' => 2350.90,
                'quantidade_vidas' => 4,
                'validade' => now()->addDays(20)->toDateString(),
                'observacoes' => 'Plano alternativo para negociação.',
                'arquivo_pdf' => UploadedFile::fake()->create('proposta-alternativa.pdf', 32, 'application/pdf'),
            ])
            ->assertRedirect(route('paginas.simples', 'propostas'));

        $lead->refresh();
        $this->assertSame('propostas', $lead->etapa);
        $this->assertSame('proposta_enviada', $lead->status);
        $this->assertSame(2, $lead->propostas()->count());

        $this->actingAs($this->corretor)->post(route('indicacoes.aceitar', $lead))->assertRedirect();

        $this->actingAs($this->corretor)
            ->post(route('pre-cadastros.store', $lead), [
                'tipo_proposta' => 'familiar',
                'pessoa' => 'PF',
                'vidas' => [
                    ['tipo' => 'titular', 'nome' => 'Titular Teste', 'sexo' => 'masculino', 'data_nascimento' => '1980-01-01', 'documentos_solicitados' => $this->documentosPorSlug(['rg', 'cpf', 'comprovante-de-residencia'])],
                    ['tipo' => 'dependente', 'nome' => 'Cônjuge Teste', 'parentesco' => 'conjuge', 'sexo' => 'feminino', 'data_nascimento' => '1982-02-02', 'documentos_solicitados' => $this->documentosPorSlug(['rg', 'cpf', 'certidao-de-casamento', 'declaracao-de-uniao-estavel'])],
                    ['tipo' => 'dependente', 'nome' => 'Filho Um', 'parentesco' => 'filho', 'sexo' => 'masculino', 'data_nascimento' => '2015-03-03', 'documentos_solicitados' => $this->documentosPorSlug(['certidao-de-nascimento'])],
                    ['tipo' => 'dependente', 'nome' => 'Filha Dois', 'parentesco' => 'filho', 'sexo' => 'feminino', 'data_nascimento' => '2018-04-04', 'documentos_solicitados' => $this->documentosPorSlug(['certidao-de-nascimento'])],
                ],
            ])
            ->assertRedirect();

        $lead->refresh()->load('preCadastro.documentosObrigatorios.tipoDocumento');
        $this->assertSame('pre_cadastros', $lead->etapa);
        $this->assertSame('aguardando_envio', $lead->status);

        $titulos = $lead->preCadastro->documentosObrigatorios->pluck('titulo')->all();
        $this->assertContains('RG - Beneficiário 1', $titulos);
        $this->assertContains('CPF - Beneficiário 1', $titulos);
        $this->assertContains('Comprovante de Residência - Beneficiário 1', $titulos);
        $this->assertContains('RG - Beneficiário 2', $titulos);
        $this->assertContains('CPF - Beneficiário 2', $titulos);
        $this->assertContains('Certidão de Casamento - Beneficiário 2', $titulos);
        $this->assertContains('Declaração de União Estável - Beneficiário 2', $titulos);
        $this->assertContains('Certidão de Nascimento - Beneficiário 3', $titulos);
        $this->assertContains('Certidão de Nascimento - Beneficiário 4', $titulos);

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.implantacao.iniciar', $lead))
            ->assertStatus(422);

        $documentosUpload = [];
        foreach ($lead->preCadastro->documentosObrigatorios as $documento) {
            if ($documento->grupo_alternativo === 'vinculo_conjuge' && str_contains($documento->titulo, 'Declara')) {
                continue;
            }

            $documentosUpload[$documento->id] = UploadedFile::fake()->create('documento-'.$documento->id.'.pdf', 20, 'application/pdf');
        }

        $documento = $lead->preCadastro->documentosObrigatorios->first();
        $this->post(route('cliente.pre-cadastro.validar-acesso', ['slug' => 'CARLOSOLIVEIRA', 'token' => $lead->preCadastro->token]), [
            'chave_acesso' => $lead->preCadastro->chave_acesso,
        ])->assertRedirect();

        $this->post(route('cliente.pre-cadastro.store', ['slug' => 'CARLOSOLIVEIRA', 'token' => $lead->preCadastro->token]), [
            'vidas' => $lead->preCadastro->vidas->mapWithKeys(fn ($vida) => [$vida->id => [
                'nome' => 'Beneficiário '.$vida->ordem,
                'cpf' => '0000000000'.$vida->ordem,
                'data_nascimento' => '1990-01-0'.$vida->ordem,
                'sexo' => $vida->ordem === 2 ? 'feminino' : 'masculino',
                'parentesco' => $vida->tipo === 'dependente' ? 'filho' : null,
            ]])->all(),
            'documentos' => $documentosUpload,
            'observacao_cliente' => 'Documento enviado pelo cliente.',
        ])->assertRedirect();

        $this->assertDatabaseHas('documentos_obrigatorios_pre_cadastro', [
            'id' => $documento->id,
            'status' => 'enviado',
        ]);
        $this->assertSame(count($documentosUpload), DocumentoEnviado::count());
        $this->assertDatabaseHas('alertas', [
            'user_id' => $this->corretor->id,
            'indicacao_id' => $lead->id,
            'pre_cadastro_id' => $lead->preCadastro->id,
            'titulo' => 'Pré-cadastro enviado',
            'tipo' => 'pre_cadastro_enviado',
            'status' => 'nao_lido',
            'lido' => false,
        ]);
        $this->assertDatabaseHas('documentos_enviados', [
            'pre_cadastro_id' => $lead->preCadastro->id,
            'beneficiario_id' => $documento->vida_proposta_id,
            'documento_obrigatorio_pre_cadastro_id' => $documento->id,
            'tipo_documento_solicitado_id' => $documento->tipo_documento_id,
            'status_ia' => 'aguardando_analise',
        ]);

        $lead->preCadastro->documentosObrigatorios->each(function ($documento) use ($lead) {
            if ($documento->grupo_alternativo === 'vinculo_conjuge' && str_contains($documento->titulo, 'Declaração')) {
                return;
            }

            $this->actingAs($this->corretor)->post(route('indicacoes.documentos.update', [$lead, $documento]), [
                'status' => 'aprovado',
            ])->assertRedirect();
        });

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.implantacao.iniciar', $lead))
            ->assertRedirect();

        $lead->refresh();
        $this->assertSame('implantacoes', $lead->etapa);
        $this->assertSame('contrato_em_analise', $lead->status);
        $this->assertDatabaseHas('timeline_eventos', ['indicacao_id' => $lead->id, 'titulo' => 'Implantação iniciada']);

        $lead->load('implantacao');
        $this->actingAs($this->corretor)
            ->get(route('implantacoes.show', $lead->implantacao))
            ->assertOk()
            ->assertSee('Implantação')
            ->assertSee('Família Teste')
            ->assertSee('Contrato vigente')
            ->assertDontSee('text-uppercase small fw-semibold text-primary">Lead', false);

        $this->actingAs($this->corretor)
            ->get(route('paginas.simples', 'implantacoes'))
            ->assertOk()
            ->assertSee('Abrir implantação')
            ->assertDontSee('href="'.route('indicacoes.show', $lead).'"', false);

        $responseAprovacaoImplantacao = $this->actingAs($this->corretor)
            ->post(route('indicacoes.implantacao.aprovar', $lead), [
                'operadora_id' => Operadora::first()->id,
                'tipo_contrato' => 'familiar',
                'quantidade_vidas' => 4,
                'data_vigencia' => now()->toDateString(),
                'valor_mensal' => 2450.90,
                'renovacao_em' => now()->addYear()->toDateString(),
                'reajuste_em' => now()->addYear()->toDateString(),
                'numero_contrato' => 'QA-123',
                'observacoes' => 'Contrato confirmado no teste.',
                'enviar_email' => 1,
                'enviar_sms' => 1,
            ]);

        $lead->refresh();
        $this->assertSame('carteira', $lead->etapa);
        $this->assertSame('contrato_vigente', $lead->status);
        $this->assertDatabaseHas('pre_cadastros', ['indicacao_id' => $lead->id, 'status' => 'convertido_em_cliente']);
        $this->assertDatabaseHas('timeline_eventos', ['indicacao_id' => $lead->id, 'titulo' => 'Contrato vigente']);
        $this->assertDatabaseHas('timeline_eventos', ['indicacao_id' => $lead->id, 'titulo' => 'E-mail automático enviado']);
        $this->assertDatabaseHas('timeline_eventos', ['indicacao_id' => $lead->id, 'titulo' => 'SMS automático enviado']);

        $cliente = Cliente::where('indicacao_id', $lead->id)->with('contratos', 'dependentes')->firstOrFail();
        $responseAprovacaoImplantacao->assertRedirect(route('clientes.show', $cliente));
        $this->assertSame('ativo', $cliente->status);
        $this->assertCount(1, $cliente->contratos);
        $this->assertCount(3, $cliente->dependentes);

        $lead->load('preCadastro.documentosObrigatorios');
        $documentoBloqueado = $lead->preCadastro->documentosObrigatorios->first();

        $this->actingAs($this->corretor)
            ->get(route('indicacoes.show', $lead))
            ->assertRedirect(route('clientes.show', $cliente));

        $this->actingAs($this->corretor)
            ->get(route('pre-cadastros.show', $lead->preCadastro))
            ->assertOk()
            ->assertSee('Revisão encerrada')
            ->assertDontSee('Revisar documento');

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.documentos.update', [$lead, $documentoBloqueado]), [
                'status' => 'corrigir',
            ])
            ->assertStatus(422);

        $this->actingAs($this->corretor)->get('/indicacoes?etapa=leads')->assertDontSee('Família Teste');
        $this->actingAs($this->corretor)->get('/propostas')->assertDontSee('Família Teste');
        $this->actingAs($this->corretor)->get('/pre-cadastros')->assertDontSee('Família Teste');
        $this->actingAs($this->corretor)->get('/implantacoes')->assertDontSee('Família Teste');
        $this->actingAs($this->corretor)
            ->get('/clientes')
            ->assertSee('Família Teste')
            ->assertSee('Detalhes')
            ->assertSee('href="'.route('clientes.show', $cliente).'"', false);
        $this->actingAs($this->corretor)
            ->get(route('clientes.show', $cliente))
            ->assertOk()
            ->assertSee('Detalhes operacionais do cliente')
            ->assertSee('Informações de contato')
            ->assertSee('Plano de saúde e contratos')
            ->assertSee('Dependentes e vidas vinculadas')
            ->assertSee('QA-123')
            ->assertSee('Contrato vigente');
        $this->actingAs($this->corretor)->get('/carteira')->assertSee('Renovações próximas')->assertSee('Família Teste');
    }

    public function test_carteira_exibe_performance_mensal_e_salva_meta_com_valores_brasileiros(): void
    {
        Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Lead Carteira Atual',
            'telefone' => '(11) 93333-1111',
            'email' => 'lead-carteira-atual@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 4,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);

        $leadAnterior = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Lead Carteira Anterior',
            'telefone' => '(11) 93333-2222',
            'email' => 'lead-carteira-anterior@example.com',
            'tipo_plano' => 'Empresarial',
            'quantidade_vidas' => 2,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);
        $leadAnterior->forceFill([
            'created_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays(2),
            'updated_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays(2),
        ])->save();

        $indicacaoClienteAtual = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Cliente Atual Carteira',
            'telefone' => '(11) 94444-1111',
            'email' => 'cliente-atual-carteira@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 4,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'carteira',
            'status' => 'contrato_vigente',
        ]);

        $clienteAtual = Cliente::create([
            'indicacao_id' => $indicacaoClienteAtual->id,
            'user_id' => $this->corretor->id,
            'nome' => 'Cliente Atual Carteira',
            'email' => 'cliente-atual-carteira@example.com',
            'telefone' => '(11) 94444-1111',
            'inicio_vigencia' => now()->toDateString(),
            'valor_mensal' => 2500,
            'status' => 'ativo',
        ]);

        Contrato::create([
            'usuario_id' => $this->corretor->id,
            'cliente_id' => $clienteAtual->id,
            'operadora_id' => Operadora::first()->id,
            'tipo_contrato' => 'familiar',
            'status' => 'vigente',
            'quantidade_vidas' => 4,
            'valor_mensal' => 2500,
            'iniciado_em' => now()->toDateString(),
            'renovacao_em' => now()->addYear()->toDateString(),
            'reajuste_em' => now()->addYear()->toDateString(),
        ]);

        $indicacaoClienteAnterior = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Cliente Anterior Carteira',
            'telefone' => '(11) 94444-2222',
            'email' => 'cliente-anterior-carteira@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 2,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'carteira',
            'status' => 'contrato_vigente',
        ]);
        $indicacaoClienteAnterior->forceFill([
            'created_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays(3),
            'updated_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays(3),
        ])->save();

        $clienteAnterior = Cliente::create([
            'indicacao_id' => $indicacaoClienteAnterior->id,
            'user_id' => $this->corretor->id,
            'nome' => 'Cliente Anterior Carteira',
            'email' => 'cliente-anterior-carteira@example.com',
            'telefone' => '(11) 94444-2222',
            'inicio_vigencia' => now()->subMonthNoOverflow()->toDateString(),
            'valor_mensal' => 1800,
            'status' => 'ativo',
        ]);

        $contratoAnterior = Contrato::create([
            'usuario_id' => $this->corretor->id,
            'cliente_id' => $clienteAnterior->id,
            'operadora_id' => Operadora::first()->id,
            'tipo_contrato' => 'familiar',
            'status' => 'vigente',
            'quantidade_vidas' => 2,
            'valor_mensal' => 1800,
            'iniciado_em' => now()->subMonthNoOverflow()->toDateString(),
            'renovacao_em' => now()->addYear()->toDateString(),
            'reajuste_em' => now()->addYear()->toDateString(),
        ]);
        $contratoAnterior->forceFill([
            'created_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays(3),
            'updated_at' => now()->subMonthNoOverflow()->startOfMonth()->addDays(3),
        ])->save();

        CorretorMetaMensal::create([
            'user_id' => $this->corretor->id,
            'mes_referencia' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
            'meta_comissao' => 1500,
            'comissao_realizada' => 1000,
        ]);

        $this->actingAs($this->corretor)
            ->post(route('carteira.meta-mensal.store'), [
                'tipo_acao' => 'salvar_meta',
                'meta_comissao' => 'R$ 2.000,00',
            ])
            ->assertRedirect(route('paginas.simples', 'carteira'));

        $this->actingAs($this->corretor)
            ->post(route('carteira.meta-mensal.store'), [
                'tipo_acao' => 'adicionar_comissao',
                'comissao_lancamento' => 'R$ 1.250,00',
            ])
            ->assertRedirect(route('paginas.simples', 'carteira'));

        $metaAtual = CorretorMetaMensal::where('user_id', $this->corretor->id)
            ->whereDate('mes_referencia', now()->startOfMonth()->toDateString())
            ->firstOrFail();

        $this->assertSame(1, CorretorMetaMensal::where('user_id', $this->corretor->id)
            ->whereDate('mes_referencia', now()->startOfMonth()->toDateString())
            ->count());
        $this->assertSame('2000.00', $metaAtual->meta_comissao);
        $this->assertSame('1250.00', $metaAtual->comissao_realizada);

        $this->actingAs($this->corretor)
            ->get('/carteira')
            ->assertOk()
            ->assertSee('Carteira estratégica')
            ->assertSee('Leads no mês')
            ->assertSee('Contratos fechados')
            ->assertSee('Vidas vendidas')
            ->assertSee('Conversão')
            ->assertSee('R$ 1.250,00')
            ->assertSee('R$ 2.000,00')
            ->assertSee('62,5%')
            ->assertSee('Você está a R$ 750,00 da sua meta mensal.')
            ->assertSee('Cliente Atual Carteira')
            ->assertSee('Abrir')
            ->assertSee('href="'.route('clientes.show', $clienteAtual).'"', false);

        $this->actingAs($this->corretor)
            ->post(route('carteira.meta-mensal.store'), [
                'tipo_acao' => 'adicionar_comissao',
                'comissao_lancamento' => 'R$ 800,00',
            ])
            ->assertRedirect(route('paginas.simples', 'carteira'))
            ->assertSessionHas('meta_atingida', true);

        $metaAtual->refresh();
        $this->assertSame('2050.00', $metaAtual->comissao_realizada);
        $this->assertSame(1, CorretorMetaMensal::where('user_id', $this->corretor->id)
            ->whereDate('mes_referencia', now()->startOfMonth()->toDateString())
            ->count());
    }

    public function test_whatsapp_usa_mensagem_em_leads_e_link_limpo_em_clientes(): void
    {
        $mensagem = 'Oi {nome}, recebi seu interesse em {tipo_plano} para {quantidade_vidas} vidas em {cidade}/{estado}.';
        $mensagemContrato = 'Olá, {nome}! Seu contrato está vigente desde {data_vigencia}. Avalie meu atendimento: {link_avaliacao}';

        $this->actingAs($this->corretor)
            ->get(route('configuracoes.mensagem-whatsapp'))
            ->assertOk()
            ->assertSee('Mensagens automáticas')
            ->assertSee('Primeiro contato com Lead')
            ->assertSee('{nome}');

        $this->actingAs($this->corretor)
            ->post(route('configuracoes.mensagem-whatsapp.update'), [
                'mensagem_primeiro_contato_whatsapp' => $mensagem,
                'mensagem_contrato_vigente_whatsapp' => $mensagemContrato,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('corretor_perfis', [
            'user_id' => $this->corretor->id,
            'mensagem_primeiro_contato_whatsapp' => $mensagem,
        ]);

        $lead = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Fernando Diniz',
            'telefone' => '(11) 99953-5578',
            'email' => 'fernando@example.com',
            'tipo_plano' => 'PME',
            'quantidade_vidas' => 11,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);

        $whatsApp = app(WhatsAppLinkService::class);
        $this->actingAs($this->corretor)
            ->get(route('indicacoes.index'))
            ->assertOk()
            ->assertSee($whatsApp->buildLeadLink($lead, $this->corretor->fresh('corretorPerfil')), false)
            ->assertSee('bi-whatsapp', false);

        $indicacaoCliente = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Cliente WhatsApp',
            'telefone' => '(11) 98888-7700',
            'email' => 'cliente-whatsapp@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 2,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'carteira',
            'status' => 'contrato_vigente',
        ]);

        $cliente = Cliente::create([
            'indicacao_id' => $indicacaoCliente->id,
            'user_id' => $this->corretor->id,
            'nome' => 'Cliente WhatsApp',
            'email' => 'cliente-whatsapp@example.com',
            'telefone' => '(11) 98888-7700',
            'inicio_vigencia' => now()->toDateString(),
            'valor_mensal' => 1000,
            'status' => 'ativo',
        ]);

        $clienteLink = $whatsApp->buildClientLink($cliente->telefone);

        $this->actingAs($this->corretor)
            ->get(route('paginas.simples', 'clientes'))
            ->assertOk()
            ->assertSee($clienteLink, false)
            ->assertDontSee($clienteLink.'?text', false);
    }

    public function test_assinatura_expirada_bloqueia_area_interna_sem_bloquear_rotas_publicas(): void
    {
        $this->corretor->assinatura->update([
            'status_assinatura' => 'teste_gratis',
            'data_fim_teste_gratis' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($this->corretor)->get('/dashboard')->assertRedirect('/assinatura');
        $this->actingAs($this->corretor)->get('/assinatura')->assertOk()->assertSee('R$ 49,90');
        $this->actingAs($this->corretor)->post(route('assinatura.assinar'))->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('assinaturas', [
            'user_id' => $this->corretor->id,
            'status_assinatura' => 'ativa',
            'valor_assinatura' => '49.90',
        ]);
        $this->get('/perfil-corretor/CARLOSOLIVEIRA')->assertOk()->assertDontSee('Nova Lead');
    }

    public function test_tarefas_e_alertas_podem_ser_resolvidos(): void
    {
        $tarefa = $this->corretor->id
            ? \App\Models\Tarefa::where('user_id', $this->corretor->id)->firstOrFail()
            : null;
        $alerta = Alerta::where('user_id', $this->corretor->id)->firstOrFail();

        $this->actingAs($this->corretor)->post(route('tarefas.concluir', $tarefa))->assertRedirect();
        $this->assertDatabaseHas('tarefas', ['id' => $tarefa->id, 'status' => 'concluida']);

        $this->actingAs($this->corretor)->post(route('alertas.resolver', $alerta))->assertRedirect();
        $this->assertDatabaseHas('alertas', ['id' => $alerta->id, 'lido' => true]);
    }

    public function test_lembretes_da_lead_geram_alertas_e_compromissos_no_cabecalho(): void
    {
        $lead = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'José Lembrete',
            'telefone' => '(11) 94444-1111',
            'email' => 'jose@example.com',
            'tipo_plano' => 'Individual',
            'quantidade_vidas' => 1,
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.lembretes.store', $lead), [
                'data_ocorrencia' => today()->addDay()->toDateString(),
                'descricao' => 'Ligar para o cliente José após o pagamento',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tarefas', [
            'indicacao_id' => $lead->id,
            'tipo' => 'lembrete',
            'titulo' => 'Ligar para o cliente José após o pagamento',
            'vencimento' => today()->addDay()->startOfDay()->format('Y-m-d H:i:s'),
            'status' => 'pendente',
        ]);

        $dadosAmanha = app(CabecalhoService::class)->dadosPara($this->corretor);

        $this->assertDatabaseHas('alertas', [
            'indicacao_id' => $lead->id,
            'tipo' => 'lembrete_amanha',
            'titulo' => 'Lembrete programado para amanhã',
            'lido' => false,
        ]);
        $this->assertTrue($dadosAmanha['alertasNaoLidos']->contains(fn ($alerta) => $alerta->tipo === 'lembrete_amanha'));

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.lembretes.store', $lead), [
                'data_ocorrencia' => today()->toDateString(),
                'descricao' => 'Retornar contato sobre carta de permanência',
            ])
            ->assertRedirect();

        $dadosHoje = app(CabecalhoService::class)->dadosPara($this->corretor);

        $this->assertDatabaseHas('alertas', [
            'indicacao_id' => $lead->id,
            'tipo' => 'lembrete_hoje',
            'titulo' => 'Lembrete programado para hoje',
            'lido' => false,
        ]);
        $this->assertTrue($dadosHoje['compromissosHoje']->contains(fn (Tarefa $tarefa) => $tarefa->titulo === 'Retornar contato sobre carta de permanência'));
    }

    public function test_abrir_notificacao_marca_alerta_como_lido_e_reduz_badge(): void
    {
        $primeiro = Alerta::create([
            'user_id' => $this->corretor->id,
            'titulo' => 'Documento enviado',
            'mensagem' => 'Um pré-cadastro foi enviado.',
            'tipo' => 'pre_cadastro_enviado',
            'status' => 'nao_lido',
            'lido' => false,
        ]);

        $segundo = Alerta::create([
            'user_id' => $this->corretor->id,
            'titulo' => 'Documento pendente',
            'mensagem' => 'Existe um documento pendente.',
            'tipo' => 'documento_pendente',
            'status' => 'nao_lido',
            'lido' => false,
        ]);

        $cabecalho = app(CabecalhoService::class);
        $quantidadeInicial = $cabecalho->dadosPara($this->corretor)['quantidadeAlertasNaoLidos'];

        $this->actingAs($this->corretor)
            ->get(route('alertas.abrir', $primeiro))
            ->assertRedirect(route('alertas.index'));

        $primeiro->refresh();
        $this->assertTrue($primeiro->lido);
        $this->assertSame('lido', $primeiro->status);
        $this->assertSame($quantidadeInicial - 1, $cabecalho->dadosPara($this->corretor)['quantidadeAlertasNaoLidos']);

        $this->actingAs($this->corretor)
            ->get(route('alertas.abrir', $segundo))
            ->assertRedirect(route('alertas.index'));

        $segundo->refresh();
        $this->assertTrue($segundo->lido);
        $this->assertSame($quantidadeInicial - 2, $cabecalho->dadosPara($this->corretor)['quantidadeAlertasNaoLidos']);
    }

    public function test_pre_cadastro_pf_exige_um_titular_estrutural(): void
    {
        $lead = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'PF Validação',
            'telefone' => '(11) 90000-0001',
            'email' => 'pfvalidacao@example.com',
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 2,
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'etapa' => 'propostas',
            'status' => 'proposta_enviada',
        ]);

        $this->actingAs($this->corretor)
            ->from(route('pre-cadastros.create', $lead))
            ->post(route('pre-cadastros.store', $lead), [
                'tipo_proposta' => 'familiar',
                'pessoa' => 'PF',
                'vidas' => [
                    ['tipo' => 'titular', 'nome' => 'Titular 1', 'sexo' => 'masculino', 'data_nascimento' => '1980-01-01', 'documentos_solicitados' => $this->documentosPorSlug(['rg'])],
                    ['tipo' => 'titular', 'nome' => 'Titular 2', 'sexo' => 'feminino', 'data_nascimento' => '1982-02-02', 'gestante' => 1, 'documentos_solicitados' => $this->documentosPorSlug(['rg'])],
                    ['tipo' => 'dependente', 'nome' => 'Dependente sem parentesco', 'sexo' => 'masculino', 'data_nascimento' => '2010-01-01', 'gestante' => 1, 'documentos_solicitados' => $this->documentosPorSlug(['rg'])],
                ],
            ])
            ->assertRedirect(route('pre-cadastros.create', $lead))
            ->assertSessionHasErrors(['vidas']);
    }

    public function test_pre_cadastro_pj_suporta_socios_colaboradores_dependentes_vinculados(): void
    {
        $lead = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Empresa QA',
            'telefone' => '(11) 90000-0002',
            'email' => 'empresa@example.com',
            'tipo_plano' => 'Empresarial',
            'quantidade_vidas' => 3,
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'etapa' => 'propostas',
            'status' => 'proposta_enviada',
        ]);

        $this->actingAs($this->corretor)
            ->post(route('pre-cadastros.store', $lead), [
                'tipo_proposta' => 'empresarial',
                'pessoa' => 'PJ',
                'vidas' => [
                    ['tipo' => 'socio', 'nome' => 'Sócia QA', 'sexo' => 'feminino', 'data_nascimento' => '1985-05-05', 'gestante' => 1, 'cpf' => '11122233344', 'documentos_solicitados' => $this->documentosPorSlug(['rg', 'cpf'])],
                    ['tipo' => 'colaborador', 'nome' => 'Colaborador QA', 'sexo' => 'masculino', 'data_nascimento' => '1990-06-06', 'cargo' => 'Analista', 'documentos_solicitados' => $this->documentosPorSlug(['rg', 'cpf'])],
                    ['tipo' => 'dependente_colaborador', 'vinculo_beneficiario_id' => 1, 'nome' => 'Filho Colaborador', 'parentesco' => 'filho', 'sexo' => 'masculino', 'data_nascimento' => '2018-07-07', 'documentos_solicitados' => $this->documentosPorSlug(['certidao-de-nascimento'])],
                ],
            ])
            ->assertRedirect();

        $lead->refresh()->load('preCadastro.vidas', 'preCadastro.documentosObrigatorios.tipoDocumento');
        $this->assertSame('pre_cadastros', $lead->etapa);
        $this->assertSame(3, $lead->quantidade_vidas);

        $vidas = $lead->preCadastro->vidas->sortBy('ordem')->values();
        $this->assertSame('socio', $vidas[0]->tipo);
        $this->assertFalse($vidas[0]->gestante);
        $this->assertSame('colaborador', $vidas[1]->tipo);
        $this->assertNull($vidas[1]->cargo);
        $this->assertSame('dependente_colaborador', $vidas[2]->tipo);
        $this->assertSame($vidas[1]->id, $vidas[2]->vinculo_beneficiario_id);

        $titulos = $lead->preCadastro->documentosObrigatorios->pluck('titulo')->all();
        $this->assertContains('RG - Beneficiário 1', $titulos);
        $this->assertContains('CPF - Beneficiário 1', $titulos);
        $this->assertContains('RG - Beneficiário 2', $titulos);
        $this->assertContains('CPF - Beneficiário 2', $titulos);
        $this->assertContains('Certidão de Nascimento - Beneficiário 3', $titulos);
    }

    public function test_link_unico_do_pre_cadastro_bloqueia_e_reabre_para_correcao(): void
    {
        Storage::fake('public');

        $lead = Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Link Persistente',
            'telefone' => '(11) 90000-0003',
            'email' => 'linkpersistente@example.com',
            'tipo_plano' => 'Individual',
            'quantidade_vidas' => 1,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'propostas',
            'status' => 'proposta_enviada',
        ]);

        $this->actingAs($this->corretor)
            ->post(route('pre-cadastros.store', $lead), [
                'tipo_proposta' => 'individual',
                'pessoa' => 'PF',
                'vidas' => [
                    ['tipo' => 'titular', 'nome' => 'Cliente Link', 'sexo' => 'feminino', 'data_nascimento' => '1991-01-01', 'documentos_solicitados' => $this->documentosPorSlug(['rg', 'cpf'])],
                ],
            ])
            ->assertRedirect();

        $lead->refresh()->load('preCadastro.documentosObrigatorios');
        $tokenOriginal = $lead->preCadastro->token;
        $rotaPublica = route('cliente.pre-cadastro.show', ['slug' => 'CARLOSOLIVEIRA', 'token' => $tokenOriginal]);

        $this->actingAs($this->corretor)
            ->get(route('pre-cadastros.show', $lead->preCadastro))
            ->assertOk()
            ->assertSee('Pré-cadastro')
            ->assertSee('Link Persistente')
            ->assertDontSee('text-uppercase small fw-semibold text-primary">Lead', false);

        $this->actingAs($this->corretor)
            ->get(route('paginas.simples', 'pre-cadastros'))
            ->assertOk()
            ->assertSee('Abrir pré-cadastro')
            ->assertDontSee('href="'.route('indicacoes.show', $lead).'"', false);

        $this->get($rotaPublica)
            ->assertOk()
            ->assertSee('Validar acesso ao pré-cadastro');

        $this->post(route('cliente.pre-cadastro.validar-acesso', ['slug' => 'CARLOSOLIVEIRA', 'token' => $tokenOriginal]), [
            'chave_acesso' => $lead->preCadastro->chave_acesso,
        ])->assertRedirect($rotaPublica);

        $this->get($rotaPublica)
            ->assertOk()
            ->assertSee('Enviar pré-cadastro');

        $documentosUpload = [];
        foreach ($lead->preCadastro->documentosObrigatorios as $documento) {
            $documentosUpload[$documento->id] = UploadedFile::fake()->create('doc-'.$documento->id.'.pdf', 20, 'application/pdf');
        }

        $this->post(route('cliente.pre-cadastro.store', ['slug' => 'CARLOSOLIVEIRA', 'token' => $tokenOriginal]), [
            'vidas' => $lead->preCadastro->vidas->mapWithKeys(fn ($vida) => [$vida->id => [
                'nome' => 'Cliente Link',
                'cpf' => '12345678900',
                'data_nascimento' => '1991-01-01',
                'sexo' => 'feminino',
                'gestante' => 1,
            ]])->all(),
            'documentos' => $documentosUpload,
        ])->assertRedirect($rotaPublica);

        $lead->refresh()->load('preCadastro');
        $this->assertSame($tokenOriginal, $lead->preCadastro->token);
        $this->assertTrue($lead->preCadastro->formulario_bloqueado);
        $this->assertSame('documentacao_em_analise', $lead->preCadastro->status);

        $this->get($rotaPublica)
            ->assertOk()
            ->assertSee('Recebemos suas informações com sucesso')
            ->assertDontSee('Enviar pré-cadastro');

        $this->post(route('cliente.pre-cadastro.store', ['slug' => 'CARLOSOLIVEIRA', 'token' => $tokenOriginal]), [
            'vidas' => $lead->preCadastro->vidas->mapWithKeys(fn ($vida) => [$vida->id => [
                'nome' => 'Cliente Link',
                'cpf' => '12345678900',
                'data_nascimento' => '1991-01-01',
                'sexo' => 'feminino',
            ]])->all(),
            'documentos' => $documentosUpload,
        ])->assertStatus(423);

        $documento = $lead->preCadastro->documentosObrigatorios()->firstOrFail();
        $this->actingAs($this->corretor)->post(route('indicacoes.documentos.update', [$lead, $documento]), [
            'status' => 'corrigir',
            'observacoes' => 'RG do titular ilegível.',
        ])->assertRedirect();

        $this->actingAs($this->corretor)->post(route('indicacoes.pre-cadastro.correcao', $lead), [
            'motivos_correcao' => 'Revise o RG do titular.',
        ])->assertRedirect();

        $lead->refresh()->load('preCadastro');
        $this->assertSame($tokenOriginal, $lead->preCadastro->token);
        $this->assertFalse($lead->preCadastro->formulario_bloqueado);
        $this->assertSame('documentacao_pendente', $lead->preCadastro->status);

        $this->get($rotaPublica)
            ->assertOk()
            ->assertSee('Seu pré-cadastro precisa de correções')
            ->assertSee('Revise o RG do titular.')
            ->assertSee('RG do titular ilegível.');
    }

    private function documentosPorSlug(array $slugs): array
    {
        $tipos = TipoDocumento::whereIn('slug', $slugs)->get()->keyBy('slug');

        return collect($slugs)
            ->map(fn (string $slug) => $tipos[$slug]?->id)
            ->filter()
            ->values()
            ->all();
    }
}
