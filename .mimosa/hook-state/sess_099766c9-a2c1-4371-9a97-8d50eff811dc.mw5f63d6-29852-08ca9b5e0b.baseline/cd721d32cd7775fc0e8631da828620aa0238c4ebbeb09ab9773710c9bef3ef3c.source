<?php

namespace App\Mail;

use App\Models\Subscription;

class TrialEnding extends TransactionalMail
{
    public function __construct(Subscription $model)
    {
        parent::__construct(
            subjectLine: 'Your trial ends in 3 days',
            title: 'Trial ending soon',
            lines: ['Your <strong>'.$model->plan->name.'</strong> trial for <strong>'.$model->workspace->name.'</strong> ends on <strong>'.optional($model->current_period_end)->format('M j, Y').'</strong>.', 'Choose a plan now to keep access to all features.'],
            actionUrl: route('dashboard.billing'),
            actionLabel: 'Choose a plan',
            footer: null,
        );
    }
}
