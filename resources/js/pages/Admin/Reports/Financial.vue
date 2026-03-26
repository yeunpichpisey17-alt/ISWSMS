<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    filters: { from: string; to: string };
    salesRevenue: { total: string; count: number };
    repairRevenue: { total: string; count: number };
    paymentsReceived: { total: string; count: number };
    paymentsByMethod: Array<{ payment_method: string; total: string; count: number }>;
    monthlyRevenue: Array<{ month: string; total: string }>;
    totalDiscount: string;
    totalTax: string;
    topCustomers: Array<{ name: string; total_paid: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/admin/reports/sales' },
    { title: 'Financial Report', href: '/admin/reports/financial' },
];

const from = ref(props.filters.from);
const to = ref(props.filters.to);

function applyFilters() {
    router.get('/admin/reports/financial', {
        from: from.value,
        to: to.value,
    }, { preserveState: true, replace: true });
}

function fmt(val: string | number | null): string {
    return Number(val ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatMethod(m: string): string {
    return m.replace(/_/g, ' ');
}
</script>

<template>
    <Head title="Financial Report" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Financial Report" description="Revenue, payments, and financial overview" />
                <a :href="`/admin/exports/payments?from=${from}&to=${to}`">
                    <Button variant="outline"><Download class="mr-2 h-4 w-4" />Export Payments</Button>
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
                    <Button @click="applyFilters">Apply</Button>
                </CardContent>
            </Card>

            <!-- Revenue Summary -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Sales Revenue</CardTitle></CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">${{ fmt(salesRevenue.total) }}</p>
                        <p class="text-xs text-muted-foreground">{{ salesRevenue.count }} completed sales</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Repair Revenue</CardTitle></CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">${{ fmt(repairRevenue.total) }}</p>
                        <p class="text-xs text-muted-foreground">{{ repairRevenue.count }} completed repairs</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Payments Received</CardTitle></CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold text-green-600">${{ fmt(paymentsReceived.total) }}</p>
                        <p class="text-xs text-muted-foreground">{{ paymentsReceived.count }} payments</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm text-muted-foreground">Total Discounts</CardTitle></CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold text-orange-600">${{ fmt(totalDiscount) }}</p>
                        <p class="text-xs text-muted-foreground">Tax collected: ${{ fmt(totalTax) }}</p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Payments by Method -->
                <Card>
                    <CardHeader><CardTitle>Payments by Method</CardTitle></CardHeader>
                    <CardContent>
                        <div v-if="paymentsByMethod.length === 0" class="py-8 text-center text-muted-foreground">No payment data.</div>
                        <div v-else class="space-y-3">
                            <div v-for="pm in paymentsByMethod" :key="pm.payment_method" class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium capitalize">{{ formatMethod(pm.payment_method) }}</p>
                                    <p class="text-xs text-muted-foreground">{{ pm.count }} transactions</p>
                                </div>
                                <p class="text-lg font-bold">${{ fmt(pm.total) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Customers -->
                <Card>
                    <CardHeader><CardTitle>Top Customers</CardTitle></CardHeader>
                    <CardContent>
                        <div v-if="topCustomers.length === 0" class="py-8 text-center text-muted-foreground">No customer data.</div>
                        <div v-else class="overflow-hidden rounded border">
                            <table class="w-full text-sm">
                                <thead class="border-b bg-muted/50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">#</th>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">Customer</th>
                                        <th class="px-3 py-2 text-right font-medium text-muted-foreground">Total Paid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(c, i) in topCustomers" :key="c.name" class="border-b">
                                        <td class="px-3 py-2 text-muted-foreground">{{ i + 1 }}</td>
                                        <td class="px-3 py-2 font-medium">{{ c.name }}</td>
                                        <td class="px-3 py-2 text-right font-medium">${{ fmt(c.total_paid) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Monthly Revenue Chart -->
            <Card>
                <CardHeader><CardTitle>Monthly Revenue (Last 12 Months)</CardTitle></CardHeader>
                <CardContent>
                    <div v-if="monthlyRevenue.length === 0" class="py-8 text-center text-muted-foreground">No data available.</div>
                    <div v-else class="space-y-2">
                        <div v-for="m in monthlyRevenue" :key="m.month" class="flex items-center gap-3 text-sm">
                            <span class="w-20 shrink-0 text-muted-foreground">{{ m.month }}</span>
                            <div class="flex-1">
                                <div class="h-6 rounded bg-primary/20">
                                    <div
                                        class="h-6 rounded bg-primary"
                                        :style="{ width: Math.max(4, (Number(m.total) / Math.max(...monthlyRevenue.map(x => Number(x.total)), 1)) * 100) + '%' }"
                                    />
                                </div>
                            </div>
                            <span class="w-24 text-right font-medium">${{ fmt(m.total) }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
