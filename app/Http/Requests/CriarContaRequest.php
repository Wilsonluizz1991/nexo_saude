<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriarContaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'telefone' => ['required', 'string', 'max:30'],
            'billing_cpf_cnpj' => ['required', 'string', 'max:20'],

            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'max:30'],
            'card_expiry_month' => ['required', 'string', 'size:2'],
            'card_expiry_year' => ['required', 'string', 'size:4'],
            'card_ccv' => ['required', 'string', 'min:3', 'max:4'],

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
        ];
    }
}