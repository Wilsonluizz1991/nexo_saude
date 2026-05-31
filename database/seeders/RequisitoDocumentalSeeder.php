<?php

namespace Database\Seeders;

use App\Models\RequisitoDocumental;
use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;

class RequisitoDocumentalSeeder extends Seeder
{
    public function run(): void
    {
        $documentoIdentidade = TipoDocumento::updateOrCreate(
            ['slug' => 'documento-de-identidade-com-foto'],
            [
                'nome' => 'Documento de identidade com foto',
                'descricao' => 'Documento de identidade com foto',
                'ativo' => true,
            ]
        );

        TipoDocumento::whereIn('nome', ['RG', 'CNH'])->update(['ativo' => false]);

        RequisitoDocumental::whereIn('nome', [
            'Titular PF - RG',
            'Conjuge - RG',
            'Cônjuge - RG',
            'Socio - RG',
            'Sócio - RG',
            'Responsavel legal - RG',
            'Responsável legal - RG',
            'Colaborador - RG',
        ])->update([
            'tipo_documento_id' => $documentoIdentidade->id,
            'ativo' => false,
        ]);

        $documentos = TipoDocumento::pluck('id', 'nome');

        $regras = [
            ['Titular PF - Documento de identidade com foto', null, 'PF', 'titular', null, null, false, 'Documento de identidade com foto', true, null],
            ['Titular PF - CPF', null, 'PF', 'titular', null, null, false, 'CPF', true, null],
            ['Titular PF - Comprovante de Residência', null, 'PF', 'titular', null, null, false, 'Comprovante de Residência', true, null],
            ['Cônjuge - Documento de identidade com foto', null, null, 'dependente', 'conjuge', null, false, 'Documento de identidade com foto', true, null],
            ['Cônjuge - CPF', null, null, 'dependente', 'conjuge', null, false, 'CPF', true, null],
            ['Cônjuge - Certidão de Casamento', null, null, 'dependente', 'conjuge', null, false, 'Certidão de Casamento', true, 'vinculo_conjuge'],
            ['Cônjuge - Declaração de União Estável', null, null, 'dependente', 'conjuge', null, false, 'Declaração de União Estável', true, 'vinculo_conjuge'],
            ['Filho - Certidão de Nascimento', null, null, 'dependente', 'filho', null, false, 'Certidão de Nascimento', true, null],
            ['Gestante - Documento de acompanhamento', null, null, null, null, 'feminino', true, 'Outro', true, null],
            ['PJ - Cartão CNPJ', null, 'PJ', null, null, null, false, 'Cartão CNPJ', true, null],
            ['PJ - Contrato Social', null, 'PJ', null, null, null, false, 'Contrato Social', true, null],
            ['PJ - Relação de Vidas', null, 'PJ', null, null, null, false, 'Relação de Vidas', true, null],
            ['PJ - Documento do Responsável Legal', null, 'PJ', null, null, null, false, 'Documento do Responsável Legal', true, null],
            ['Sócio - Documento de identidade com foto', null, 'PJ', 'socio', null, null, false, 'Documento de identidade com foto', true, null],
            ['Sócio - CPF', null, 'PJ', 'socio', null, null, false, 'CPF', true, null],
            ['Responsável legal - Documento de identidade com foto', null, 'PJ', 'responsavel_legal', null, null, false, 'Documento de identidade com foto', true, null],
            ['Responsável legal - CPF', null, 'PJ', 'responsavel_legal', null, null, false, 'CPF', true, null],
            ['Colaborador - Documento de identidade com foto', null, null, 'colaborador', null, null, false, 'Documento de identidade com foto', true, null],
            ['Colaborador - CPF', null, null, 'colaborador', null, null, false, 'CPF', true, null],
        ];

        foreach ($regras as [$nome, $tipoProposta, $tipoPessoa, $papel, $parentesco, $sexo, $gestante, $documento, $obrigatorio, $grupo]) {
            RequisitoDocumental::updateOrCreate([
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
