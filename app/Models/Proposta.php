<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposta extends Model
{
    protected $table = 'propostas';

    protected $fillable = ['indicacao_id', 'operadora_id', 'cliente_id', 'titulo', 'arquivo_pdf_path', 'validade', 'quantidade_vidas', 'valor_mensal', 'observacoes', 'status'];

    protected function casts(): array
    {
        return ['validade' => 'date', 'valor_mensal' => 'decimal:2'];
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
