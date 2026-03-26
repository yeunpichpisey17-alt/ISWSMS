<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    public function index(): Response
    {
        $adjustments = StockAdjustment::with(['product', 'adjuster'])
            ->latest()
            ->paginate(15)
            ->through(fn (StockAdjustment $adj) => [
                'id' => $adj->id,
                'product' => $adj->product->name,
                'product_sku' => $adj->product->sku,
                'type' => $adj->type,
                'quantity' => $adj->quantity,
                'reason' => $adj->reason,
                'adjusted_by' => $adj->adjuster->name,
                'created_at' => $adj->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/StockAdjustments/Index', [
            'adjustments' => $adjustments,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/StockAdjustments/Create', [
            'products' => Product::where('is_active', true)->get(['id', 'name', 'sku', 'stock_quantity']),
        ]);
    }

    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $adjustment = StockAdjustment::create([
                'product_id' => $request->product_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'adjusted_by' => auth()->id(),
            ]);

            $product = Product::findOrFail($request->product_id);

            match ($request->type) {
                'addition' => $product->increment('stock_quantity', $request->quantity),
                'deduction' => $product->decrement('stock_quantity', $request->quantity),
                'adjustment' => $product->update(['stock_quantity' => $request->quantity]),
            };
        });

        return redirect()->route('admin.stock-adjustments.index')
            ->with('success', 'Stock adjustment recorded successfully.');
    }
}
