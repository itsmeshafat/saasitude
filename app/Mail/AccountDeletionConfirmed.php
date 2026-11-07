<?php

namespace App\Mail;

class AccountDeletionConfirmed extends TransactionalMail
{
    public function __construct()
    {
        parent::__construct(
            subjectLine: 'Your account has been deleted',
            title: 'Account deleted',
            lines: ['Your account and personal data have been deleted as requested.', 'Data retained for billing compliance will be purged after 30 days.'],
            actionUrl: null,
            actionLabel: null,
            footer: 'This action cannot be undone.',
        );
    }
}
