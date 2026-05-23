<?php

namespace App\Services;

class PlanoSaudeService
{
    public static function ehIndividual(?string $tipo): bool
    {
        return str($tipo ?? '')->lower()->contains('individual');
    }

    public static function normalizarQuantidadeVidas(?string $tipo, mixed $quantidade): int
    {
        if (self::ehIndividual($tipo)) {
            return 1;
        }

        return max(1, min(999, (int) ($quantidade ?: 1)));
    }

    public static function normalizarTipo(?string $tipo): string
    {
        $tipo = trim((string) $tipo);

        return $tipo !== '' ? $tipo : 'Individual';
    }
}
