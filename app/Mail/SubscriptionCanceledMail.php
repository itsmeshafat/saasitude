<?php

namespace App\Mail;

use App\Models\Subscription;

class SubscriptionCanceledMail extends TransactionalMail
{
    public function __construct(Subscription $model)
    {
        parent::__construct(
            subjectLine: 'Subscription canceled',
            title: 'Subscription canceled',
            lines: ['Your <strong>'.$model->plan->name.'</strong> subscription for <strong>'.$model->workspace->name.'</strong> will be canceled on <strong>'.optional($model->current_period_end)->format('M j, Y').'</strong>.', 'You keep full access until then. You can resume any time before that date.'],
            actionUrl: route('dashboard.billing'),
            actionLabel: 'View billing',
            footer: null,
        );
    }
}
