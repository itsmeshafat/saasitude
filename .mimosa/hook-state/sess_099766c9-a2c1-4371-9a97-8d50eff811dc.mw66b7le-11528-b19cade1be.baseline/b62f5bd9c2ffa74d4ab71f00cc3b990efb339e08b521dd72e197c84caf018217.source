<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 22px; }
        .header { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f3f4f6; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .total { font-weight: bold; }
        .footer { margin-top: 40px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice</h1>
        <p><strong>Invoice #:</strong> {{ $invoice->number }}</p>
        <p><strong>Date:</strong> {{ $invoice->issued_at->format('F j, Y') }}</p>
        <p><strong>Workspace:</strong> {{ $invoice->workspace->name }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->line_items ?? [] as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td style="text-align:right">{{ number_format($item['amount'] / 100, 2) }} {{ strtoupper($item['currency']) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td>Total</td>
                <td style="text-align:right">{{ $invoice->formattedAmount() }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Status: {{ ucfirst($invoice->status) }}</p>
    </div>
</body>
</html>
