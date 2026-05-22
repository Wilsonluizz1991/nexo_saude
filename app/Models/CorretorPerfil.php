<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorretorPerfil extends Model
{
    protected $table = 'corretor_perfis';

    protected $fillable = ['user_id', 'slug', 'foto_path', 'nome_publico', 'bio', 'especialidades', 'cidade_regiao', 'cidade', 'estado', 'anos_experiencia', 'publico_ativo'];

    protected function casts(): array
    {
        return ['especialidades' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
