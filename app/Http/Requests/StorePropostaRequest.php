<?php

namespace App\Http\Requests;

use App\Services\PlanoSaudeService;
use Illuminate\Foundation\Http\FormRequest;

class StorePropostaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $tipo = $this->route('indicacao')?->tipo_plano ?? $this->input('tipo_plano');

        $this->merge([
            'quantidade_vidas' => PlanoSaudeService::normalizarQuantidadeVidas($tipo, $this->input('quantidade_vidas')),
        ]);
    }

    public function rules(): array
    {
        $campoArquivo = $this->file('arquivos_pdf') ? 'arquivos_pdf' : 'arquivo_pdf';
        $arquivos = $this->file($campoArquivo);
        $multiplosArquivos = is_array($arquivos);

        $regras = [
            'titulo' => ['required', 'string', 'max:160'],
            'operadora_id' => ['nullable', 'exists:operadoras,id'],
            'valor_mensal' => ['nullable', 'numeric', 'min:0'],
            'quantidade_vidas' => ['nullable', 'integer', 'min:1'],
            'validade' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($multiplosArquivos) {
            $regras[$campoArquivo] = ['required', 'array', 'min:1'];
            $regras[$campoArquivo.'.*'] = ['required', 'file', 'mimes:pdf', 'max:10240'];
        } else {
            $regras[$campoArquivo] = ['required', 'file', 'mimes:pdf', 'max:10240'];
        }

        return $regras;
    }
}
