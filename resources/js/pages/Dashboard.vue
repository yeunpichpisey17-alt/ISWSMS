<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { usePermissions } from '@/composables/usePermissions';
import type { BreadcrumbItem } from '@/types';
import {
    DollarSign,
    ShoppingBag,
    Users,
    Package,
    AlertTriangle,
    Wrench,
    ShieldCheck,
    CreditCard,
} from 'lucide-vue-next';

type Stats = {
    totalSales: string;
    totalSalesCount: number;
    totalCustomers: number;
    totalProducts: number;
    lowStockCount: number;
    pendingRepairs: number;
    pendingWarranty: number;
    totalRevenue: string;
};

type MonthlySale = {
    month: string;
    total: string;
    count: number;
};

type RecentSale = {
    id: number;
    sale_number: string;
    customer: string;
    grand_total: string;
    status: string;
    created_at: string;
};

type RecentRepair = {
    id: number;
    repair_number: string;
    customer: string;
    device_name: string;
    status: string;
    created_at: string;
};

type LowStockProduct = {
    id: number;
    name: string;
    sku: string;
    category: string;
    stock_quantity: number;
    min_stock_level: number;
};

defineProps<{
    stats: Stats;
    monthlySales: MonthlySale[];
    recentSales: RecentSale[];
    recentRepairs: RecentRepair[];
    lowStockProducts: LowStockProduct[];
}>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
];

function statusVariant(status: string) {
    switch (status) {
        case 'completed': case 'delivered': return 'default' as const;
        case 'pending': case 'received': case 'diagnosing': case 'in_repair': return 'secondary' as const;
        case 'cancelled': return 'destructive' as const;
        default: return 'outline' as const;
    }
}

function formatStatus(s: string): string {
    return s.replace(/_/g, ' ');
}

