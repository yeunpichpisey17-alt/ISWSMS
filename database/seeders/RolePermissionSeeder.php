<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions grouped by module
        $permissions = [
            'users' => ['users.view', 'users.create', 'users.edit', 'users.delete'],
            'roles' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
            'permissions' => ['permissions.view'],
            'products' => ['products.view', 'products.create', 'products.edit', 'products.delete'],
            'sales' => ['sales.view', 'sales.create', 'sales.edit', 'sales.delete'],
            'customers' => ['customers.view', 'customers.create', 'customers.edit', 'customers.delete'],
            'inventory' => ['inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete'],
            'purchases' => ['purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete'],
            'suppliers' => ['suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete'],
            'repairs' => ['repairs.view', 'repairs.create', 'repairs.edit', 'repairs.delete'],
            'warranty' => ['warranty.view', 'warranty.create', 'warranty.edit', 'warranty.delete'],
            'reports' => ['reports.view', 'reports.financial', 'reports.sales', 'reports.inventory'],
            'payments' => ['payments.view', 'payments.create', 'payments.edit', 'payments.delete'],
        ];

        // Create all permissions
        foreach ($permissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                Permission::create(['name' => $permission]);
            }
        }

        // Create roles and assign permissions
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo(Permission::all());

        $sales = Role::create(['name' => 'Sales']);
        $sales->givePermissionTo([
            'sales.view', 'sales.create', 'sales.edit', 'sales.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'products.view',
            'reports.sales',
        ]);

        $technician = Role::create(['name' => 'Technician']);
        $technician->givePermissionTo([
            'repairs.view', 'repairs.create', 'repairs.edit', 'repairs.delete',
            'warranty.view', 'warranty.create', 'warranty.edit', 'warranty.delete',
            'products.view',
            'customers.view',
        ]);

        $inventory = Role::create(['name' => 'Inventory']);
        $inventory->givePermissionTo([
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'reports.inventory',
        ]);

        $accountant = Role::create(['name' => 'Accountant']);
        $accountant->givePermissionTo([
            'reports.view', 'reports.financial', 'reports.sales', 'reports.inventory',
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            'sales.view',
            'purchases.view',
        ]);
    }
}
