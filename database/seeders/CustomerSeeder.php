<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()->count(20)->create([
            'status' => 'active',
        ]);

        // Sample Khmer customers
        Customer::create([
            'name' => 'Sokha Kim',
            'phone' => '+855 12 345 678',
            'email' => 'sokha.phnom@example.com',
            'address' => 'St. 278, Daun Penh, Phnom Penh',
            'status' => 'active',
        ]);

        Customer::create([
            'name' => 'Vanna Sok',
            'phone' => '+855 17 123 456',
            'email' => 'vanna.siem@example.com',
            'address' => 'Pub St, Siem Reap',
            'status' => 'active',
        ]);

        Customer::create([
            'name' => 'Sophea Touch',
            'phone' => '+855 99 987 654',
            'email' => 'sophea.batt@example.com',
            'address' => 'Battambang Market',
            'status' => 'active',
        ]);
    }
}
