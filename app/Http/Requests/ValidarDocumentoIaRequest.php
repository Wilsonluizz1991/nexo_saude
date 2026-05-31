<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidarDocumentoIaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'fase_validacao' => $this->input('fase_validacao', 'completa'),
        ]);
    }

    public function rules(): array
    {
        return [
            'fase_validacao' => ['required', 'in:documental,titularidade,completa'],
            'arquivo' => ['required_unless:fase_validacao,titularidade', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'ia_validacao_id' => ['nullable', 'integer'],
            'tipo_documento_esperado' => ['nullable', 'string', 'max:255'],
            'nome_beneficiario_atual' => ['nullable', 'string', 'max:255'],
            'cpf_beneficiario_atual' => ['nullable', 'string', 'max:30'],
            'data_nascimento_beneficiario_atual' => ['nullable', 'date'],
            'sexo_beneficiario_atual' => ['nullable', 'string', 'max:30'],
            'tipo_beneficiario_atual' => ['nullable', 'string', 'max:60'],
        ];
    }
}
