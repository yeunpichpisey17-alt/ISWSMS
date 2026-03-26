<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type SaleDetail = {
    id: number;
    sale_number: string;
    customer: {
        id: number;
        name: string;
        phone?: string;
        email?: string;
    } | null;
    total_amount: string;
    discount: string;
    total_tax: string;
    grand_total: string;
    status: string;
    notes: string | null;
    created_at: string;
    items: Array<{
        id: number;
        product_name: string;
        product_sku: string;
        serial_number: string | null;
        quantity: number;
        unit_price: string;
        subtotal: string;
    }>;
};

const props = defineProps<{ sale: SaleDetail }>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sales', href: '/admin/sales' },
    { title: props.sale.sale_number, href: `/admin/sales/${props.sale.id}` },
];

function statusVariant(status: string) {
    switch (status) {
        case 'completed': return 'default' as const;
        case 'pending': return 'secondary' as const;
        case 'cancelled': return 'destructive' as const;
        default: return 'outline' as const;
    }
}
</script>

<template>
    <Head :title="`Sale ${sale.sale_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading :title="`Sale: ${sale.sale_number}`" description="View sale details" />
                <div class="flex gap-2">
                    <a :href="`/admin/invoices/sale/${sale.id}`">
                        <Button variant="outline"><Download class="mr-2 h-4 w-4" />Invoice PDF</Button>
                    </a>
                    <Link v-if="sale.status !== 'cancelled' && can('sales.edit')" :href="`/admin/sales/${sale.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Link href="/admin/sales">
                        <Button variant="outline">Back to List</Button>
                    </Link>
                </div>
            </div>

            <!-- Sale Info -->
            <Card>
                <CardContent class="grid grid-cols-2 gap-4 pt-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-muted-foreground">Sale Number</p>
                        <p class="font-medium font-mono">{{ sale.sale_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Customer</p>
                        <p class="font-medium">{{ sale.customer?.name ?? 'Walk-in' }}</p>
                        <p v-if="sale.customer?.phone" class="text-sm text-muted-foreground">{{ sale.customer.phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        <Badge :variant="statusVariant(sale.status)" class="capitalize">{{ sale.status }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Date</p>
                        <p class="font-medium">{{ sale.created_at }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Grand Total</p>
                        <p class="text-lg font-bold">${{ Number(sale.grand_total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</p>
                    </div>
                    <div v-if="sale.notes">
                        <p class="text-sm text-muted-foreground">Notes</p>
                        <p class="font-medium">{{ sale.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Items Table -->
            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Product</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">SKU</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Serial #</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Qty</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Unit Price</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in sale.items" :key="item.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-medium">{{ item.product_name }}</td>
                            <td class="px-4 py-3 text-muted-foreground font-mono">{{ item.product_sku }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.serial_number || '—' }}</td>
                            <td class="px-4 py-3 text-right">{{ item.quantity }}</td>
                            <td class="px-4 py-3 text-right">${{ Number(item.unit_price).toFixed(2) }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ Number(item.subtotal).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <Card>
                <CardContent class="pt-6">
                    <div class="ml-auto max-w-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>${{ Number(sale.total_amount).toFixed(2) }}</span>
                        </div>
                        <div v-if="Number(sale.discount) > 0" class="flex justify-between">
                            <span class="text-muted-foreground">Discount</span>
                            <span class="text-destructive">-${{ Number(sale.discount).toFixed(2) }}</span>
                        </div>
                        <div v-if="Number(sale.total_tax) > 0" class="flex justify-between">
                            <span class="text-muted-foreground">Tax</span>
                            <span>${{ Number(sale.total_tax).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-lg font-bold">Grand Total</span>
                            <span class="text-lg font-bold">${{ Number(sale.grand_total).toFixed(2) }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
