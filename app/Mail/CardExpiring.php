<?php

namespace App\Mail;

use App\Models\Subscription;

class CardExpiring extends TransactionalMail
{
    public function __construct(Subscription $model)
    {
        parent::__construct(
            subjectLine: 'Your card is expiring soon',
            title: 'Card expiring soon',
            lines: ['The card used for <strong>'.$model->workspace->name.'</strong> expires soon.', 'Update your card to avoid failed renewals.'],
            actionUrl: route('dashboard.billing.portal'),
            actionLabel: 'Update card',
            footer: null,
        );
    }
}
