<?php

namespace App\Services;

class DocumentoFiscalService
{
    public function normalizar(?string $documento): string
    {
        return preg_replace('/\D/', '', (string) $documento);
    }

    public function cpfValido(?string $cpf): bool
    {
        $cpf = $this->normalizar($cpf);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($posicao = 9; $posicao < 11; $posicao++) {
            $soma = 0;

            for ($indice = 0; $indice < $posicao; $indice++) {
                $soma += (int) $cpf[$indice] * (($posicao + 1) - $indice);
            }

            $digito = (($soma * 10) % 11) % 10;

            if ((int) $cpf[$posicao] !== $digito) {
                return false;
            }
        }

        return true;
    }

    public function cnpjValido(?string $cnpj): bool
    {
        $cnpj = $this->normalizar($cnpj);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $pesos = [
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        ];

        for ($posicao = 12; $posicao < 14; $posicao++) {
            $soma = 0;

            foreach ($pesos[$posicao - 12] as $indice => $peso) {
                $soma += (int) $cnpj[$indice] * $peso;
            }

            $resto = $soma % 11;
            $digito = $resto < 2 ? 0 : 11 - $resto;

            if ((int) $cnpj[$posicao] !== $digito) {
                return false;
            }
        }

        return true;
    }

    public function cpfOuCnpjValido(?string $documento): bool
    {
        $documento = $this->normalizar($documento);

        return strlen($documento) === 11
            ? $this->cpfValido($documento)
            : $this->cnpjValido($documento);
    }
}
