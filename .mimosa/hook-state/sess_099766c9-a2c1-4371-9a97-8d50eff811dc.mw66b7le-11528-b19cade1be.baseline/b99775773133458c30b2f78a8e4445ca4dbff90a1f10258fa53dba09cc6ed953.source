<?php

namespace App\Notifications;

use App\Models\Workspace;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationSentNotification extends Notification
{
    public function __construct(
        private readonly Workspace $workspace,
        private readonly string $token,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        // ponytail: stub — proper template in T-021
        $url = url("/dashboard/team/accept/{$this->token}");

        return (new MailMessage)
            ->subject("You've been invited to {$this->workspace->name}")
            ->line("You have been invited to join **{$this->workspace->name}**.")
            ->action('Accept Invitation', $url)
            ->line('This invitation expires in 7 days.');
    }
}
