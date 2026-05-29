<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Assinatura;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AsaasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (! $this->webhookAutenticado($request)) {
            Log::warning('Webhook Asaas rejeitado por token invalido', [
                'ip' => $request->ip(),
                'event' => $request->input('event'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook nao autorizado.',
            ], 401);
        }

        Log::info('Webhook Asaas recebido', [
            'event' => $request->input('event'),
        ]);

        $event = $request->input('event');
        $payment = $request->input('payment', []);
        $subscription = $request->input('subscription', []);
        $payloadSeguro = $this->sanitizarPayload($request->all());

        $asaasSubscriptionId = data_get($subscription, 'id')
            ?? data_get($payment, 'subscription');

        if (! $asaasSubscriptionId) {
            Log::warning('Webhook Asaas sem subscription ID', [
                'event' => $event,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Subscription ID não encontrado.',
            ], 400);
        }

        $assinatura = Assinatura::where('asaas_subscription_id', $asaasSubscriptionId)->first();

        if (! $assinatura) {
            Log::warning('Webhook Asaas para assinatura não encontrada', [
                'event' => $event,
                'asaas_subscription_id' => $asaasSubscriptionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Assinatura não encontrada.',
            ], 404);
        }

        if (! $this->assinaturaConfereComPayload($assinatura, $payment, $subscription)) {
            Log::warning('Webhook Asaas com vinculo financeiro divergente', [
                'event' => $event,
                'assinatura_id' => $assinatura->id,
                'asaas_subscription_id' => $asaasSubscriptionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Vinculo da assinatura nao confere.',
            ], 422);
        }

        match ($event) {
            'PAYMENT_RECEIVED',
            'PAYMENT_CONFIRMED' => $this->marcarComoAtiva($assinatura, $payment, $payloadSeguro),

            'PAYMENT_OVERDUE',
            'PAYMENT_DUNNING_RECEIVED',
            'PAYMENT_CREDIT_CARD_CAPTURE_REFUSED',
            'PAYMENT_REFUNDED',
            'PAYMENT_REFUND_IN_PROGRESS',
            'PAYMENT_CHARGEBACK_REQUESTED',
            'PAYMENT_CHARGEBACK_DISPUTE',
            'PAYMENT_AWAITING_CHARGEBACK_REVERSAL',
            'PAYMENT_DUNNING_REQUESTED' => $this->marcarComoVencida($assinatura, $payment, $payloadSeguro),

            'PAYMENT_DELETED' => $this->marcarComoBloqueada($assinatura, $payment, $payloadSeguro),

            'SUBSCRIPTION_DELETED' => $this->marcarComoCancelada($assinatura, $payloadSeguro),

            default => $this->registrarEventoNaoMapeado($assinatura, $event, $payloadSeguro),
        };

        return response()->json([
            'success' => true,
        ]);
    }

    private function marcarComoAtiva(Assinatura $assinatura, array $payment, array $payload): void
    {
        $dueDate = data_get($payment, 'dueDate');
        $paymentDate = data_get($payment, 'paymentDate') ?: data_get($payment, 'clientPaymentDate');

        $nextPaymentAt = $dueDate
            ? Carbon::parse($dueDate)->addMonth()
            : now()->addMonth();

        $lastPaymentAt = $paymentDate
            ? Carbon::parse($paymentDate)
            : now();

        $assinatura->update([
            'status' => 'active',
            'status_assinatura' => 'ativa',
            'last_payment_at' => $lastPaymentAt,
            'next_payment_at' => $nextPaymentAt,
            'vencimento_assinatura' => $nextPaymentAt->toDateString(),
            'canceled_at' => null,
            'expired_at' => null,
            'gateway_payload' => $payload,
        ]);

        if ($assinatura->user) {
            $assinatura->user->update([
                'billing_status' => 'active',
                'billing_amount' => $assinatura->valor ?: $assinatura->valor_assinatura ?: 49.90,
                'next_billing_at' => $nextPaymentAt,
                'billing_suspended_at' => null,
                'subscription_canceled_at' => null,
            ]);
        }

        Log::info('Assinatura marcada como ativa via webhook Asaas', [
            'assinatura_id' => $assinatura->id,
            'asaas_subscription_id' => $assinatura->asaas_subscription_id,
        ]);
    }

    private function marcarComoVencida(Assinatura $assinatura, array $payment, array $payload): void
    {
        $dueDate = data_get($payment, 'dueDate');

        $assinatura->update([
            'status' => 'overdue',
            'status_assinatura' => 'vencida',
            'next_payment_at' => $dueDate ? Carbon::parse($dueDate) : $assinatura->next_payment_at,
            'vencimento_assinatura' => $dueDate ? Carbon::parse($dueDate)->toDateString() : $assinatura->vencimento_assinatura,
            'gateway_payload' => $payload,
        ]);

        if ($assinatura->user) {
            $assinatura->user->update([
                'billing_status' => 'overdue',
                'billing_suspended_at' => now(),
            ]);
        }

        Log::warning('Assinatura marcada como vencida via webhook Asaas', [
            'assinatura_id' => $assinatura->id,
            'asaas_subscription_id' => $assinatura->asaas_subscription_id,
            'event' => data_get($payload, 'event'),
        ]);
    }

    private function marcarComoBloqueada(Assinatura $assinatura, array $payment, array $payload): void
    {
        $assinatura->update([
            'status' => 'pending',
            'status_assinatura' => 'bloqueada',
            'gateway_payload' => $payload,
        ]);

        if ($assinatura->user) {
            $assinatura->user->update([
                'billing_status' => 'blocked',
                'billing_suspended_at' => now(),
            ]);
        }

        Log::warning('Assinatura marcada como bloqueada via webhook Asaas', [
            'assinatura_id' => $assinatura->id,
            'asaas_subscription_id' => $assinatura->asaas_subscription_id,
        ]);
    }

    private function marcarComoCancelada(Assinatura $assinatura, array $payload): void
    {
        $assinatura->update([
            'status' => 'canceled',
            'status_assinatura' => 'cancelada',
            'canceled_at' => now(),
            'gateway_payload' => $payload,
        ]);

        if ($assinatura->user) {
            $assinatura->user->update([
                'billing_status' => 'canceled',
                'subscription_canceled_at' => now(),
                'billing_suspended_at' => now(),
            ]);
        }

        Log::warning('Assinatura cancelada via webhook Asaas', [
            'assinatura_id' => $assinatura->id,
            'asaas_subscription_id' => $assinatura->asaas_subscription_id,
        ]);
    }

    private function registrarEventoNaoMapeado(Assinatura $assinatura, ?string $event, array $payload): void
    {
        Log::info('Webhook Asaas ignorado sem ação local', [
            'event' => $event,
            'assinatura_id' => $assinatura->id,
            'asaas_subscription_id' => $assinatura->asaas_subscription_id,
        ]);

        $assinatura->update([
            'gateway_payload' => $payload,
        ]);
    }

    private function webhookAutenticado(Request $request): bool
    {
        $tokenEsperado = config('services.asaas.webhook_token');

        if (! $tokenEsperado) {
            return ! app()->environment('production');
        }

        $tokenRecebido = $request->header('asaas-access-token')
            ?: $request->header('access_token')
            ?: $request->header('x-asaas-webhook-token');

        return is_string($tokenRecebido) && hash_equals($tokenEsperado, $tokenRecebido);
    }

    private function assinaturaConfereComPayload(Assinatura $assinatura, array $payment, array $subscription): bool
    {
        $customerPayload = data_get($subscription, 'customer') ?: data_get($payment, 'customer');
        $customerLocal = $assinatura->asaas_customer_id ?: $assinatura->user?->asaas_customer_id;

        if ($customerPayload && $customerLocal && $customerPayload !== $customerLocal) {
            return false;
        }

        $subscriptionPayload = data_get($subscription, 'id') ?: data_get($payment, 'subscription');

        return ! $subscriptionPayload || $subscriptionPayload === $assinatura->asaas_subscription_id;
    }

    private function sanitizarPayload(array $payload): array
    {
        $sensitivos = [
            'creditcard',
            'creditcardholderinfo',
            'creditcardnumber',
            'creditcardtoken',
            'card_number',
            'card_ccv',
            'ccv',
            'cvv',
            'token',
        ];

        foreach ($payload as $chave => $valor) {
            $normalizada = strtolower((string) $chave);

            if (in_array($normalizada, $sensitivos, true)) {
                $payload[$chave] = '[redacted]';
                continue;
            }

            if (is_array($valor)) {
                $payload[$chave] = $this->sanitizarPayload($valor);
            }
        }

        return $payload;
    }
}
