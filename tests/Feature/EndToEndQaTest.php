<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\AuditoriaLog;
use App\Models\CorretorPerfil;
use App\Models\Indicacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndQaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config(['services.asaas.webhook_token' => 'qa-webhook-token']);
    }

    public function test_cadastro_corretor_cria_cliente_asaas_assinatura_trial_e_perfil_sem_foto_falsa(): void
    {
        Http::fake([
            '*/customers' => Http::response([
                'id' => 'cus_qa_123',
                'name' => 'QA Corretor',
            ], 200),
            '*/subscriptions' => Http::response([
                'id' => 'sub_qa_123',
                'customer' => 'cus_qa_123',
                'value' => 49.90,
                'nextDueDate' => now()->addDays(30)->toDateString(),
                'creditCard' => [
                    'creditCardBrand' => 'VISA',
                    'creditCardNumber' => '1111',
                    'creditCardToken' => 'card-token-qa',
                ],
            ], 200),
        ]);

        $this->post(route('register.store'), [
            'name' => 'QA Corretor',
            'email' => 'qa-corretor@example.com',
            'telefone' => '(11) 98888-0000',
            'billing_cpf_cnpj' => '12345678901',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'card_holder_name' => 'QA Corretor',
            'card_number' => '4111111111111111',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2030',
            'card_ccv' => '123',
            'accepted_terms' => '1',
        ])->assertRedirect(route('perfil-publico.edit'));

        Http::assertSentCount(2);

        $user = User::where('email', 'qa-corretor@example.com')->firstOrFail();
        $this->assertSame('corretor', $user->perfil);
        $this->assertSame('cus_qa_123', $user->asaas_customer_id);
        $this->assertSame('sub_qa_123', $user->asaas_subscription_id);
        $this->assertSame('trial', $user->billing_status);

        $this->assertDatabaseHas('assinaturas', [
            'user_id' => $user->id,
            'asaas_customer_id' => 'cus_qa_123',
            'asaas_subscription_id' => 'sub_qa_123',
            'status' => 'trialing',
            'status_assinatura' => 'teste_gratis',
        ]);

        $perfil = CorretorPerfil::where('user_id', $user->id)->firstOrFail();
        $this->assertNull($perfil->foto_path);
    }

    public function test_login_e_bloqueios_por_status_de_assinatura(): void
    {
        $ativos = [
            'teste_gratis' => ['status' => 'trialing', 'billing_status' => 'trial'],
            'ativa' => ['status' => 'active', 'billing_status' => 'active'],
        ];

        foreach ($ativos as $statusAssinatura => $dados) {
            $corretor = $this->corretorComAssinatura([
                'email' => "ativo-{$statusAssinatura}@example.com",
                'billing_status' => $dados['billing_status'],
            ], [
                'status' => $dados['status'],
                'status_assinatura' => $statusAssinatura,
                'data_fim_teste_gratis' => now()->addDays(10)->toDateString(),
                'trial_ends_at' => now()->addDays(10),
            ]);

            $this->post(route('login.store'), [
                'email' => $corretor->email,
                'password' => 'password',
            ])->assertRedirect(route('dashboard'));

            auth()->logout();
        }

        foreach (['vencida' => 'overdue', 'cancelada' => 'canceled', 'bloqueada' => 'blocked'] as $statusAssinatura => $billingStatus) {
            $corretor = $this->corretorComAssinatura([
                'email' => "bloqueio-{$statusAssinatura}@example.com",
                'billing_status' => $billingStatus,
            ], [
                'status' => $billingStatus === 'blocked' ? 'pending' : $billingStatus,
                'status_assinatura' => $statusAssinatura,
            ]);

            $this->actingAs($corretor)
                ->get(route('dashboard'))
                ->assertRedirect(route('assinatura.bloqueada'));
        }

        $bloqueado = $this->corretorComAssinatura([
            'email' => 'usuario-bloqueado@example.com',
            'blocked_at' => now(),
        ]);

        auth()->logout();
        $this->flushSession();

        $this->post(route('login.store'), [
            'email' => $bloqueado->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_fluxos_de_assinatura_cancelar_atualizar_cartao_regularizar_e_reativar(): void
    {
        Http::fake([
            '*/subscriptions/sub_qa/creditCard' => Http::response([
                'creditCard' => [
                    'creditCardBrand' => 'MASTERCARD',
                    'creditCardNumber' => '2222',
                    'creditCardToken' => 'new-card-token',
                ],
            ], 200),
            '*/subscriptions/sub_qa' => Http::response(['deleted' => true], 200),
            '*/subscriptions' => Http::response([
                'id' => 'sub_reactivated',
                'customer' => 'cus_qa',
                'status' => 'ACTIVE',
                'value' => 49.90,
                'nextDueDate' => now()->toDateString(),
                'creditCard' => [
                    'creditCardBrand' => 'VISA',
                    'creditCardNumber' => '3333',
                    'creditCardToken' => 'reactivated-token',
                ],
            ], 200),
        ]);

        $corretor = $this->corretorComAssinatura([
            'asaas_customer_id' => 'cus_qa',
            'asaas_subscription_id' => 'sub_qa',
            'billing_status' => 'active',
        ], [
            'asaas_customer_id' => 'cus_qa',
            'asaas_subscription_id' => 'sub_qa',
            'status' => 'active',
            'status_assinatura' => 'ativa',
        ]);

        $cartao = $this->dadosCartao();

        $this->actingAs($corretor)
            ->post(route('configuracoes.assinatura.cartao.update'), $cartao)
            ->assertRedirect();

        $this->assertDatabaseHas('assinaturas', [
            'user_id' => $corretor->id,
            'card_brand' => 'MASTERCARD',
            'card_last_four' => '2222',
        ]);

        $this->actingAs($corretor)
            ->post(route('configuracoes.assinatura.cancelar'), [
                'confirmar_cancelamento' => '1',
            ])
            ->assertRedirect(route('assinatura.bloqueada'));

        $this->assertDatabaseHas('assinaturas', [
            'user_id' => $corretor->id,
            'status' => 'canceled',
            'status_assinatura' => 'cancelada',
        ]);

        $corretor->refresh();

        $this->actingAs($corretor)
            ->post(route('assinatura.reativar'), $cartao)
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('assinaturas', [
            'user_id' => $corretor->id,
            'asaas_subscription_id' => 'sub_reactivated',
            'status' => 'active',
            'status_assinatura' => 'ativa',
        ]);

        $corretor->assinatura->update([
            'asaas_subscription_id' => 'sub_qa',
            'status' => 'overdue',
            'status_assinatura' => 'vencida',
        ]);
        $corretor->update([
            'billing_status' => 'overdue',
            'asaas_subscription_id' => 'sub_qa',
        ]);

        $this->actingAs($corretor)
            ->post(route('assinatura.regularizar'), $cartao)
            ->assertRedirect();
    }

    public function test_jornada_comercial_principal_do_corretor_e_paginas_operacionais(): void
    {
        Storage::fake('public');

        $corretor = User::where('email', 'carlos@nexosaude.local')->firstOrFail();

        foreach ([
            route('dashboard'),
            route('busca.index', ['q' => 'Ana']),
            route('indicacoes.index'),
            route('indicacoes.create'),
            route('perfil-publico.edit'),
            route('configuracoes.perfil'),
            route('configuracoes.assinatura'),
            route('agenda.index'),
            route('tarefas.index'),
            route('alertas.index'),
            route('paginas.simples', 'clientes'),
            route('paginas.simples', 'carteira'),
        ] as $url) {
            $this->actingAs($corretor)->get($url)->assertOk();
        }

        $this->actingAs($corretor)
            ->post(route('indicacoes.store'), [
                'nome_cliente' => 'Lead QA E2E',
                'telefone' => '(11) 97777-0000',
                'email' => 'lead-qa-e2e@example.com',
                'tipo_plano' => 'Familiar',
                'quantidade_vidas' => 2,
                'cidade' => 'Sao Paulo',
                'estado' => 'SP',
                'possui_preferencias' => 'nao',
            ])
            ->assertRedirect();

        $lead = Indicacao::where('email', 'lead-qa-e2e@example.com')->firstOrFail();

        $this->actingAs($corretor)
            ->get(route('indicacoes.show', $lead))
            ->assertOk()
            ->assertSee('Lead QA E2E');
    }

    public function test_admin_login_dashboard_ajax_crud_auditoria_e_seguranca(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin QA',
            'email' => 'admin-qa@example.com',
            'password' => Hash::make('password'),
            'perfil' => 'admin',
            'is_admin' => true,
        ]);

        $corretor = $this->corretorComAssinatura([
            'email' => 'corretor-admin-qa@example.com',
        ]);

        $this->post(route('login.store'), [
            'email' => 'admin-qa@example.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Usuários totais');
        $this->actingAs($admin)->get(route('admin.usuarios.index'))->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.usuarios.index', ['q' => 'Admin QA']))
            ->assertOk()
            ->assertSee('Admin QA');
        $this->actingAs($admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.usuarios.index', ['page' => 1]))
            ->assertOk()
            ->assertSee('admin-users-table');

        $this->actingAs($admin)
            ->post(route('admin.usuarios.store'), [
                'name' => 'Usuario Admin Criado',
                'email' => 'usuario-admin-criado@example.com',
                'telefone' => '(11) 90000-1111',
                'perfil' => 'corretor',
                'password' => 'password123',
            ])
            ->assertRedirect(route('admin.usuarios.index'));

        $usuario = User::where('email', 'usuario-admin-criado@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.usuarios.update', $usuario), [
                'name' => 'Usuario Admin Editado',
                'email' => 'usuario-admin-criado@example.com',
                'telefone' => '(11) 90000-2222',
                'perfil' => 'corretor',
            ])
            ->assertRedirect(route('admin.usuarios.index'));

        $this->actingAs($admin)->post(route('admin.usuarios.bloquear', $usuario))->assertRedirect();
        $this->assertNotNull($usuario->fresh()->blocked_at);

        $this->actingAs($admin)->post(route('admin.usuarios.desbloquear', $usuario))->assertRedirect();
        $this->assertNull($usuario->fresh()->blocked_at);

        $this->actingAs($admin)->delete(route('admin.usuarios.destroy', $usuario))->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $usuario->id]);

        $this->assertGreaterThanOrEqual(5, AuditoriaLog::where('admin_user_id', $admin->id)->count());

        $this->actingAs($admin)->get(route('admin.auditoria.index'))->assertOk();
        $this->actingAs($admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('admin.auditoria.index', ['page' => 1]))
            ->assertOk()
            ->assertSee('admin-audit-table');

        $this->actingAs($corretor)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.usuarios.bloquear', $admin))->assertSessionHasErrors('usuario');
        $this->actingAs($admin)->delete(route('admin.usuarios.destroy', $admin))->assertSessionHasErrors('usuario');
    }

    public function test_webhooks_asaas_sincronizam_assinatura_e_usuario(): void
    {
        $corretor = $this->corretorComAssinatura([
            'asaas_customer_id' => 'cus_webhook',
            'asaas_subscription_id' => 'sub_webhook',
            'billing_status' => 'blocked',
        ], [
            'asaas_customer_id' => 'cus_webhook',
            'asaas_subscription_id' => 'sub_webhook',
            'status' => 'pending',
            'status_assinatura' => 'bloqueada',
        ]);

        foreach (['PAYMENT_CONFIRMED', 'PAYMENT_RECEIVED'] as $event) {
            $this->enviarWebhook($event)->assertOk();

            $corretor->refresh();
            $this->assertSame('active', $corretor->billing_status);
            $this->assertSame('active', $corretor->assinatura->fresh()->status);
            $this->assertSame('ativa', $corretor->assinatura->fresh()->status_assinatura);
        }

        foreach (['PAYMENT_OVERDUE', 'PAYMENT_CREDIT_CARD_CAPTURE_REFUSED'] as $event) {
            $this->enviarWebhook($event)->assertOk();

            $corretor->refresh();
            $this->assertSame('overdue', $corretor->billing_status);
            $this->assertSame('overdue', $corretor->assinatura->fresh()->status);
            $this->assertSame('vencida', $corretor->assinatura->fresh()->status_assinatura);
        }

        $this->enviarWebhook('SUBSCRIPTION_DELETED', [
            'subscription' => [
                'id' => 'sub_webhook',
                'customer' => 'cus_webhook',
            ],
        ])->assertOk();

        $corretor->refresh();
        $this->assertSame('canceled', $corretor->billing_status);
        $this->assertSame('canceled', $corretor->assinatura->fresh()->status);
        $this->assertSame('cancelada', $corretor->assinatura->fresh()->status_assinatura);
    }

    private function enviarWebhook(string $event, array $payloadExtra = [])
    {
        $payload = array_replace_recursive([
            'event' => $event,
            'payment' => [
                'subscription' => 'sub_webhook',
                'customer' => 'cus_webhook',
                'dueDate' => now()->toDateString(),
                'paymentDate' => now()->toDateString(),
            ],
        ], $payloadExtra);

        return $this->withHeader('asaas-access-token', 'qa-webhook-token')
            ->postJson('/webhooks/asaas', $payload);
    }

    private function corretorComAssinatura(array $userOverrides = [], array $assinaturaOverrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'perfil' => 'corretor',
            'password' => Hash::make('password'),
            'billing_status' => 'trial',
            'trial_ends_at' => now()->addDays(10),
        ], $userOverrides));

        Assinatura::create(array_merge([
            'user_id' => $user->id,
            'data_inicio_teste_gratis' => now()->subDay()->toDateString(),
            'data_fim_teste_gratis' => now()->addDays(10)->toDateString(),
            'status_assinatura' => 'teste_gratis',
            'valor_assinatura' => 49.90,
            'vencimento_assinatura' => now()->addDays(10)->toDateString(),
            'status' => 'trialing',
            'valor' => 49.90,
            'trial_started_at' => now()->subDay(),
            'trial_ends_at' => now()->addDays(10),
            'next_payment_at' => now()->addDays(10),
        ], $assinaturaOverrides));

        return $user->fresh('assinatura');
    }

    private function dadosCartao(): array
    {
        return [
            'billing_cpf_cnpj' => '12345678901',
            'holder_phone' => '(11) 98888-0000',
            'card_holder_name' => 'QA Corretor',
            'card_number' => '4111111111111111',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2030',
            'card_ccv' => '123',
            'holder_postal_code' => '01001000',
            'holder_address_number' => '100',
        ];
    }
}
