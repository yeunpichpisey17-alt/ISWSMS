<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Mobile Phones', 'description' => 'Smartphones and accessories'],
            ['name' => 'Motorcycles', 'description' => 'Moto parts and vehicles'],
            ['name' => 'Electronics', 'description' => 'TV, printers, gadgets'],
            ['name' => 'Groceries', 'description' => 'Food, beverages, daily needs'],
            ['name' => 'Agriculture', 'description' => 'Rice, fertilizers, farm tools'],
            ['name' => 'SIM Cards', 'description' => 'Prepaid mobile topups'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
