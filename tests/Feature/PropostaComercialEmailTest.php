<?php

namespace Tests\Feature;

use App\Mail\PropostaComercialClienteMail;
use App\Models\Indicacao;
use App\Models\Operadora;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropostaComercialEmailTest extends TestCase
{
    use RefreshDatabase;

    private User $corretor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
        $this->corretor = User::where('email', 'carlos@nexosaude.local')->firstOrFail();
    }

    public function test_upload_de_uma_proposta_salva_e_envia_email_singular(): void
    {
        Mail::fake();
        $lead = $this->criarLead();

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.propostas.store', $lead), $this->payload([
                UploadedFile::fake()->create('proposta.pdf', 32, 'application/pdf'),
            ]))
            ->assertRedirect(route('paginas.simples', 'propostas'));

        $lead->refresh();
        $this->assertCount(1, $lead->propostas);
        $this->assertDatabaseHas('timeline_eventos', [
            'indicacao_id' => $lead->id,
            'descricao' => 'Proposta anexada e enviada por e-mail ao cliente.',
        ]);

        Mail::assertSent(PropostaComercialClienteMail::class, function (PropostaComercialClienteMail $mail) use ($lead) {
            $mail->assertHasSubject('Sua proposta comercial de plano de saúde');

            return $mail->hasTo($lead->email)
                && $mail->propostas->count() === 1
                && str_contains($mail->render(), 'uma proposta comercial para sua análise')
                && str_contains($mail->linkPublico, $mail->propostas->first()->public_group_token);
        });
    }

    public function test_upload_de_multiplas_propostas_salva_todos_os_arquivos_e_email_plural_com_anexos(): void
    {
        Mail::fake();
        $lead = $this->criarLead();

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.propostas.store', $lead), $this->payload([
                UploadedFile::fake()->create('proposta-a.pdf', 32, 'application/pdf'),
                UploadedFile::fake()->create('proposta-b.pdf', 32, 'application/pdf'),
            ]))
            ->assertRedirect(route('paginas.simples', 'propostas'));

        $lead->refresh();
        $this->assertCount(2, $lead->propostas);
        $this->assertSame(1, $lead->propostas->pluck('public_group_token')->unique()->count());
        $this->assertDatabaseHas('timeline_eventos', [
            'indicacao_id' => $lead->id,
            'descricao' => 'Cotações anexadas e enviadas por e-mail ao cliente.',
        ]);

        Mail::assertSent(PropostaComercialClienteMail::class, function (PropostaComercialClienteMail $mail) use ($lead) {
            $mail->assertHasSubject('Suas cotações de plano de saúde');
            $html = $mail->render();

            if (! $mail->hasTo($lead->email) || $mail->propostas->count() !== 2 || ! str_contains($html, 'algumas cotações para que você possa comparar')) {
                return false;
            }

            foreach ($mail->propostas as $proposta) {
                if (! $mail->hasAttachment(Storage::disk('public')->path($proposta->arquivo_pdf_path), [
                    'as' => str($proposta->titulo)->slug()->append('.pdf')->toString(),
                    'mime' => 'application/pdf',
                ])) {
                    return false;
                }
            }

            return true;
        });
    }

    public function test_formulario_da_lead_renderiza_input_multiplo_de_pdf(): void
    {
        $lead = $this->criarLead();

        $this->actingAs($this->corretor)
            ->get(route('indicacoes.show', $lead))
            ->assertOk()
            ->assertSee('name="arquivos_pdf[]"', false)
            ->assertSee('accept="application/pdf"', false)
            ->assertSee('multiple', false)
            ->assertSee('Selecione uma ou mais propostas em PDF');
    }

    public function test_formulario_da_area_de_propostas_renderiza_input_multiplo_de_pdf(): void
    {
        $lead = $this->criarLead();
        $lead->update(['etapa' => 'propostas', 'status' => 'proposta_enviada']);

        $this->actingAs($this->corretor)
            ->get(route('propostas.show', $lead))
            ->assertOk()
            ->assertSee('name="arquivos_pdf[]"', false)
            ->assertSee('accept="application/pdf"', false)
            ->assertSee('multiple', false)
            ->assertSee('Selecione uma ou mais propostas em PDF');
    }

    public function test_envio_plural_arquivos_pdf_tambem_e_aceito(): void
    {
        Mail::fake();
        $lead = $this->criarLead();
        $payload = $this->payload([
            UploadedFile::fake()->create('plural-a.pdf', 32, 'application/pdf'),
            UploadedFile::fake()->create('plural-b.pdf', 32, 'application/pdf'),
        ]);
        $payload['arquivos_pdf'] = $payload['arquivo_pdf'];
        unset($payload['arquivo_pdf']);

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.propostas.store', $lead), $payload)
            ->assertRedirect(route('paginas.simples', 'propostas'));

        $lead->refresh();
        $this->assertCount(2, $lead->propostas);
        $this->assertSame(1, $lead->propostas->pluck('public_group_token')->unique()->count());
    }

    public function test_falha_no_envio_de_email_nao_impede_salvar_propostas(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP indisponivel'));
        $lead = $this->criarLead();

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.propostas.store', $lead), $this->payload([
                UploadedFile::fake()->create('proposta.pdf', 32, 'application/pdf'),
            ]))
            ->assertRedirect(route('paginas.simples', 'propostas'));

        $this->assertCount(1, $lead->refresh()->propostas);
        $this->assertDatabaseHas('timeline_eventos', [
            'indicacao_id' => $lead->id,
            'descricao' => 'Proposta anexada. Não foi possível enviar o e-mail automaticamente.',
        ]);
    }

    public function test_cliente_sem_email_nao_quebra_fluxo(): void
    {
        Mail::fake();
        $lead = $this->criarLead(email: null);

        $this->actingAs($this->corretor)
            ->post(route('indicacoes.propostas.store', $lead), $this->payload([
                UploadedFile::fake()->create('proposta.pdf', 32, 'application/pdf'),
            ]))
            ->assertRedirect(route('paginas.simples', 'propostas'));

        $this->assertCount(1, $lead->refresh()->propostas);
        $this->assertDatabaseHas('timeline_eventos', [
            'indicacao_id' => $lead->id,
            'descricao' => 'Proposta anexada. Cliente sem e-mail cadastrado para envio automático.',
        ]);
        Mail::assertNothingSent();
    }

    public function test_link_publico_de_proposta_unica_funciona_com_token_valido(): void
    {
        Mail::fake();
        $lead = $this->criarLead();
        $this->criarPropostas($lead, ['proposta.pdf']);
        $proposta = $lead->refresh()->propostas->first();

        $this->get(route('publico.propostas.show', $proposta->public_group_token))
            ->assertOk()
            ->assertSee('Proposta Familiar')
            ->assertSee(route('publico.propostas.download', ['token' => $proposta->public_group_token, 'proposta' => $proposta]), false);
    }

    public function test_link_publico_de_multiplas_propostas_lista_arquivos_com_token_valido(): void
    {
        Mail::fake();
        $lead = $this->criarLead();
        $this->criarPropostas($lead, ['proposta-a.pdf', 'proposta-b.pdf']);
        $token = $lead->refresh()->propostas->first()->public_group_token;

        $this->get(route('publico.propostas.show', $token))
            ->assertOk()
            ->assertSee('Proposta Familiar 1')
            ->assertSee('Proposta Familiar 2');
    }

    public function test_token_publico_invalido_retorna_404(): void
    {
        $this->get(route('publico.propostas.show', 'token-invalido'))
            ->assertNotFound();
    }

    private function criarPropostas(Indicacao $lead, array $arquivos): void
    {
        $this->actingAs($this->corretor)
            ->post(route('indicacoes.propostas.store', $lead), $this->payload(
                collect($arquivos)
                    ->map(fn (string $nome) => UploadedFile::fake()->create($nome, 32, 'application/pdf'))
                    ->all()
            ));
    }

    private function payload(array $arquivos): array
    {
        return [
            'titulo' => 'Proposta Familiar',
            'operadora_id' => Operadora::firstOrFail()->id,
            'valor_mensal' => 450.90,
            'quantidade_vidas' => 3,
            'validade' => now()->addDays(10)->toDateString(),
            'observacoes' => 'Cotação enviada para análise.',
            'arquivo_pdf' => $arquivos,
        ];
    }

    private function criarLead(?string $email = 'cliente-proposta@example.com', ?string $telefone = '(11) 98888-1111'): Indicacao
    {
        return Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Cliente Proposta',
            'telefone' => $telefone,
            'email' => $email,
            'tipo_plano' => 'Familiar',
            'quantidade_vidas' => 3,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);
    }
}
