<?php

namespace App\Mail;

use App\Models\Subscription;

class TrialStarted extends TransactionalMail
{
    public function __construct(Subscription $model)
    {
        parent::__construct(
            subjectLine: 'Your trial has started',
            title: 'Trial started',
            lines: ['Your <strong>'.$model->plan->name.'</strong> trial for <strong>'.$model->workspace->name.'</strong> is active until <strong>'.optional($model->trial_ends_at)->format('M j, Y').'.</strong>.'],
            actionUrl: route('dashboard.billing'),
            actionLabel: 'View billing',
            footer: 'No card is required during the trial.',
        );
    }
}
