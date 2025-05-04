<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpcomingClassNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function via($notifiable)
    {
        return ['mail']; // You can add 'database' too if needed
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject('Upcoming Class Reminder')
        ->greeting('Hello ' . $notifiable->first_name)
        ->line("You have an upcoming class:")
        ->line('Subject: ' . $this->details['subject'])
        ->line('Time: ' . $this->details['time'])
        ->action('View Class Schedule', url(route('student.schedule')))
        ->line('This is an automated message from your student portal.'); // Using a markdown view (optional, but more flexible for styling)
   
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
