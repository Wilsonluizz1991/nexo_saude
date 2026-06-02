<?php

namespace App\Http\Requests;

use App\Rules\CpfCnpjValido;
use App\Services\DocumentoFiscalService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class CriarContaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('billing_cpf_cnpj')) {
            $this->merge([
                'billing_cpf_cnpj' => app(DocumentoFiscalService::class)->normalizar($this->input('billing_cpf_cnpj')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'telefone' => ['required', 'string', 'max:30'],
            'billing_cpf_cnpj' => ['required', 'string', 'max:14', new CpfCnpjValido, Rule::unique('users', 'billing_cpf_cnpj')],

            'password' => ['required', 'string', PasswordRule::min(8)->mixedCase()->letters()->numbers()->symbols(), 'confirmed'],

            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'max:30'],
            'card_expiry_month' => ['required', 'string', 'size:2'],
            'card_expiry_year' => ['required', 'string', 'size:4'],
            'card_ccv' => ['required', 'string', 'min:3', 'max:4'],
            'holder_postal_code' => ['required', 'string', 'max:20'],
            'holder_address_number' => ['required', 'string', 'max:20'],

            'card_brand' => ['nullable', 'string', 'max:30'],
            'card_last_four' => ['nullable', 'string', 'max:4'],

            'accepted_terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'accepted_terms.accepted' => 'Você precisa aceitar os termos para iniciar o teste gratuito.',
            'billing_cpf_cnpj.required' => 'Informe seu CPF ou CNPJ.',
            'card_holder_name.required' => 'Informe o nome impresso no cartão.',
            'card_number.required' => 'Informe o número do cartão.',
            'card_expiry_month.required' => 'Informe o mês de validade do cartão.',
            'card_expiry_year.required' => 'Informe o ano de validade do cartão.',
            'card_ccv.required' => 'Informe o CVV do cartão.',
            'holder_postal_code.required' => 'Informe o CEP do titular do cartão.',
            'holder_address_number.required' => 'Informe o número do endereço do titular do cartão.',
        ];
    }
}
