<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Download } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog, DialogClose, DialogContent, DialogDescription,
    DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type SaleItem = {
    id: number;
    sale_number: string;
    customer: string;
    total_amount: string;
    grand_total: string;
    status: string;
    items_count: number;
    created_at: string;
};

type PaginatedSales = {
    data: SaleItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    sales: PaginatedSales;
    filters: { search?: string; status?: string };
}>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sales', href: '/admin/sales' },
];

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

watch(status, () => applyFilters());

function applyFilters() {
    router.get('/admin/sales', {
        search: search.value || undefined,
        status: status.value === 'all' ? undefined : status.value,
    }, { preserveState: true, replace: true });
}

const deleteDialog = ref(false);
const itemToDelete = ref<SaleItem | null>(null);

function confirmDelete(item: SaleItem) {
    itemToDelete.value = item;
    deleteDialog.value = true;
}

function deleteItem() {
    if (!itemToDelete.value) {
return;
}

    router.delete(`/admin/sales/${itemToDelete.value.id}`, {
        onFinish: () => {
 deleteDialog.value = false; itemToDelete.value = null; 
},
    });
}

function statusVariant(s: string) {
    switch (s) {
        case 'completed': return 'default' as const;
        case 'pending': return 'secondary' as const;
        case 'cancelled': return 'destructive' as const;
        default: return 'outline' as const;
    }
}
</script>

<template>
    <Head title="Sales" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Sales" description="Manage sales transactions" />
                <div class="flex gap-2">
                    <a href="/admin/exports/sales">
                        <Button variant="outline"><Download class="mr-2 h-4 w-4" />Export</Button>
                    </a>
                    <Link v-if="can('sales.create')" href="/admin/sales/create">
                        <Button><Plus class="mr-2 h-4 w-4" />New Sale</Button>
                    </Link>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <Input v-model="search" placeholder="Search by sale # or customer..." class="w-72" />
                <Select v-model="status">
                    <SelectTrigger class="w-36"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="cancelled">Cancelled</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Sale #</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Customer</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-center font-medium text-muted-foreground">Items</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Date</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sale in sales.data" :key="sale.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-mono font-medium">{{ sale.sale_number }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ sale.customer }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ Number(sale.grand_total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                            <td class="px-4 py-3"><Badge :variant="statusVariant(sale.status)" class="capitalize">{{ sale.status }}</Badge></td>
                            <td class="px-4 py-3 text-center">{{ sale.items_count }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ sale.created_at }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/admin/sales/${sale.id}`"><Button variant="outline" size="sm">View</Button></Link>
                                    <Link v-if="sale.status !== 'cancelled' && can('sales.edit')" :href="`/admin/sales/${sale.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button v-if="sale.status !== 'completed' && can('sales.delete')" variant="destructive" size="sm" @click="confirmDelete(sale)">Delete</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sales.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">No sales found.</td>
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

        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Sale</DialogTitle>
                    <DialogDescription>Are you sure you want to delete sale <strong>{{ itemToDelete?.sale_number }}</strong>? Stock quantities will be restored.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child><Button variant="outline">Cancel</Button></DialogClose>
                    <Button variant="destructive" @click="deleteItem">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
