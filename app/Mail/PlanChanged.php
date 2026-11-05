<?php

namespace App\Mail;

use App\Models\Subscription;

class PlanChanged extends TransactionalMail
{
    public function __construct(Subscription $model)
    {
        parent::__construct(
            subjectLine: 'Your plan has changed',
            title: 'Plan changed',
            lines: ['Your workspace <strong>'.$model->workspace->name.'</strong> is now on the <strong>'.$model->plan->name.'</strong> plan.', 'Prorated charges appear on your next invoice.'],
            actionUrl: route('dashboard.billing'),
            actionLabel: 'View billing',
            footer: null,
        );
    }
}
