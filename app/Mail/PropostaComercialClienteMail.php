<?php

namespace App\Mail;

use App\Models\Indicacao;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PropostaComercialClienteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Indicacao $indicacao,
        public User $corretor,
        public Collection $propostas,
        public string $linkPublico,
    ) {
    }

    public function build(): self
    {
        $email = $this
            ->subject($this->propostas->count() === 1 ? 'Sua proposta comercial de plano de saúde' : 'Suas cotações de plano de saúde')
            ->view('emails.propostas.proposta-comercial-cliente');

        foreach ($this->propostas as $proposta) {
            if (! $proposta->arquivo_pdf_path || ! Storage::disk('public')->exists($proposta->arquivo_pdf_path)) {
                continue;
            }

            $email->attach(Storage::disk('public')->path($proposta->arquivo_pdf_path), [
                'as' => str($proposta->titulo ?: 'proposta-comercial')->slug()->append('.pdf')->toString(),
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}
