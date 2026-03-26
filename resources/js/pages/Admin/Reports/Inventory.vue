<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ProductRow = {
    id: number;
    name: string;
    sku: string;
    category: string;
    cost_price: string;
    selling_price: string;
    stock_quantity: number;
    min_stock_level: number;
    stock_value: string;
};

type PaginatedProducts = {
    data: ProductRow[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    filters: { category: string | null; stock_filter: string | null };
    products: PaginatedProducts;
    summary: {
        total_products: number;
        total_stock: number;
        total_stock_value: string;
        total_retail_value: string;
        low_stock_count: number;
        out_of_stock_count: number;
    };
    categoryBreakdown: Array<{ category: string; product_count: number; total_stock: number; stock_value: string }>;
    categories: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/admin/reports/sales' },
    { title: 'Inventory Report', href: '/admin/reports/inventory' },
];

const category = ref(props.filters.category ?? 'all');
const stockFilter = ref(props.filters.stock_filter ?? 'all');

function applyFilters() {
    router.get('/admin/reports/inventory', {
        category: category.value === 'all' ? undefined : category.value,
        stock_filter: stockFilter.value === 'all' ? undefined : stockFilter.value,
    }, { preserveState: true, replace: true });
}

function fmt(val: string | number | null): string {
    return Number(val ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<template>
    <Head title="Inventory Report" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Inventory Report" description="Stock levels and valuation overview" />
                <a href="/admin/exports/products">
                    <Button variant="outline"><Download class="mr-2 h-4 w-4" />Export CSV</Button>
                </a>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="flex flex-wrap items-end gap-4 pt-6">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Category</label>
                        <Select v-model="category">
                            <SelectTrigger class="w-44"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Categories</SelectItem>
                                <SelectItem v-for="(name, id) in categories" :key="id" :value="String(id)">{{ name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Stock Level</label>
                        <Select v-model="stockFilter">
                            <SelectTrigger class="w-36"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All</SelectItem>
                                <SelectItem value="low">Low Stock</SelectItem>
                                <SelectItem value="out">Out of Stock</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button @click="applyFilters">Apply</Button>
                </CardContent>
            </Card>

            <!-- Summary Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Active Products</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold">{{ summary.total_products }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Total Stock</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold">{{ Number(summary.total_stock).toLocaleString() }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Stock Value (Cost)</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold">${{ fmt(summary.total_stock_value) }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Retail Value</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold">${{ fmt(summary.total_retail_value) }}</p></CardContent>
                </Card>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Low Stock Items</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold text-orange-600">{{ summary.low_stock_count }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Out of Stock</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold text-red-600">{{ summary.out_of_stock_count }}</p></CardContent>
                </Card>
            </div>

            <!-- Category Breakdown -->
            <Card>
                <CardHeader><CardTitle>Stock by Category</CardTitle></CardHeader>
                <CardContent>
                    <div class="overflow-hidden rounded border">
                        <table class="w-full text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-muted-foreground">Category</th>
                                    <th class="px-3 py-2 text-right font-medium text-muted-foreground">Products</th>
                                    <th class="px-3 py-2 text-right font-medium text-muted-foreground">Total Stock</th>
                                    <th class="px-3 py-2 text-right font-medium text-muted-foreground">Stock Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cat in categoryBreakdown" :key="cat.category" class="border-b">
                                    <td class="px-3 py-2 font-medium">{{ cat.category }}</td>
                                    <td class="px-3 py-2 text-right">{{ cat.product_count }}</td>
                                    <td class="px-3 py-2 text-right">{{ Number(cat.total_stock).toLocaleString() }}</td>
                                    <td class="px-3 py-2 text-right font-medium">${{ fmt(cat.stock_value) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Products Table -->
            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Product</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">SKU</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Category</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Cost</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Price</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Stock</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Min</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in products.data" :key="product.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-medium">{{ product.name }}</td>
                            <td class="px-4 py-3 font-mono text-muted-foreground">{{ product.sku }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ product.category }}</td>
                            <td class="px-4 py-3 text-right">${{ fmt(product.cost_price) }}</td>
                            <td class="px-4 py-3 text-right">${{ fmt(product.selling_price) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span :class="product.stock_quantity <= product.min_stock_level ? 'text-destructive font-bold' : ''">
                                    {{ product.stock_quantity }}
                                </span>
                                <Badge v-if="product.stock_quantity <= 0" variant="destructive" class="ml-1">Out</Badge>
                                <Badge v-else-if="product.stock_quantity <= product.min_stock_level" variant="secondary" class="ml-1">Low</Badge>
                            </td>
                            <td class="px-4 py-3 text-right text-muted-foreground">{{ product.min_stock_level }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ fmt(product.stock_value) }}</td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">No products found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="products.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in products.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
