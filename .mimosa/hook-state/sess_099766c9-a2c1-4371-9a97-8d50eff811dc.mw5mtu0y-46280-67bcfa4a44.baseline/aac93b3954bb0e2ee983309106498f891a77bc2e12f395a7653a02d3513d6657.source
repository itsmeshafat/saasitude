<?php

namespace App\Http\Middleware;

use Closure;
use HasinHayder\TyroDashboard\Services\AdminNotice;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Shows an admin bar notice when the current workspace trial ends within 3 days. */
class TrialEndingNotice
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $subscription = $user->currentWorkspace()?->activeSubscription();

            if ($subscription !== null
                && $subscription->status === 'trialing'
                && $subscription->trial_ends_at !== null
                && $subscription->trial_ends_at->diffInDays(now()) <= 3) {
                AdminNotice::show(
                    'Your trial ends in '.$subscription->trial_ends_at->diffForHumans().'. <a href="'.route('dashboard.plans').'">Choose a plan</a> to keep access.',
                    '#f59e0b',
                    '#ffffff',
                    'center',
                );
            }
        }

        return $next($request);
    }
}
