<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_corretor_nao_acessa_admin(): void
    {
        $corretor = User::factory()->create(['perfil' => 'corretor']);
        $this->criarAssinatura($corretor);

        $this->actingAs($corretor)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_usuario_bloqueado_nao_acessa_sistema_nem_consegue_login(): void
    {
        $bloqueado = User::factory()->create([
            'email' => 'bloqueado@example.com',
            'password' => Hash::make('password'),
            'perfil' => 'admin',
            'is_admin' => true,
            'blocked_at' => now(),
        ]);

        $this->actingAs($bloqueado)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => 'bloqueado@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_billing_status_inconsistente_bloqueia_area_interna_do_corretor(): void
    {
        $corretor = User::factory()->create([
            'perfil' => 'corretor',
            'billing_status' => 'canceled',
        ]);

        $this->criarAssinatura($corretor, [
            'status' => 'active',
            'status_assinatura' => 'ativa',
        ]);

        $this->actingAs($corretor)
            ->get(route('dashboard'))
            ->assertRedirect(route('assinatura.bloqueada'));
    }

    public function test_ultimo_admin_nao_pode_perder_privilegios_bloqueio_ou_exclusao(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'perfil' => 'admin',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.usuarios.edit', $admin))
            ->put(route('admin.usuarios.update', $admin), [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'telefone' => null,
                'perfil' => 'corretor',
            ])
            ->assertSessionHasErrors('usuario');

        $admin->refresh();
        $this->assertTrue($admin->is_admin);
        $this->assertSame('admin', $admin->perfil);

        $this->actingAs($admin)
            ->post(route('admin.usuarios.bloquear', $admin))
            ->assertSessionHasErrors('usuario');

        $this->actingAs($admin)
            ->delete(route('admin.usuarios.destroy', $admin))
            ->assertSessionHasErrors('usuario');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_webhook_asaas_exige_token_confere_customer_e_sanitiza_payload(): void
    {
        config(['services.asaas.webhook_token' => 'secret-webhook']);

        $corretor = User::factory()->create([
            'asaas_customer_id' => 'cus_local',
            'asaas_subscription_id' => 'sub_local',
            'billing_status' => 'blocked',
        ]);

        $assinatura = $this->criarAssinatura($corretor, [
            'asaas_customer_id' => 'cus_local',
            'asaas_subscription_id' => 'sub_local',
            'status' => 'pending',
            'status_assinatura' => 'bloqueada',
        ]);

        $payload = [
            'event' => 'PAYMENT_CONFIRMED',
            'payment' => [
                'subscription' => 'sub_local',
                'customer' => 'cus_local',
                'dueDate' => now()->toDateString(),
                'paymentDate' => now()->toDateString(),
                'creditCard' => ['number' => '4111111111111111'],
                'creditCardToken' => 'card-token',
            ],
        ];

        $this->postJson('/webhooks/asaas', $payload)->assertUnauthorized();

        $this->withHeader('asaas-access-token', 'secret-webhook')
            ->postJson('/webhooks/asaas', array_replace_recursive($payload, [
                'payment' => ['customer' => 'cus_errado'],
            ]))
            ->assertStatus(422);

        $this->withHeader('asaas-access-token', 'secret-webhook')
            ->postJson('/webhooks/asaas', $payload)
            ->assertOk();

        $assinatura->refresh();
        $this->assertSame('active', $assinatura->status);
        $this->assertSame('ativa', $assinatura->status_assinatura);
        $this->assertSame('[redacted]', $assinatura->gateway_payload['payment']['creditCard']);
        $this->assertSame('[redacted]', $assinatura->gateway_payload['payment']['creditCardToken']);
    }

    private function criarAssinatura(User $user, array $overrides = []): Assinatura
    {
        return Assinatura::create(array_merge([
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
        ], $overrides));
    }
}
