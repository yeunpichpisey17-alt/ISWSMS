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

type CategoryItem = {
    id: number;
    name: string;
    description: string | null;
    products_count: number;
    created_at: string;
};

type PaginatedCategories = {
    data: CategoryItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

defineProps<{ categories: PaginatedCategories }>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Categories', href: '/admin/categories' },
];

const deleteDialog = ref(false);
const itemToDelete = ref<CategoryItem | null>(null);

function confirmDelete(item: CategoryItem) {
    itemToDelete.value = item;
    deleteDialog.value = true;
}

function deleteItem() {
    if (!itemToDelete.value) {
return;
}

    router.delete(`/admin/categories/${itemToDelete.value.id}`, {
        onFinish: () => {
            deleteDialog.value = false;
            itemToDelete.value = null;
        },
    });
}
</script>

<template>
    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Categories" description="Manage product categories" />
                <Link v-if="can('products.create')" href="/admin/categories/create">
                    <Button>Create Category</Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Description</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Products</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Created</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in categories.data" :key="item.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-medium">{{ item.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.description || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.products_count }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.created_at }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link v-if="can('products.edit')" :href="`/admin/categories/${item.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button v-if="can('products.delete')" variant="destructive" size="sm" @click="confirmDelete(item)">Delete</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No categories found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="categories.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in categories.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>

        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Category</DialogTitle>
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
