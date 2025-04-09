<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class CustomResetPassword extends Notification
{
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {

        $greetingName = $notifiable->admin ? $notifiable->admin->first_name : ($notifiable->student ? $notifiable->student->first_name : 'Valued User');
        $resetUrl = url(route('password.reset', [
            'token' => $this->token, 
            'email' => $notifiable->email
        ], false));

        return (new MailMessage)
        ->subject('Reset Your Password - CIT Portal')
        ->greeting('Hello, ' . $greetingName . '!')  // Directly access the name for greeting
        ->line('We received a request to reset your password Click the button below to proceed.')
            ->action('Reset Password', $resetUrl)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request this, please ignore this email.')
            ->salutation('From, ' . config('app.name'))
            ->markdown('emails.auth.reset-password', ['resetUrl' => $resetUrl, 'greetingName' => $greetingName]);
    }
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
