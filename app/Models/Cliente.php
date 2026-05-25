<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = ['indicacao_id', 'user_id', 'nome', 'email', 'telefone', 'inicio_vigencia', 'valor_mensal', 'status'];

    protected function casts(): array
    {
        return [
            'inicio_vigencia' => 'date',
            'valor_mensal' => 'decimal:2',
        ];
    }

    public function indicacao() { return $this->belongsTo(Indicacao::class); }
    public function contratos() { return $this->hasMany(Contrato::class); }
    public function dependentes() { return $this->hasMany(Dependente::class); }
    public function avaliacoesAtendimento() { return $this->hasMany(AvaliacaoAtendimento::class); }
}
