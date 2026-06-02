<?php

namespace App\Rules;

use App\Services\DocumentoFiscalService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfCnpjValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(DocumentoFiscalService::class)->cpfOuCnpjValido((string) $value)) {
            $fail('Informe um CPF ou CNPJ válido.');
        }
    }
}
