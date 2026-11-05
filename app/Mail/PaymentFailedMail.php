<?php

namespace App\Mail;

use App\Models\Subscription;

class PaymentFailedMail extends TransactionalMail
{
    public function __construct(Subscription $subscription)
    {
        parent::__construct(
            subjectLine: 'Payment failed - action required',
            title: 'Payment failed',
            lines: [
                'A payment for <strong>'.$subscription->workspace->name.'</strong> failed.',
                'Update your payment method to avoid an interruption in service.',
            ],
            actionUrl: route('dashboard.billing'),
            actionLabel: 'Update payment method',
            footer: null,
        );
    }
}
