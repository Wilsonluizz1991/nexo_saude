<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvaliacaoAtendimento extends Model
{
    protected $table = 'avaliacoes_atendimento';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'indicacao_id',
        'token',
        'status',
        'nota_atendimento',
        'nota_clareza',
        'nota_agilidade',
        'nota_confianca',
        'nota_recomendacao',
        'comentario',
        'respondida_em',
    ];

    protected function casts(): array
    {
        return [
            'nota_atendimento' => 'integer',
            'nota_clareza' => 'integer',
            'nota_agilidade' => 'integer',
            'nota_confianca' => 'integer',
            'nota_recomendacao' => 'integer',
            'respondida_em' => 'datetime',
        ];
    }

    public function corretor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function indicacao()
    {
        return $this->belongsTo(Indicacao::class);
    }

    public function getMediaAttribute(): ?float
    {
        if ($this->status !== 'respondida') {
            return null;
        }

        $notas = [
            $this->nota_atendimento,
            $this->nota_clareza,
            $this->nota_agilidade,
            $this->nota_confianca,
            $this->nota_recomendacao,
        ];

        if (collect($notas)->contains(fn ($nota) => ! is_numeric($nota))) {
            return null;
        }

        return round(array_sum($notas) / count($notas), 1);
    }
}
