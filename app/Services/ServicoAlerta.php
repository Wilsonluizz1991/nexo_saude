<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\Indicacao;
use App\Models\User;

class ServicoAlerta
{
    public function gerarAutomaticos(User $user): void
    {
        Indicacao::where('user_id', $user->id)
            ->where('etapa', 'propostas')
            ->where('status', 'proposta_enviada')
            ->where('updated_at', '<=', now()->subDays(3))
            ->get()
            ->each(fn ($indicacao) => $this->criar($user, $indicacao->id, 'Proposta sem resposta', 'A proposta está sem resposta há mais de 3 dias.', 'atencao'));

        DocumentoObrigatorioPreCadastro::whereIn('status', ['pendente', 'recusado', 'corrigir'])
            ->whereHas('preCadastro.indicacao', fn ($query) => $query->where('user_id', $user->id))
            ->get()
            ->each(fn ($documento) => $this->criar($user, $documento->preCadastro->indicacao_id, 'Documento pendente ou recusado', $documento->titulo, 'atencao'));

        Cliente::where('user_id', $user->id)
            ->where('updated_at', '<=', now()->subDays(30))
            ->get()
            ->each(fn ($cliente) => $this->criar($user, $cliente->indicacao_id, 'Cliente sem contato', 'Cliente sem atualização recente na carteira.', 'info'));
    }

    public function criar(User $user, ?int $indicacaoId, string $titulo, string $mensagem, string $tipo = 'info'): Alerta
    {
        return Alerta::firstOrCreate([
            'user_id' => $user->id,
            'indicacao_id' => $indicacaoId,
            'titulo' => $titulo,
            'lido' => false,
        ], [
            'mensagem' => $mensagem,
            'tipo' => $tipo,
        ]);
    }
}