function formatCurrency(value: string | number): string {
    return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-6 p-10">
            <!-- Stat Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="flex items-center gap-4 pt-6">
                        <div class="rounded-lg bg-primary/10 p-3">
                            <DollarSign class="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Revenue</p>
                            <p class="text-2xl font-bold">${{ formatCurrency(stats.totalRevenue) }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 pt-6">
                        <div class="rounded-lg bg-blue-500/10 p-3">
                            <ShoppingBag class="h-6 w-6 text-blue-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Sales</p>
                            <p class="text-2xl font-bold">{{ stats.totalSalesCount }}</p>
                            <p class="text-xs text-muted-foreground">${{ formatCurrency(stats.totalSales) }} completed</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 pt-6">
                        <div class="rounded-lg bg-green-500/10 p-3">
                            <Users class="h-6 w-6 text-green-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Customers</p>
                            <p class="text-2xl font-bold">{{ stats.totalCustomers }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 pt-6">
                        <div class="rounded-lg bg-purple-500/10 p-3">
                            <Package class="h-6 w-6 text-purple-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Products</p>
                            <p class="text-2xl font-bold">{{ stats.totalProducts }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Alert Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card v-if="stats.lowStockCount > 0" class="border-orange-200 dark:border-orange-800">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <div class="rounded-lg bg-orange-500/10 p-3">
                            <AlertTriangle class="h-6 w-6 text-orange-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Low Stock Alert</p>
                            <p class="text-2xl font-bold">{{ stats.lowStockCount }}</p>
                            <p class="text-xs text-muted-foreground">products need restocking</p>
                        </div>
                    </CardContent>
                </Card>
                <Card v-if="stats.pendingRepairs > 0">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <div class="rounded-lg bg-yellow-500/10 p-3">
                            <Wrench class="h-6 w-6 text-yellow-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Active Repairs</p>
                            <p class="text-2xl font-bold">{{ stats.pendingRepairs }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card v-if="stats.pendingWarranty > 0">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <div class="rounded-lg bg-indigo-500/10 p-3">
                            <ShieldCheck class="h-6 w-6 text-indigo-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Open Warranty Claims</p>
                            <p class="text-2xl font-bold">{{ stats.pendingWarranty }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Monthly Sales Chart (simple bar visualization) -->
            <Card v-if="monthlySales.length > 0">
                <CardContent class="pt-6">
                    <h3 class="mb-4 text-lg font-semibold">Monthly Sales (Last 12 Months)</h3>
                    <div class="flex items-end gap-2" style="height: 200px">
                        <div
                            v-for="sale in monthlySales"
                            :key="sale.month"
                            class="flex flex-1 flex-col items-center gap-1"
                        >
                            <span class="text-xs font-medium">${{ (Number(sale.total) / 1000).toFixed(1) }}k</span>
                            <div
                                class="w-full rounded-t bg-primary transition-all"
                                :style="{
                                    height: `${Math.max(8, (Number(sale.total) / Math.max(...monthlySales.map(s => Number(s.total)))) * 160)}px`,
                                }"
                            />
                            <span class="text-xs text-muted-foreground">{{ sale.month.slice(5) }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Sales -->
                <Card>
                    <CardContent class="pt-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold">Recent Sales</h3>
                            <Link v-if="can('sales.view')" href="/admin/sales">
                                <Button variant="ghost" size="sm">View All</Button>
                            </Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="sale in recentSales" :key="sale.id" class="flex items-center justify-between rounded-lg border p-3">
                                <div>
                                    <Link :href="`/admin/sales/${sale.id}`" class="font-mono text-sm font-medium hover:text-primary">
                                        {{ sale.sale_number }}
                                    </Link>
                                    <p class="text-sm text-muted-foreground">{{ sale.customer }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">${{ formatCurrency(sale.grand_total) }}</p>
                                    <Badge :variant="statusVariant(sale.status)" class="capitalize">{{ sale.status }}</Badge>
                                </div>
                            </div>
                            <p v-if="recentSales.length === 0" class="py-4 text-center text-muted-foreground">No sales yet.</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Repairs -->
                <Card>
                    <CardContent class="pt-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold">Recent Repairs</h3>
                            <Link v-if="can('repairs.view')" href="/admin/repairs">
                                <Button variant="ghost" size="sm">View All</Button>
                            </Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="repair in recentRepairs" :key="repair.id" class="flex items-center justify-between rounded-lg border p-3">
                                <div>
                                    <Link :href="`/admin/repairs/${repair.id}`" class="font-mono text-sm font-medium hover:text-primary">
                                        {{ repair.repair_number }}
                                    </Link>
                                    <p class="text-sm text-muted-foreground">{{ repair.customer }} &mdash; {{ repair.device_name }}</p>
                                </div>
                                <Badge :variant="statusVariant(repair.status)" class="capitalize">{{ formatStatus(repair.status) }}</Badge>
                            </div>
                            <p v-if="recentRepairs.length === 0" class="py-4 text-center text-muted-foreground">No repairs yet.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Low Stock Products -->
            <Card v-if="lowStockProducts.length > 0">
                <CardContent class="pt-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Low Stock Products</h3>
                        <Link v-if="can('products.view')" href="/admin/products">
                            <Button variant="ghost" size="sm">View All Products</Button>
                        </Link>
                    </div>
                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-muted-foreground">Product</th>
                                    <th class="px-4 py-2 text-left font-medium text-muted-foreground">SKU</th>
                                    <th class="px-4 py-2 text-left font-medium text-muted-foreground">Category</th>
                                    <th class="px-4 py-2 text-right font-medium text-muted-foreground">Stock</th>
                                    <th class="px-4 py-2 text-right font-medium text-muted-foreground">Min Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in lowStockProducts" :key="product.id" class="border-b">
                                    <td class="px-4 py-2 font-medium">{{ product.name }}</td>
                                    <td class="px-4 py-2 font-mono text-muted-foreground">{{ product.sku }}</td>
                                    <td class="px-4 py-2 text-muted-foreground">{{ product.category }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-destructive">{{ product.stock_quantity }}</td>
                                    <td class="px-4 py-2 text-right text-muted-foreground">{{ product.min_stock_level }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
