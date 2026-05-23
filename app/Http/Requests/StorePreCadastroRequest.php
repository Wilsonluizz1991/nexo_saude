<?php

namespace App\Http\Requests;

use App\Services\PlanoSaudeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePreCadastroRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $vidas = collect($this->input('vidas', []))->values();

        if (PlanoSaudeService::ehIndividual($this->input('tipo_proposta')) && $vidas->count() > 1) {
            $vidas = $vidas->take(1)->map(function (array $vida) {
                $vida['tipo'] = 'titular';
                unset($vida['vinculo_beneficiario_id']);

                return $vida;
            });

            $this->merge([
                'pessoa' => 'PF',
                'vidas' => $vidas->all(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'tipo_proposta' => ['required', 'in:individual,familiar,empresarial'],
            'pessoa' => ['required', 'in:PF,PJ'],
            'vidas' => ['required', 'array', 'min:1'],
            'vidas.*.tipo' => ['required', 'in:titular,dependente,socio,colaborador,dependente_socio,dependente_colaborador,responsavel_legal'],
            'vidas.*.vinculo_beneficiario_id' => ['nullable', 'integer', 'min:0'],
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

            if ($tipoProposta === 'individual') {
                if ($pessoa !== 'PF') {
                    $validator->errors()->add('pessoa', 'Propostas individuais devem ser PF.');
                }

                if ($vidas->count() !== 1) {
                    $validator->errors()->add('vidas', 'Plano individual deve conter exatamente uma vida.');
                }
            }

            if ($tipoProposta === 'familiar' && $pessoa !== 'PF') {
                $validator->errors()->add('pessoa', 'Propostas familiares devem ser PF.');
            }

            if ($tipoProposta === 'empresarial' && $pessoa !== 'PJ') {
                $validator->errors()->add('pessoa', 'Propostas empresariais devem ser PJ.');
            }

            if ($pessoa === 'PF') {
                $titulares = $vidas->where('tipo', 'titular')->count();

                if ($titulares !== 1) {
                    $validator->errors()->add('vidas', 'Convenios PF devem ter exatamente um titular estrutural.');
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
                        $validator->errors()->add("vidas.$indice.tipo", 'PJ permite socios, colaboradores, dependentes vinculados ou responsavel legal.');
                    }
                });

                $vidas->each(function (array $vida, int $indice) use ($validator, $vidas) {
                    $tipo = $vida['tipo'] ?? null;

                    if (! in_array($tipo, ['dependente_socio', 'dependente_colaborador'], true)) {
                        return;
                    }

                    if (! array_key_exists('vinculo_beneficiario_id', $vida) || $vida['vinculo_beneficiario_id'] === '') {
                        $validator->errors()->add("vidas.$indice.vinculo_beneficiario_id", 'Informe a qual vida este dependente esta vinculado.');

                        return;
                    }

                    $indiceVinculo = (int) $vida['vinculo_beneficiario_id'];
                    $vidaVinculada = $vidas->get($indiceVinculo);
                    $tipoEsperado = $tipo === 'dependente_colaborador' ? 'colaborador' : 'socio';

                    if (! $vidaVinculada || ($vidaVinculada['tipo'] ?? null) !== $tipoEsperado) {
                        $validator->errors()->add(
                            "vidas.$indice.vinculo_beneficiario_id",
                            $tipo === 'dependente_colaborador'
                                ? 'Dependente de colaborador precisa estar vinculado a um colaborador.'
                                : 'Dependente de socio precisa estar vinculado a um socio.'
                        );
                    }
                });
            }
        });
    }

    public function messages(): array
    {
        return [
            'vidas.required' => 'Adicione pelo menos um beneficiario.',
            'vidas.min' => 'Adicione pelo menos um beneficiario.',
            'vidas.*.tipo.required' => 'Beneficiario sem tipo nao pode ser enviado.',
            'vidas.*.documentos_solicitados.required' => 'Selecione os documentos solicitados de cada beneficiario.',
            'vidas.*.documentos_solicitados.min' => 'Selecione ao menos um documento para cada beneficiario.',
        ];
    }
}
