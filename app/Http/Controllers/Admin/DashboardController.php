<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Repair;
use App\Models\Sale;
use App\Models\WarrantyClaim;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Summary Stats
        $totalSales = Sale::where('status', 'completed')->sum('grand_total');
        $totalSalesCount = Sale::count();
        $totalCustomers = Customer::count();
        $totalProducts = Product::count();
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->count();
        $pendingRepairs = Repair::whereNotIn('status', ['completed', 'delivered', 'cancelled'])->count();
        $pendingWarranty = WarrantyClaim::whereNotIn('status', ['completed', 'rejected'])->count();
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        // Monthly Sales (last 12 months)
        $monthExpression = $this->yearMonthExpression('created_at');

        $monthlySales = Sale::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->select(
                DB::raw("{$monthExpression} as month"),
                DB::raw('SUM(grand_total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupByRaw($monthExpression)
            ->orderBy('month')
            ->get();

        // Recent Sales
        $recentSales = Sale::with('customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Sale $sale) => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer' => $sale->customer?->name ?? 'Walk-in',
                'grand_total' => $sale->grand_total,
                'status' => $sale->status,
                'created_at' => $sale->created_at->format('Y-m-d H:i'),
            ]);

        // Recent Repairs
        $recentRepairs = Repair::with('customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Repair $repair) => [
                'id' => $repair->id,
                'repair_number' => $repair->repair_number,
                'customer' => $repair->customer->name,
                'device_name' => $repair->device_name,
                'status' => $repair->status,
                'created_at' => $repair->created_at->format('Y-m-d H:i'),
            ]);

        // Low Stock Products
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->with('category')
            ->limit(10)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name,
                'stock_quantity' => $product->stock_quantity,
                'min_stock_level' => $product->min_stock_level,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalSales' => $totalSales,
                'totalSalesCount' => $totalSalesCount,
                'totalCustomers' => $totalCustomers,
                'totalProducts' => $totalProducts,
                'lowStockCount' => $lowStockCount,
                'pendingRepairs' => $pendingRepairs,
                'pendingWarranty' => $pendingWarranty,
                'totalRevenue' => $totalRevenue,
            ],
            'monthlySales' => $monthlySales,
            'recentSales' => $recentSales,
            'recentRepairs' => $recentRepairs,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
