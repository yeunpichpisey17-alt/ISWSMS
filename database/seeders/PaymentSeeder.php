<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $sales = Sale::where('status', 'completed')->pluck('id')->toArray();
        foreach ($sales as $saleId) {
            Payment::create([
                'payment_number' => 'PAY' . rand(1000, 9999),
                'sale_id' => $saleId,
                'customer_id' => Sale::find($saleId)->customer_id,
                'amount' => rand(50, 500),
                'payment_method' => 'cash',
                'status' => 'completed',
                'received_by' => 1,
                'notes' => 'Sample payment',
            ]);
        }
    }
}
