<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuthorizeUserEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $token
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $app_name = config("app.name");
        return (new MailMessage)
            ->subject("Email verification")
            ->line("$app_name received a request to verify your account, $notifiable->name.")
            ->line("Follow this link to finish setting up your account:")
            ->action("Verify Email", route("email-verification", ["token" => $this->token]))
            ->line('This link will expire in 15 minutes.')
            ->line("If you didn't request for this link, you can safely ignore this mail.");
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
