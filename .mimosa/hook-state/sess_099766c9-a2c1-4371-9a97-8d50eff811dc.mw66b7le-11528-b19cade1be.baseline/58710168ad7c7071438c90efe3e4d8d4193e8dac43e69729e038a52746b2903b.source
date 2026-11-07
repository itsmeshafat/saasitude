<?php

namespace App\Mail;

use App\Models\Payment;

class PaymentSucceeded extends TransactionalMail
{
    public function __construct(Payment $model)
    {
        parent::__construct(
            subjectLine: 'Your receipt from '.config('app.name'),
            title: 'Payment received',
            lines: ['Thanks! We received your payment of <strong>'.strtoupper($model->currency).' '.number_format($model->amount / 100, 2).'</strong>.', 'Your invoice is available in your billing dashboard.'],
            actionUrl: route('dashboard.billing.invoices'),
            actionLabel: 'View invoice',
            footer: null,
        );
    }
}
