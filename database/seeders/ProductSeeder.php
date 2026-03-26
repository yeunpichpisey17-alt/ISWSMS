<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->count(50)->create();

        // Sample low stock for dashboard
        Product::create([
            'category_id' => 1,
            'sku' => 'KH11001',
            'name' => 'Samsung Galaxy A15',
            'description' => '4GB RAM, 128GB storage',
            'cost_price' => 120.00,
            'selling_price' => 180.00,
            'stock_quantity' => 3,
            'min_stock_level' => 5,
            'unit' => 'pcs',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => 2,
            'sku' => 'KH22002',
            'name' => 'Honda Wave 110i Spare Part',
            'description' => 'Front tire',
            'cost_price' => 25.00,
            'selling_price' => 40.00,
            'stock_quantity' => 1,
            'min_stock_level' => 10,
            'unit' => 'pcs',
            'is_active' => true,
        ]);
    }
}
