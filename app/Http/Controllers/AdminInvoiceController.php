<?php

namespace App\Http\Controllers;

use App\Actions\GenerateInvoice;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Invoice::with(['workspace', 'payment'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('workspace', fn ($w) => $w->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->paginate(25)->withQueryString();

        return view('admin.billing.invoices', compact('invoices'));
    }

    public function download(Invoice $invoice): StreamedResponse
    {
        abort_unless($invoice->pdf_path && Storage::exists($invoice->pdf_path), 404, 'PDF not found.');

        return Storage::download(
            $invoice->pdf_path,
            "{$invoice->number}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function regenerate(Invoice $invoice, GenerateInvoice $generateInvoice): RedirectResponse
    {
        $payment = $invoice->payment;

        if (! $payment) {
            return back()->with('error', 'No payment linked to this invoice.');
        }

        // Delete old PDF if present
        if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
            Storage::delete($invoice->pdf_path);
        }

        $invoice->update(['pdf_path' => null]);

        // Re-use GenerateInvoice but skip number allocation since it exists
        $pdf = Pdf::loadView(
            'billing.invoice-pdf',
            ['invoice' => $invoice->load(['workspace', 'payment.subscription.plan'])],
        );
        $path = "invoices/{$invoice->workspace_id}/{$invoice->number}.pdf";
        Storage::put($path, $pdf->output());
        $invoice->update(['pdf_path' => $path]);

        return back()->with('success', "PDF regenerated for {$invoice->number}.");
    }
}
