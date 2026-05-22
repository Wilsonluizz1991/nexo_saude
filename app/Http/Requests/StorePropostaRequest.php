<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropostaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:160'],
            'operadora_id' => ['nullable', 'exists:operadoras,id'],
            'valor_mensal' => ['nullable', 'numeric', 'min:0'],
            'quantidade_vidas' => ['nullable', 'integer', 'min:1'],
            'validade' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'arquivo_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
