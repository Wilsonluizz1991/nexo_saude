<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Dependente;
use App\Models\Indicacao;
use App\Models\TimelineEvento;
use Illuminate\Support\Facades\Mail;

class ImplantacaoService
{
    public function iniciar(Indicacao $indicacao): void
    {
        $indicacao->implantacao()->firstOrCreate([], [
            'status' => 'contrato_em_analise',
            'data_inicio' => now()->toDateString(),
        ]);

        $indicacao->update(['etapa' => 'implantacoes', 'status' => 'contrato_em_analise']);
        $indicacao->preCadastro?->update(['status' => 'documentacao_aprovada']);

        TimelineEvento::create([
            'indicacao_id' => $indicacao->id,
            'titulo' => 'Documentação aprovada',
            'descricao' => 'Corretor aprovou a documentação e iniciou a implantação.',
        ]);
        TimelineEvento::create(['indicacao_id' => $indicacao->id, 'titulo' => 'Implantação iniciada']);
    }

    public function contratoVigente(Indicacao $indicacao, array $dados): Cliente
    {
        $implantacao = $indicacao->implantacao()->firstOrCreate([], ['status' => 'contrato_em_analise']);
        $implantacao->update([
            'status' => 'contrato_vigente',
            'data_aprovacao' => $dados['data_vigencia'],
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        $proposta = $indicacao->propostas()->latest()->first();

        $cliente = Cliente::updateOrCreate(
            ['indicacao_id' => $indicacao->id],
            [
                'user_id' => $indicacao->user_id,
                'nome' => $indicacao->nome_cliente,
                'email' => $indicacao->email,
                'telefone' => $indicacao->telefone,
                'inicio_vigencia' => $dados['data_vigencia'],
                'valor_mensal' => $dados['valor_mensal'],
                'status' => 'ativo',
            ]
        );

        Contrato::updateOrCreate(['cliente_id' => $cliente->id, 'proposta_id' => $proposta?->id], [
            'usuario_id' => $indicacao->user_id,
            'operadora_id' => $dados['operadora_id'],
            'tipo_contrato' => $dados['tipo_contrato'],
            'status' => 'vigente',
            'quantidade_vidas' => $dados['quantidade_vidas'],
            'valor_mensal' => $dados['valor_mensal'],
            'numero_contrato' => $dados['numero_contrato'] ?? null,
            'iniciado_em' => $dados['data_vigencia'],
            'renovacao_em' => $dados['renovacao_em'],
            'reajuste_em' => $dados['reajuste_em'],
            'observacoes' => $dados['observacoes'] ?? null,
        ]);

        foreach ($indicacao->preCadastro?->vidas ?? [] as $vida) {
            if (in_array($vida->tipo, ['dependente', 'dependente_socio', 'dependente_colaborador'], true)) {
                Dependente::updateOrCreate(['cliente_id' => $cliente->id, 'nome' => $vida->nome], [
                    'data_nascimento' => $vida->data_nascimento,
                    'sexo' => $vida->sexo,
                    'parentesco' => $vida->parentesco,
                    'gestante' => $vida->gestante,
                    'status' => 'ativo',
                ]);
            }
        }

        $indicacao->update(['etapa' => 'carteira', 'status' => 'contrato_vigente']);
        $indicacao->preCadastro?->update(['status' => 'convertido_em_cliente']);

        TimelineEvento::create([
            'indicacao_id' => $indicacao->id,
            'titulo' => 'Contrato vigente',
            'descricao' => 'Contrato vigente confirmado. Cliente convertido para carteira ativa.',
        ]);

        if (! empty($dados['enviar_email']) && $indicacao->email) {
            Mail::raw($this->mensagemEmailContrato($indicacao, $dados), function ($message) use ($indicacao) {
                $message->to($indicacao->email)->subject('Seu plano de saúde já está vigente 🎉');
            });
            TimelineEvento::create(['indicacao_id' => $indicacao->id, 'titulo' => 'E-mail automático enviado']);
        }

        if (! empty($dados['enviar_sms'])) {
            TimelineEvento::create([
                'indicacao_id' => $indicacao->id,
                'titulo' => 'SMS automático enviado',
                'descricao' => 'Parabéns! Seu contrato foi aprovado e já está vigente. Em caso de dúvidas, fale com seu corretor.',
            ]);
        }

        return $cliente;
    }

    private function mensagemEmailContrato(Indicacao $indicacao, array $dados): string
    {
        return "Parabéns, {$indicacao->nome_cliente}!\n\n"
            ."Seu plano de saúde já está vigente.\n\n"
            ."Resumo do contrato:\n"
            ."- Tipo: {$dados['tipo_contrato']}\n"
            ."- Quantidade de vidas: {$dados['quantidade_vidas']}\n"
            ."- Vigência: {$dados['data_vigencia']}\n"
            ."- Valor mensal: R$ {$dados['valor_mensal']}\n"
            ."- Renovação: {$dados['renovacao_em']}\n"
            ."- Reajuste: {$dados['reajuste_em']}\n\n"
            .'Em caso de dúvidas, fale com seu corretor.';
    }
}
