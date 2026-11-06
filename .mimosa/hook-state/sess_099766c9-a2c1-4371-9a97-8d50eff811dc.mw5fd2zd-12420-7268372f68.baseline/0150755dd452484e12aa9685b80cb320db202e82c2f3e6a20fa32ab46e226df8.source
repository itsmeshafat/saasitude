<?php

namespace App\Mail;

use App\Models\Subscription;

class SubscriptionResumed extends TransactionalMail
{
    public function __construct(Subscription $model)
    {
        parent::__construct(
            subjectLine: 'Subscription resumed',
            title: 'Subscription resumed',
            lines: ['Your <strong>'.$model->plan->name.'</strong> subscription for <strong>'.$model->workspace->name.'</strong> has been resumed.', 'Your next renewal is on <strong>'.optional($model->current_period_end)->format('M j, Y').'</strong>.'],
            actionUrl: route('dashboard.billing'),
            actionLabel: 'View billing',
            footer: null,
        );
    }
}
