<?php

namespace App\Console\Commands;

use App\Services\Asaas\AsaasSubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:criar-assinatura-asaas-teste')]
#[Description('Cria uma assinatura de teste no Asaas')]
class CriarAssinaturaAsaasTeste extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AsaasSubscriptionService $asaasSubscriptionService)
    {
        $this->info('Criando assinatura de teste no Asaas...');
        $this->newLine();

        $response = $asaasSubscriptionService->create([
            'customer' => 'cus_000008035687',
            'value' => 49.90,
            'nextDueDate' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'description' => 'Assinatura Nexo Saúde - Plano Profissional',

            'card_holder_name' => 'Nexo Saude Teste',
            'card_number' => '4444444444444448',
            'card_expiry_month' => '12',
            'card_expiry_year' => '2030',
            'card_ccv' => '123',

            'holder_name' => 'Nexo Saude Teste',
            'holder_email' => 'teste@nexosaude.com.br',
            'holder_cpf_cnpj' => '12345678909',
            'holder_phone' => '11988887777',
            'holder_postal_code' => '01001000',
            'holder_address_number' => '100',
        ]);

        if ($response['success']) {
            $this->info('Assinatura criada com sucesso!');
            $this->newLine();

            $this->line(json_encode(
                $response['data'],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ));

            return Command::SUCCESS;
        }

        $this->error('Erro ao criar assinatura no Asaas.');
        $this->newLine();

        $this->line(json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ));

        return Command::FAILURE;
    }
}