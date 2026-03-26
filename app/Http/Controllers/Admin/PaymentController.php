<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Repair;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $payments = Payment::with(['customer', 'sale', 'repair', 'receivedBy'])
            ->when($request->input('search'), fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            }))
            ->when($request->input('method'), fn ($q, $method) => $q->where('payment_method', $method))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Payment $payment) => [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'customer' => $payment->customer?->name ?? '—',
                'reference' => $payment->sale
                    ? $payment->sale->sale_number
                    : ($payment->repair ? $payment->repair->repair_number : '—'),
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => $payment->status,
                'received_by' => $payment->receivedBy?->name,
                'created_at' => $payment->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'method', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Payments/Create', [
            'customers' => Customer::where('status', 'active')->get(['id', 'name']),
            'sales' => Sale::where('status', '!=', 'cancelled')
                ->with('customer')
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn ($sale) => [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'customer_name' => $sale->customer?->name ?? 'Walk-in',
                    'grand_total' => $sale->grand_total,
                ]),
            'repairs' => Repair::whereNotIn('status', ['cancelled', 'delivered'])
                ->with('customer')
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn ($repair) => [
                    'id' => $repair->id,
                    'repair_number' => $repair->repair_number,
                    'customer_name' => $repair->customer->name,
                    'actual_cost' => $repair->actual_cost,
                ]),
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['payment_number'] = Payment::generatePaymentNumber();
        $data['received_by'] = auth()->id();
        $data['status'] = 'completed';

        Payment::create($data);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment): Response
    {
        $payment->load(['customer', 'sale', 'repair', 'receivedBy']);

        return Inertia::render('Admin/Payments/Show', [
            'payment' => [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'customer' => $payment->customer ? [
                    'id' => $payment->customer->id,
                    'name' => $payment->customer->name,
                ] : null,
                'sale' => $payment->sale ? [
                    'id' => $payment->sale->id,
                    'sale_number' => $payment->sale->sale_number,
                ] : null,
                'repair' => $payment->repair ? [
                    'id' => $payment->repair->id,
                    'repair_number' => $payment->repair->repair_number,
                ] : null,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'status' => $payment->status,
                'reference_number' => $payment->reference_number,
                'notes' => $payment->notes,
                'received_by' => $payment->receivedBy?->name,
                'created_at' => $payment->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        if ($payment->status === 'completed') {
            return back()->with('error', 'Cannot delete completed payments.');
        }

        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }
}
