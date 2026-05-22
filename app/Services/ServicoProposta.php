<?php

namespace App\Services;

use App\Models\Indicacao;
use App\Models\Proposta;
use Illuminate\Http\UploadedFile;

class ServicoProposta
{
    public function anexar(Indicacao $indicacao, array $dados, UploadedFile $arquivo): Proposta
    {
        $proposta = $indicacao->propostas()->create([
            'titulo' => $dados['titulo'],
            'operadora_id' => $dados['operadora_id'] ?? null,
            'valor_mensal' => $dados['valor_mensal'] ?? null,
            'quantidade_vidas' => $dados['quantidade_vidas'] ?? $indicacao->quantidade_vidas,
            'validade' => $dados['validade'] ?? null,
            'observacoes' => $dados['observacoes'] ?? null,
            'arquivo_pdf_path' => $arquivo->store('propostas', 'public'),
            'status' => 'enviada',
        ]);

        $indicacao->update(['etapa' => 'propostas', 'status' => 'proposta_enviada']);
        $indicacao->timelineEventos()->create(['titulo' => 'Proposta enviada', 'descricao' => 'PDF da proposta anexado pelo corretor.']);

        return $proposta;
    }
}
