<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmployeeInvitation extends Notification
{
    public function __construct(private string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $url         = $frontendUrl
            . '/set-password?token=' . $this->token
            . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Benvingut/da — Configura la teva contrasenya')
            ->greeting('Hola, ' . $notifiable->name . '!')
            ->line("S'ha creat un compte d'accés per a tu.")
            ->action('Crear contrasenya', $url)
            ->line('Aquest enllaç caducarà en 60 minuts.')
            ->line('Si no esperes aquest correu, pots ignorar-lo.');
    }
}
