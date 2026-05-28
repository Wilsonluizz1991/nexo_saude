<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasCustomerService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.base_url');
        $this->apiKey = config('services.asaas.api_key');
    }

    /**
     * Cria um cliente no Asaas.
     */
    public function create(array $data): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
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
                'response' => $response->body(),
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
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'access_token' => $this->apiKey,
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
                'response' => $response->body(),
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
}