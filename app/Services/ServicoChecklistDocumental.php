<?php

namespace App\Services;

use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\PreCadastro;
use App\Models\RequisitoDocumental;
use App\Models\Vida;

class ServicoChecklistDocumental
{
    public function gerarParaPreCadastro(PreCadastro $preCadastro): void
    {
        $preCadastro->documentosObrigatorios()->delete();

        if ($preCadastro->pessoa === 'PJ') {
            $this->gerarRegrasPJ($preCadastro);
        }

        foreach ($preCadastro->vidas as $vida) {
            $this->gerarParaVida($preCadastro, $vida);
        }
    }

    public function gerarParaVida(PreCadastro $preCadastro, Vida $vida): void
    {
        $parentesco = $this->normalizar($vida->parentesco);
        $papelVida = $this->papelDocumental($vida);

        $requisitos = RequisitoDocumental::query()
            ->where('ativo', true)
            ->where(function ($query) use ($preCadastro) {
                $query->whereNull('tipo_pessoa')->orWhere('tipo_pessoa', $preCadastro->pessoa);
            })
            ->where(function ($query) use ($preCadastro) {
                $query->whereNull('tipo_proposta')->orWhere('tipo_proposta', $preCadastro->tipo_proposta);
            })
            ->where(function ($query) use ($papelVida) {
                $query->whereNull('papel_vida')->orWhere('papel_vida', $papelVida);
            })
            ->where(function ($query) use ($parentesco) {
                $query->whereNull('parentesco')->orWhere('parentesco', $parentesco);
            })
            ->where(function ($query) use ($vida) {
                $query->whereNull('sexo')->orWhere('sexo', $vida->sexo);
            })
            ->where(function ($query) use ($vida) {
                $query->where('exige_gestante', false)
                    ->orWhere(fn ($gestante) => $gestante->where('exige_gestante', true)->whereRaw('? = 1', [$vida->gestante ? 1 : 0]));
            })
            ->with('tipoDocumento')
            ->get();

        foreach ($requisitos as $requisito) {
            $this->criarDocumento($preCadastro, $vida, $requisito);
        }
    }

    private function gerarRegrasPJ(PreCadastro $preCadastro): void
    {
        RequisitoDocumental::query()
            ->where('ativo', true)
            ->where('tipo_pessoa', 'PJ')
            ->whereNull('papel_vida')
            ->with('tipoDocumento')
            ->get()
            ->each(fn (RequisitoDocumental $requisito) => $this->criarDocumento($preCadastro, null, $requisito));
    }

    private function papelDocumental(Vida $vida): string
    {
        return match ($vida->tipo) {
            'dependente_socio', 'dependente_colaborador' => 'dependente',
            default => $vida->tipo,
        };
    }

    private function criarDocumento(PreCadastro $preCadastro, ?Vida $vida, RequisitoDocumental $requisito): DocumentoObrigatorioPreCadastro
    {
        return DocumentoObrigatorioPreCadastro::firstOrCreate([
            'pre_cadastro_id' => $preCadastro->id,
            'vida_proposta_id' => $vida?->id,
            'tipo_documento_id' => $requisito->tipo_documento_id,
            'requisito_documental_id' => $requisito->id,
        ], [
            'titulo' => $vida ? "{$requisito->tipoDocumento->nome} - {$vida->nome}" : $requisito->tipoDocumento->nome,
            'obrigatorio' => $requisito->obrigatorio,
            'status' => 'pendente',
            'grupo_alternativo' => $requisito->grupo_alternativo,
        ]);
    }

    private function normalizar(?string $valor): ?string
    {
        if (! $valor) {
            return null;
        }

        $valor = mb_strtolower(trim($valor));

        return match ($valor) {
            'cônjuge', 'conjuge', 'esposa', 'esposo', 'companheiro', 'companheira' => 'conjuge',
            'filha', 'filho(a)', 'filho' => 'filho',
            default => $valor,
        };
    }
}
