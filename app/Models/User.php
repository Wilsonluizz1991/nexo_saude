<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'telefone', 'perfil', 'avatar_path', 'password', 'ultimo_login_em', 'ultimo_ip',
    'timezone', 'idioma', 'formato_data', 'receber_alertas_email', 'receber_notificacoes_aniversario',
    'receber_notificacoes_renovacao', 'receber_notificacoes_tarefas',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_login_em' => 'datetime',
            'password' => 'hashed',
            'receber_alertas_email' => 'boolean',
            'receber_notificacoes_aniversario' => 'boolean',
            'receber_notificacoes_renovacao' => 'boolean',
            'receber_notificacoes_tarefas' => 'boolean',
        ];
    }

    public function assinatura()
    {
        return $this->hasOne(Assinatura::class);
    }

    public function corretorPerfil()
    {
        return $this->hasOne(CorretorPerfil::class);
    }

    public function sessoesUsuario()
    {
        return $this->hasMany(SessaoUsuario::class, 'usuario_id');
    }

    public function metasMensais()
    {
        return $this->hasMany(CorretorMetaMensal::class);
    }

    public function avaliacoesAtendimento()
    {
        return $this->hasMany(AvaliacaoAtendimento::class);
    }
}
