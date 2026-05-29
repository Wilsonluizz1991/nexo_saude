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

            'gateway' => null,
            'valor' => self::VALOR_MENSAL,
            'status' => 'trialing',
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(30),
            'next_payment_at' => now()->addDays(30),
        ]);
    }

    public function estaAtiva(?Assinatura $assinatura): bool
    {
        if (! $assinatura) {
            return false;
        }

        if ($this->estaCancelada($assinatura)) {
            return false;
        }

        if ($this->estaVencidaOuBloqueada($assinatura)) {
            return false;
        }

        if ($this->estaExpirada($assinatura)) {
            return false;
        }

        if ($this->estaEmTeste($assinatura)) {
            return true;
        }

        return $this->estaAtivaPaga($assinatura);
    }

    public function usuarioPodeAcessarAreaInterna(User $user): bool
    {
        if ($user->blocked_at) {
            return false;
        }

        if ($user->is_admin || $user->perfil === 'admin') {
            return true;
        }

        if (in_array($user->billing_status, [
            'blocked',
            'canceled',
            'cancelled',
            'overdue',
            'past_due',
            'suspended',
            'expired',
        ], true)) {
            return false;
        }

        if ($user->billing_status === 'trial'
            && $user->trial_ends_at
            && $user->trial_ends_at->endOfDay()->isPast()) {
            return false;
        }

        return $this->estaAtiva($user->assinatura);
    }

    public function estaEmTeste(?Assinatura $assinatura): bool
    {
        if (! $assinatura) {
            return false;
        }

        if ($this->estaCancelada($assinatura) || $this->estaVencidaOuBloqueada($assinatura)) {
            return false;
        }

        if ($this->estaExpirada($assinatura)) {
            return false;
        }

        if ($assinatura->status === 'trialing' && $assinatura->trial_ends_at) {
            return $assinatura->trial_ends_at->endOfDay()->isFuture();
        }

        return $assinatura->status_assinatura === 'teste_gratis'
            && $assinatura->data_fim_teste_gratis
            && $assinatura->data_fim_teste_gratis->endOfDay()->isFuture();
    }

    public function estaVencidaOuBloqueada(?Assinatura $assinatura): bool
    {
        if (! $assinatura) {
            return true;
        }

        return in_array($assinatura->status, [
            'overdue',
            'past_due',
            'dunning',
            'suspended',
            'expired',
            'refunded',
            'chargeback',
            'pending',
        ], true) || in_array($assinatura->status_assinatura, [
            'vencida',
            'bloqueada',
        ], true);
    }

    public function estaCancelada(?Assinatura $assinatura): bool
    {
        if (! $assinatura) {
            return true;
        }

        return in_array($assinatura->status, [
            'canceled',
            'cancelled',
        ], true) || $assinatura->status_assinatura === 'cancelada';
    }

    public function estaExpirada(?Assinatura $assinatura): bool
    {
        if (! $assinatura) {
            return true;
        }

        if ($assinatura->status_assinatura === 'teste_gratis'
            && $assinatura->data_fim_teste_gratis
            && $assinatura->data_fim_teste_gratis->endOfDay()->isPast()) {
            return true;
        }

        if ($assinatura->status === 'trialing' && $assinatura->trial_ends_at) {
            return $assinatura->trial_ends_at->endOfDay()->isPast();
        }

        return false;
    }

    public function diasRestantesTeste(?Assinatura $assinatura): int
    {
        if (! $assinatura) {
            return 0;
        }

        $fim = $assinatura->trial_ends_at ?: $assinatura->data_fim_teste_gratis;

        if (! $fim || $fim->isPast()) {
            return 0;
        }

        return now()->startOfDay()->diffInDays($fim->endOfDay());
    }

    public function statusComercial(?Assinatura $assinatura): string
    {
        if (! $assinatura) {
            return 'sem_assinatura';
        }

        if ($this->estaCancelada($assinatura)) {
            return 'cancelada';
        }

        if ($this->estaVencidaOuBloqueada($assinatura)) {
            return 'vencida';
        }

        if ($this->estaExpirada($assinatura)) {
            return 'vencida';
        }

        if ($this->estaEmTeste($assinatura)) {
            return 'teste_gratis';
        }

        if ($this->estaAtivaPaga($assinatura)) {
            return 'ativa';
        }

        return 'bloqueada';
    }

    public function ativar(Assinatura $assinatura): Assinatura
    {
        $assinatura->update([
            'status_assinatura' => 'ativa',
            'valor_assinatura' => self::VALOR_MENSAL,
            'vencimento_assinatura' => now()->addMonth()->toDateString(),

            'status' => 'active',
            'valor' => self::VALOR_MENSAL,
            'last_payment_at' => now(),
            'next_payment_at' => now()->addMonth(),
            'canceled_at' => null,
            'expired_at' => null,
        ]);

        if ($assinatura->user) {
            $assinatura->user->update([
                'billing_status' => 'active',
                'billing_amount' => self::VALOR_MENSAL,
                'next_billing_at' => now()->addMonth(),
                'billing_suspended_at' => null,
                'subscription_canceled_at' => null,
            ]);
        }

        return $assinatura;
    }

    private function estaAtivaPaga(Assinatura $assinatura): bool
    {
        if (in_array($assinatura->status, ['active', 'paid'], true)) {
            return true;
        }

        return $assinatura->status_assinatura === 'ativa';
    }
}
