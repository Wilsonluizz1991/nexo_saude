<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarPerfilPublicoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique('corretor_perfis', 'slug')->ignore($this->user()->corretorPerfil?->id)],
            'foto' => ['nullable', 'image', 'max:4096'],
            'nome_publico' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:700'],
            'especialidades' => ['nullable', 'string', 'max:255'],
            'cidade_regiao' => ['nullable', 'string', 'max:160'],
        ];
    }
}
