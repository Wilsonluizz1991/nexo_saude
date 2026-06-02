<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CriarContaRequest;
use App\Models\CorretorPerfil;
use App\Models\User;
use App\Services\Asaas\AsaasCustomerService;
use App\Services\Asaas\AsaasSubscriptionService;
use App\Services\Billing\RegistrarAssinaturaService;
use App\Services\ServicoSessaoUsuario;
use App\Services\WhatsAppLinkService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(
        CriarContaRequest $request,
        AsaasCustomerService $asaasCustomerService,
        AsaasSubscriptionService $asaasSubscriptionService,
        RegistrarAssinaturaService $registrarAssinaturaService
    ) {
        $mensagemErroPublica = 'Não foi possível criar sua conta e ativar o período gratuito. Verifique os dados informados e tente novamente.';

        try {
            $user = DB::transaction(function () use (
                $request,
                $asaasCustomerService,
                $asaasSubscriptionService,
                $registrarAssinaturaService,
                &$mensagemErroPublica
            ) {
                $telefone = preg_replace('/\D/', '', $request->telefone);
                $cpfCnpj = $request->validated('billing_cpf_cnpj');

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'telefone' => $request->telefone,
                    'billing_cpf_cnpj' => $cpfCnpj,
                    'password' => Hash::make($request->password),
                    'perfil' => 'corretor',

                    'billing_status' => 'trial',
                    'billing_payment_method' => 'CREDIT_CARD',
                    'billing_amount' => 49.90,
                    'trial_ends_at' => now()->addDays(30),
                    'next_billing_at' => now()->addDays(30),
                ]);

                $customerCriadoNaTentativa = false;
                $customerResponse = $asaasCustomerService->findByCpfCnpj($cpfCnpj, $user->email);
                $asaasCustomer = $customerResponse['data'] ?? null;

                if (! ($asaasCustomer['id'] ?? null)) {
                    $customerResponse = $asaasCustomerService->create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'cpfCnpj' => $cpfCnpj,
                        'phone' => $telefone,
                        'mobilePhone' => $telefone,
                    ]);

                    $customerCriadoNaTentativa = (bool) ($customerResponse['success'] ?? false);
                    $asaasCustomer = $customerResponse['data'] ?? null;
                }

                if (!($customerResponse['success'] ?? false) && ! ($asaasCustomer['id'] ?? null)) {
                    $mensagemErroPublica = $this->mensagemErroGateway(
                        $customerResponse,
                        'Não foi possível validar seus dados cadastrais no momento. Revise as informações e tente novamente.'
                    );

                    throw new \RuntimeException('Não foi possível criar o cliente no Asaas.');
                }

                $asaasCustomerId = $asaasCustomer['id'] ?? null;

                if (!$asaasCustomerId) {
                    throw new \RuntimeException('O Asaas não retornou o ID do cliente.');
                }

                $user->update([
                    'asaas_customer_id' => $asaasCustomerId,
                ]);

                $subscriptionResponse = $asaasSubscriptionService->create([
                    'customer' => $asaasCustomerId,
                    'value' => 49.90,
                    'nextDueDate' => Carbon::now()->addDays(30)->format('Y-m-d'),
                    'description' => 'Assinatura Nexo Saúde - Plano Profissional',

                    'card_holder_name' => $request->card_holder_name,
                    'card_number' => $request->card_number,
                    'card_expiry_month' => $request->card_expiry_month,
                    'card_expiry_year' => $request->card_expiry_year,
                    'card_ccv' => $request->card_ccv,

                    'holder_name' => $request->card_holder_name,
                    'holder_email' => $user->email,
                    'holder_cpf_cnpj' => $cpfCnpj,
                    'holder_phone' => $telefone,
                    'holder_postal_code' => $request->holder_postal_code,
                    'holder_address_number' => $request->holder_address_number,
                    'remote_ip' => $request->ip(),
                ]);

                if (!($subscriptionResponse['success'] ?? false)) {
                    if ($customerCriadoNaTentativa && $asaasCustomerId) {
                        $asaasCustomerService->delete($asaasCustomerId);
                    }

                    $mensagemErroPublica = $this->mensagemErroAssinatura($subscriptionResponse);

                    throw new \RuntimeException('Não foi possível criar a assinatura no Asaas.');
                }

                $asaasSubscription = $subscriptionResponse['data'];
                $asaasSubscriptionId = $asaasSubscription['id'] ?? null;

                if (!$asaasSubscriptionId) {
                    throw new \RuntimeException('O Asaas não retornou o ID da assinatura.');
                }

                $user->update([
                    'asaas_subscription_id' => $asaasSubscriptionId,
                    'billing_status' => 'trial',
                    'billing_payment_method' => 'CREDIT_CARD',
                    'billing_amount' => 49.90,
                    'trial_ends_at' => now()->addDays(30),
                    'next_billing_at' => isset($asaasSubscription['nextDueDate'])
                        ? Carbon::parse($asaasSubscription['nextDueDate'])
                        : now()->addDays(30),
                    'subscription_started_at' => now(),
                ]);

                $registrarAssinaturaService->registrar($user, $asaasSubscription);

                $user->corretorPerfil()->create([
                    'slug' => CorretorPerfil::gerarSlugPublico($user->name),
                    'public_hash' => CorretorPerfil::gerarHashPublico(),
                    'nome_publico' => $user->name,
                    'bio' => 'Especialista em planos de saúde.',
                    'especialidades' => ['Planos individuais', 'Planos familiares'],
                    'mensagem_primeiro_contato_whatsapp' => WhatsAppLinkService::DEFAULT_LEAD_TEMPLATE,
                    'cidade_regiao' => 'São Paulo e região',
                ]);

                return $user;
            });

            $user->sendEmailVerificationNotification();

            Auth::login($user);
            session(['email_verification_after' => route('perfil-publico.edit')]);

            return redirect()->route('verification.notice');
        } catch (\Throwable $e) {
            Log::error('Erro ao criar conta com assinatura Asaas', [
                'message' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                    'card_number',
                    'card_ccv',
                ]))
                ->withErrors([
                    'billing' => $mensagemErroPublica,
                ]);
        }
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request, ServicoSessaoUsuario $sessoes)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            $user = $request->user();

            if ($user->blocked_at) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Sua conta esta bloqueada. Entre em contato com o suporte.',
                ]);
            }

            $sessoes->registrarAcesso($user, $request);

            if (! $user->hasVerifiedEmail()) {
                return redirect()->intended(route('verification.notice'));
            }

            if ($this->usuarioAdministrador($user)) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'E-mail ou senha inválidos.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function usuarioAdministrador(?User $user): bool
    {
        return (bool) ($user?->is_admin || $user?->perfil === 'admin');
    }

    private function mensagemErroAssinatura(array $gatewayResponse): string
    {
        $codigo = data_get($gatewayResponse, 'response.errors.0.code');

        if ($codigo === 'invalid_creditCard') {
            return 'O cartão informado foi recusado pela operadora. Verifique número, validade, CVV, nome impresso e se o cartão está habilitado para compras online.';
        }

        return $this->mensagemErroGateway(
            $gatewayResponse,
            'Não foi possível validar o cartão no momento. Revise os dados de pagamento e tente novamente.'
        );
    }

    private function mensagemErroGateway(array $gatewayResponse, string $fallback): string
    {
        $descricao = trim((string) data_get($gatewayResponse, 'response.errors.0.description'));

        if ($descricao !== '') {
            return $descricao;
        }

        return $fallback;
    }
}
