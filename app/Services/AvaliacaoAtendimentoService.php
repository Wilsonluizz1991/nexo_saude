<?php

namespace App\Services;

use App\Models\AvaliacaoAtendimento;
use App\Models\Cliente;
use App\Models\Indicacao;
use Illuminate\Support\Str;

class AvaliacaoAtendimentoService
{
    public function obterOuCriar(Cliente $cliente, ?Indicacao $indicacao = null): AvaliacaoAtendimento
    {
        return AvaliacaoAtendimento::firstOrCreate(
            [
                'cliente_id' => $cliente->id,
                'indicacao_id' => $indicacao?->id,
            ],
            [
                'user_id' => $cliente->user_id,
                'token' => $this->gerarToken(),
                'status' => 'pendente',
            ]
        );
    }

    public function link(AvaliacaoAtendimento $avaliacao): string
    {
        return route('publico.avaliacoes.show', $avaliacao->token);
    }

    public function mediaDoCorretor(int $corretorId): array
    {
        $avaliacoes = AvaliacaoAtendimento::where('user_id', $corretorId)
            ->where('status', 'respondida')
            ->latest('respondida_em')
            ->get();

        if ($avaliacoes->isEmpty()) {
            return [
                'media' => null,
                'total' => 0,
                'premium' => false,
                'avaliacoes' => collect(),
            ];
        }

        $media = round($avaliacoes->map(fn ($avaliacao) => $avaliacao->media)->filter()->avg(), 1);

        return [
            'media' => $media,
            'total' => $avaliacoes->count(),
            'premium' => $avaliacoes->count() >= 1 && $media >= 4.9,
            'avaliacoes' => $avaliacoes,
        ];
    }

    private function gerarToken(): string
    {
        do {
            $token = Str::random(40);
        } while (AvaliacaoAtendimento::where('token', $token)->exists());

        return $token;
    }
}
