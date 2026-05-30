<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresIn = (int) config('auth.verification.expire', 60);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes($expiresIn),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('Confirme seu e-mail na Nexo Saude')
            ->view('emails.verify-email', [
                'url' => $url,
                'user' => $notifiable,
                'expiresIn' => $expiresIn,
            ]);
    }
}
