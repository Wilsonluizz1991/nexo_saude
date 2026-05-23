<?php

namespace App\Http\Requests;

use App\Models\Operadora;
use App\Services\PlanoSaudeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreIndicacaoPublicaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tipo_plano' => PlanoSaudeService::normalizarTipo($this->input('tipo_plano')),
            'quantidade_vidas' => PlanoSaudeService::normalizarQuantidadeVidas(
                $this->input('tipo_plano'),
                $this->input('quantidade_vidas')
            ),
        ]);

        if ($this->input('possui_preferencias') !== 'sim') {
            $this->merge([
                'operadoras' => [],
                'hospitais' => [],
                'faixa_valor_mensal' => null,
            ]);

            return;
        }

        $operadoras = Operadora::where('ativa', true)->get(['id', 'nome']);
        $operadorasPorNome = $operadoras->mapWithKeys(fn (Operadora $operadora) => [
            Str::lower(trim($operadora->nome)) => $operadora->id,
        ]);

        $operadorasNormalizadas = collect($this->input('operadoras', []))
            ->filter(fn ($operadora) => filled($operadora))
            ->map(function ($operadora) use ($operadorasPorNome) {
                $operadora = trim((string) $operadora);

                if (ctype_digit($operadora)) {
                    return (int) $operadora;
                }

                return $operadorasPorNome->get(Str::lower($operadora), $operadora);
            })
            ->unique()
            ->take(3)
            ->values()
            ->all();

        $this->merge([
            'operadoras' => $operadorasNormalizadas,
        ]);
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
            'operadoras.*' => ['integer', 'distinct', 'exists:operadoras,id'],
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
