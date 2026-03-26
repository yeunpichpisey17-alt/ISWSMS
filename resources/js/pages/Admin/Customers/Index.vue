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

type Customer = {
    id: number;
    name: string;
    phone?: string;
    email?: string;
    status: string;
    sales_count: number;
    created_at: string;
};

type PaginatedCustomers = {
    data: Customer[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    customers: PaginatedCustomers;
    filters: { search?: string; status?: string };
}>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Customers', href: '/admin/customers' },
];

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

watch(status, () => applyFilters());

function applyFilters() {
    router.get('/admin/customers', {
        search: search.value || undefined,
        status: status.value === 'all' ? undefined : status.value,
    }, { preserveState: true, replace: true });
}

const deleteDialog = ref(false);
const itemToDelete = ref<Customer | null>(null);

function confirmDelete(item: Customer) {
    itemToDelete.value = item;
    deleteDialog.value = true;
}

function deleteItem() {
    if (!itemToDelete.value) {
return;
}

    router.delete(`/admin/customers/${itemToDelete.value.id}`, {
        onFinish: () => {
 deleteDialog.value = false; itemToDelete.value = null; 
},
    });
}
</script>

<template>
    <Head title="Customers" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Customers" description="Manage your customer database" />
                <div class="flex gap-2">
                    <a href="/admin/exports/customers">
                        <Button variant="outline"><Download class="mr-2 h-4 w-4" />Export</Button>
                    </a>
                    <Link v-if="can('customers.create')" href="/admin/customers/create">
                        <Button><Plus class="mr-2 h-4 w-4" />New Customer</Button>
                    </Link>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <Input v-model="search" placeholder="Search by name, phone, email..." class="w-72" />
                <Select v-model="status">
                    <SelectTrigger class="w-36"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Phone</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-center font-medium text-muted-foreground">Sales</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="customer in customers.data" :key="customer.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-medium">{{ customer.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ customer.phone || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ customer.email || '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ customer.sales_count }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="customer.status === 'active' ? 'default' : 'destructive'" class="capitalize">{{ customer.status }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link v-if="can('customers.edit')" :href="`/admin/customers/${customer.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button v-if="can('customers.delete')" variant="destructive" size="sm" @click="confirmDelete(customer)">Delete</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customers.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No customers found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="customers.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in customers.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>

        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Customer</DialogTitle>
                    <DialogDescription>Are you sure you want to delete <strong>{{ itemToDelete?.name }}</strong>? This action cannot be undone.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child><Button variant="outline">Cancel</Button></DialogClose>
                    <Button variant="destructive" @click="deleteItem">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
