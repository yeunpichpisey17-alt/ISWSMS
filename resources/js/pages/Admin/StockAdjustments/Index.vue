<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type AdjustmentItem = {
    id: number;
    product: string;
    product_sku: string;
    type: string;
    quantity: number;
    reason: string;
    adjusted_by: string;
    created_at: string;
};

type PaginatedAdjustments = {
    data: AdjustmentItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

defineProps<{ adjustments: PaginatedAdjustments }>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Stock Adjustments', href: '/admin/stock-adjustments' },
];

function typeBadgeVariant(type: string) {
    switch (type) {
        case 'addition':
            return 'default';
        case 'deduction':
            return 'destructive';
        default:
            return 'secondary';
    }
}
</script>

<template>
    <Head title="Stock Adjustments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Stock Adjustments" description="Manual stock quantity adjustments" />
                <Link v-if="can('inventory.create')" href="/admin/stock-adjustments/create">
                    <Button>New Adjustment</Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Product</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">SKU</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Type</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Quantity</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Reason</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Adjusted By</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in adjustments.data" :key="item.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-medium">{{ item.product }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.product_sku }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="typeBadgeVariant(item.type)" class="capitalize">{{ item.type }}</Badge>
                            </td>
                            <td class="px-4 py-3">{{ item.quantity }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-muted-foreground">{{ item.reason }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.adjusted_by }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.created_at }}</td>
                        </tr>
                        <tr v-if="adjustments.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">No stock adjustments found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="adjustments.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in adjustments.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
