<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::factory()->count(10)->create();

        // Sample suppliers
        Supplier::create([
            'name' => 'Smart Axiata Co., Ltd.',
            'phone' => '+855 12 800 000',
            'email' => 'supply@smart.com.kh',
            'address' => 'St. 102, Phnom Penh',
            'contact_person' => 'Sokha Kim',
        ]);

        Supplier::create([
            'name' => 'Lucky Supermarket',
            'phone' => '+855 23 999 888',
            'email' => 'procurement@lucky.com.kh',
            'address' => 'Sihanouk Blvd, Phnom Penh',
            'contact_person' => 'Vanna Sok',
        ]);
    }
}
