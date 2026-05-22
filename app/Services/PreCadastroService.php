<?php

namespace App\Services;

use App\Models\Indicacao;
use App\Models\PreCadastro;
use App\Models\TipoDocumento;
use Illuminate\Support\Str;

class PreCadastroService
{
    public function __construct(
        private ChecklistDocumentalService $checklist,
        private ServicoTimeline $timeline,
    ) {
    }

    public function iniciar(Indicacao $indicacao, array $dados): PreCadastro
    {
        $preCadastro = PreCadastro::updateOrCreate(
            ['indicacao_id' => $indicacao->id],
            [
                'token' => $indicacao->preCadastro?->token ?? Str::random(48),
                'tipo_proposta' => $dados['tipo_proposta'],
                'pessoa' => $dados['pessoa'],
                'status' => 'aguardando_envio',
                'formulario_bloqueado' => false,
                'motivos_correcao' => null,
            ]
        );

        $preCadastro->vidas()->delete();
        $mapaBeneficiarios = [];

        foreach (array_values($dados['vidas']) as $indice => $vidaDados) {
            $vida = $preCadastro->vidas()->create([
                'tipo' => $vidaDados['tipo'],
                'ordem' => $indice + 1,
                'nome' => null,
                'parentesco' => null,
                'sexo' => null,
                'data_nascimento' => null,
                'gestante' => false,
                'cpf' => null,
                'cargo' => null,
            ]);

            $mapaBeneficiarios[$indice] = $vida;
        }

        foreach (array_values($dados['vidas']) as $indice => $vidaDados) {
            if (! in_array($vidaDados['tipo'], ['dependente_socio', 'dependente_colaborador'], true)) {
                continue;
            }

            $indiceVinculo = (int) ($vidaDados['vinculo_beneficiario_id'] ?? -1);
            if (isset($mapaBeneficiarios[$indice], $mapaBeneficiarios[$indiceVinculo])) {
                $mapaBeneficiarios[$indice]->update([
                    'vinculo_beneficiario_id' => $mapaBeneficiarios[$indiceVinculo]->id,
                ]);
            }
        }

        $preCadastro->load('vidas');
        $this->gerarDocumentosSelecionados($preCadastro, $mapaBeneficiarios, array_values($dados['vidas']));

        $indicacao->update([
            'etapa' => 'pre_cadastros',
            'status' => 'aguardando_envio',
            'quantidade_vidas' => $preCadastro->vidas()->count(),
        ]);
        $indicacao->timelineEventos()->create([
            'titulo' => 'Pré-cadastro iniciado',
            'descricao' => 'Estrutura de beneficiários definida pelo corretor e checklist documental inteligente gerado.',
        ]);
        $indicacao->timelineEventos()->create([
            'titulo' => 'Link gerado',
            'descricao' => 'Link único de pré-cadastro liberado para o cliente.',
        ]);
        $this->timeline->registrar([
            'indicacao_id' => $indicacao->id,
            'pre_cadastro_id' => $preCadastro->id,
            'tipo' => 'link_pre_cadastro_gerado',
            'titulo' => 'Link gerado',
            'descricao' => 'Link tokenizado liberado para preenchimento documental do cliente.',
        ]);

        return $preCadastro;
    }

    private function parentescoDaVida(array $vidaDados): ?string
    {
        if (in_array($vidaDados['tipo'], ['titular', 'socio', 'colaborador', 'responsavel_legal'], true)) {
            return null;
        }

        return $vidaDados['parentesco'] ?? null;
    }

    private function gerarDocumentosSelecionados(PreCadastro $preCadastro, array $mapaBeneficiarios, array $vidas): void
    {
        $preCadastro->documentosObrigatorios()->delete();

        $tipos = TipoDocumento::where('ativo', true)
            ->whereIn('id', collect($vidas)->flatMap(fn (array $vida) => $vida['documentos_solicitados'] ?? [])->unique()->values())
            ->get()
            ->keyBy('id');

        foreach ($vidas as $indiceVida => $vidaDados) {
            $vida = $mapaBeneficiarios[$indiceVida] ?? null;
            if (! $vida) {
                continue;
            }

            foreach (array_values(array_unique($vidaDados['documentos_solicitados'] ?? [])) as $ordem => $tipoDocumentoId) {
                $tipoDocumento = $tipos->get((int) $tipoDocumentoId);
                if (! $tipoDocumento) {
                    continue;
                }

                $preCadastro->documentosObrigatorios()->create([
                    'vida_proposta_id' => $vida->id,
                    'tipo_documento_id' => $tipoDocumento->id,
                    'requisito_documental_id' => null,
                    'titulo' => "{$tipoDocumento->nome} - Beneficiário {$vida->ordem}",
                    'obrigatorio' => true,
                    'ordem' => $ordem + 1,
                    'status' => 'pendente',
                    'grupo_alternativo' => null,
                ]);
            }
        }
    }
}
