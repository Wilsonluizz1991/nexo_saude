<?php

namespace App\Services;

use App\Models\Assinatura;
use App\Models\User;
use Carbon\CarbonImmutable;

class AssinaturaService
{
    public const VALOR_MENSAL = 49.90;

    public function iniciarTesteGratis(User $user): Assinatura
    {
        $inicio = CarbonImmutable::today();

        return Assinatura::create([
            'user_id' => $user->id,
            'data_inicio_teste_gratis' => $inicio,
            'data_fim_teste_gratis' => $inicio->addDays(30),
            'status_assinatura' => 'teste_gratis',
            'valor_assinatura' => self::VALOR_MENSAL,
            'vencimento_assinatura' => $inicio->addDays(30),
        ]);
    }

    public function estaAtiva(?Assinatura $assinatura): bool
    {
        if (! $assinatura) {
            return false;
        }

        if ($assinatura->status_assinatura === 'ativa') {
            return true;
        }

        return $assinatura->status_assinatura === 'teste_gratis'
            && $assinatura->data_fim_teste_gratis->endOfDay()->isFuture();
    }

    public function ativar(Assinatura $assinatura): Assinatura
    {
        $assinatura->update([
            'status_assinatura' => 'ativa',
            'valor_assinatura' => self::VALOR_MENSAL,
            'vencimento_assinatura' => now()->addMonth()->toDateString(),
        ]);

        return $assinatura;
    }
}
