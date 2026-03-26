<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
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

type SupplierItem = {
    id: number;
    name: string;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    purchase_orders_count: number;
    created_at: string;
};

type PaginatedSuppliers = {
    data: SupplierItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

defineProps<{ suppliers: PaginatedSuppliers }>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Suppliers', href: '/admin/suppliers' },
];

const deleteDialog = ref(false);
const itemToDelete = ref<SupplierItem | null>(null);

function confirmDelete(item: SupplierItem) {
    itemToDelete.value = item;
    deleteDialog.value = true;
}

function deleteItem() {
    if (!itemToDelete.value) {
return;
}

    router.delete(`/admin/suppliers/${itemToDelete.value.id}`, {
        onFinish: () => {
            deleteDialog.value = false;
            itemToDelete.value = null;
        },
    });
}
</script>

<template>
    <Head title="Suppliers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Suppliers" description="Manage suppliers" />
                <Link v-if="can('suppliers.create')" href="/admin/suppliers/create">
                    <Button>Create Supplier</Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Contact Person</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Phone</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">POs</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Created</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in suppliers.data" :key="item.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-medium">{{ item.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.contact_person || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.email || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.phone || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.purchase_orders_count }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.created_at }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link v-if="can('suppliers.edit')" :href="`/admin/suppliers/${item.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button v-if="can('suppliers.delete')" variant="destructive" size="sm" @click="confirmDelete(item)">Delete</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="suppliers.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">No suppliers found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="suppliers.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in suppliers.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>

        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Supplier</DialogTitle>
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
