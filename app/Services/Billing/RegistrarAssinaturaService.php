<?php

namespace App\Services\Billing;

use App\Models\Assinatura;
use App\Models\User;
use Carbon\Carbon;

class RegistrarAssinaturaService
{
    public function registrar(User $user, array $asaasSubscription): Assinatura
    {
        $creditCard = $asaasSubscription['creditCard'] ?? [];

        return Assinatura::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'gateway' => 'asaas',
                'asaas_customer_id' => $asaasSubscription['customer'] ?? $user->asaas_customer_id,
                'asaas_subscription_id' => $asaasSubscription['id'] ?? null,

                'valor' => $asaasSubscription['value'] ?? 49.90,
                'status' => 'trialing',

                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays(30),
                'next_payment_at' => isset($asaasSubscription['nextDueDate'])
                    ? Carbon::parse($asaasSubscription['nextDueDate'])
                    : now()->addDays(30),

                'card_brand' => $creditCard['creditCardBrand'] ?? null,
                'card_last_four' => $creditCard['creditCardNumber'] ?? null,
                'card_token' => $creditCard['creditCardToken'] ?? null,

                'gateway_payload' => Assinatura::sanitizarGatewayPayload($asaasSubscription),

                // Compatibilidade com os campos antigos
                'data_inicio_teste_gratis' => now()->toDateString(),
                'data_fim_teste_gratis' => now()->addDays(30)->toDateString(),
                'status_assinatura' => 'teste_gratis',
                'valor_assinatura' => $asaasSubscription['value'] ?? 49.90,
                'vencimento_assinatura' => isset($asaasSubscription['nextDueDate'])
                    ? Carbon::parse($asaasSubscription['nextDueDate'])->toDateString()
                    : now()->addDays(30)->toDateString(),
            ]
        );
    }
}
