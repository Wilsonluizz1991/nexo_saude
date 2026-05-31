<?php

namespace App\Mail;

use App\Models\Indicacao;
use App\Models\PreCadastro;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreCadastroCorrecaoSolicitadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Indicacao $indicacao,
        public PreCadastro $preCadastro,
        public User $corretor,
        public string $linkPreCadastro,
        public string $nomeDocumento,
        public ?string $motivoCorrecao,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Correção solicitada em seu pré-cadastro')
            ->view('emails.pre-cadastro-correcao-solicitada');
    }
}
