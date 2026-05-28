<?php

namespace App\Console\Commands;

use App\Services\Asaas\AsaasCustomerService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:criar-cliente-asaas-teste')]
#[Description('Cria um cliente de teste no Asaas')]
class CriarClienteAsaasTeste extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AsaasCustomerService $asaasCustomerService)
    {
        $this->info('Criando cliente de teste no Asaas...');
        $this->newLine();

        $response = $asaasCustomerService->create([
            'name' => 'Nexo Saúde Cliente Teste',
            'email' => 'teste@nexosaude.com.br',
            'cpfCnpj' => '12345678909',
            'phone' => '1133334444',
            'mobilePhone' => '11988887777',
        ]);

        if ($response['success']) {
            $this->info('Cliente criado com sucesso!');
            $this->newLine();

            $this->line(json_encode(
                $response['data'],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ));

            return Command::SUCCESS;
        }

        $this->error('Erro ao criar cliente no Asaas.');
        $this->newLine();

        $this->line(json_encode(
            $response,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ));

        return Command::FAILURE;
    }
}