<?php

namespace Tests\Feature;

use App\Mail\PreCadastroCorrecaoSolicitadaMail;
use App\Mail\PreCadastroLinkClienteMail;
use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\Indicacao;
use App\Models\PreCadastro;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PreCadastroEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $corretor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->corretor = User::where('email', 'carlos@nexosaude.local')->firstOrFail();
    }

    public function test_geracao_de_link_envia_email_para_cliente(): void
    {
        Mail::fake();
        $lead = $this->criarLead();

        $response = $this->actingAs($this->corretor)
            ->post(route('pre-cadastros.store', $lead), $this->payloadPreCadastro());

        $response->assertRedirect();
        $lead->refresh();

        Mail::assertSent(PreCadastroLinkClienteMail::class, function (PreCadastroLinkClienteMail $mail) use ($lead) {
            return $mail->hasTo($lead->email)
                && $mail->indicacao->is($lead)
                && $mail->corretor->is($this->corretor)
                && $mail->preCadastro->is($lead->preCadastro)
                && str_contains($mail->linkPreCadastro, $lead->preCadastro->token)
                && $mail->preCadastro->chave_acesso !== null;
        });
    }

    public function test_geracao_de_link_sem_email_nao_quebra_fluxo(): void
    {
        Mail::fake();
        $lead = $this->criarLead(null);

        $response = $this->actingAs($this->corretor)
            ->post(route('pre-cadastros.store', $lead), $this->payloadPreCadastro());

        $response->assertRedirect();
        $this->assertNotNull($lead->refresh()->preCadastro);
        Mail::assertNothingSent();
    }

    public function test_solicitacao_de_correcao_envia_email_com_documento_motivo_link_e_token(): void
    {
        Mail::fake();
        $lead = $this->criarPreCadastroParaCorrecao();
        $motivo = 'Documento ilegível. Envie uma nova foto com o documento inteiro.';

        $response = $this->actingAs($this->corretor)
            ->from(route('pre-cadastros.show', $lead->preCadastro))
            ->post(route('indicacoes.pre-cadastro.correcao', $lead), [
                'motivos_correcao' => $motivo,
            ]);

        $response->assertRedirect(route('pre-cadastros.show', $lead->preCadastro));

        Mail::assertSent(PreCadastroCorrecaoSolicitadaMail::class, function (PreCadastroCorrecaoSolicitadaMail $mail) use ($lead, $motivo) {
            return $mail->hasTo($lead->email)
                && $mail->indicacao->is($lead)
                && $mail->corretor->is($this->corretor)
                && $mail->preCadastro->is($lead->preCadastro)
                && $mail->nomeDocumento === 'Documento de identidade com foto'
                && $mail->motivoCorrecao === $motivo
                && str_contains($mail->linkPreCadastro, $lead->preCadastro->token)
                && $mail->preCadastro->chave_acesso !== null;
        });
    }

    public function test_solicitacao_de_correcao_sem_email_nao_quebra_fluxo(): void
    {
        Mail::fake();
        $lead = $this->criarPreCadastroParaCorrecao(null);

        $response = $this->actingAs($this->corretor)
            ->from(route('pre-cadastros.show', $lead->preCadastro))
            ->post(route('indicacoes.pre-cadastro.correcao', $lead), [
                'motivos_correcao' => 'Documento ilegível.',
            ]);

        $response->assertRedirect(route('pre-cadastros.show', $lead->preCadastro));
        $this->assertFalse($lead->preCadastro->refresh()->formulario_bloqueado);
        Mail::assertNothingSent();
    }

    public function test_falha_de_smtp_nao_impede_geracao_do_link(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP indisponivel'));
        $lead = $this->criarLead();

        $response = $this->actingAs($this->corretor)
            ->post(route('pre-cadastros.store', $lead), $this->payloadPreCadastro());

        $response->assertRedirect();
        $this->assertNotNull($lead->refresh()->preCadastro);
    }

    public function test_falha_de_smtp_nao_impede_solicitacao_de_correcao(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP indisponivel'));
        $lead = $this->criarPreCadastroParaCorrecao();

        $response = $this->actingAs($this->corretor)
            ->from(route('pre-cadastros.show', $lead->preCadastro))
            ->post(route('indicacoes.pre-cadastro.correcao', $lead), [
                'motivos_correcao' => 'Documento ilegível.',
            ]);

        $response->assertRedirect(route('pre-cadastros.show', $lead->preCadastro));
        $this->assertFalse($lead->preCadastro->refresh()->formulario_bloqueado);
    }

    private function criarLead(?string $email = 'cliente-pre-cadastro@example.com'): Indicacao
    {
        return Indicacao::create([
            'user_id' => $this->corretor->id,
            'origem' => 'cadastro_interno',
            'nome_cliente' => 'Cliente Pré Cadastro',
            'telefone' => '(11) 90000-0001',
            'email' => $email,
            'tipo_plano' => 'Individual',
            'quantidade_vidas' => 1,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);
    }

    private function criarPreCadastroParaCorrecao(?string $email = 'cliente-correcao@example.com'): Indicacao
    {
        $lead = $this->criarLead($email);
        $preCadastro = $lead->preCadastro()->create([
            'token' => 'token-correcao-'.str()->random(16),
            'chave_acesso' => strtoupper(str()->random(4)).'-'.strtoupper(str()->random(4)),
            'chave_expira_em' => now()->addDays(14),
            'tipo_proposta' => 'individual',
            'pessoa' => 'PF',
            'status' => 'documentacao_em_analise',
            'formulario_bloqueado' => true,
            'enviado_em' => now(),
            'bloqueado_em' => now(),
        ]);
        $vida = $preCadastro->vidas()->create([
            'tipo' => 'titular',
            'ordem' => 1,
            'nome' => 'Cliente Pré Cadastro',
            'sexo' => 'masculino',
            'data_nascimento' => '1980-01-01',
            'gestante' => false,
            'cpf' => '12345678909',
        ]);
        $tipoDocumento = TipoDocumento::where('nome', 'Documento de identidade com foto')->firstOrFail();

        DocumentoObrigatorioPreCadastro::create([
            'pre_cadastro_id' => $preCadastro->id,
            'vida_proposta_id' => $vida->id,
            'tipo_documento_id' => $tipoDocumento->id,
            'titulo' => 'Documento de identidade com foto - Beneficiário 1',
            'obrigatorio' => true,
            'ordem' => 1,
            'status' => 'corrigir',
            'observacoes' => 'Documento ilegível.',
        ]);

        $lead->update([
            'etapa' => 'pre_cadastros',
            'status' => 'documentacao_em_analise',
        ]);

        return $lead->refresh();
    }

    private function payloadPreCadastro(): array
    {
        return [
            'tipo_proposta' => 'individual',
            'pessoa' => 'PF',
            'vidas' => [
                [
                    'tipo' => 'titular',
                    'documentos_solicitados' => $this->idsDocumentos(['Documento de identidade com foto', 'CPF']),
                ],
            ],
        ];
    }

    private function idsDocumentos(array $nomes): array
    {
        return TipoDocumento::whereIn('nome', $nomes)->pluck('id')->all();
    }
}
