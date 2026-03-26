<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Repair;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepairFactory extends Factory
{
    protected $model = Repair::class;

    public function definition(): array
    {
        $customers = Customer::pluck('id')->toArray();
        $products = Product::pluck('id')->toArray();
        return [
            'customer_id' => $this->faker->randomElement($customers) ?: 1,
            'product_id' => $this->faker->randomElement($products) ?: 1,
            'repair_number' => 'R' . $this->faker->unique()->numberBetween(1000, 9999),
            'device_name' => 'Samsung Galaxy A15', // example
            'issue_description' => $this->faker->sentence(),
            'estimated_cost' => $this->faker->randomFloat(2, 10, 200),
            'status' => $this->faker->randomElement(['received', 'diagnosing', 'in_repair', 'completed']),
            'received_by' => 1,
            'notes' => $this->faker->sentence(),
        ];
    }
}
