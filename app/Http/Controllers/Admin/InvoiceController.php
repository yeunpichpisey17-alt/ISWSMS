<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function sale(Sale $sale): Response
    {
        $sale->load(['customer', 'saleItems.product']);

        $pdf = Pdf::loadView('invoices.sale', compact('sale'))
            ->setPaper('a4');

        return $pdf->download("invoice-{$sale->sale_number}.pdf");
    }

    public function repair(Repair $repair): Response
    {
        $repair->load(['customer', 'product', 'assignedTo', 'parts']);

        $pdf = Pdf::loadView('invoices.repair', compact('repair'))
            ->setPaper('a4');

        return $pdf->download("invoice-{$repair->repair_number}.pdf");
    }
}
