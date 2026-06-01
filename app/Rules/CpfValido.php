<?php

namespace App\Rules;

use App\Services\DocumentoFiscalService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(DocumentoFiscalService::class)->cpfValido((string) $value)) {
            $fail('Informe um CPF valido.');
        }
    }
}
