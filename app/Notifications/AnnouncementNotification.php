<?php

namespace App\Notifications;

use App\Support\AppUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected array $announcement,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New SSG Announcement')
            ->greeting('Hello!')
            ->line($this->announcement['title'])
            ->line($this->announcement['description'])
            ->action('Open Student Portal', AppUrl::route('student.announcements.index'));
    }

    public function toArray(object $notifiable): array
    {
        return $this->announcement;
    }
}
