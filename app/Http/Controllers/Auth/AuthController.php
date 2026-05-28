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
        try {
            $user = DB::transaction(function () use (
                $request,
                $asaasCustomerService,
                $asaasSubscriptionService,
                $registrarAssinaturaService
            ) {
                $telefone = preg_replace('/\D/', '', $request->telefone);
                $cpfCnpj = preg_replace('/\D/', '', $request->billing_cpf_cnpj);

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'telefone' => $request->telefone,
                    'password' => Hash::make($request->password),

                    'billing_status' => 'trial',
                    'billing_payment_method' => 'CREDIT_CARD',
                    'billing_amount' => 49.90,
                    'trial_ends_at' => now()->addDays(30),
                    'next_billing_at' => now()->addDays(30),
                ]);

                $customerResponse = $asaasCustomerService->create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'cpfCnpj' => $cpfCnpj,
                    'mobilePhone' => $telefone,
                ]);

                if (!($customerResponse['success'] ?? false)) {
                    throw new \RuntimeException('Não foi possível criar o cliente no Asaas.');
                }

                $asaasCustomer = $customerResponse['data'];
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

                    'holder_name' => $user->name,
                    'holder_email' => $user->email,
                    'holder_cpf_cnpj' => $cpfCnpj,
                    'holder_phone' => $telefone,
                    'holder_postal_code' => '01001000',
                    'holder_address_number' => '100',
                ]);

                if (!($subscriptionResponse['success'] ?? false)) {
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

            Auth::login($user);

            return redirect()->route('perfil-publico.edit');
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
                    'billing' => 'Não foi possível criar sua conta e ativar o período gratuito. Verifique os dados informados e tente novamente.',
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
            $sessoes->registrarAcesso($request->user(), $request);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'E-mail ou senha inválidos.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}