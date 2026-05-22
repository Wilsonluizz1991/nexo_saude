<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIndicacaoPublicaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('possui_preferencias') !== 'sim') {
            $this->merge([
                'operadoras' => [],
                'hospitais' => [],
                'faixa_valor_mensal' => null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'tipo_plano' => ['required', 'string', 'max:80'],
            'quantidade_vidas' => ['required', 'integer', 'min:1', 'max:999'],
            'cidade' => ['required', 'string', 'max:120'],
            'estado' => ['required', 'string', 'size:2'],
            'possui_preferencias' => ['required', 'in:sim,nao,ainda_nao_sei'],
            'operadoras' => ['nullable', 'array', 'max:3'],
            'operadoras.*' => ['exists:operadoras,id'],
            'hospitais' => ['nullable', 'array', 'max:3'],
            'hospitais.*' => ['nullable', 'string', 'max:120'],
            'faixa_valor_mensal' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'operadoras.max' => 'Selecione no máximo 3 operadoras.',
            'hospitais.max' => 'Informe no máximo 3 hospitais.',
        ];
    }
}
