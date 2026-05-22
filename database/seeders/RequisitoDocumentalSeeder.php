<?php

namespace Database\Seeders;

use App\Models\RequisitoDocumental;
use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;

class RequisitoDocumentalSeeder extends Seeder
{
    public function run(): void
    {
        $documentos = TipoDocumento::pluck('id', 'nome');

        $regras = [
            ['Titular PF - RG', null, 'PF', 'titular', null, null, false, 'RG', true, null],
            ['Titular PF - CPF', null, 'PF', 'titular', null, null, false, 'CPF', true, null],
            ['Titular PF - Comprovante de Residência', null, 'PF', 'titular', null, null, false, 'Comprovante de Residência', true, null],
            ['Cônjuge - RG', null, null, 'dependente', 'conjuge', null, false, 'RG', true, null],
            ['Cônjuge - CPF', null, null, 'dependente', 'conjuge', null, false, 'CPF', true, null],
            ['Cônjuge - Certidão de Casamento', null, null, 'dependente', 'conjuge', null, false, 'Certidão de Casamento', true, 'vinculo_conjuge'],
            ['Cônjuge - Declaração de União Estável', null, null, 'dependente', 'conjuge', null, false, 'Declaração de União Estável', true, 'vinculo_conjuge'],
            ['Filho - Certidão de Nascimento', null, null, 'dependente', 'filho', null, false, 'Certidão de Nascimento', true, null],
            ['Gestante - Documento de acompanhamento', null, null, null, null, 'feminino', true, 'Outro', true, null],
            ['PJ - Cartão CNPJ', null, 'PJ', null, null, null, false, 'Cartão CNPJ', true, null],
            ['PJ - Contrato Social', null, 'PJ', null, null, null, false, 'Contrato Social', true, null],
            ['PJ - Relação de Vidas', null, 'PJ', null, null, null, false, 'Relação de Vidas', true, null],
            ['PJ - Documento do Responsável Legal', null, 'PJ', null, null, null, false, 'Documento do Responsável Legal', true, null],
            ['Sócio - RG', null, 'PJ', 'socio', null, null, false, 'RG', true, null],
            ['Sócio - CPF', null, 'PJ', 'socio', null, null, false, 'CPF', true, null],
            ['Responsável legal - RG', null, 'PJ', 'responsavel_legal', null, null, false, 'RG', true, null],
            ['Responsável legal - CPF', null, 'PJ', 'responsavel_legal', null, null, false, 'CPF', true, null],
            ['Colaborador - RG', null, null, 'colaborador', null, null, false, 'RG', true, null],
            ['Colaborador - CPF', null, null, 'colaborador', null, null, false, 'CPF', true, null],
        ];

        foreach ($regras as [$nome, $tipoProposta, $tipoPessoa, $papel, $parentesco, $sexo, $gestante, $documento, $obrigatorio, $grupo]) {
            RequisitoDocumental::firstOrCreate([
                'nome' => $nome,
                'tipo_documento_id' => $documentos[$documento],
                'papel_vida' => $papel,
                'parentesco' => $parentesco,
                'tipo_pessoa' => $tipoPessoa,
            ], [
                'tipo_proposta' => $tipoProposta,
                'sexo' => $sexo,
                'exige_gestante' => $gestante,
                'obrigatorio' => $obrigatorio,
                'grupo_alternativo' => $grupo,
                'ativo' => true,
            ]);
        }
    }
}
