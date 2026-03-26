<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type POItem = {
    id: number;
    po_number: string;
    supplier: string;
    status: string;
    order_date: string;
    total_amount: string;
    created_by: string;
    created_at: string;
};

type PaginatedPOs = {
    data: POItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

defineProps<{ purchaseOrders: PaginatedPOs }>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Purchase Orders', href: '/admin/purchase-orders' },
];

const deleteDialog = ref(false);
const itemToDelete = ref<POItem | null>(null);

function confirmDelete(item: POItem) {
    itemToDelete.value = item;
    deleteDialog.value = true;
}

function deleteItem() {
    if (!itemToDelete.value) {
return;
}

    router.delete(`/admin/purchase-orders/${itemToDelete.value.id}`, {
        onFinish: () => {
            deleteDialog.value = false;
            itemToDelete.value = null;
        },
    });
}

function statusVariant(status: string) {
    switch (status) {
        case 'draft': return 'outline' as const;
        case 'pending': return 'secondary' as const;
        case 'received': return 'default' as const;
        case 'cancelled': return 'destructive' as const;
        default: return 'outline' as const;
    }
}
</script>

<template>
    <Head title="Purchase Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Purchase Orders" description="Manage purchase orders and stock receiving" />
                <Link v-if="can('purchases.create')" href="/admin/purchase-orders/create">
                    <Button>Create Purchase Order</Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">PO Number</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Supplier</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Order Date</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Created By</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="po in purchaseOrders.data" :key="po.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-medium">{{ po.po_number }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ po.supplier }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="statusVariant(po.status)" class="capitalize">{{ po.status }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ po.order_date }}</td>
                            <td class="px-4 py-3 text-right">${{ Number(po.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ po.created_by }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/admin/purchase-orders/${po.id}`">
                                        <Button variant="outline" size="sm">View</Button>
                                    </Link>
                                    <Link v-if="po.status === 'draft' && can('purchases.edit')" :href="`/admin/purchase-orders/${po.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button v-if="po.status === 'draft' && can('purchases.delete')" variant="destructive" size="sm" @click="confirmDelete(po)">Delete</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="purchaseOrders.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">No purchase orders found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="purchaseOrders.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in purchaseOrders.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>

        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Purchase Order</DialogTitle>
                    <DialogDescription>Are you sure you want to delete <strong>{{ itemToDelete?.po_number }}</strong>? This action cannot be undone.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child><Button variant="outline">Cancel</Button></DialogClose>
                    <Button variant="destructive" @click="deleteItem">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
