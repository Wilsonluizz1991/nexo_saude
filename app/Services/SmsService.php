<?php

namespace App\Services;

use App\Models\Indicacao;
use App\Models\PreCadastro;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsService
{
    public function enviarPreCadastro(PreCadastro $preCadastro, string $link): SmsMessage
    {
        $indicacao = $preCadastro->indicacao;
        $mensagem = "Ola, {$indicacao?->nome_cliente}! Seu pre-cadastro Nexo Saude esta pronto: {$link} Token de acesso: {$preCadastro->chave_acesso}";

        return $this->enviar(
            telefone: (string) $indicacao?->telefone,
            mensagem: $mensagem,
            indicacao: $indicacao,
            preCadastro: $preCadastro
        );
    }

    public function enviar(string $telefone, string $mensagem, ?Indicacao $indicacao = null, ?PreCadastro $preCadastro = null): SmsMessage
    {
        $sms = SmsMessage::create([
            'user_id' => $indicacao?->user_id,
            'indicacao_id' => $indicacao?->id,
            'pre_cadastro_id' => $preCadastro?->id,
            'to' => $this->normalizarTelefone($telefone),
            'message' => $mensagem,
            'provider' => config('services.sms.provider', 'log'),
            'status' => 'pending',
        ]);

        return $this->tentarEnviar($sms);
    }

    public function tentarEnviar(SmsMessage $sms): SmsMessage
    {
        try {
            $sms->increment('attempts');

            if ($sms->provider === 'http' && config('services.sms.endpoint')) {
                Http::timeout(10)
                    ->retry(2, 250)
                    ->withHeaders(array_filter([
                        'Authorization' => config('services.sms.token') ? 'Bearer '.config('services.sms.token') : null,
                    ]))
                    ->post(config('services.sms.endpoint'), [
                        'to' => $sms->to,
                        'message' => $sms->message,
                    ])
                    ->throw();
            } else {
                Log::info('SMS simulado/logado', [
                    'to' => $sms->to,
                    'message' => $sms->message,
                    'sms_id' => $sms->id,
                ]);
            }

            $sms->update([
                'status' => 'sent',
                'sent_at' => now(),
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $sms->update([
                'status' => 'failed',
                'last_error' => $exception->getMessage(),
            ]);

            Log::warning('Falha no envio de SMS', [
                'sms_id' => $sms->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return $sms->refresh();
    }

    private function normalizarTelefone(string $telefone): string
    {
        return preg_replace('/\D/', '', $telefone) ?: $telefone;
    }
}

