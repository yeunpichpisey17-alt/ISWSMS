<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $categories = Category::pluck('id')->toArray();
        return [
            'category_id' => $this->faker->randomElement($categories) ?: 1,
            'sku' => 'KH' . $this->faker->unique()->numberBetween(10000, 99999),
            'name' => $this->faker->randomElement([
                'Samsung Galaxy A15',
                'iPhone 13',
                'Honda Wave 110i',
                'Yamaha Sirius',
                'Hisense TV 55"',
                'Canon Printer',
                'Angkor Beer Case',
                'Pringles 180g',
                'Jasmine Rice 5kg',
                'Smart SIM Prepaid',
                'Wing Money Topup',
                'Xiaomi Rednote 12',
                'Oppo Reno10',
                'Sony Earbuds',
                'Logitech Mouse'
            ]),
            'description' => $this->faker->sentence(),
            'cost_price' => $this->faker->randomFloat(2, 50, 500),
            'selling_price' => $this->faker->randomFloat(2, 80, 800),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'min_stock_level' => $this->faker->numberBetween(5, 20),
            'unit' => $this->faker->randomElement(['pcs', 'box', 'kg', 'pack']),
            'is_active' => true,
        ];
    }
}
