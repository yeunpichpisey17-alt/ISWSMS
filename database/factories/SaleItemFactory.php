<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        $products = Product::pluck('id')->toArray();
        return [
            'sale_id' => 1, // Will be overridden
            'product_id' => $this->faker->randomElement($products) ?: 1,
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $this->faker->randomFloat(2, 50, 500),
            'subtotal' => $this->faker->randomFloat(2, 50, 2000),
            'serial_number' => null,
        ];
    }
}
