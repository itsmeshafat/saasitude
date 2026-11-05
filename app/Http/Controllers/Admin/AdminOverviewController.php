<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\View\View;

/**
 * Billing analytics overview.
 *
 * Chart series are computed from payments (actual collected revenue) and
 * subscriptions (new per month) over the trailing 12 months.
 */
class AdminOverviewController extends Controller
{
    /**
     * Billing analytics overview.
     *
     * Metric formulas (period = current calendar month):
     * - MRR  = SUM of active subscriptions' monthly-normalised amounts.
     *          Monthly amount = the plan price (minor units) of each active
     *          subscription; annual prices are divided by 10 (annual = 10x
     *          monthly per docs/04-billing.md). Amounts are joined from
     *          plan_prices matching the subscription's plan + currency +
     *          interval; when no matching price row exists the subscription
     *          contributes 0 (free/internal).
     * - ARR  = MRR x 12.
     * - Churn rate = subscriptions canceled in period / subscriptions active
     *          at the start of the period.
     * - Trial conversion = trials started in period that activated /
     *          trials started in period.
     */
    public function show(): View
    {
        $periodStart = now()->startOfMonth();

        $activeSubscriptions = Subscription::with('plan.prices')
            ->whereIn('status', ['active', 'trialing'])
            ->get();

        $mrrMinor = $activeSubscriptions
            ->filter(fn ($s) => $s->status === 'active')
            ->sum(function (Subscription $s) {
                $price = $s->plan->prices->firstWhere('interval', 'month');

                if (! $price) {
                    $annual = $s->plan->prices->firstWhere('interval', 'year');

                    return $annual ? intdiv($annual->amount_minor, 10) : 0;
                }

                return $price->amount_minor;
            });

        $activeAtPeriodStart = Subscription::where('created_at', '<', $periodStart)
            ->where('current_period_end', '>', $periodStart)
            ->count();

        $canceledThisPeriod = Subscription::whereNotNull('canceled_at')
            ->where('canceled_at', '>=', $periodStart)
            ->count();

        $churnRate = $activeAtPeriodStart > 0
            ? round($canceledThisPeriod / $activeAtPeriodStart * 100, 1)
            : 0.0;

        $trialsStarted = Subscription::whereNotNull('trial_ends_at')
            ->where('created_at', '>=', $periodStart)
            ->count();

        $trialConversions = Subscription::whereNotNull('trial_ends_at')
            ->where('created_at', '>=', $periodStart)
            ->where('status', 'active')
            ->count();

        $trialConversionRate = $trialsStarted > 0
            ? round($trialConversions / $trialsStarted * 100, 1)
            : 0.0;

        $failedPayments = Payment::where('status', 'failed')
            ->where('created_at', '>=', $periodStart)
            ->count();

        $byGateway = Subscription::query()
            ->whereIn('status', ['active', 'trialing'])
            ->selectRaw('gateway, count(*) as total')
            ->groupBy('gateway')
            ->pluck('total', 'gateway');

        // ---- Chart series (last 12 months) ----
        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());

        $revenueByMonth = $months->map(function ($month) {
            return (int) Payment::where('status', 'succeeded')
                ->whereBetween('created_at', [$month, $month->copy()->endOfMonth()])
                ->sum('amount');
        });

        $newSubsByMonth = $months->map(function ($month) {
            return Subscription::whereBetween('created_at', [$month, $month->copy()->endOfMonth()])->count();
        });

        $gatewayMix = Payment::where('status', 'succeeded')
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('gateway, sum(amount) as total')
            ->groupBy('gateway')
            ->pluck('total', 'gateway');

        // Month-over-month delta for the MRR proxy (this month's revenue vs last month's).
        $revenueThisMonth = $revenueByMonth->last();
        $revenueLastMonth = $revenueByMonth->slice(-2, 1)->first();
        $revenueDeltaPct = $revenueLastMonth > 0
            ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100, 1)
            : null;

        $labels = $months->map(fn ($m) => $m->format('M'))->all();

        return view('dashboard.overview', [
            'mrrMinor' => $mrrMinor,
            'arrMinor' => $mrrMinor * 12,
            'churnRate' => $churnRate,
            'trialConversionRate' => $trialConversionRate,
            'activeCount' => $activeSubscriptions->where('status', 'active')->count(),
            'trialCount' => $activeSubscriptions->where('status', 'trialing')->count(),
            'workspaceCount' => Workspace::count(),
            'failedPayments' => $failedPayments,
            'byGateway' => $byGateway,
            // Charts
            'revenueByMonth' => $revenueByMonth->all(),
            'newSubsByMonth' => $newSubsByMonth->all(),
            'gatewayMix' => $gatewayMix,
            'monthLabels' => $labels,
            'revenueDeltaPct' => $revenueDeltaPct,
            'revenueThisMonth' => $revenueThisMonth,
        ]);
    }
}
