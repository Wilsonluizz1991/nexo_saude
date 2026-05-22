<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Indicacao;
use App\Models\TimelineEvento;
use App\Models\User;

class IndicacaoService
{
    public function criarPorSolicitacaoPublica(User $corretor, array $dados): Indicacao
    {
        $possuiPreferencias = $dados['possui_preferencias'] === 'sim';

        $indicacao = Indicacao::create([
            'user_id' => $corretor->id,
            'origem' => 'link_publico',
            'nome_cliente' => $dados['nome'],
            'telefone' => $dados['telefone'],
            'email' => $dados['email'] ?? null,
            'tipo_plano' => $dados['tipo_plano'],
            'quantidade_vidas' => $dados['quantidade_vidas'],
            'cidade' => $dados['cidade'],
            'estado' => strtoupper($dados['estado']),
            'possui_preferencias' => $possuiPreferencias,
            'operadoras_preferidas' => $possuiPreferencias ? array_slice($dados['operadoras'] ?? [], 0, 3) : [],
            'hospitais_preferidos' => $possuiPreferencias ? array_values(array_filter(array_slice($dados['hospitais'] ?? [], 0, 3))) : [],
            'faixa_valor_mensal' => $possuiPreferencias ? ($dados['faixa_valor_mensal'] ?? null) : null,
            'etapa' => 'lead',
            'status' => 'nova',
        ]);

        TimelineEvento::create([
            'indicacao_id' => $indicacao->id,
            'titulo' => 'Lead criada',
            'descricao' => 'Pedido recebido pelo link público do corretor.',
        ]);

        Alerta::create([
            'user_id' => $corretor->id,
            'indicacao_id' => $indicacao->id,
            'titulo' => 'Nova Lead recebida',
            'mensagem' => "Lead recebida pelo link público: {$indicacao->nome_cliente}.",
            'tipo' => 'info',
            'lido' => false,
        ]);

        return $indicacao;
    }
}
