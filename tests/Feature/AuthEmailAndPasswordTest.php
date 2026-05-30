<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthEmailAndPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_cria_usuario_nao_verificado_e_envia_email_de_confirmacao(): void
    {
        Notification::fake();
        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_auth_123'], 200),
            '*/subscriptions' => Http::response([
                'id' => 'sub_auth_123',
                'customer' => 'cus_auth_123',
                'value' => 49.90,
                'nextDueDate' => now()->addDays(30)->toDateString(),
                'creditCard' => [
                    'creditCardBrand' => 'VISA',
                    'creditCardNumber' => '1111',
                    'creditCardToken' => 'card-token-auth',
                ],
            ], 200),
        ]);

        $this->post(route('register.store'), $this->dadosCadastro([
            'email' => 'novo-corretor@example.com',
        ]))->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'novo-corretor@example.com')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_usuario_nao_verificado_nao_acessa_dashboard(): void
    {
        $user = $this->corretorComAssinatura([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_usuario_confirma_email_com_link_valido_e_acessa_fluxo_correto(): void
    {
        $user = $this->corretorComAssinatura([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get($this->verificationUrl($user))
            ->assertRedirect(route('perfil-publico.edit'));

        $this->assertNotNull($user->fresh()->email_verified_at);

        $this->actingAs($user->fresh())
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_reenvio_de_confirmacao_funciona_com_rate_limit_de_rota(): void
    {
        Notification::fake();

        $user = $this->corretorComAssinatura([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_link_invalido_ou_expirado_nao_confirma_email(): void
    {
        $user = $this->corretorComAssinatura([
            'email_verified_at' => null,
        ]);

        $invalidHashUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'hash-invalido']
        );

        $this->actingAs($user)->get($invalidHashUrl)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);

        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $this->actingAs($user)->get($expiredUrl)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_tela_esqueci_minha_senha_abre_corretamente(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Solicitar link');
    }

    public function test_solicitacao_de_reset_envia_email_sem_revelar_existencia_do_usuario(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $this->post(route('password.email'), [
            'email' => 'nao-existe@example.com',
        ])->assertSessionHas('status');
    }

    public function test_reset_com_token_valido_altera_senha_e_token_nao_pode_ser_reutilizado(): void
    {
        $user = User::factory()->create([
            'email' => 'senha@example.com',
            'password' => Hash::make('password'),
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.update'), $this->dadosReset($user, $token))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('nova-senha-segura', $user->fresh()->password));

        $this->post(route('password.update'), $this->dadosReset($user, $token, 'outra-senha-segura'))
            ->assertSessionHasErrors('email');
    }

    public function test_reset_com_token_invalido_falha(): void
    {
        $user = User::factory()->create([
            'email' => 'token-invalido@example.com',
        ]);

        $this->post(route('password.update'), $this->dadosReset($user, 'token-invalido'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_confirma_email_e_reseta_senha(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-confirmacao@example.com',
            'perfil' => 'admin',
            'is_admin' => true,
            'email_verified_at' => null,
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)
            ->get($this->verificationUrl($admin))
            ->assertRedirect(route('admin.dashboard'));

        auth()->logout();

        $token = Password::createToken($admin->fresh());

        $this->post(route('password.update'), $this->dadosReset($admin->fresh(), $token))
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('nova-senha-segura', $admin->fresh()->password));
    }

    public function test_corretor_bloqueado_continua_sem_acesso_mesmo_com_email_confirmado(): void
    {
        $user = $this->corretorComAssinatura([
            'blocked_at' => now(),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    private function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );
    }

    private function dadosCadastro(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Novo Corretor',
            'email' => 'novo@example.com',
            'telefone' => '(11) 98888-0000',
            'billing_cpf_cnpj' => '12345678901',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'card_holder_name' => 'Novo Corretor',
            'card_number' => '4111111111111111',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2030',
            'card_ccv' => '123',
            'accepted_terms' => '1',
        ], $overrides);
    }

    private function dadosReset(User $user, string $token, string $password = 'nova-senha-segura'): array
    {
        return [
            'token' => $token,
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
        ];
    }

    private function corretorComAssinatura(array $userOverrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'perfil' => 'corretor',
            'password' => Hash::make('password'),
            'billing_status' => 'trial',
            'trial_ends_at' => now()->addDays(10),
        ], $userOverrides));

        Assinatura::create([
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
        ]);

        return $user->fresh('assinatura');
    }
}
