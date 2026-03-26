<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create default admin user first
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@iswsms.test',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Admin');

        // Create sample users for each role
        $sales = User::factory()->create([
            'name' => 'Sales User',
            'email' => 'sales@iswsms.test',
            'password' => Hash::make('password'),
        ]);
        $sales->assignRole('Sales');

        $technician = User::factory()->create([
            'name' => 'Technician User',
            'email' => 'technician@iswsms.test',
            'password' => Hash::make('password'),
        ]);
        $technician->assignRole('Technician');

        $inventory = User::factory()->create([
            'name' => 'Inventory User',
            'email' => 'inventory@iswsms.test',
            'password' => Hash::make('password'),
        ]);
        $inventory->assignRole('Inventory');

        $accountant = User::factory()->create([
            'name' => 'Accountant User',
            'email' => 'accountant@iswsms.test',
            'password' => Hash::make('password'),
        ]);
        $accountant->assignRole('Accountant');

        $this->call([
            CategorySeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            PurchaseOrderSeeder::class,
            SaleSeeder::class,
            RepairSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
