<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposta extends Model
{
    protected $table = 'propostas';

    protected $fillable = ['indicacao_id', 'operadora_id', 'cliente_id', 'titulo', 'arquivo_pdf_path', 'public_token', 'public_group_token', 'validade', 'quantidade_vidas', 'valor_mensal', 'percentual_comissao', 'valor_comissao_prevista', 'observacoes', 'status', 'enviado_email_em'];

    protected function casts(): array
    {
        return [
            'validade' => 'date',
            'valor_mensal' => 'decimal:2',
            'percentual_comissao' => 'decimal:2',
            'valor_comissao_prevista' => 'decimal:2',
            'enviado_email_em' => 'datetime',
        ];
    }

    public function operadora()
    {
        return $this->belongsTo(Operadora::class);
    }

    public function indicacao()
    {
        return $this->belongsTo(Indicacao::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
