<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicacao extends Model
{
    protected $table = 'indicacoes';

    protected $fillable = ['user_id', 'origem', 'nome_cliente', 'telefone', 'email', 'tipo_plano', 'quantidade_vidas', 'cidade', 'estado', 'possui_preferencias', 'operadoras_preferidas', 'hospitais_preferidos', 'faixa_valor_mensal', 'etapa', 'status', 'observacoes'];

    protected function casts(): array
    {
        return [
            'possui_preferencias' => 'boolean',
            'operadoras_preferidas' => 'array',
            'hospitais_preferidos' => 'array',
        ];
    }

    public function propostas() { return $this->hasMany(Proposta::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function preCadastro() { return $this->hasOne(PreCadastro::class); }
    public function implantacao() { return $this->hasOne(Implantacao::class); }
    public function cliente() { return $this->hasOne(Cliente::class); }
    public function timelineEventos() { return $this->hasMany(TimelineEvento::class); }
    public function tarefas() { return $this->hasMany(Tarefa::class); }
}
