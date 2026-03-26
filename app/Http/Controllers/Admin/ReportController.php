<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Repair;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function sales(Request $request): Response
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));
        $status = $request->input('status');

        $query = Sale::with('customer')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($status) {
            $query->where('status', $status);
        }

        $sales = $query->latest()->paginate(20)->withQueryString();

        $summary = Sale::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN grand_total ELSE 0 END) as completed_revenue")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN grand_total ELSE 0 END) as pending_revenue")
            ->selectRaw('SUM(grand_total) as total_revenue')
            ->selectRaw('SUM(discount) as total_discount')
            ->selectRaw('SUM(total_tax) as total_tax')
            ->first();

        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when($status, fn ($q) => $q->where('sales.status', $status))
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $dailySales = Sale::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/Reports/Sales', [
            'filters' => ['from' => $from, 'to' => $to, 'status' => $status],
            'sales' => $sales->through(fn (Sale $sale) => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer' => $sale->customer?->name ?? 'Walk-in',
                'grand_total' => $sale->grand_total,
                'discount' => $sale->discount,
                'status' => $sale->status,
                'created_at' => $sale->created_at->format('Y-m-d H:i'),
            ]),
            'summary' => $summary,
            'topProducts' => $topProducts,
            'dailySales' => $dailySales,
        ]);
    }

    public function inventory(Request $request): Response
    {
        $category = $request->input('category');
        $stockFilter = $request->input('stock_filter'); // all, low, out

        $query = Product::with('category')->where('is_active', true);

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($stockFilter === 'low') {
            $query->whereColumn('stock_quantity', '<=', 'min_stock_level')
                ->where('stock_quantity', '>', 0);
        } elseif ($stockFilter === 'out') {
            $query->where('stock_quantity', '<=', 0);
        }

        $products = $query->orderBy('stock_quantity')->paginate(20)->withQueryString();

        $summary = Product::where('is_active', true)
            ->selectRaw('COUNT(*) as total_products')
            ->selectRaw('SUM(stock_quantity) as total_stock')
            ->selectRaw('SUM(stock_quantity * cost_price) as total_stock_value')
            ->selectRaw('SUM(stock_quantity * selling_price) as total_retail_value')
            ->selectRaw('SUM(CASE WHEN stock_quantity <= min_stock_level AND stock_quantity > 0 THEN 1 ELSE 0 END) as low_stock_count')
            ->selectRaw('SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_count')
            ->first();

        $categoryBreakdown = Product::where('is_active', true)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category',
                DB::raw('COUNT(*) as product_count'),
                DB::raw('SUM(products.stock_quantity) as total_stock'),
                DB::raw('SUM(products.stock_quantity * products.cost_price) as stock_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('stock_value')
            ->get();

        $categories = \App\Models\Category::pluck('name', 'id');

        return Inertia::render('Admin/Reports/Inventory', [
            'filters' => ['category' => $category, 'stock_filter' => $stockFilter],
            'products' => $products->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'stock_quantity' => $product->stock_quantity,
                'min_stock_level' => $product->min_stock_level,
                'stock_value' => number_format($product->stock_quantity * $product->cost_price, 2, '.', ''),
            ]),
            'summary' => $summary,
            'categoryBreakdown' => $categoryBreakdown,
            'categories' => $categories,
        ]);
    }

    public function financial(Request $request): Response
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $salesRevenue = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('SUM(grand_total) as total, COUNT(*) as count')
            ->first();

        $repairRevenue = Repair::where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('SUM(actual_cost) as total, COUNT(*) as count')
            ->first();

        $paymentsReceived = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('SUM(amount) as total, COUNT(*) as count')
            ->first();

        $paymentsByMethod = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $monthExpression = $this->yearMonthExpression('created_at');

        $monthlyRevenue = Payment::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->select(
                DB::raw("{$monthExpression} as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupByRaw($monthExpression)
            ->orderBy('month')
            ->get();

        $totalDiscount = Sale::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->sum('discount');

        $totalTax = Sale::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->sum('total_tax');

        $topCustomers = Customer::withSum(['payments as total_paid' => fn ($q) => $q
                ->where('status', 'completed')
                ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ], 'amount')
            ->having('total_paid', '>', 0)
            ->orderByDesc('total_paid')
            ->limit(10)
            ->get()
            ->map(fn ($c) => ['name' => $c->name, 'total_paid' => $c->total_paid]);

        return Inertia::render('Admin/Reports/Financial', [
            'filters' => ['from' => $from, 'to' => $to],
            'salesRevenue' => $salesRevenue,
            'repairRevenue' => $repairRevenue,
            'paymentsReceived' => $paymentsReceived,
            'paymentsByMethod' => $paymentsByMethod,
            'monthlyRevenue' => $monthlyRevenue,
            'totalDiscount' => $totalDiscount,
            'totalTax' => $totalTax,
            'topCustomers' => $topCustomers,
        ]);
    }

    // CSV Export endpoints
    public function exportSales(Request $request): StreamedResponse
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $sales = Sale::with('customer')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Sale #', 'Customer', 'Total Amount', 'Discount', 'Tax', 'Grand Total', 'Status', 'Date']);
            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->sale_number,
                    $sale->customer?->name ?? 'Walk-in',
                    $sale->total_amount,
                    $sale->discount,
                    $sale->total_tax,
                    $sale->grand_total,
                    $sale->status,
                    $sale->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, 'sales-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportCustomers(): StreamedResponse
    {
        $customers = Customer::withCount('sales')->latest()->get();

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Phone', 'Email', 'Address', 'Status', 'Sales Count', 'Created']);
            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->name,
                    $customer->phone,
                    $customer->email,
                    $customer->address,
                    $customer->status,
                    $customer->sales_count,
                    $customer->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, 'customers-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportProducts(): StreamedResponse
    {
        $products = Product::with('category')->where('is_active', true)->get();

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'SKU', 'Category', 'Cost Price', 'Selling Price', 'Stock Qty', 'Min Stock', 'Status']);
            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->name,
                    $product->sku,
                    $product->category->name,
                    $product->cost_price,
                    $product->selling_price,
                    $product->stock_quantity,
                    $product->min_stock_level,
                    $product->is_active ? 'Active' : 'Inactive',
                ]);
            }
            fclose($handle);
        }, 'products-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportRepairs(Request $request): StreamedResponse
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $repairs = Repair::with(['customer', 'assignedTo'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($repairs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Repair #', 'Customer', 'Device', 'Brand', 'Status', 'Priority', 'Technician', 'Est. Cost', 'Actual Cost', 'Date']);
            foreach ($repairs as $repair) {
                fputcsv($handle, [
                    $repair->repair_number,
                    $repair->customer->name,
                    $repair->device_name,
                    $repair->device_brand,
                    $repair->status,
                    $repair->priority,
                    $repair->assignedTo?->name ?? 'Unassigned',
                    $repair->estimated_cost,
                    $repair->actual_cost,
                    $repair->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, 'repairs-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPayments(Request $request): StreamedResponse
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $payments = Payment::with(['customer', 'sale', 'repair'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Payment #', 'Customer', 'Reference', 'Amount', 'Method', 'Status', 'Date']);
            foreach ($payments as $payment) {
                $reference = $payment->sale?->sale_number ?? $payment->repair?->repair_number ?? '—';
                fputcsv($handle, [
                    $payment->payment_number,
                    $payment->customer?->name ?? '—',
                    $reference,
                    $payment->amount,
                    $payment->payment_method,
                    $payment->status,
                    $payment->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, 'payments-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
