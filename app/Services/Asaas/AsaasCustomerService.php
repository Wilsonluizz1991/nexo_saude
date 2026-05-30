<?php

namespace App\Services\Asaas;

use App\Models\Assinatura;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasCustomerService
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

    /**
     * Cria um cliente no Asaas.
     */
    public function create(array $data): array
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
                ->post($this->baseUrl . '/customers', [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'cpfCnpj' => $data['cpfCnpj'],
                    'phone' => $data['phone'] ?? null,
                    'mobilePhone' => $data['mobilePhone'] ?? null,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Erro ao criar cliente no Asaas', [
                'status' => $response->status(),
                'response' => Assinatura::sanitizarGatewayPayload($response->json() ?? []),
            ]);

            return [
                'success' => false,
                'message' => 'Falha ao criar cliente no Asaas.',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Exceção ao criar cliente no Asaas', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Busca um cliente pelo ID.
     */
    public function find(string $customerId): array
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
                ->get($this->baseUrl . '/customers/' . $customerId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Erro ao buscar cliente no Asaas', [
                'status' => $response->status(),
                'response' => Assinatura::sanitizarGatewayPayload($response->json() ?? []),
            ]);

            return [
                'success' => false,
                'message' => 'Cliente não encontrado no Asaas.',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Exceção ao buscar cliente no Asaas', [
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
