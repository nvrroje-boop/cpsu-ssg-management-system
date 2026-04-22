<?php

namespace App\Notifications;

use App\Support\AppUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected array $event,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Upcoming SSG Event')
            ->greeting('Hello!')
            ->line($this->event['event_title'])
            ->line('Location: '.$this->event['location'])
            ->action('View Events', AppUrl::route('student.events.index'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->event;
    }
}
