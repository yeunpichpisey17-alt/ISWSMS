<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->sale_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a1a2e; }
        .company-detail { font-size: 10px; color: #666; margin-top: 4px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 28px; color: #1a1a2e; letter-spacing: 2px; }
        .invoice-title .number { font-size: 14px; color: #666; margin-top: 4px; }
        .meta-row { width: 100%; margin-bottom: 24px; }
        .meta-row table { width: 100%; }
        .meta-row td { vertical-align: top; padding: 4px 0; }
        .meta-label { font-weight: bold; color: #555; width: 100px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #1a1a2e; border-bottom: 2px solid #1a1a2e; padding-bottom: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .items-table th { background-color: #1a1a2e; color: #fff; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .items-table th.right { text-align: right; }
        .items-table td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        .items-table td.right { text-align: right; }
        .items-table tr:nth-child(even) { background-color: #f9f9f9; }
        .totals { width: 300px; margin-left: auto; }
        .totals table { width: 100%; }
        .totals td { padding: 4px 8px; }
        .totals .total-label { text-align: right; color: #555; }
        .totals .total-value { text-align: right; font-weight: bold; }
        .totals .grand-total td { border-top: 2px solid #1a1a2e; padding-top: 8px; font-size: 16px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 12px; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <table style="width: 100%; margin-bottom: 30px;">
        <tr>
            <td style="vertical-align: top;">
                <div class="company-name">ISWSMS</div>
                <div class="company-detail">Inventory, Sales, Warranty & Service Management</div>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <div class="invoice-title">
                    <h1>INVOICE</h1>
                    <div class="number">{{ $sale->sale_number }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 24px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-title">Bill To</div>
                @if($sale->customer)
                    <p><strong>{{ $sale->customer->name }}</strong></p>
                    @if($sale->customer->phone)<p>{{ $sale->customer->phone }}</p>@endif
                    @if($sale->customer->email)<p>{{ $sale->customer->email }}</p>@endif
                    @if($sale->customer->address)<p>{{ $sale->customer->address }}</p>@endif
                @else
                    <p>Walk-in Customer</p>
                @endif
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <div class="section-title" style="text-align: right;">Invoice Details</div>
                <table style="margin-left: auto;">
                    <tr><td class="meta-label">Date:</td><td>{{ $sale->created_at->format('M d, Y') }}</td></tr>
                    <tr><td class="meta-label">Status:</td><td><span class="status-badge status-{{ $sale->status }}">{{ ucfirst($sale->status) }}</span></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->product->sku }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                <td class="right">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="total-label">Subtotal</td>
                <td class="total-value">${{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            @if($sale->discount > 0)
            <tr>
                <td class="total-label">Discount</td>
                <td class="total-value" style="color: #e74c3c;">-${{ number_format($sale->discount, 2) }}</td>
            </tr>
            @endif
            @if($sale->total_tax > 0)
            <tr>
                <td class="total-label">Tax</td>
                <td class="total-value">${{ number_format($sale->total_tax, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="total-label" style="font-size: 16px;"><strong>Grand Total</strong></td>
                <td class="total-value" style="font-size: 16px;">${{ number_format($sale->grand_total, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($sale->notes)
    <div style="margin-top: 24px;">
        <div class="section-title">Notes</div>
        <p>{{ $sale->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Generated on {{ now()->format('M d, Y h:i A') }} &mdash; ISWSMS</p>
    </div>
</body>
</html>
