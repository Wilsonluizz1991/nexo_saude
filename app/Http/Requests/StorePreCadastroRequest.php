<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePreCadastroRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tipo_proposta' => ['required', 'in:individual,familiar,empresarial'],
            'pessoa' => ['required', 'in:PF,PJ'],
            'vidas' => ['required', 'array', 'min:1'],
            'vidas.*.tipo' => ['required', 'in:titular,dependente,socio,colaborador,dependente_socio,dependente_colaborador,responsavel_legal'],
            'vidas.*.documentos_solicitados' => ['required', 'array', 'min:1'],
            'vidas.*.documentos_solicitados.*' => ['integer', 'exists:tipos_documentos,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $dados = $validator->getData();
            $tipoProposta = $dados['tipo_proposta'] ?? null;
            $pessoa = $dados['pessoa'] ?? null;
            $vidas = collect($dados['vidas'] ?? [])->values();

            if ($tipoProposta === 'familiar' && $pessoa !== 'PF') {
                $validator->errors()->add('pessoa', 'Propostas familiares devem ser PF.');
            }

            if ($tipoProposta === 'empresarial' && $pessoa !== 'PJ') {
                $validator->errors()->add('pessoa', 'Propostas empresariais devem ser PJ.');
            }

            if ($pessoa === 'PF') {
                $titulares = $vidas->where('tipo', 'titular')->count();
                if ($titulares !== 1) {
                    $validator->errors()->add('vidas', 'Convênios PF devem ter exatamente um titular estrutural.');
                }

                $vidas->each(function (array $vida, int $indice) use ($validator) {
                    if (! in_array($vida['tipo'] ?? null, ['titular', 'dependente'], true)) {
                        $validator->errors()->add("vidas.$indice.tipo", 'PF permite apenas titular e dependentes.');
                    }
                });
            }

            if ($pessoa === 'PJ') {
                $validosPj = ['socio', 'colaborador', 'dependente_socio', 'dependente_colaborador', 'responsavel_legal'];
                $vidas->each(function (array $vida, int $indice) use ($validator, $validosPj) {
                    if (! in_array($vida['tipo'] ?? null, $validosPj, true)) {
                        $validator->errors()->add("vidas.$indice.tipo", 'PJ permite sócios, colaboradores, dependentes vinculados ou responsável legal.');
                    }
                });
            }
        });
    }

    public function messages(): array
    {
        return [
            'vidas.required' => 'Adicione pelo menos um beneficiário.',
            'vidas.min' => 'Adicione pelo menos um beneficiário.',
            'vidas.*.tipo.required' => 'Beneficiário sem tipo não pode ser enviado.',
            'vidas.*.documentos_solicitados.required' => 'Selecione os documentos solicitados de cada beneficiário.',
            'vidas.*.documentos_solicitados.min' => 'Selecione ao menos um documento para cada beneficiário.',
        ];
    }
}
