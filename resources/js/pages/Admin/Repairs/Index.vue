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

type RepairItem = {
    id: number;
    repair_number: string;
    customer: string;
    device_name: string;
    device_brand: string | null;
    status: string;
    priority: string;
    assigned_to: string;
    estimated_cost: string;
    created_at: string;
};

type PaginatedRepairs = {
    data: RepairItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    repairs: PaginatedRepairs;
    filters: { search?: string; status?: string; priority?: string };
}>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Repairs', href: '/admin/repairs' },
];

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');
const priority = ref(props.filters.priority ?? 'all');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

watch([status, priority], () => applyFilters());

function applyFilters() {
    router.get('/admin/repairs', {
        search: search.value || undefined,
        status: status.value === 'all' ? undefined : status.value,
        priority: priority.value === 'all' ? undefined : priority.value,
    }, { preserveState: true, replace: true });
}

const deleteDialog = ref(false);
const itemToDelete = ref<RepairItem | null>(null);

function confirmDelete(item: RepairItem) {
    itemToDelete.value = item;
    deleteDialog.value = true;
}

function deleteItem() {
    if (!itemToDelete.value) {
return;
}

    router.delete(`/admin/repairs/${itemToDelete.value.id}`, {
        onFinish: () => {
 deleteDialog.value = false; itemToDelete.value = null; 
},
    });
}

function statusVariant(s: string) {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        received: 'outline', diagnosing: 'secondary', waiting_parts: 'secondary',
        in_repair: 'secondary', completed: 'default', delivered: 'default', cancelled: 'destructive',
    };

    return map[s] ?? 'outline';
}

function priorityVariant(p: string) {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        low: 'outline', normal: 'secondary', high: 'default', urgent: 'destructive',
    };

    return map[p] ?? 'outline';
}

function formatStatus(s: string): string {
    return s.replace(/_/g, ' ');
}
</script>

<template>
    <Head title="Repairs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Repairs" description="Manage repair tickets and service orders" />
                <div class="flex gap-2">
                    <a href="/admin/exports/repairs">
                        <Button variant="outline"><Download class="mr-2 h-4 w-4" />Export</Button>
                    </a>
                    <Link v-if="can('repairs.create')" href="/admin/repairs/create">
                        <Button><Plus class="mr-2 h-4 w-4" />New Repair</Button>
                    </Link>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <Input v-model="search" placeholder="Search by repair #, device, customer..." class="w-72" />
                <Select v-model="status">
                    <SelectTrigger class="w-40"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="received">Received</SelectItem>
                        <SelectItem value="diagnosing">Diagnosing</SelectItem>
                        <SelectItem value="waiting_parts">Waiting Parts</SelectItem>
                        <SelectItem value="in_repair">In Repair</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                        <SelectItem value="delivered">Delivered</SelectItem>
                        <SelectItem value="cancelled">Cancelled</SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="priority">
                    <SelectTrigger class="w-36"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Priority</SelectItem>
                        <SelectItem value="low">Low</SelectItem>
                        <SelectItem value="normal">Normal</SelectItem>
                        <SelectItem value="high">High</SelectItem>
                        <SelectItem value="urgent">Urgent</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Repair #</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Customer</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Device</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Priority</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Technician</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Est. Cost</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="repair in repairs.data" :key="repair.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-mono font-medium">{{ repair.repair_number }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ repair.customer }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ repair.device_name }}</span>
                                <span v-if="repair.device_brand" class="ml-1 text-muted-foreground">({{ repair.device_brand }})</span>
                            </td>
                            <td class="px-4 py-3"><Badge :variant="statusVariant(repair.status)" class="capitalize">{{ formatStatus(repair.status) }}</Badge></td>
                            <td class="px-4 py-3"><Badge :variant="priorityVariant(repair.priority)" class="capitalize">{{ repair.priority }}</Badge></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ repair.assigned_to }}</td>
                            <td class="px-4 py-3 text-right">${{ Number(repair.estimated_cost).toFixed(2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/admin/repairs/${repair.id}`"><Button variant="outline" size="sm">View</Button></Link>
                                    <Link v-if="can('repairs.edit')" :href="`/admin/repairs/${repair.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button
                                        v-if="!['completed', 'delivered'].includes(repair.status) && can('repairs.delete')"
                                        variant="destructive" size="sm" @click="confirmDelete(repair)"
                                    >Delete</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="repairs.data.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">No repairs found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="repairs.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in repairs.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>

        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Repair</DialogTitle>
                    <DialogDescription>Are you sure you want to delete repair <strong>{{ itemToDelete?.repair_number }}</strong>?</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child><Button variant="outline">Cancel</Button></DialogClose>
                    <Button variant="destructive" @click="deleteItem">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
