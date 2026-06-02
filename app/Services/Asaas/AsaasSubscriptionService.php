<?php

namespace App\Services\Asaas;

use App\Models\Assinatura;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasSubscriptionService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected bool|string $sslVerification;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.base_url');
        $this->apiKey = config('services.asaas.api_key');
        $this->sslVerification = $this->sslVerification();
    }

    public function create(array $data): array
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
                ])
                ->withOptions([
                    'verify' => $this->sslVerification,
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

            Log::error('Erro ao criar assinatura no Asaas', [
                'status' => $response->status(),
                'response' => Assinatura::sanitizarGatewayPayload($response->json() ?? []),
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
            $response = Http::timeout(60)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
                ])
                ->withOptions([
                    'verify' => $this->sslVerification,
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
                'response' => Assinatura::sanitizarGatewayPayload($response->json() ?? []),
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
            $response = Http::withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
                ])
                ->withOptions([
                    'verify' => $this->sslVerification,
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
            $response = Http::withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
                ])
                ->withOptions([
                    'verify' => $this->sslVerification,
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

    private function sslVerification(): bool|string
    {
        $caBundle = config('services.asaas.ca_bundle');

        if (is_string($caBundle) && $caBundle !== '') {
            return $caBundle;
        }

        return filter_var(config('services.asaas.verify_ssl', true), FILTER_VALIDATE_BOOLEAN);
    }
}
