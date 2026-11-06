<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalReminder extends TransactionalMail
{
    public function __construct(
        public readonly Subscription $subscription,
        public readonly int $daysUntilRenewal,
    ) {
        parent::__construct(
            subjectLine: "Your subscription renews in {$daysUntilRenewal} day(s)",
            title: 'Subscription renewing soon',
            lines: [
                'Your <strong>'.$subscription->plan->name.'</strong> subscription for <strong>'.$subscription->workspace->name.'</strong> renews in <strong>'.$daysUntilRenewal.' day(s)</strong> on '.$subscription->current_period_end?->format('F j, Y').'.',
                'To keep your service running, please make sure your payment method is up to date.',
            ],
            actionUrl: config('app.url').'/dashboard/billing',
            actionLabel: 'Review billing',
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.transactional');
    }
}
