<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRepairRequest;
use App\Http\Requests\Admin\UpdateRepairRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Repair;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RepairController extends Controller
{
    public function index(Request $request): Response
    {
        $repairs = Repair::with(['customer', 'assignedTo'])
            ->when($request->input('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('repair_number', 'like', "%{$search}%")
                    ->orWhere('device_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            }))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('priority'), fn ($q, $priority) => $q->where('priority', $priority))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Repair $repair) => [
                'id' => $repair->id,
                'repair_number' => $repair->repair_number,
                'customer' => $repair->customer->name,
                'device_name' => $repair->device_name,
                'device_brand' => $repair->device_brand,
                'status' => $repair->status,
                'priority' => $repair->priority,
                'assigned_to' => $repair->assignedTo?->name ?? 'Unassigned',
                'estimated_cost' => $repair->estimated_cost,
                'created_at' => $repair->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/Repairs/Index', [
            'repairs' => $repairs,
            'filters' => $request->only(['search', 'status', 'priority']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Repairs/Create', [
            'customers' => Customer::where('status', 'active')->get(['id', 'name', 'phone']),
            'products' => Product::where('is_active', true)->get(['id', 'name', 'sku']),
            'technicians' => User::role('Technician')->get(['id', 'name']),
        ]);
    }

    public function store(StoreRepairRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['repair_number'] = Repair::generateRepairNumber();
        $data['received_by'] = auth()->id();

        Repair::create($data);

        return redirect()->route('admin.repairs.index')
            ->with('success', 'Repair ticket created successfully.');
    }

    public function show(Repair $repair): Response
    {
        $repair->load(['customer', 'product', 'assignedTo', 'receivedBy', 'parts.product', 'payments']);

        return Inertia::render('Admin/Repairs/Show', [
            'repair' => [
                'id' => $repair->id,
                'repair_number' => $repair->repair_number,
                'customer' => [
                    'id' => $repair->customer->id,
                    'name' => $repair->customer->name,
                    'phone' => $repair->customer->phone,
                    'email' => $repair->customer->email,
                ],
                'product' => $repair->product ? [
                    'id' => $repair->product->id,
                    'name' => $repair->product->name,
                    'sku' => $repair->product->sku,
                ] : null,
                'serial_number' => $repair->serial_number,
                'device_name' => $repair->device_name,
                'device_brand' => $repair->device_brand,
                'device_model' => $repair->device_model,
                'issue_description' => $repair->issue_description,
                'diagnosis' => $repair->diagnosis,
                'resolution' => $repair->resolution,
                'status' => $repair->status,
                'priority' => $repair->priority,
                'estimated_cost' => $repair->estimated_cost,
                'actual_cost' => $repair->actual_cost,
                'assigned_to' => $repair->assignedTo?->name,
                'received_by' => $repair->receivedBy->name,
                'estimated_completion' => $repair->estimated_completion?->format('Y-m-d'),
                'completed_at' => $repair->completed_at?->format('Y-m-d H:i'),
                'delivered_at' => $repair->delivered_at?->format('Y-m-d H:i'),
                'notes' => $repair->notes,
                'created_at' => $repair->created_at->format('Y-m-d H:i'),
                'parts' => $repair->parts->map(fn ($part) => [
                    'id' => $part->id,
                    'part_name' => $part->part_name,
                    'product' => $part->product?->name,
                    'quantity' => $part->quantity,
                    'unit_cost' => $part->unit_cost,
                    'total_cost' => $part->total_cost,
                ]),
                'payments' => $repair->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'created_at' => $payment->created_at->format('Y-m-d H:i'),
                ]),
            ],
            'technicians' => User::role('Technician')->get(['id', 'name']),
        ]);
    }

    public function edit(Repair $repair): Response
    {
        $repair->load('parts');

        return Inertia::render('Admin/Repairs/Edit', [
            'repair' => [
                'id' => $repair->id,
                'repair_number' => $repair->repair_number,
                'customer_id' => $repair->customer_id,
                'device_name' => $repair->device_name,
                'device_brand' => $repair->device_brand,
                'device_model' => $repair->device_model,
                'issue_description' => $repair->issue_description,
                'diagnosis' => $repair->diagnosis,
                'resolution' => $repair->resolution,
                'status' => $repair->status,
                'priority' => $repair->priority,
                'estimated_cost' => $repair->estimated_cost,
                'actual_cost' => $repair->actual_cost,
                'assigned_to' => $repair->assigned_to,
                'estimated_completion' => $repair->estimated_completion?->format('Y-m-d'),
                'notes' => $repair->notes,
            ],
            'technicians' => User::role('Technician')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateRepairRequest $request, Repair $repair): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'completed' && $repair->status !== 'completed') {
            $data['completed_at'] = now();
        }

        if ($data['status'] === 'delivered' && $repair->status !== 'delivered') {
            $data['delivered_at'] = now();
        }

        $repair->update($data);

        return redirect()->route('admin.repairs.show', $repair)
            ->with('success', 'Repair updated successfully.');
    }

    public function destroy(Repair $repair): RedirectResponse
    {
        if (in_array($repair->status, ['completed', 'delivered'])) {
            return back()->with('error', 'Cannot delete completed or delivered repairs.');
        }

        $repair->parts()->delete();
        $repair->delete();

        return redirect()->route('admin.repairs.index')
            ->with('success', 'Repair deleted successfully.');
    }
}
