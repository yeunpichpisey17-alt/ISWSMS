<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::pluck('id')->toArray();

        for ($i = 0; $i < 20; $i++) {
            $customerId = fake()->randomElement($customers);
            $sale = Sale::factory()->create([
                'customer_id' => $customerId,
                'status' => fake()->randomElement(['pending', 'completed']),
            ]);

            // Add sale items
            SaleItem::factory()->count(fake()->numberBetween(1, 5))->create([
                'sale_id' => $sale->id,
            ]);
        }
    }
}
