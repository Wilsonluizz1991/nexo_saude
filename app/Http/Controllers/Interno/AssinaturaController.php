<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Services\Asaas\AsaasSubscriptionService;
use App\Services\AssinaturaService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssinaturaController extends Controller
{
    public function bloqueada(AssinaturaService $assinaturaService)
    {
        if (auth()->user()?->is_admin || auth()->user()?->perfil === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $assinatura = auth()->user()->assinatura;

        return view('interno.assinatura.bloqueada', [
            'assinatura' => $assinatura,
            'statusComercial' => $assinaturaService->statusComercial($assinatura),
            'diasRestantesTeste' => $assinaturaService->diasRestantesTeste($assinatura),
        ]);
    }

    public function assinar(AssinaturaService $service)
    {
        if (auth()->user()?->is_admin || auth()->user()?->perfil === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $service->ativar(auth()->user()->assinatura);

        return redirect()->route('dashboard')->with('status', 'Assinatura ativada por R$ 49,90/mês.');
    }

    public function regularizar(Request $request, AsaasSubscriptionService $asaasSubscriptionService)
    {
        if (auth()->user()?->is_admin || auth()->user()?->perfil === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $dados = $request->validate([
            'billing_cpf_cnpj' => ['required', 'string', 'max:20'],
            'holder_phone' => ['required', 'string', 'max:30'],
            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'max:30'],
            'card_expiry_month' => ['required', 'string', 'size:2'],
            'card_expiry_year' => ['required', 'string', 'size:4'],
            'card_ccv' => ['required', 'string', 'min:3', 'max:4'],
            'holder_postal_code' => ['nullable', 'string', 'max:20'],
            'holder_address_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user = auth()->user();
        $assinatura = $user->assinatura;

        if (! $assinatura || ! $assinatura->asaas_subscription_id) {
            return back()->withErrors([
                'assinatura' => 'Não encontramos uma assinatura válida para regularizar.',
            ]);
        }

        $response = $asaasSubscriptionService->updateCreditCard($assinatura->asaas_subscription_id, [
            'card_holder_name' => $dados['card_holder_name'],
            'card_number' => $dados['card_number'],
            'card_expiry_month' => $dados['card_expiry_month'],
            'card_expiry_year' => $dados['card_expiry_year'],
            'card_ccv' => $dados['card_ccv'],

            'holder_name' => $user->name,
            'holder_email' => $user->email,
            'holder_cpf_cnpj' => $dados['billing_cpf_cnpj'],
            'holder_phone' => $dados['holder_phone'],
            'holder_postal_code' => $dados['holder_postal_code'] ?? '01001000',
            'holder_address_number' => $dados['holder_address_number'] ?? '100',
            'remote_ip' => $request->ip(),
        ]);

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput($request->except(['card_number', 'card_ccv']))
                ->withErrors([
                    'assinatura' => 'Não foi possível atualizar o cartão. Verifique os dados informados e tente novamente.',
                ]);
        }

        $asaasData = $response['data'] ?? [];
        $creditCard = $asaasData['creditCard'] ?? [];

        $assinatura->update([
            'card_brand' => $creditCard['creditCardBrand'] ?? $assinatura->card_brand,
            'card_last_four' => $creditCard['creditCardNumber'] ?? substr(preg_replace('/\D/', '', $dados['card_number']), -4),
            'card_token' => $creditCard['creditCardToken'] ?? $assinatura->card_token,
            'gateway_payload' => \App\Models\Assinatura::sanitizarGatewayPayload($asaasData),
        ]);

        return back()->with('status', 'Cartão atualizado com sucesso. Aguarde a confirmação da cobrança para liberar o acesso.');
    }

    public function reativar(Request $request, AsaasSubscriptionService $asaasSubscriptionService)
    {
        if (auth()->user()?->is_admin || auth()->user()?->perfil === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $dados = $request->validate([
            'billing_cpf_cnpj' => ['required', 'string', 'max:20'],
            'holder_phone' => ['required', 'string', 'max:30'],
            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'max:30'],
            'card_expiry_month' => ['required', 'string', 'size:2'],
            'card_expiry_year' => ['required', 'string', 'size:4'],
            'card_ccv' => ['required', 'string', 'min:3', 'max:4'],
            'holder_postal_code' => ['nullable', 'string', 'max:20'],
            'holder_address_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user = auth()->user();
        $assinatura = $user->assinatura;

        if (! $assinatura) {
            return back()->withErrors([
                'assinatura' => 'Não encontramos uma assinatura vinculada à sua conta.',
            ]);
        }

        $asaasCustomerId = $assinatura->asaas_customer_id ?: $user->asaas_customer_id;

        if (! $asaasCustomerId) {
            return back()->withErrors([
                'assinatura' => 'Não encontramos o cadastro financeiro da sua conta. Entre em contato com o suporte.',
            ]);
        }

        $response = $asaasSubscriptionService->create([
            'customer' => $asaasCustomerId,
            'value' => 49.90,
            'nextDueDate' => Carbon::now()->format('Y-m-d'),
            'description' => 'Reativação da Assinatura Nexo Saúde - Plano Profissional',

            'card_holder_name' => $dados['card_holder_name'],
            'card_number' => $dados['card_number'],
            'card_expiry_month' => $dados['card_expiry_month'],
            'card_expiry_year' => $dados['card_expiry_year'],
            'card_ccv' => $dados['card_ccv'],

            'holder_name' => $user->name,
            'holder_email' => $user->email,
            'holder_cpf_cnpj' => $dados['billing_cpf_cnpj'],
            'holder_phone' => $dados['holder_phone'],
            'holder_postal_code' => $dados['holder_postal_code'] ?? '01001000',
            'holder_address_number' => $dados['holder_address_number'] ?? '100',
            'remote_ip' => $request->ip(),
        ]);

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput($request->except(['card_number', 'card_ccv']))
                ->withErrors([
                    'assinatura' => 'Não foi possível reativar sua assinatura. Verifique os dados do cartão e tente novamente.',
                ]);
        }

        $asaasSubscription = $response['data'] ?? [];
        $creditCard = $asaasSubscription['creditCard'] ?? [];
        $asaasStatus = strtolower($asaasSubscription['status'] ?? 'active');
        $nextDueDate = isset($asaasSubscription['nextDueDate'])
            ? Carbon::parse($asaasSubscription['nextDueDate'])
            : now();

        $assinatura->update([
            'gateway' => 'asaas',
            'asaas_customer_id' => $asaasCustomerId,
            'asaas_subscription_id' => $asaasSubscription['id'] ?? null,
            'valor' => 49.90,
            'valor_assinatura' => 49.90,
            'status' => $asaasStatus === 'active' ? 'active' : 'pending',
            'status_assinatura' => $asaasStatus === 'active' ? 'ativa' : 'bloqueada',
            'next_payment_at' => $nextDueDate->copy()->addMonth(),
            'vencimento_assinatura' => $nextDueDate->copy()->addMonth()->toDateString(),
            'last_payment_at' => now(),
            'canceled_at' => null,
            'expired_at' => null,
            'card_brand' => $creditCard['creditCardBrand'] ?? $assinatura->card_brand,
            'card_last_four' => $creditCard['creditCardNumber'] ?? substr(preg_replace('/\D/', '', $dados['card_number']), -4),
            'card_token' => $creditCard['creditCardToken'] ?? $assinatura->card_token,
            'gateway_payload' => \App\Models\Assinatura::sanitizarGatewayPayload($asaasSubscription),
        ]);

        $user->update([
            'billing_status' => $asaasStatus === 'active' ? 'active' : 'blocked',
            'billing_payment_method' => 'CREDIT_CARD',
            'billing_amount' => 49.90,
            'asaas_customer_id' => $asaasCustomerId,
            'asaas_subscription_id' => $asaasSubscription['id'] ?? null,
            'next_billing_at' => $nextDueDate->copy()->addMonth(),
            'subscription_started_at' => now(),
            'subscription_canceled_at' => null,
            'billing_suspended_at' => null,
        ]);

        if ($asaasStatus === 'active') {
            return redirect()->route('dashboard')->with('status', 'Assinatura reativada com sucesso.');
        }

        return redirect()->route('assinatura.bloqueada')->with('status', 'Assinatura enviada para reativação. Aguarde a confirmação do pagamento.');
    }
}
