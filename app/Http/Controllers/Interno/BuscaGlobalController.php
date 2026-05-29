<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Models\Indicacao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BuscaGlobalController extends Controller
{
    public function __invoke(Request $request)
    {
        $termo = trim((string) $request->query('q', ''));
        $resultados = collect();

        if (mb_strlen($termo) >= 3) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $termo).'%';

            $resultados = Indicacao::query()
                ->select(['id', 'user_id', 'nome_cliente', 'telefone', 'email', 'tipo_plano', 'quantidade_vidas', 'cidade', 'estado', 'status', 'etapa', 'observacoes', 'faixa_valor_mensal', 'hospitais_preferidos', 'created_at'])
                ->where('user_id', $request->user()->id)
                ->with([
                    'propostas:id,indicacao_id,operadora_id,titulo,status,observacoes',
                    'propostas.operadora:id,nome',
                    'preCadastro:id,indicacao_id,tipo_proposta,pessoa,status,chave_acesso,motivos_correcao',
                    'implantacao:id,indicacao_id,status,observacoes',
                    'cliente:id,indicacao_id,user_id,nome,email,telefone,status',
                    'cliente.contratos:id,cliente_id,operadora_id,numero_contrato,tipo_contrato,status',
                    'cliente.contratos.operadora:id,nome',
                    'cliente.dependentes:id,cliente_id,nome',
                ])
                ->where(function (Builder $query) use ($like): void {
                    $query
                        ->where('nome_cliente', 'like', $like)
                        ->orWhere('telefone', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('tipo_plano', 'like', $like)
                        ->orWhere('cidade', 'like', $like)
                        ->orWhere('estado', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhere('etapa', 'like', $like)
                        ->orWhere('observacoes', 'like', $like)
                        ->orWhere('faixa_valor_mensal', 'like', $like)
                        ->orWhere('hospitais_preferidos', 'like', $like)
                        ->orWhereHas('propostas', function (Builder $proposta) use ($like): void {
                            $proposta
                                ->where('titulo', 'like', $like)
                                ->orWhere('status', 'like', $like)
                                ->orWhere('observacoes', 'like', $like)
                                ->orWhereHas('operadora', fn (Builder $operadora) => $operadora->where('nome', 'like', $like));
                        })
                        ->orWhereHas('preCadastro', function (Builder $preCadastro) use ($like): void {
                            $preCadastro
                                ->where('tipo_proposta', 'like', $like)
                                ->orWhere('pessoa', 'like', $like)
                                ->orWhere('status', 'like', $like)
                                ->orWhere('chave_acesso', 'like', $like)
                                ->orWhere('motivos_correcao', 'like', $like);
                        })
                        ->orWhereHas('implantacao', function (Builder $implantacao) use ($like): void {
                            $implantacao
                                ->where('status', 'like', $like)
                                ->orWhere('observacoes', 'like', $like);
                        })
                        ->orWhereHas('cliente', function (Builder $cliente) use ($like): void {
                            $cliente
                                ->where('nome', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('telefone', 'like', $like)
                                ->orWhere('status', 'like', $like)
                                ->orWhereHas('dependentes', fn (Builder $dependente) => $dependente->where('nome', 'like', $like))
                                ->orWhereHas('contratos', function (Builder $contrato) use ($like): void {
                                    $contrato
                                        ->where('numero_contrato', 'like', $like)
                                        ->orWhere('tipo_contrato', 'like', $like)
                                        ->orWhere('status', 'like', $like)
                                        ->orWhereHas('operadora', fn (Builder $operadora) => $operadora->where('nome', 'like', $like));
                                });
                        });
                })
                ->latest()
                ->paginate(5)
                ->withQueryString()
                ->through(fn (Indicacao $indicacao) => $this->formatarResultado($indicacao));
        }

        return view('interno.busca.index', [
            'termo' => $termo,
            'resultados' => $resultados,
        ]);
    }

    private function formatarResultado(Indicacao $indicacao): array
    {
        $etapa = $this->rotuloEtapa($indicacao);
        $url = $this->urlDestino($indicacao);

        return [
            'titulo' => $indicacao->nome_cliente,
            'subtitulo' => "{$indicacao->telefone} | {$indicacao->email}",
            'etapa' => $etapa,
            'plano' => $indicacao->tipo_plano,
            'vidas' => $indicacao->quantidade_vidas,
            'status' => str_replace('_', ' ', $indicacao->status),
            'local' => trim($indicacao->cidade.' / '.$indicacao->estado, ' /'),
            'url' => $url,
        ];
    }

    private function rotuloEtapa(Indicacao $indicacao): string
    {
        return [
            'lead' => 'Lead',
            'propostas' => 'Propostas',
            'pre_cadastros' => 'Pre-cadastro',
            'implantacoes' => 'Implantacao',
            'clientes' => 'Cliente',
            'carteira' => 'Carteira',
            'perdida' => 'Perdida',
        ][$indicacao->etapa] ?? ucfirst(str_replace('_', ' ', $indicacao->etapa));
    }

    private function urlDestino(Indicacao $indicacao): string
    {
        if ($indicacao->etapa === 'propostas') {
            return route('propostas.show', $indicacao);
        }

        if ($indicacao->etapa === 'pre_cadastros' && $indicacao->preCadastro) {
            return route('pre-cadastros.show', $indicacao->preCadastro);
        }

        if ($indicacao->etapa === 'implantacoes' && $indicacao->implantacao) {
            return route('implantacoes.show', $indicacao->implantacao);
        }

        if (in_array($indicacao->etapa, ['clientes', 'carteira'], true) && $indicacao->cliente) {
            return route('clientes.show', $indicacao->cliente);
        }

        return route('indicacoes.show', $indicacao);
    }
}
