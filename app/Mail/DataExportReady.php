<?php

namespace App\Mail;

use App\Models\User;

class DataExportReady extends TransactionalMail
{
    public function __construct(User $model)
    {
        parent::__construct(
            subjectLine: 'Your data export is ready',
            title: 'Data export ready',
            lines: ['Your account data export has finished processing.', 'The download link expires in 7 days.'],
            actionUrl: url('/dashboard/data-privacy'),
            actionLabel: 'Download export',
            footer: null,
        );
    }
}
