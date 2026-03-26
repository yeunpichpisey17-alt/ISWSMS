<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
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

type ClaimItem = {
    id: number;
    claim_number: string;
    customer: string;
    product: string;
    serial_number: string | null;
    status: string;
    warranty_end: string;
    is_expired: boolean;
    created_at: string;
};

type PaginatedClaims = {
    data: ClaimItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    claims: PaginatedClaims;
    filters: { search?: string; status?: string };
}>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Warranty Claims', href: '/admin/warranty-claims' },
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
    router.get('/admin/warranty-claims', {
        search: search.value || undefined,
        status: status.value === 'all' ? undefined : status.value,
    }, { preserveState: true, replace: true });
}

const deleteDialog = ref(false);
const itemToDelete = ref<ClaimItem | null>(null);

function confirmDelete(item: ClaimItem) {
    itemToDelete.value = item;
    deleteDialog.value = true;
}

function deleteItem() {
    if (!itemToDelete.value) {
return;
}

    router.delete(`/admin/warranty-claims/${itemToDelete.value.id}`, {
        onFinish: () => {
 deleteDialog.value = false; itemToDelete.value = null; 
},
    });
}

function statusVariant(s: string) {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        submitted: 'outline', under_review: 'secondary', approved: 'default',
        rejected: 'destructive', in_repair: 'secondary', completed: 'default',
    };

    return map[s] ?? 'outline';
}

function formatStatus(s: string): string {
    return s.replace(/_/g, ' ');
}
</script>

<template>
    <Head title="Warranty Claims" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Warranty Claims" description="Manage warranty claims and coverage" />
                <Link v-if="can('warranty.create')" href="/admin/warranty-claims/create">
                    <Button><Plus class="mr-2 h-4 w-4" />New Claim</Button>
                </Link>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <Input v-model="search" placeholder="Search by claim #, serial #, customer..." class="w-72" />
                <Select v-model="status">
                    <SelectTrigger class="w-40"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="submitted">Submitted</SelectItem>
                        <SelectItem value="under_review">Under Review</SelectItem>
                        <SelectItem value="approved">Approved</SelectItem>
                        <SelectItem value="rejected">Rejected</SelectItem>
                        <SelectItem value="in_repair">In Repair</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Claim #</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Customer</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Product</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Serial #</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Warranty Until</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="claim in claims.data" :key="claim.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-mono font-medium">{{ claim.claim_number }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ claim.customer }}</td>
                            <td class="px-4 py-3">{{ claim.product }}</td>
                            <td class="px-4 py-3 font-mono text-muted-foreground">{{ claim.serial_number || '—' }}</td>
                            <td class="px-4 py-3"><Badge :variant="statusVariant(claim.status)" class="capitalize">{{ formatStatus(claim.status) }}</Badge></td>
                            <td class="px-4 py-3">
                                <span :class="claim.is_expired ? 'text-destructive' : 'text-muted-foreground'">
                                    {{ claim.warranty_end }}
                                    <span v-if="claim.is_expired" class="ml-1 text-xs">(expired)</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/admin/warranty-claims/${claim.id}`"><Button variant="outline" size="sm">View</Button></Link>
                                    <Link v-if="can('warranty.edit')" :href="`/admin/warranty-claims/${claim.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button
                                        v-if="!['completed', 'in_repair'].includes(claim.status) && can('warranty.delete')"
                                        variant="destructive" size="sm" @click="confirmDelete(claim)"
                                    >Delete</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="claims.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">No warranty claims found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="claims.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in claims.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>

        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Warranty Claim</DialogTitle>
                    <DialogDescription>Are you sure you want to delete claim <strong>{{ itemToDelete?.claim_number }}</strong>?</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child><Button variant="outline">Cancel</Button></DialogClose>
                    <Button variant="destructive" @click="deleteItem">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
