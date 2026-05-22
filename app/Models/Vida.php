<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vida extends Model
{
    protected $table = 'vidas';

    protected $fillable = ['pre_cadastro_id', 'tipo', 'vinculo_beneficiario_id', 'ordem', 'nome', 'parentesco', 'sexo', 'data_nascimento', 'gestante', 'cpf', 'cargo', 'telefone', 'email'];

    protected function casts(): array
    {
        return ['data_nascimento' => 'date', 'gestante' => 'boolean'];
    }

    public function documentosSolicitados() { return $this->hasMany(DocumentoSolicitado::class); }
    public function documentosObrigatorios() { return $this->hasMany(DocumentoObrigatorioPreCadastro::class, 'vida_proposta_id'); }
    public function vinculoBeneficiario() { return $this->belongsTo(Vida::class, 'vinculo_beneficiario_id'); }
    public function dependentesVinculados() { return $this->hasMany(Vida::class, 'vinculo_beneficiario_id'); }
}
