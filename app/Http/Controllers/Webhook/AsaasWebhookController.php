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
        Log::info('Webhook Asaas recebido', [
            'payload' => $request->all(),
        ]);

        $event = $request->input('event');
        $payment = $request->input('payment', []);
        $subscription = $request->input('subscription', []);

        $asaasSubscriptionId =
            data_get($subscription, 'id')
            ?? data_get($payment, 'subscription')
            ?? null;

        if (! $asaasSubscriptionId) {
            Log::warning('Webhook Asaas sem subscription ID', [
                'event' => $event,
                'payload' => $request->all(),
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

        match ($event) {
            'PAYMENT_RECEIVED',
            'PAYMENT_CONFIRMED' => $this->marcarComoAtiva($assinatura, $payment, $request->all()),

            'PAYMENT_DUNNING_RECEIVED',
            'PAYMENT_OVERDUE',
            'PAYMENT_CREDIT_CARD_CAPTURE_REFUSED' => $this->marcarComoInadimplente($assinatura, $request->all()),

            'PAYMENT_DELETED',
            'SUBSCRIPTION_DELETED' => $this->marcarComoCancelada($assinatura, $request->all()),

            default => $this->registrarEventoNaoMapeado($assinatura, $event, $request->all()),
        };

        return response()->json([
            'success' => true,
        ]);
    }

    private function marcarComoAtiva(Assinatura $assinatura, array $payment, array $payload): void
    {
        $nextPaymentAt = data_get($payment, 'dueDate')
            ? Carbon::parse(data_get($payment, 'dueDate'))->addMonth()
            : now()->addMonth();

        $assinatura->update([
            'status' => 'active',
            'status_assinatura' => 'ativa',
            'last_payment_at' => now(),
            'next_payment_at' => $nextPaymentAt,
            'vencimento_assinatura' => $nextPaymentAt->toDateString(),
            'gateway_payload' => $payload,
        ]);

        if ($assinatura->user) {
            $assinatura->user->update([
                'billing_status' => 'active',
                'billing_amount' => $assinatura->valor ?: 49.90,
                'next_billing_at' => $nextPaymentAt,
            ]);
        }
    }

    private function marcarComoInadimplente(Assinatura $assinatura, array $payload): void
    {
        $assinatura->update([
            'status' => 'overdue',
            'status_assinatura' => 'inadimplente',
            'gateway_payload' => $payload,
        ]);

        if ($assinatura->user) {
            $assinatura->user->update([
                'billing_status' => 'overdue',
                'billing_suspended_at' => now(),
            ]);
        }
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
            ]);
        }
    }

    private function registrarEventoNaoMapeado(Assinatura $assinatura, ?string $event, array $payload): void
    {
        Log::info('Webhook Asaas ignorado sem ação local', [
            'event' => $event,
            'assinatura_id' => $assinatura->id,
        ]);

        $assinatura->update([
            'gateway_payload' => $payload,
        ]);
    }
}