<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Download } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type PaymentItem = {
    id: number;
    payment_number: string;
    customer: string;
    reference: string;
    amount: string;
    payment_method: string;
    status: string;
    received_by: string | null;
    created_at: string;
};

type PaginatedPayments = {
    data: PaymentItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    payments: PaginatedPayments;
    filters: { search?: string; method?: string; status?: string };
}>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payments', href: '/admin/payments' },
];

const search = ref(props.filters.search ?? '');
const method = ref(props.filters.method ?? 'all');
const status = ref(props.filters.status ?? 'all');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

watch([method, status], () => applyFilters());

function applyFilters() {
    router.get('/admin/payments', {
        search: search.value || undefined,
        method: method.value === 'all' ? undefined : method.value,
        status: status.value === 'all' ? undefined : status.value,
    }, { preserveState: true, replace: true });
}

function statusVariant(s: string) {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        completed: 'default', pending: 'secondary', refunded: 'outline', failed: 'destructive',
    };

    return map[s] ?? 'outline';
}
</script>

<template>
    <Head title="Payments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading title="Payments" description="Track payment transactions" />
                <div class="flex gap-2">
                    <a href="/admin/exports/payments">
                        <Button variant="outline"><Download class="mr-2 h-4 w-4" />Export</Button>
                    </a>
                    <Link v-if="can('payments.create')" href="/admin/payments/create">
                        <Button><Plus class="mr-2 h-4 w-4" />Record Payment</Button>
                    </Link>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <Input v-model="search" placeholder="Search by payment # or customer..." class="w-72" />
                <Select v-model="method">
                    <SelectTrigger class="w-40"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Methods</SelectItem>
                        <SelectItem value="cash">Cash</SelectItem>
                        <SelectItem value="credit_card">Credit Card</SelectItem>
                        <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                        <SelectItem value="mobile_payment">Mobile Payment</SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="status">
                    <SelectTrigger class="w-36"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="refunded">Refunded</SelectItem>
                        <SelectItem value="failed">Failed</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Payment #</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Customer</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Reference</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Amount</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Method</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Received By</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Date</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in payments.data" :key="payment.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-mono font-medium">{{ payment.payment_number }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ payment.customer }}</td>
                            <td class="px-4 py-3 font-mono text-muted-foreground">{{ payment.reference }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ Number(payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                            <td class="px-4 py-3 capitalize">{{ payment.payment_method.replace('_', ' ') }}</td>
                            <td class="px-4 py-3"><Badge :variant="statusVariant(payment.status)" class="capitalize">{{ payment.status }}</Badge></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ payment.received_by || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ payment.created_at }}</td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="`/admin/payments/${payment.id}`"><Button variant="outline" size="sm">View</Button></Link>
                            </td>
                        </tr>
                        <tr v-if="payments.data.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-muted-foreground">No payments found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="payments.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in payments.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="rounded-md border px-3 py-1.5 text-sm transition-colors hover:bg-muted" :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''" v-html="link.label" />
                    <span v-else class="rounded-md px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
