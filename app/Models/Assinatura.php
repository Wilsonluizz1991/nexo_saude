<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assinatura extends Model
{
    protected $table = 'assinaturas';

    protected $hidden = [
        'card_token',
        'gateway_payload',
    ];

    protected $fillable = [
        'user_id',

        // Campos antigos
        'data_inicio_teste_gratis',
        'data_fim_teste_gratis',
        'status_assinatura',
        'valor_assinatura',
        'vencimento_assinatura',

        // Gateway / Asaas
        'gateway',
        'asaas_customer_id',
        'asaas_subscription_id',

        // Billing
        'valor',
        'status',

        // Trial / cobrança
        'trial_started_at',
        'trial_ends_at',
        'next_payment_at',
        'last_payment_at',

        // Cartão
        'card_brand',
        'card_last_four',
        'card_token',

        // Controle
        'canceled_at',
        'expired_at',

        // Auditoria
        'gateway_payload',
    ];

    protected function casts(): array
    {
        return [
            // Campos antigos
            'data_inicio_teste_gratis' => 'date',
            'data_fim_teste_gratis' => 'date',
            'vencimento_assinatura' => 'date',
            'valor_assinatura' => 'decimal:2',

            // Campos novos
            'valor' => 'decimal:2',
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'next_payment_at' => 'datetime',
            'last_payment_at' => 'datetime',
            'canceled_at' => 'datetime',
            'expired_at' => 'datetime',
            'gateway_payload' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function sanitizarGatewayPayload(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        $sensitivos = [
            'creditcard',
            'creditcardholderinfo',
            'creditcardnumber',
            'creditcardtoken',
            'card_number',
            'card_ccv',
            'ccv',
            'cvv',
            'token',
        ];

        foreach ($payload as $chave => $valor) {
            $normalizada = strtolower((string) $chave);

            if (in_array($normalizada, $sensitivos, true)) {
                $payload[$chave] = '[redacted]';
                continue;
            }

            if (is_array($valor)) {
                $payload[$chave] = self::sanitizarGatewayPayload($valor);
            }
        }

        return $payload;
    }
}
