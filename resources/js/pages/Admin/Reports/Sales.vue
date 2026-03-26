<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type SaleRow = {
    id: number;
    sale_number: string;
    customer: string;
    grand_total: string;
    discount: string;
    status: string;
    created_at: string;
};

type PaginatedSales = {
    data: SaleRow[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    filters: { from: string; to: string; status: string | null };
    sales: PaginatedSales;
    summary: {
        total_count: number;
        completed_revenue: string;
        pending_revenue: string;
        total_revenue: string;
        total_discount: string;
        total_tax: string;
    };
    topProducts: Array<{ name: string; sku: string; total_qty: number; total_revenue: string }>;
    dailySales: Array<{ date: string; count: number; total: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/admin/reports/sales' },
    { title: 'Sales Report', href: '/admin/reports/sales' },
];

const from = ref(props.filters.from);
const to = ref(props.filters.to);
const status = ref(props.filters.status ?? 'all');

function applyFilters() {
    router.get('/admin/reports/sales', {
        from: from.value,
        to: to.value,
        status: status.value === 'all' ? undefined : status.value,
    }, { preserveState: true, replace: true });
}

function statusVariant(s: string) {
    const map: Record<string, 'default' | 'secondary' | 'destructive'> = {
        completed: 'default', pending: 'secondary', cancelled: 'destructive',
    };

    return map[s] ?? ('outline' as const);
}

function fmt(val: string | number | null): string {
    return Number(val ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<template>
    <Head title="Sales Report" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Sales Report" description="Analyze sales performance and trends" />
                <a :href="`/admin/exports/sales?from=${from}&to=${to}`">
                    <Button variant="outline"><Download class="mr-2 h-4 w-4" />Export CSV</Button>
                </a>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="flex flex-wrap items-end gap-4 pt-6">
                    <div class="space-y-1">
                        <label class="text-sm font-medium">From</label>
                        <Input v-model="from" type="date" class="w-40" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">To</label>
                        <Input v-model="to" type="date" class="w-40" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Status</label>
                        <Select v-model="status">
                            <SelectTrigger class="w-36"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All</SelectItem>
                                <SelectItem value="completed">Completed</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="cancelled">Cancelled</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button @click="applyFilters">Apply</Button>
                </CardContent>
            </Card>

            <!-- Summary Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Total Sales</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold">{{ summary.total_count }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Total Revenue</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold">${{ fmt(summary.total_revenue) }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Completed Revenue</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold text-green-600">${{ fmt(summary.completed_revenue) }}</p></CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Total Discount</CardTitle></CardHeader>
                    <CardContent><p class="text-2xl font-bold text-orange-600">${{ fmt(summary.total_discount) }}</p></CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Daily Sales Chart (simple bar) -->
                <Card>
                    <CardHeader><CardTitle>Daily Sales</CardTitle></CardHeader>
                    <CardContent>
                        <div v-if="dailySales.length === 0" class="py-8 text-center text-muted-foreground">No data for selected period.</div>
                        <div v-else class="space-y-2">
                            <div v-for="day in dailySales" :key="day.date" class="flex items-center gap-3 text-sm">
                                <span class="w-24 shrink-0 text-muted-foreground">{{ day.date }}</span>
                                <div class="flex-1">
                                    <div class="h-5 rounded bg-primary/20">
                                        <div
                                            class="h-5 rounded bg-primary"
                                            :style="{ width: Math.max(4, (Number(day.total) / Math.max(...dailySales.map(d => Number(d.total)), 1)) * 100) + '%' }"
                                        />
                                    </div>
                                </div>
                                <span class="w-20 text-right font-medium">${{ fmt(day.total) }}</span>
                                <span class="w-8 text-right text-muted-foreground">{{ day.count }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Products -->
                <Card>
                    <CardHeader><CardTitle>Top Products</CardTitle></CardHeader>
                    <CardContent>
                        <div v-if="topProducts.length === 0" class="py-8 text-center text-muted-foreground">No data for selected period.</div>
                        <div v-else class="overflow-hidden rounded border">
                            <table class="w-full text-sm">
                                <thead class="border-b bg-muted/50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">Product</th>
                                        <th class="px-3 py-2 text-right font-medium text-muted-foreground">Qty</th>
                                        <th class="px-3 py-2 text-right font-medium text-muted-foreground">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in topProducts" :key="p.sku" class="border-b">
                                        <td class="px-3 py-2">
                                            <span class="font-medium">{{ p.name }}</span>
                                            <span class="ml-1 text-xs text-muted-foreground">({{ p.sku }})</span>
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ p.total_qty }}</td>
                                        <td class="px-3 py-2 text-right font-medium">${{ fmt(p.total_revenue) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Sales Table -->
            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Sale #</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Customer</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Grand Total</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Discount</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sale in sales.data" :key="sale.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-mono font-medium">
                                <Link :href="`/admin/sales/${sale.id}`" class="hover:underline">{{ sale.sale_number }}</Link>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ sale.customer }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ fmt(sale.grand_total) }}</td>
                            <td class="px-4 py-3 text-right text-muted-foreground">${{ fmt(sale.discount) }}</td>
                            <td class="px-4 py-3"><Badge :variant="statusVariant(sale.status)" class="capitalize">{{ sale.status }}</Badge></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ sale.created_at }}</td>
                        </tr>
                        <tr v-if="sales.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No sales found for the selected period.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="sales.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in sales.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
