<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmployeeInvitation extends Notification
{
    private const TRANSLATIONS = [
        'ca' => [
            'subject'   => 'Benvingut/da — Configura la teva contrasenya',
            'greeting'  => 'Hola, :name!',
            'intro'     => "S'ha creat un compte d'accés per a tu.",
            'action'    => 'Crear contrasenya',
            'expiry'    => 'Aquest enllaç caducarà en 60 minuts.',
            'ignore'    => 'Si no esperes aquest correu, pots ignorar-lo.',
            'salutation' => 'Salutacions,',
        ],
        'es' => [
            'subject'   => 'Bienvenido/a — Configura tu contraseña',
            'greeting'  => '¡Hola, :name!',
            'intro'     => 'Se ha creado una cuenta de acceso para ti.',
            'action'    => 'Crear contraseña',
            'expiry'    => 'Este enlace caducará en 60 minutos.',
            'ignore'    => 'Si no esperabas este correo, puedes ignorarlo.',
            'salutation' => 'Saludos,',
        ],
        'en' => [
            'subject'   => 'Welcome — Set up your password',
            'greeting'  => 'Hi, :name!',
            'intro'     => 'An access account has been created for you.',
            'action'    => 'Set password',
            'expiry'    => 'This link will expire in 60 minutes.',
            'ignore'    => "If you weren't expecting this email, you can ignore it.",
            'salutation' => 'Regards,',
        ],
    ];

    public function __construct(private string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->locale ?? 'ca';
        $t      = self::TRANSLATIONS[$locale] ?? self::TRANSLATIONS['ca'];

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $url         = $frontendUrl
            . '/set-password?token=' . $this->token
            . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject($t['subject'])
            ->greeting(str_replace(':name', $notifiable->name, $t['greeting']))
            ->line($t['intro'])
            ->action($t['action'], $url)
            ->line($t['expiry'])
            ->line($t['ignore'])
            ->salutation($t['salutation']);
    }
}
