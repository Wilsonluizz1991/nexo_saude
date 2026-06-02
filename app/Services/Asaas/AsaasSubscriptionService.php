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
                ->post($this->baseUrl . '/subscriptions', $this->subscriptionPayload($data));

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Erro ao criar assinatura no Asaas', [
                'status' => $response->status(),
                'response' => Assinatura::sanitizarGatewayPayload($response->json() ?? []),
                'diagnostico' => $this->safeDiagnostics($data),
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
                ->put($this->baseUrl . '/subscriptions/' . $subscriptionId . '/creditCard', $this->cardPayload($data));

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
                'diagnostico' => $this->safeDiagnostics($data),
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

    private function subscriptionPayload(array $data): array
    {
        return array_merge([
            'customer' => $data['customer'],
            'billingType' => 'CREDIT_CARD',
            'value' => $data['value'] ?? 49.90,
            'nextDueDate' => $data['nextDueDate'] ?? Carbon::now()->addDays(30)->format('Y-m-d'),
            'cycle' => 'MONTHLY',
            'description' => $data['description'] ?? 'Assinatura Nexo Saúde - Plano Profissional',
        ], $this->cardPayload($data));
    }

    private function cardPayload(array $data): array
    {
        $telefone = $this->digits($data['holder_phone'] ?? '');
        $expiryMonth = str_pad($this->digits($data['card_expiry_month'] ?? ''), 2, '0', STR_PAD_LEFT);
        $expiryYear = $this->normalizeExpiryYear($data['card_expiry_year'] ?? '');

        Log::info('Payload cartão Asaas', [
    'holder_name' => trim((string) ($data['card_holder_name'] ?? '')),
    'card_digits' => strlen($this->digits($data['card_number'] ?? '')),
    'expiry_month' => $expiryMonth,
    'expiry_year' => $expiryYear,
    'ccv_digits' => strlen($this->digits($data['card_ccv'] ?? '')),
    'cpf_digits' => strlen($this->digits($data['holder_cpf_cnpj'] ?? '')),
]);

        return [
            'creditCard' => [
                'holderName' => trim((string) ($data['card_holder_name'] ?? '')),
                'number' => $this->digits($data['card_number'] ?? ''),
                'expiryMonth' => $expiryMonth,
                'expiryYear' => $expiryYear,
                'ccv' => $this->digits($data['card_ccv'] ?? ''),
            ],
            'creditCardHolderInfo' => [
                'name' => trim((string) ($data['holder_name'] ?? '')),
                'email' => trim((string) ($data['holder_email'] ?? '')),
                'cpfCnpj' => $this->digits($data['holder_cpf_cnpj'] ?? ''),
                'postalCode' => $this->digits($data['holder_postal_code'] ?? '01001000'),
                'addressNumber' => trim((string) ($data['holder_address_number'] ?? '100')),
                'phone' => $telefone,
                'mobilePhone' => $telefone,
            ],
            'remoteIp' => $data['remote_ip'] ?? request()->ip(),
        ];
    }

    private function safeDiagnostics(array $data): array
    {
        return [
            'asaas_env' => config('services.asaas.env'),
            'remote_ip' => $data['remote_ip'] ?? request()->ip(),
            'card_number_digits' => strlen($this->digits($data['card_number'] ?? '')),
            'card_expiry_month' => str_pad($this->digits($data['card_expiry_month'] ?? ''), 2, '0', STR_PAD_LEFT),
            'card_expiry_year_digits' => strlen($this->digits($data['card_expiry_year'] ?? '')),
            'card_ccv_digits' => strlen($this->digits($data['card_ccv'] ?? '')),
            'holder_document_digits' => strlen($this->digits($data['holder_cpf_cnpj'] ?? '')),
            'holder_phone_digits' => strlen($this->digits($data['holder_phone'] ?? '')),
            'holder_postal_code_digits' => strlen($this->digits($data['holder_postal_code'] ?? '')),
            'holder_address_number_present' => trim((string) ($data['holder_address_number'] ?? '')) !== '',
        ];
    }

    private function normalizeExpiryYear(?string $value): string
    {
        $year = $this->digits($value);

        if (strlen($year) === 2) {
            return '20' . $year;
        }

        return $year;
    }

    private function sslVerification(): bool|string
    {
        $caBundle = config('services.asaas.ca_bundle');

        if (is_string($caBundle) && $caBundle !== '') {
            return $caBundle;
        }

        return filter_var(config('services.asaas.verify_ssl', true), FILTER_VALIDATE_BOOLEAN);
    }

    private function digits(?string $value): string
    {
        return preg_replace('/\D/', '', (string) $value);
    }
}