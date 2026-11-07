<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Workspace;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Streaming CSV export of an admin table. Honors the same query filters
     * as the list pages (search/status/gateway) plus from/to dates.
     */
    public function csv(Request $request, string $type): StreamedResponse
    {
        $exporter = match ($type) {
            'payments' => fn (Request $r) => $this->paymentsQuery($r),
            'invoices' => fn (Request $r) => $this->invoicesQuery($r),
            'subscriptions' => fn (Request $r) => $this->subscriptionsQuery($r),
            'workspaces' => fn (Request $r) => $this->workspacesQuery($r),
            default => null,
        };

        abort_if($exporter === null, 404);

        // Payments carry financial data — same gate as the list page.
        if ($type === 'payments') {
            abort_unless($request->user()->hasPrivilege('billing.view'), 403);
        }

        $query = $exporter($request);
        $filename = $type.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query, $type): void {
            // echo-based streaming (no stream wrappers): rows are CSV-escaped
            // via the local csvLine() helper (RFC 4180 quoting).
            $headers = match ($type) {
                'payments' => ['id', 'workspace', 'gateway', 'gateway_payment_id', 'amount', 'currency', 'status', 'created_at'],
                'invoices' => ['number', 'workspace', 'amount', 'currency', 'status', 'issued_at'],
                'subscriptions' => ['id', 'workspace', 'plan', 'gateway', 'status', 'created_at', 'current_period_end'],
                'workspaces' => ['id', 'name', 'owner_email', 'members', 'plan', 'status', 'created_at'],
            };
            echo $this->csvLine($headers);

            $query->chunk(500, function ($rows) use ($type): void {
                foreach ($rows as $row) {
                    echo $this->csvLine(match ($type) {
                        'payments' => [$row->id, $row->workspace?->name, $row->gateway, $row->gateway_payment_id, $row->amount, $row->currency, $row->status, $row->created_at?->toIso8601String()],
                        'invoices' => [$row->number, $row->workspace?->name, $row->amount, $row->currency, $row->status, $row->issued_at?->toIso8601String() ?? $row->created_at?->toIso8601String()],
                        'subscriptions' => [$row->id, $row->workspace?->name, $row->plan?->name, $row->gateway, $row->status, $row->created_at?->toIso8601String(), $row->current_period_end?->toIso8601String()],
                        'workspaces' => [$row->id, $row->name, $row->owner?->email, $row->members_count, $row->latestSubscription?->plan?->name, $row->latestSubscription?->status, $row->created_at?->toIso8601String()],
                    });
                }
            });
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** Monthly billing report PDF (table-based; dompdf). */
    public function report(Request $request)
    {
        $month = $request->date('month', 'Y-m') ?? now()->startOfMonth();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $data = [
            'monthLabel' => $start->format('F Y'),
            'revenue' => (int) Payment::where('status', 'succeeded')->whereBetween('created_at', [$start, $end])->sum('amount'),
            'refunded' => (int) Payment::where('status', 'refunded')->whereBetween('created_at', [$start, $end])->sum('amount'),
            'failedCount' => Payment::where('status', 'failed')->whereBetween('created_at', [$start, $end])->count(),
            'newSubs' => Subscription::whereBetween('created_at', [$start, $end])->count(),
            'canceledSubs' => Subscription::whereNotNull('canceled_at')->whereBetween('canceled_at', [$start, $end])->count(),
            'activeSubs' => Subscription::whereIn('status', ['active', 'trialing'])->count(),
            'workspaceCount' => Workspace::count(),
            'gatewaySplit' => Payment::where('status', 'succeeded')->whereBetween('created_at', [$start, $end])
                ->selectRaw('gateway, sum(amount) as total, count(*) as count')->groupBy('gateway')->get(),
            'recentPayments' => Payment::with('workspace')->whereBetween('created_at', [$start, $end])->latest()->limit(20)->get(),
        ];

        $pdf = Pdf::loadView('admin.reports.monthly', $data);

        return $pdf->download("billing-report-{$start->format('Y-m')}.pdf");
    }

    private function paymentsQuery(Request $request)
    {
        return Payment::query()
            ->with(['workspace', 'subscription.plan'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->query('search');
                $q->where(function ($w) use ($search) {
                    $w->where('gateway_payment_id', 'like', "%{$search}%")
                        ->orWhereHas('workspace', fn ($ws) => $ws->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest();
    }

    private function invoicesQuery(Request $request)
    {
        return Invoice::query()
            ->with('workspace')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->query('search');
                $q->where(function ($w) use ($search) {
                    $w->where('number', 'like', "%{$search}%")
                        ->orWhereHas('workspace', fn ($ws) => $ws->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest();
    }

    private function subscriptionsQuery(Request $request)
    {
        return Subscription::query()
            ->with(['workspace.owner', 'plan'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('gateway'), fn ($q, $gateway) => $q->where('gateway', $gateway))
            ->latest('id');
    }

    private function workspacesQuery(Request $request)
    {
        return Workspace::query()
            ->withCount('members')
            ->with(['owner', 'latestSubscription.plan'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->query('search');
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhereHas('owner', fn ($o) => $o->where('email', 'like', "%{$search}%"));
                });
            })
            ->latest();
    }

    /** RFC 4180 CSV line: quote fields containing delimiters, quotes or newlines. */
    private function csvLine(array $fields): string
    {
        return implode(',', array_map(function ($field): string {
            $field = (string) ($field ?? '');

            if (preg_match('/[",\r\n]/', $field)) {
                return '"'.str_replace('"', '""', $field).'"';
            }

            return $field;
        }, $fields))."\r\n";
    }
}
