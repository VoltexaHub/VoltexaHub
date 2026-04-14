<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyDigest extends Notification
{
    use Queueable;

    /** @param array<int, array{type:string, line:string, url:?string}> $items */
    public function __construct(public array $items) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Your weekly digest from '.config('app.name'))
            ->greeting("Hi {$notifiable->name},")
            ->line('Here\'s what you missed this week on '.config('app.name').':');

        foreach ($this->items as $item) {
            $mail->line('• '.$item['line']);
        }

        return $mail
            ->action('Open '.config('app.name'), url('/'))
            ->line('You can change or turn off digests any time in your notification preferences.');
    }
}
