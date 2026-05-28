<?php

namespace App\Services\Asaas;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasSubscriptionService
{
   :contentReference[oaicite:0]{index=0}string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.base_url');
        $this->apiKey = config('services.asaas.api_key');
    }

    public function create(array $data): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
                ])
                ->post($this->baseUrl . '/subscriptions', [
                    'customer' => $data['customer'],
                    'billingType' => 'CREDIT_CARD',
                    'value' => $data['value'] ?? 49.90,
                    'nextDueDate' => $data['nextDueDate'] ?? Carbon::now()->addDays(30)->format('Y-m-d'),
                    'cycle' => 'MONTHLY',
                    'description' => $data['description'] ?? 'Assinatura Nexo Saúde - Plano Profissional',
                    'creditCard' => [
                        'holderName' => $data['card_holder_name'],
                        'number' => preg_replace('/\D/', '', $data['card_number']),
                        'expiryMonth' => $data['card_expiry_month'],
                        'expiryYear' => $data['card_expiry_year'],
                        'ccv' => $data['card_ccv'],
                    ],
                    'creditCardHolderInfo' => [
                        'name' => $data['holder_name'],
                        'email' => $data['holder_email'],
                        'cpfCnpj' => preg_replace('/\D/', '', $data['holder_cpf_cnpj']),
                        'postalCode' => $data['holder_postal_code'] ?? '01001000',
                        'addressNumber' => $data['holder_address_number'] ?? '100',
                        'phone' => preg_replace('/\D/', '', $data['holder_phone']),
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Erro ao criar assinatura no Asaas', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Falha ao criar assinatura no Asaas.',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Exceção ao criar assinatura no Asaas', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function updateCreditCard(string $subscriptionId, array $data): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
                ])
                ->put($this->baseUrl . '/subscriptions/' . $subscriptionId . '/creditCard', [
                    'creditCard' => [
                        'holderName' => $data['card_holder_name'],
                        'number' => preg_replace('/\D/', '', $data['card_number']),
                        'expiryMonth' => $data['card_expiry_month'],
                        'expiryYear' => $data['card_expiry_year'],
                        'ccv' => $data['card_ccv'],
                    ],
                    'creditCardHolderInfo' => [
                        'name' => $data['holder_name'],
                        'email' => $data['holder_email'],
                        'cpfCnpj' => preg_replace('/\D/', '', $data['holder_cpf_cnpj']),
                        'postalCode' => preg_replace('/\D/', '', $data['holder_postal_code'] ?? '01001000'),
                        'addressNumber' => $data['holder_address_number'] ?? '100',
                        'phone' => preg_replace('/\D/', '', $data['holder_phone']),
                    ],
                    'remoteIp' => $data['remote_ip'] ?? request()->ip(),
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Erro ao atualizar cartão da assinatura no Asaas', [
                'subscription_id' => $subscriptionId,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Falha ao atualizar cartão no Asaas.',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Exceção ao atualizar cartão da assinatura no Asaas', [
                'subscription_id' => $subscriptionId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function find(string $subscriptionId): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
                ])
                ->get($this->baseUrl . '/subscriptions/' . $subscriptionId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Assinatura não encontrada no Asaas.',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Exceção ao buscar assinatura no Asaas', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function cancel(string $subscriptionId): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
                ])
                ->delete($this->baseUrl . '/subscriptions/' . $subscriptionId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao cancelar assinatura no Asaas.',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Exceção ao cancelar assinatura no Asaas', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}