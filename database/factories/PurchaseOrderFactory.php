<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $suppliers = Supplier::pluck('id')->toArray();
        return [
            'supplier_id' => $this->faker->randomElement($suppliers) ?: 1,
            'po_number' => 'PO-' . $this->faker->unique()->numberBetween(1000, 9999),
            'status' => $this->faker->randomElement(['pending', 'received', 'partial']),
            'order_date' => now()->subDays($this->faker->numberBetween(1, 30)),
            'expected_date' => now()->addDays($this->faker->numberBetween(1, 30)),
            'received_date' => $this->faker->boolean(70) ? now()->subDays($this->faker->numberBetween(1, 10)) : null,
            'total_amount' => $this->faker->randomFloat(2, 500, 10000),
            'notes' => $this->faker->sentence(),
            'created_by' => 1,
        ];
    }
}
