<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Repair Invoice {{ $repair->repair_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; padding: 40px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a1a2e; }
        .company-detail { font-size: 10px; color: #666; margin-top: 4px; }
        .invoice-title h1 { font-size: 28px; color: #1a1a2e; letter-spacing: 2px; }
        .invoice-title .number { font-size: 14px; color: #666; margin-top: 4px; }
        .meta-label { font-weight: bold; color: #555; width: 120px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #1a1a2e; border-bottom: 2px solid #1a1a2e; padding-bottom: 4px; }
        .info-table { width: 100%; margin-bottom: 16px; }
        .info-table td { padding: 4px 8px; vertical-align: top; }
        .info-table .label { font-weight: bold; color: #555; width: 140px; }
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
        .status-delivered { background: #d4edda; color: #155724; }
        .status-in_repair { background: #fff3cd; color: #856404; }
        .status-received { background: #d1ecf1; color: #0c5460; }
        .priority-urgent { background: #f8d7da; color: #721c24; }
        .priority-high { background: #fff3cd; color: #856404; }
        .priority-normal { background: #d1ecf1; color: #0c5460; }
        .priority-low { background: #e2e3e5; color: #383d41; }
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
                    <h1>REPAIR INVOICE</h1>
                    <div class="number">{{ $repair->repair_number }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 24px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-title">Customer</div>
                <p><strong>{{ $repair->customer->name }}</strong></p>
                @if($repair->customer->phone)<p>{{ $repair->customer->phone }}</p>@endif
                @if($repair->customer->email)<p>{{ $repair->customer->email }}</p>@endif
                @if($repair->customer->address)<p>{{ $repair->customer->address }}</p>@endif
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-title">Repair Details</div>
                <table class="info-table">
                    <tr><td class="label">Date:</td><td>{{ $repair->created_at->format('M d, Y') }}</td></tr>
                    <tr><td class="label">Status:</td><td><span class="status-badge status-{{ $repair->status }}">{{ ucfirst(str_replace('_', ' ', $repair->status)) }}</span></td></tr>
                    <tr><td class="label">Priority:</td><td><span class="status-badge priority-{{ $repair->priority }}">{{ ucfirst($repair->priority) }}</span></td></tr>
                    @if($repair->assignedTo)<tr><td class="label">Technician:</td><td>{{ $repair->assignedTo->name }}</td></tr>@endif
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title">Device Information</div>
    <table class="info-table" style="margin-bottom: 24px;">
        <tr><td class="label">Device:</td><td>{{ $repair->device_name }}</td></tr>
        @if($repair->device_brand)<tr><td class="label">Brand:</td><td>{{ $repair->device_brand }}</td></tr>@endif
        @if($repair->device_model)<tr><td class="label">Model:</td><td>{{ $repair->device_model }}</td></tr>@endif
        @if($repair->serial_number)<tr><td class="label">Serial #:</td><td>{{ $repair->serial_number }}</td></tr>@endif
    </table>

    @if($repair->issue_description)
    <div style="margin-bottom: 16px;">
        <div class="section-title">Issue</div>
        <p>{{ $repair->issue_description }}</p>
    </div>
    @endif

    @if($repair->diagnosis)
    <div style="margin-bottom: 16px;">
        <div class="section-title">Diagnosis</div>
        <p>{{ $repair->diagnosis }}</p>
    </div>
    @endif

    @if($repair->resolution)
    <div style="margin-bottom: 16px;">
        <div class="section-title">Resolution</div>
        <p>{{ $repair->resolution }}</p>
    </div>
    @endif

    @if($repair->parts->count() > 0)
    <div class="section-title">Parts Used</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Part</th>
                <th class="right">Qty</th>
                <th class="right">Unit Cost</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($repair->parts as $i => $part)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $part->part_name }}</td>
                <td class="right">{{ $part->quantity }}</td>
                <td class="right">${{ number_format($part->unit_cost, 2) }}</td>
                <td class="right">${{ number_format($part->total_cost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="totals">
        <table>
            @if($repair->estimated_cost)
            <tr>
                <td class="total-label">Estimated Cost</td>
                <td class="total-value">${{ number_format($repair->estimated_cost, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="total-label" style="font-size: 16px;"><strong>Actual Cost</strong></td>
                <td class="total-value" style="font-size: 16px;">${{ number_format($repair->actual_cost ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($repair->notes)
    <div style="margin-top: 24px;">
        <div class="section-title">Notes</div>
        <p>{{ $repair->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Generated on {{ now()->format('M d, Y h:i A') }} &mdash; ISWSMS</p>
    </div>
</body>
</html>
