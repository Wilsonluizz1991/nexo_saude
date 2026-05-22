<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLembreteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'data_ocorrencia' => ['required', 'date', 'after_or_equal:today'],
            'descricao' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_ocorrencia.required' => 'Informe a data do lembrete.',
            'data_ocorrencia.after_or_equal' => 'A data do lembrete não pode estar no passado.',
            'descricao.required' => 'Descreva o lembrete para o corretor.',
            'descricao.max' => 'O lembrete deve ter no máximo 255 caracteres.',
        ];
    }
}
