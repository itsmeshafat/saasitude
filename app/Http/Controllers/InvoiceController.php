<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $workspace = $request->user()->currentWorkspace();

        abort_unless($workspace !== null, 403);

        $invoices = Invoice::where('workspace_id', $workspace->id)
            ->latest()
            ->paginate(15);

        $paidThisYear = (int) Invoice::where('workspace_id', $workspace->id)
            ->where('status', 'paid')
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return view('dashboard.billing.invoices', compact('invoices', 'workspace', 'paidThisYear'));
    }

    public function download(Request $request, Invoice $invoice): StreamedResponse|Response
    {
        $workspace = $request->user()->currentWorkspace();

        abort_unless($workspace && $invoice->workspace_id === $workspace->id, 403);

        if (! $invoice->pdf_path || ! Storage::exists($invoice->pdf_path)) {
            abort(404, 'Invoice PDF not available.');
        }

        return Storage::download(
            $invoice->pdf_path,
            "{$invoice->number}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }
}
