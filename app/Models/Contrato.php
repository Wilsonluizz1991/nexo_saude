<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = 'contratos';

    protected $fillable = ['usuario_id', 'cliente_id', 'proposta_id', 'operadora_id', 'tipo_contrato', 'status', 'quantidade_vidas', 'valor_mensal', 'percentual_comissao', 'valor_comissao_prevista', 'valor_comissao_real', 'numero_contrato', 'iniciado_em', 'renovacao_em', 'reajuste_em', 'cancelado_em', 'motivo_cancelamento', 'observacoes'];

    protected function casts(): array
    {
        return [
            'valor_mensal' => 'decimal:2',
            'percentual_comissao' => 'decimal:2',
            'valor_comissao_prevista' => 'decimal:2',
            'valor_comissao_real' => 'decimal:2',
            'iniciado_em' => 'date',
            'renovacao_em' => 'date',
            'reajuste_em' => 'date',
            'cancelado_em' => 'date',
        ];
    }

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function operadora() { return $this->belongsTo(Operadora::class); }
    public function proposta() { return $this->belongsTo(Proposta::class); }
}
