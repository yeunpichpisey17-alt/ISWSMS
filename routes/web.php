<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\RepairController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarrantyClaimController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });

// Inventory & Product Management (permission-based)
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Categories
        Route::middleware('permission:products.create')->group(function () {
            Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        });
        Route::middleware('permission:products.view')->group(function () {
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        });
        Route::middleware('permission:products.edit')->group(function () {
            Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        });
        Route::middleware('permission:products.delete')->group(function () {
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // Products
        Route::middleware('permission:products.create')->group(function () {
            Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
        });
        Route::middleware('permission:products.view')->group(function () {
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
        });
        Route::middleware('permission:products.edit')->group(function () {
            Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        });
        Route::middleware('permission:products.delete')->group(function () {
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        });

        // Suppliers
        Route::middleware('permission:suppliers.create')->group(function () {
            Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
            Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        });
        Route::middleware('permission:suppliers.view')->group(function () {
            Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        });
        Route::middleware('permission:suppliers.edit')->group(function () {
            Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
            Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        });
        Route::middleware('permission:suppliers.delete')->group(function () {
            Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        });

        // Purchase Orders
        Route::middleware('permission:purchases.create')->group(function () {
            Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
            Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        });
        Route::middleware('permission:purchases.view')->group(function () {
            Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
            Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        });
        Route::middleware('permission:purchases.edit')->group(function () {
            Route::get('purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
            Route::put('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
            Route::patch('purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
        });
        Route::middleware('permission:purchases.delete')->group(function () {
            Route::delete('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
        });

        // Stock Adjustments
        Route::middleware('permission:inventory.create')->group(function () {
            Route::get('stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
            Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
        });
        Route::middleware('permission:inventory.view')->group(function () {
            Route::get('stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
        });

        // Customers
        Route::middleware('permission:customers.create')->group(function () {
            Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
            Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        });
        Route::middleware('permission:customers.view')->group(function () {
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        });
        Route::middleware('permission:customers.edit')->group(function () {
            Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        });
        Route::middleware('permission:customers.delete')->group(function () {
            Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        });

        // Sales
        Route::middleware('permission:sales.create')->group(function () {
            Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
            Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
        });
        Route::middleware('permission:sales.view')->group(function () {
            Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
            Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        });
        Route::middleware('permission:sales.edit')->group(function () {
            Route::get('sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
            Route::put('sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
        });
        Route::middleware('permission:sales.delete')->group(function () {
            Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
        });

        // Repairs
        Route::middleware('permission:repairs.create')->group(function () {
            Route::get('repairs/create', [RepairController::class, 'create'])->name('repairs.create');
            Route::post('repairs', [RepairController::class, 'store'])->name('repairs.store');
        });
        Route::middleware('permission:repairs.view')->group(function () {
            Route::get('repairs', [RepairController::class, 'index'])->name('repairs.index');
            Route::get('repairs/{repair}', [RepairController::class, 'show'])->name('repairs.show');
        });
        Route::middleware('permission:repairs.edit')->group(function () {
            Route::get('repairs/{repair}/edit', [RepairController::class, 'edit'])->name('repairs.edit');
            Route::put('repairs/{repair}', [RepairController::class, 'update'])->name('repairs.update');
        });
        Route::middleware('permission:repairs.delete')->group(function () {
            Route::delete('repairs/{repair}', [RepairController::class, 'destroy'])->name('repairs.destroy');
        });

        // Warranty Claims
        Route::middleware('permission:warranty.create')->group(function () {
            Route::get('warranty-claims/create', [WarrantyClaimController::class, 'create'])->name('warranty-claims.create');
            Route::post('warranty-claims', [WarrantyClaimController::class, 'store'])->name('warranty-claims.store');
        });
        Route::middleware('permission:warranty.view')->group(function () {
            Route::get('warranty-claims', [WarrantyClaimController::class, 'index'])->name('warranty-claims.index');
            Route::get('warranty-claims/{warrantyClaim}', [WarrantyClaimController::class, 'show'])->name('warranty-claims.show');
        });
        Route::middleware('permission:warranty.edit')->group(function () {
            Route::get('warranty-claims/{warrantyClaim}/edit', [WarrantyClaimController::class, 'edit'])->name('warranty-claims.edit');
            Route::put('warranty-claims/{warrantyClaim}', [WarrantyClaimController::class, 'update'])->name('warranty-claims.update');
        });
        Route::middleware('permission:warranty.delete')->group(function () {
            Route::delete('warranty-claims/{warrantyClaim}', [WarrantyClaimController::class, 'destroy'])->name('warranty-claims.destroy');
        });

        // Reports
        Route::middleware('permission:reports.view')->group(function () {
            Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
            Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        });
        Route::middleware('permission:reports.financial')->group(function () {
            Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
        });

        // Invoices (PDF)
        Route::middleware('permission:sales.view')->group(function () {
            Route::get('invoices/sale/{sale}', [InvoiceController::class, 'sale'])->name('invoices.sale');
        });
        Route::middleware('permission:repairs.view')->group(function () {
            Route::get('invoices/repair/{repair}', [InvoiceController::class, 'repair'])->name('invoices.repair');
        });

        // Exports
        Route::middleware('permission:reports.sales')->group(function () {
            Route::get('exports/sales', [ReportController::class, 'exportSales'])->name('exports.sales');
        });
        Route::middleware('permission:reports.inventory')->group(function () {
            Route::get('exports/products', [ReportController::class, 'exportProducts'])->name('exports.products');
        });
        Route::middleware('permission:reports.view')->group(function () {
            Route::get('exports/customers', [ReportController::class, 'exportCustomers'])->name('exports.customers');
            Route::get('exports/repairs', [ReportController::class, 'exportRepairs'])->name('exports.repairs');
            Route::get('exports/payments', [ReportController::class, 'exportPayments'])->name('exports.payments');
        });

        // Payments
        Route::middleware('permission:payments.create')->group(function () {
            Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
            Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        });
        Route::middleware('permission:payments.view')->group(function () {
            Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        });
        Route::middleware('permission:payments.delete')->group(function () {
            Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        });
    });

require __DIR__ . '/settings.php';