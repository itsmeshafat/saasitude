<?php

namespace App\Mail;

use App\Models\Payment;

class RefundIssued extends TransactionalMail
{
    public function __construct(Payment $model)
    {
        parent::__construct(
            subjectLine: 'Refund issued',
            title: 'Refund issued',
            lines: ['We have refunded <strong>'.strtoupper($model->currency).' '.number_format($model->amount / 100, 2).'</strong> to your original payment method.', 'It may take 5-10 business days to appear on your statement.'],
            actionUrl: route('dashboard.billing'),
            actionLabel: 'View payments',
            footer: null,
        );
    }
}
