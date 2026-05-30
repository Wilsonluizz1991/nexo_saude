<?php

namespace App\Models;

use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'telefone',
    'perfil',
    'avatar_path',
    'password',
    'ultimo_login_em',
    'ultimo_ip',
    'timezone',
    'idioma',
    'formato_data',
    'receber_alertas_email',
    'receber_notificacoes_aniversario',
    'receber_notificacoes_renovacao',
    'receber_notificacoes_tarefas',
    'is_admin', 
    'admin_since',
    'blocked_at',

    // Billing / Asaas
    'asaas_customer_id',
    'asaas_subscription_id',
    'billing_status',
    'billing_payment_method',
    'billing_amount',
    'trial_ends_at',
    'next_billing_at',
    'subscription_started_at',
    'subscription_canceled_at',
    'billing_suspended_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmailContract
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
            'is_admin' => 'boolean',
            'admin_since' => 'datetime',
            'blocked_at' => 'datetime',

            'receber_alertas_email' => 'boolean',
            'receber_notificacoes_aniversario' => 'boolean',
            'receber_notificacoes_renovacao' => 'boolean',
            'receber_notificacoes_tarefas' => 'boolean',

            // Billing / Asaas
            'billing_amount' => 'decimal:2',
            'trial_ends_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'subscription_started_at' => 'datetime',
            'subscription_canceled_at' => 'datetime',
            'billing_suspended_at' => 'datetime',
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

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
