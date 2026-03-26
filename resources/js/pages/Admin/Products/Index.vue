<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
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

type ProductItem = {
    id: number;
    name: string;
    sku: string;
    category: string;
    cost_price: string;
    selling_price: string;
    stock_quantity: number;
    min_stock_level: number;
    is_low_stock: boolean;
    is_active: boolean;
    image: string | null;
    created_at: string;
};

type PaginatedProducts = {
    data: ProductItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    products: PaginatedProducts;
    categories: Record<string, string>;
    filters: { search?: string; category?: string; stock?: string };
}>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/admin/products' },
];

const search = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? 'all');
const stock = ref(props.filters.stock ?? 'all');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

watch([category, stock], () => applyFilters());

function applyFilters() {
    router.get('/admin/products', {
        search: search.value || undefined,
        category: category.value === 'all' ? undefined : category.value,
        stock: stock.value === 'all' ? undefined : stock.value,
    }, { preserveState: true, replace: true });
}

const deleteDialog = ref(false);
const itemToDelete = ref<ProductItem | null>(null);

function confirmDelete(item: ProductItem) {
    itemToDelete.value = item;
    deleteDialog.value = true;
}

function deleteItem() {
    if (!itemToDelete.value) {
return;
}

    router.delete(`/admin/products/${itemToDelete.value.id}`, {
        onFinish: () => {
 deleteDialog.value = false; itemToDelete.value = null; 
},
    });
}
</script>

<template>
    <Head title="Products" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Products" description="Manage products inventory" />
                <div class="flex gap-2">
                    <a href="/admin/exports/products">
                        <Button variant="outline"><Download class="mr-2 h-4 w-4" />Export</Button>
                    </a>
                    <Link v-if="can('products.create')" href="/admin/products/create">
                        <Button>Create Product</Button>
                    </Link>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <Input v-model="search" placeholder="Search by name or SKU..." class="w-64" />
                <Select v-model="category">
                    <SelectTrigger class="w-44"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Categories</SelectItem>
                        <SelectItem v-for="(name, id) in categories" :key="id" :value="String(id)">{{ name }}</SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="stock">
                    <SelectTrigger class="w-36"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Stock</SelectItem>
                        <SelectItem value="low">Low Stock</SelectItem>
                        <SelectItem value="out">Out of Stock</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Image</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">SKU</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Category</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cost Price</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Selling Price</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Stock</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Active</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in products.data" :key="item.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3">
                                <img v-if="item.image" :src="item.image" :alt="item.name" class="h-10 w-10 rounded object-cover" />
                                <div v-else class="flex h-10 w-10 items-center justify-center rounded bg-muted text-xs text-muted-foreground">N/A</div>
                            </td>
                            <td class="px-4 py-3 font-medium">{{ item.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.sku }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.category }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.cost_price }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.selling_price }}</td>
                            <td class="px-4 py-3">
                                <span class="text-muted-foreground">{{ item.stock_quantity }}</span>
                                <Badge v-if="item.is_low_stock" variant="destructive" class="ml-2">Low Stock</Badge>
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="item.is_active ? 'default' : 'secondary'">{{ item.is_active ? 'Active' : 'Inactive' }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link v-if="can('products.edit')" :href="`/admin/products/${item.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button v-if="can('products.delete')" variant="destructive" size="sm" @click="confirmDelete(item)">Delete</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-muted-foreground">No products found.</td>
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

        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Product</DialogTitle>
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
