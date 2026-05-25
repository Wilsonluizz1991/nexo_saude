<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\Indicacao;
use App\Models\User;
use Illuminate\Support\Carbon;

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

        $this->gerarAlertasAniversarioTitular($user);
    }

    public function gerarAlertasAniversarioTitular(User $user): void
    {
        if (! $user->receber_notificacoes_aniversario) {
            return;
        }

        $hoje = Carbon::today();
        $anoAtual = $hoje->year;

        Cliente::query()
            ->where('user_id', $user->id)
            ->where('status', 'ativo')
            ->whereHas('contratos', fn ($query) => $query->where('status', 'ativo'))
            ->with([
                'contratos' => fn ($query) => $query->where('status', 'ativo'),
                'indicacao.preCadastro.vidas',
            ])
            ->get()
            ->each(function (Cliente $cliente) use ($user, $hoje, $anoAtual): void {
                $titular = $cliente->indicacao?->preCadastro?->vidas
                    ?->firstWhere('tipo', 'titular');

                if (! $titular || ! $titular->data_nascimento) {
                    return;
                }

                if ((int) $titular->data_nascimento->format('m') !== (int) $hoje->format('m') || (int) $titular->data_nascimento->format('d') !== (int) $hoje->format('d')) {
                    return;
                }

                foreach ($cliente->contratos as $contrato) {
                    $chave = "aniversario_titular:{$cliente->id}:{$contrato->id}:{$anoAtual}";
                    $idade = $titular->data_nascimento->age;
                    $nomeTitular = $titular->nome ?: $cliente->nome;

                    $this->criarUnico($user, [
                        'chave' => $chave,
                        'cliente_id' => $cliente->id,
                        'indicacao_id' => $cliente->indicacao_id,
                        'titulo' => 'Aniversário do titular',
                        'mensagem' => "Hoje é aniversário de {$nomeTitular}".($idade ? " ({$idade} anos)" : '').'. Envie uma mensagem de parabéns para fortalecer o relacionamento com o cliente.',
                        'tipo' => 'info',
                        'data_referencia' => $hoje->toDateString(),
                    ]);
                }
            });
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

    private function criarUnico(User $user, array $dados): Alerta
    {
        $chave = $dados['chave'] ?? null;

        if ($chave) {
            return Alerta::firstOrCreate([
                'user_id' => $user->id,
                'chave' => $chave,
            ], [
                'indicacao_id' => $dados['indicacao_id'] ?? null,
                'cliente_id' => $dados['cliente_id'] ?? null,
                'titulo' => $dados['titulo'],
                'mensagem' => $dados['mensagem'] ?? null,
                'tipo' => $dados['tipo'] ?? 'info',
                'status' => 'novo',
                'lido' => false,
                'data_referencia' => $dados['data_referencia'] ?? null,
            ]);
        }

        return Alerta::firstOrCreate([
            'user_id' => $user->id,
            'indicacao_id' => $dados['indicacao_id'] ?? null,
            'cliente_id' => $dados['cliente_id'] ?? null,
            'titulo' => $dados['titulo'],
            'data_referencia' => $dados['data_referencia'] ?? null,
        ], [
            'mensagem' => $dados['mensagem'] ?? null,
            'tipo' => $dados['tipo'] ?? 'info',
            'status' => 'novo',
            'lido' => false,
        ]);
    }
}
