<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateInvoice
{
    public function execute(Payment $payment): Invoice
    {
        return DB::transaction(function () use ($payment): Invoice {
            $existing = Invoice::where('payment_id', $payment->id)->first();
            if ($existing) {
                return $existing;
            }

            $number = $this->nextInvoiceNumber();

            $invoice = Invoice::create([
                'workspace_id' => $payment->workspace_id,
                'payment_id' => $payment->id,
                'subscription_id' => $payment->subscription_id,
                'number' => $number,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => 'paid',
                'line_items' => [
                    [
                        'description' => $payment->subscription?->plan->name ?? 'Subscription',
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                    ],
                ],
                'issued_at' => now(),
            ]);

            $pdfPath = $this->generatePdf($invoice->load(['workspace', 'payment.subscription.plan']));
            $invoice->update(['pdf_path' => $pdfPath]);

            return $invoice;
        });
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->year;

        // Locked counter via DB advisory lock substitute: increment a settings counter
        $counter = DB::table('settings')
            ->where('key', "invoice_counter_{$year}")
            ->lockForUpdate()
            ->value('value');

        $next = $counter ? (int) $counter + 1 : 1;

        DB::table('settings')->upsert(
            [['key' => "invoice_counter_{$year}", 'value' => (string) $next, 'group' => 'billing']],
            ['key'],
            ['value'],
        );

        return sprintf('INV-%d-%06d', $year, $next);
    }

    private function generatePdf(Invoice $invoice): string
    {
        $pdf = Pdf::loadView('billing.invoice-pdf', ['invoice' => $invoice]);

        $path = "invoices/{$invoice->workspace_id}/{$invoice->number}.pdf";
        Storage::put($path, $pdf->output());

        return $path;
    }
}
