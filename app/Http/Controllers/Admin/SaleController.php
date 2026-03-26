<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSaleRequest;
use App\Http\Requests\Admin\UpdateSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function index(Request $request): Response
    {
        $sales = Sale::with('customer')
            ->withCount('saleItems')
            ->when($request->input('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            }))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Sale $sale) => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer' => $sale->customer?->name ?? 'Walk-in',
                'total_amount' => $sale->total_amount,
                'total_tax' => $sale->total_tax,
                'grand_total' => $sale->grand_total,
                'status' => $sale->status,
                'items_count' => $sale->sale_items_count,
                'created_at' => $sale->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sales/Create', [
            'customers' => Customer::where('status', 'active')->get(['id', 'name']),
            'products' => Product::where('is_active', true)
                ->where('stock_quantity', '>', 0)
                ->get(['id', 'name', 'selling_price', 'stock_quantity', 'sku']),
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $items = collect($data['items']);
            $totalAmount = $items->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
            $discount = $data['discount'] ?? 0;
            $totalTax = $data['tax_amount'] ?? 0;
            $grandTotal = $totalAmount - $discount + $totalTax;

            $saleNumber = 'SALE-' . date('Ymd') . '-' . str_pad(
                Sale::whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?: null,
                'sale_number' => $saleNumber,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'total_tax' => $totalTax,
                'grand_total' => $grandTotal,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $sale->saleItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                    'serial_number' => $item['serial_number'] ?? null,
                ]);

                Product::where('id', $item['product_id'])
                    ->decrement('stock_quantity', $item['quantity']);
            }

            return redirect()->route('admin.sales.index')
                ->with('success', 'Sale created successfully.');
        });
    }

    public function show(Sale $sale): Response
    {
        $sale->load(['customer', 'saleItems.product']);

        return Inertia::render('Admin/Sales/Show', [
            'sale' => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name,
                    'phone' => $sale->customer->phone,
                    'email' => $sale->customer->email,
                ] : null,
                'total_amount' => $sale->total_amount,
                'discount' => $sale->discount,
                'total_tax' => $sale->total_tax,
                'grand_total' => $sale->grand_total,
                'status' => $sale->status,
                'notes' => $sale->notes,
                'created_at' => $sale->created_at->format('Y-m-d H:i'),
                'items' => $sale->saleItems->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'serial_number' => $item->serial_number,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]),
            ],
        ]);
    }

    public function edit(Sale $sale): Response
    {
        if ($sale->status === 'cancelled') {
            return redirect()->route('admin.sales.show', $sale)
                ->with('error', 'Cancelled sales cannot be edited.');
        }

        $sale->load('saleItems');

        return Inertia::render('Admin/Sales/Edit', [
            'sale' => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer_id' => $sale->customer_id,
                'total_amount' => $sale->total_amount,
                'discount' => $sale->discount,
                'total_tax' => $sale->total_tax,
                'grand_total' => $sale->grand_total,
                'status' => $sale->status,
                'notes' => $sale->notes,
                'items' => $sale->saleItems->map(fn ($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'serial_number' => $item->serial_number,
                ]),
            ],
            'customers' => Customer::where('status', 'active')->get(['id', 'name']),
            'products' => Product::where('is_active', true)->get(['id', 'name', 'selling_price', 'stock_quantity', 'sku']),
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        $data = $request->validated();

        $sale->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $sale->notes,
        ]);

        return redirect()->route('admin.sales.show', $sale)
            ->with('success', 'Sale updated successfully.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        if ($sale->status === 'completed') {
            return back()->with('error', 'Completed sales cannot be deleted.');
        }

        DB::transaction(function () use ($sale) {
            // Restore stock for each item
            foreach ($sale->saleItems as $item) {
                Product::where('id', $item->product_id)
                    ->increment('stock_quantity', $item->quantity);
            }

            $sale->saleItems()->delete();
            $sale->delete();
        });

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale deleted and stock restored.');
    }
}
