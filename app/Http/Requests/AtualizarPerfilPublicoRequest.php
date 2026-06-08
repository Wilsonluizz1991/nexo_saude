<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AtualizarPerfilPublicoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remover_foto' => ['nullable', 'boolean'],
            'nome_publico' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string', 'max:700'],
            'especialidades' => ['required', 'string', 'max:255'],
            'cidade_regiao' => ['required', 'string', 'max:160'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome_publico.required' => 'Informe o nome que será exibido no perfil público.',
            'bio.required' => 'Informe uma biografia para o seu perfil público.',
            'especialidades.required' => 'Informe pelo menos uma especialidade.',
            'cidade_regiao.required' => 'Informe a cidade ou região de atendimento.',
        ];
    }
}
