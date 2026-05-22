<?php

namespace App\Http\Requests;

use App\Models\PreCadastro;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDocumentoClienteRequest extends FormRequest
{
    public function rules(): array
    {
        $emCorrecao = $this->emModoCorrecao();
        $vidasPrefix = $emCorrecao ? 'nullable' : 'required';
        $campoPessoa = $emCorrecao ? ['nullable'] : ['required'];

        return [
            'vidas' => [$vidasPrefix, 'array', 'min:1'],
            'vidas.*.nome' => [...$campoPessoa, 'string', 'max:255'],
            'vidas.*.cpf' => [...$campoPessoa, 'string', 'max:30', 'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/'],
            'vidas.*.data_nascimento' => [...$campoPessoa, 'date', 'before_or_equal:today'],
            'vidas.*.sexo' => [...$campoPessoa, 'in:masculino,feminino,outro'],
            'vidas.*.parentesco' => ['nullable', 'string', 'max:80'],
            'vidas.*.vinculo_beneficiario_id' => ['nullable', 'integer', 'min:0'],
            'vidas.*.gestante' => ['nullable', 'boolean'],
            'documentos' => ['required', 'array'],
            'documentos.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'observacao_cliente' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach (($validator->getData()['vidas'] ?? []) as $indice => $vida) {
                if (($vida['sexo'] ?? null) !== 'feminino' && ! empty($vida['gestante'])) {
                    $validator->errors()->add("vidas.$indice.gestante", 'Gestação só pode ser informada para beneficiária com sexo feminino.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'vidas.*.data_nascimento.before_or_equal' => 'A data de nascimento não pode ser futura.',
            'vidas.*.cpf.regex' => 'Informe o CPF no formato 000.000.000-00.',
        ];
    }

    private function emModoCorrecao(): bool
    {
        $token = $this->route('token');
        if (! $token) {
            return false;
        }

        $preCadastro = PreCadastro::where('token', $token)->first();

        return $preCadastro
            && $preCadastro->enviado_em
            && in_array($preCadastro->status, ['documentacao_pendente', 'correcao_solicitada'], true);
    }
}
