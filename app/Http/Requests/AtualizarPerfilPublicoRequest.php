<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class AtualizarPerfilPublicoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'foto' => ['nullable', 'image', 'max:4096'],
            'nome_publico' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:700'],
            'especialidades' => ['nullable', 'string', 'max:255'],
            'cidade_regiao' => ['nullable', 'string', 'max:160'],
        ];
    }
}
