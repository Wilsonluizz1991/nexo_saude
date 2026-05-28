<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('app:testar-asaas-connection')]
#[Description('Testa conexão com a API do Asaas')]
class TestarAsaasConnection extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = config('services.asaas.base_url');
        $apiKey = config('services.asaas.api_key');

        $this->info('Testando conexão com Asaas...');
        $this->newLine();

        if (!$baseUrl) {
            $this->error('ASAAS_BASE_URL não configurado.');
            return Command::FAILURE;
        }

        if (!$apiKey) {
            $this->error('ASAAS_API_KEY não configurado.');
            return Command::FAILURE;
        }

        try {
            $response = Http::withoutVerifying()
    ->withHeaders([
        'accept' => 'application/json',
        'content-type' => 'application/json',
        'access_token' => $apiKey,
    ])
    ->get($baseUrl . '/customers');

            $this->line('Status HTTP: ' . $response->status());
            $this->newLine();

            if ($response->successful()) {
                $this->info('Conexão com Asaas realizada com sucesso!');
                $this->newLine();

                $this->line('Resposta da API:');
                $this->line(json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                return Command::SUCCESS;
            }

            $this->error('Falha ao conectar com Asaas.');
            $this->newLine();

            $this->line('Resposta da API:');
            $this->line($response->body());

            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Erro ao conectar com Asaas.');
            $this->newLine();

            $this->line($e->getMessage());

            return Command::FAILURE;
        }
    }
}