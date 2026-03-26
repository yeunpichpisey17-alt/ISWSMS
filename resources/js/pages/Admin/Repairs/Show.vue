<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type RepairDetail = {
    id: number;
    repair_number: string;
    customer: { id: number; name: string; phone?: string; email?: string };
    product: { id: number; name: string; sku: string } | null;
    serial_number: string | null;
    device_name: string;
    device_brand: string | null;
    device_model: string | null;
    issue_description: string;
    diagnosis: string | null;
    resolution: string | null;
    status: string;
    priority: string;
    estimated_cost: string;
    actual_cost: string;
    assigned_to: string | null;
    received_by: string;
    estimated_completion: string | null;
    completed_at: string | null;
    delivered_at: string | null;
    notes: string | null;
    created_at: string;
    parts: Array<{
        id: number;
        part_name: string;
        product: string | null;
        quantity: number;
        unit_cost: string;
        total_cost: string;
    }>;
    payments: Array<{
        id: number;
        payment_number: string;
        amount: string;
        payment_method: string;
        status: string;
        created_at: string;
    }>;
};

const props = defineProps<{
    repair: RepairDetail;
    technicians: Array<{ id: number; name: string }>;
}>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Repairs', href: '/admin/repairs' },
    { title: props.repair.repair_number, href: `/admin/repairs/${props.repair.id}` },
];

function statusVariant(status: string) {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        received: 'outline', diagnosing: 'secondary', waiting_parts: 'secondary',
        in_repair: 'secondary', completed: 'default', delivered: 'default', cancelled: 'destructive',
    };

    return map[status] ?? 'outline';
}

function priorityVariant(priority: string) {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        low: 'outline', normal: 'secondary', high: 'default', urgent: 'destructive',
    };

    return map[priority] ?? 'outline';
}

function formatStatus(s: string): string {
    return s.replace(/_/g, ' ');
}
</script>

<template>
    <Head :title="`Repair ${repair.repair_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading :title="`Repair: ${repair.repair_number}`" description="View repair ticket details" />
                <div class="flex gap-2">
                    <a :href="`/admin/invoices/repair/${repair.id}`">
                        <Button variant="outline"><Download class="mr-2 h-4 w-4" />Invoice PDF</Button>
                    </a>
                    <Link v-if="can('repairs.edit')" :href="`/admin/repairs/${repair.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Link href="/admin/repairs">
                        <Button variant="outline">Back to List</Button>
                    </Link>
                </div>
            </div>

            <!-- Repair Info -->
            <Card>
                <CardContent class="grid grid-cols-2 gap-4 pt-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-muted-foreground">Repair Number</p>
                        <p class="font-medium font-mono">{{ repair.repair_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Customer</p>
                        <p class="font-medium">{{ repair.customer.name }}</p>
                        <p v-if="repair.customer.phone" class="text-sm text-muted-foreground">{{ repair.customer.phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        <Badge :variant="statusVariant(repair.status)" class="capitalize">{{ formatStatus(repair.status) }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Device</p>
                        <p class="font-medium">{{ repair.device_name }}</p>
                        <p v-if="repair.device_brand || repair.device_model" class="text-sm text-muted-foreground">
                            {{ [repair.device_brand, repair.device_model].filter(Boolean).join(' ') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Priority</p>
                        <Badge :variant="priorityVariant(repair.priority)" class="capitalize">{{ repair.priority }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Assigned To</p>
                        <p class="font-medium">{{ repair.assigned_to || 'Unassigned' }}</p>
                    </div>
                    <div v-if="repair.serial_number">
                        <p class="text-sm text-muted-foreground">Serial Number</p>
                        <p class="font-medium font-mono">{{ repair.serial_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Estimated Cost</p>
                        <p class="font-medium">${{ Number(repair.estimated_cost).toFixed(2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Actual Cost</p>
                        <p class="font-medium">${{ Number(repair.actual_cost).toFixed(2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Received By</p>
                        <p class="font-medium">{{ repair.received_by }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Created</p>
                        <p class="font-medium">{{ repair.created_at }}</p>
                    </div>
                    <div v-if="repair.estimated_completion">
                        <p class="text-sm text-muted-foreground">Est. Completion</p>
                        <p class="font-medium">{{ repair.estimated_completion }}</p>
                    </div>
                    <div v-if="repair.completed_at">
                        <p class="text-sm text-muted-foreground">Completed At</p>
                        <p class="font-medium">{{ repair.completed_at }}</p>
                    </div>
                    <div v-if="repair.delivered_at">
                        <p class="text-sm text-muted-foreground">Delivered At</p>
                        <p class="font-medium">{{ repair.delivered_at }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Issue & Resolution -->
            <Card>
                <CardContent class="space-y-4 pt-6">
                    <div>
                        <h3 class="font-semibold">Issue Description</h3>
                        <p class="mt-1 text-muted-foreground">{{ repair.issue_description }}</p>
                    </div>
                    <div v-if="repair.diagnosis">
                        <h3 class="font-semibold">Diagnosis</h3>
                        <p class="mt-1 text-muted-foreground">{{ repair.diagnosis }}</p>
                    </div>
                    <div v-if="repair.resolution">
                        <h3 class="font-semibold">Resolution</h3>
                        <p class="mt-1 text-muted-foreground">{{ repair.resolution }}</p>
                    </div>
                    <div v-if="repair.notes">
                        <h3 class="font-semibold">Notes</h3>
                        <p class="mt-1 text-muted-foreground">{{ repair.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Parts Used -->
            <div v-if="repair.parts.length > 0" class="overflow-hidden rounded-xl border">
                <div class="border-b bg-muted/50 px-4 py-3">
                    <h3 class="font-semibold">Parts Used</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Part Name</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Qty</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Unit Cost</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="part in repair.parts" :key="part.id" class="border-b">
                            <td class="px-4 py-3">{{ part.part_name }}</td>
                            <td class="px-4 py-3 text-right">{{ part.quantity }}</td>
                            <td class="px-4 py-3 text-right">${{ Number(part.unit_cost).toFixed(2) }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ Number(part.total_cost).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Payments -->
            <div v-if="repair.payments.length > 0" class="overflow-hidden rounded-xl border">
                <div class="border-b bg-muted/50 px-4 py-3">
                    <h3 class="font-semibold">Payments</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Payment #</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Amount</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Method</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in repair.payments" :key="payment.id" class="border-b">
                            <td class="px-4 py-3 font-mono">{{ payment.payment_number }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ Number(payment.amount).toFixed(2) }}</td>
                            <td class="px-4 py-3 capitalize">{{ payment.payment_method.replace('_', ' ') }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="payment.status === 'completed' ? 'default' : 'secondary'" class="capitalize">{{ payment.status }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ payment.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
