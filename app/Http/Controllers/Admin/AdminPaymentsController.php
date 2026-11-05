<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\GatewayManager;
use HasinHayder\Tyro\Support\TyroAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AdminPaymentsController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->query('search');

        $payments = Payment::query()
            ->with(['workspace.owner', 'subscription.plan'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('gateway_payment_id', 'like', "%{$search}%")
                        ->orWhereHas('workspace', fn ($w) => $w->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.payments-admin', [
            'payments' => $payments,
            'search' => $search,
            'statuses' => ['pending', 'succeeded', 'failed', 'refunded'],
            'filterStatus' => (string) $request->query('status', ''),
        ]);
    }

    /**
     * Issue a full refund for a payment. Requires the billing.refund
     * privilege, is blocked while impersonating, calls the gateway where
     * supported, and always writes an audit entry.
     */
    public function refund(Request $request, Payment $payment, GatewayManager $gateways): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('billing.refund'), 403);
        abort_if(session('impersonator_id'), 403, 'Refunds are blocked while impersonating.');

        if ($payment->status !== 'succeeded') {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Only succeeded payments can be refunded.');
        }

        try {
            $gateway = $gateways->resolve($payment->gateway);

            if (in_array('refunds', $gateway->capabilities())) {
                // Gateway-side refund (Stripe/Paddle). Adapters throw on failure.
                $gateway->refundPayment($payment);
            }

            $payment->update(['status' => 'refunded']);
        } catch (RuntimeException $e) {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Gateway refund failed: '.$e->getMessage());
        }

        TyroAudit::log('payment.refunded', $payment, ['status' => 'succeeded'], [
            'status' => 'refunded',
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'admin_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment refunded.');
    }
}
