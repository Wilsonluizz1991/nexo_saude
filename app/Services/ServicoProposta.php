<?php

namespace App\Services;

use App\Mail\PropostaComercialClienteMail;
use App\Models\Indicacao;
use App\Models\Proposta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ServicoProposta
{
    public function anexar(Indicacao $indicacao, array $dados, UploadedFile|array $arquivos): Collection
    {
        $arquivos = collect(is_array($arquivos) ? $arquivos : [$arquivos])->filter();
        $grupoToken = $this->gerarTokenUnico('public_group_token');

        $propostas = DB::transaction(function () use ($indicacao, $dados, $arquivos, $grupoToken) {
            $criadas = collect();
            $total = $arquivos->count();

            foreach ($arquivos->values() as $indice => $arquivo) {
                $titulo = $dados['titulo'];

                if ($total > 1) {
                    $titulo .= ' '.($indice + 1);
                }

                $criadas->push($indicacao->propostas()->create([
                    'titulo' => $titulo,
                    'operadora_id' => $dados['operadora_id'] ?? null,
                    'valor_mensal' => $dados['valor_mensal'] ?? null,
                    'quantidade_vidas' => PlanoSaudeService::normalizarQuantidadeVidas(
                        $indicacao->tipo_plano,
                        $dados['quantidade_vidas'] ?? $indicacao->quantidade_vidas
                    ),
                    'validade' => $dados['validade'] ?? null,
                    'observacoes' => $dados['observacoes'] ?? null,
                    'arquivo_pdf_path' => $arquivo->store('propostas', 'public'),
                    'public_token' => $this->gerarTokenUnico('public_token'),
                    'public_group_token' => $grupoToken,
                    'status' => 'enviada',
                ]));
            }

            $indicacao->update(['etapa' => 'propostas', 'status' => 'proposta_enviada']);

            return $criadas;
        });

        $propostas->each->load('operadora');
        $resultadoEmail = $this->enviarEmailCliente($indicacao, $propostas, $this->linkPublico($propostas));
        $this->registrarTimeline($indicacao, $propostas->count(), $resultadoEmail);

        return $propostas;
    }

    public function linkPublico(Collection $propostas): string
    {
        $token = $propostas->first()?->public_group_token;

        return route('publico.propostas.show', ['token' => $token]);
    }

    private function enviarEmailCliente(Indicacao $indicacao, Collection $propostas, string $linkPublico): string
    {
        $indicacao->loadMissing('user');
        $corretor = $indicacao->user;

        if (! $indicacao->email) {
            Log::info('E-mail de proposta comercial não enviado: cliente sem e-mail.', [
                'indicacao_id' => $indicacao->id,
                'propostas' => $propostas->pluck('id')->all(),
            ]);

            return 'sem_email';
        }

        if (! $corretor) {
            Log::warning('E-mail de proposta comercial não enviado: corretor ausente.', [
                'indicacao_id' => $indicacao->id,
                'propostas' => $propostas->pluck('id')->all(),
            ]);

            return 'falha';
        }

        try {
            Mail::to($indicacao->email)->send(new PropostaComercialClienteMail($indicacao, $corretor, $propostas, $linkPublico));
            Proposta::whereIn('id', $propostas->pluck('id'))->update(['enviado_email_em' => now()]);

            return 'enviado';
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar e-mail de proposta comercial.', [
                'indicacao_id' => $indicacao->id,
                'propostas' => $propostas->pluck('id')->all(),
                'message' => $e->getMessage(),
            ]);

            return 'falha';
        }
    }

    private function registrarTimeline(Indicacao $indicacao, int $quantidade, string $resultadoEmail): void
    {
        $plural = $quantidade > 1;

        $descricao = match ($resultadoEmail) {
            'enviado' => $plural
                ? 'Cotações anexadas e enviadas por e-mail ao cliente.'
                : 'Proposta anexada e enviada por e-mail ao cliente.',
            'sem_email' => $plural
                ? 'Cotações anexadas. Cliente sem e-mail cadastrado para envio automático.'
                : 'Proposta anexada. Cliente sem e-mail cadastrado para envio automático.',
            default => $plural
                ? 'Cotações anexadas. Não foi possível enviar o e-mail automaticamente.'
                : 'Proposta anexada. Não foi possível enviar o e-mail automaticamente.',
        };

        $indicacao->timelineEventos()->create([
            'titulo' => $plural ? 'Cotações enviadas' : 'Proposta enviada',
            'descricao' => $descricao,
        ]);
    }

    private function gerarTokenUnico(string $coluna): string
    {
        do {
            $token = Str::random(48);
        } while (Proposta::where($coluna, $token)->exists());

        return $token;
    }
}
