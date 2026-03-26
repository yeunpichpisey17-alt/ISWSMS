<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePurchaseOrderRequest;
use App\Http\Requests\Admin\UpdatePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SerialNumber;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function index(): Response
    {
        $purchaseOrders = PurchaseOrder::with('supplier', 'creator')
            ->latest()
            ->paginate(15)
            ->through(fn (PurchaseOrder $po) => [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'supplier' => $po->supplier->name,
                'status' => $po->status,
                'order_date' => $po->order_date->format('Y-m-d'),
                'total_amount' => $po->total_amount,
                'created_by' => $po->creator->name,
                'created_at' => $po->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['supplier', 'creator', 'items.product']);

        return Inertia::render('Admin/PurchaseOrders/Show', [
            'purchaseOrder' => [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'supplier' => $purchaseOrder->supplier->name,
                'status' => $purchaseOrder->status,
                'order_date' => $purchaseOrder->order_date->format('Y-m-d'),
                'expected_date' => $purchaseOrder->expected_date?->format('Y-m-d'),
                'received_date' => $purchaseOrder->received_date?->format('Y-m-d'),
                'total_amount' => $purchaseOrder->total_amount,
                'notes' => $purchaseOrder->notes,
                'created_by' => $purchaseOrder->creator->name,
                'items' => $purchaseOrder->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/PurchaseOrders/Create', [
            'suppliers' => Supplier::all(['id', 'name']),
            'products' => Product::where('is_active', true)->get(['id', 'name', 'sku', 'cost_price']),
            'poNumber' => PurchaseOrder::generatePoNumber(),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $po = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'po_number' => $request->po_number,
                'status' => 'draft',
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'notes' => $request->notes,
                'total_amount' => 0,
                'created_by' => auth()->id(),
            ]);

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
                $totalAmount += $totalPrice;
            }

            $po->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.purchase-orders.index')
            ->with('success', 'Purchase order created successfully.');
    }

    public function edit(PurchaseOrder $purchaseOrder): Response
    {
        if ($purchaseOrder->status !== 'draft') {
            return Inertia::render('Admin/PurchaseOrders/Show', [
                'purchaseOrder' => $purchaseOrder,
            ]);
        }

        $purchaseOrder->load('items');

        return Inertia::render('Admin/PurchaseOrders/Edit', [
            'purchaseOrder' => [
                'id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'po_number' => $purchaseOrder->po_number,
                'order_date' => $purchaseOrder->order_date->format('Y-m-d'),
                'expected_date' => $purchaseOrder->expected_date?->format('Y-m-d'),
                'notes' => $purchaseOrder->notes,
                'items' => $purchaseOrder->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]),
            ],
            'suppliers' => Supplier::all(['id', 'name']),
            'products' => Product::where('is_active', true)->get(['id', 'name', 'sku', 'cost_price']),
        ]);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Only draft purchase orders can be edited.');
        }

        DB::transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'notes' => $request->notes,
            ]);

            $purchaseOrder->items()->delete();

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
                $totalAmount += $totalPrice;
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.purchase-orders.index')
            ->with('success', 'Purchase order updated successfully.');
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,received,cancelled'],
            'serial_numbers' => ['nullable', 'array'],
            'serial_numbers.*' => ['nullable', 'array'],
            'serial_numbers.*.*' => ['nullable', 'string'],
        ]);

        $newStatus = $request->status;

        // Validate status transitions
        $validTransitions = [
            'draft' => ['pending', 'cancelled'],
            'pending' => ['received', 'cancelled'],
        ];

        if (! isset($validTransitions[$purchaseOrder->status]) ||
            ! in_array($newStatus, $validTransitions[$purchaseOrder->status])) {
            return back()->with('error', 'Invalid status transition.');
        }

        DB::transaction(function () use ($request, $purchaseOrder, $newStatus) {
            $purchaseOrder->update([
                'status' => $newStatus,
                'received_date' => $newStatus === 'received' ? now() : $purchaseOrder->received_date,
            ]);

            if ($newStatus === 'received') {
                $purchaseOrder->load('items');

                foreach ($purchaseOrder->items as $item) {
                    // Update product stock
                    $item->product->increment('stock_quantity', $item->quantity);

                    // Create serial numbers if provided
                    $serialNumbers = $request->input("serial_numbers.{$item->id}", []);
                    foreach (array_filter($serialNumbers) as $sn) {
                        SerialNumber::create([
                            'product_id' => $item->product_id,
                            'purchase_order_item_id' => $item->id,
                            'serial_number' => $sn,
                            'status' => 'in_stock',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase order status updated to {$newStatus}.");
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Only draft purchase orders can be deleted.');
        }

        $purchaseOrder->delete();

        return redirect()->route('admin.purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }
}
