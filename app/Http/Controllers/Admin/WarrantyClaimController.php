<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarrantyClaimRequest;
use App\Http\Requests\Admin\UpdateWarrantyClaimRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\WarrantyClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarrantyClaimController extends Controller
{
    public function index(Request $request): Response
    {
        $claims = WarrantyClaim::with(['customer', 'product'])
            ->when($request->input('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            }))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (WarrantyClaim $claim) => [
                'id' => $claim->id,
                'claim_number' => $claim->claim_number,
                'customer' => $claim->customer->name,
                'product' => $claim->product->name,
                'serial_number' => $claim->serial_number,
                'status' => $claim->status,
                'warranty_end' => $claim->warranty_end->format('Y-m-d'),
                'is_expired' => $claim->warranty_end->isPast(),
                'created_at' => $claim->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/Warranty/Index', [
            'claims' => $claims,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Warranty/Create', [
            'customers' => Customer::where('status', 'active')->get(['id', 'name']),
            'products' => Product::where('is_active', true)->get(['id', 'name', 'sku']),
            'sales' => Sale::with('customer')->latest()->limit(50)->get(['id', 'sale_number', 'customer_id']),
        ]);
    }

    public function store(StoreWarrantyClaimRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['claim_number'] = WarrantyClaim::generateClaimNumber();
        $data['handled_by'] = auth()->id();

        WarrantyClaim::create($data);

        return redirect()->route('admin.warranty-claims.index')
            ->with('success', 'Warranty claim submitted successfully.');
    }

    public function show(WarrantyClaim $warrantyClaim): Response
    {
        $warrantyClaim->load(['customer', 'product', 'sale', 'repair', 'handledBy']);

        return Inertia::render('Admin/Warranty/Show', [
            'claim' => [
                'id' => $warrantyClaim->id,
                'claim_number' => $warrantyClaim->claim_number,
                'customer' => [
                    'id' => $warrantyClaim->customer->id,
                    'name' => $warrantyClaim->customer->name,
                    'phone' => $warrantyClaim->customer->phone,
                ],
                'product' => [
                    'id' => $warrantyClaim->product->id,
                    'name' => $warrantyClaim->product->name,
                    'sku' => $warrantyClaim->product->sku,
                ],
                'sale' => $warrantyClaim->sale ? [
                    'id' => $warrantyClaim->sale->id,
                    'sale_number' => $warrantyClaim->sale->sale_number,
                ] : null,
                'serial_number' => $warrantyClaim->serial_number,
                'issue_description' => $warrantyClaim->issue_description,
                'resolution' => $warrantyClaim->resolution,
                'status' => $warrantyClaim->status,
                'warranty_start' => $warrantyClaim->warranty_start->format('Y-m-d'),
                'warranty_end' => $warrantyClaim->warranty_end->format('Y-m-d'),
                'is_expired' => $warrantyClaim->warranty_end->isPast(),
                'repair' => $warrantyClaim->repair ? [
                    'id' => $warrantyClaim->repair->id,
                    'repair_number' => $warrantyClaim->repair->repair_number,
                    'status' => $warrantyClaim->repair->status,
                ] : null,
                'handled_by' => $warrantyClaim->handledBy?->name,
                'notes' => $warrantyClaim->notes,
                'created_at' => $warrantyClaim->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function edit(WarrantyClaim $warrantyClaim): Response
    {
        return Inertia::render('Admin/Warranty/Edit', [
            'claim' => [
                'id' => $warrantyClaim->id,
                'claim_number' => $warrantyClaim->claim_number,
                'status' => $warrantyClaim->status,
                'resolution' => $warrantyClaim->resolution,
                'repair_id' => $warrantyClaim->repair_id,
                'notes' => $warrantyClaim->notes,
            ],
        ]);
    }

    public function update(UpdateWarrantyClaimRequest $request, WarrantyClaim $warrantyClaim): RedirectResponse
    {
        $data = $request->validated();
        $data['handled_by'] = auth()->id();

        $warrantyClaim->update($data);

        return redirect()->route('admin.warranty-claims.show', $warrantyClaim)
            ->with('success', 'Warranty claim updated successfully.');
    }

    public function destroy(WarrantyClaim $warrantyClaim): RedirectResponse
    {
        if (in_array($warrantyClaim->status, ['completed', 'in_repair'])) {
            return back()->with('error', 'Cannot delete active warranty claims.');
        }

        $warrantyClaim->delete();

        return redirect()->route('admin.warranty-claims.index')
            ->with('success', 'Warranty claim deleted successfully.');
    }
}
