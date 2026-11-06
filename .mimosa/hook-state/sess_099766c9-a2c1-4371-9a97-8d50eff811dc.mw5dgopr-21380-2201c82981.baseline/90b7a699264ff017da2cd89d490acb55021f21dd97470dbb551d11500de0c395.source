<?php

namespace App\Http\Middleware;

use Closure;
use HasinHayder\TyroDashboard\Services\AdminNotice;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Shows a payment banner when the current workspace subscription is past due. */
class PastDueBanner
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $subscription = $user->currentWorkspace()?->activeSubscription();

            if ($subscription !== null && $subscription->status === 'past_due') {
                AdminNotice::show(
                    'Your last payment failed. <a href="'.route('dashboard.billing.payment-methods').'">Update your payment method</a> to restore full access.',
                    '#dc2626',
                    '#ffffff',
                    'center',
                );
            }
        }

        return $next($request);
    }
}
