<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type PaymentDetail = {
    id: number;
    payment_number: string;
    customer: { id: number; name: string } | null;
    sale: { id: number; sale_number: string } | null;
    repair: { id: number; repair_number: string } | null;
    amount: string;
    payment_method: string;
    status: string;
    reference_number: string | null;
    notes: string | null;
    received_by: string | null;
    created_at: string;
};

const props = defineProps<{ payment: PaymentDetail }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payments', href: '/admin/payments' },
    { title: props.payment.payment_number, href: `/admin/payments/${props.payment.id}` },
];

function statusVariant(status: string) {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        completed: 'default', pending: 'secondary', refunded: 'outline', failed: 'destructive',
    };

    return map[status] ?? 'outline';
}
</script>

<template>
    <Head :title="`Payment ${payment.payment_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading :title="`Payment: ${payment.payment_number}`" description="View payment details" />
                <Link href="/admin/payments"><Button variant="outline">Back to List</Button></Link>
            </div>

            <Card>
                <CardContent class="grid grid-cols-2 gap-4 pt-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-muted-foreground">Payment Number</p>
                        <p class="font-medium font-mono">{{ payment.payment_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Amount</p>
                        <p class="text-lg font-bold">${{ Number(payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        <Badge :variant="statusVariant(payment.status)" class="capitalize">{{ payment.status }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Payment Method</p>
                        <p class="font-medium capitalize">{{ payment.payment_method.replace('_', ' ') }}</p>
                    </div>
                    <div v-if="payment.customer">
                        <p class="text-sm text-muted-foreground">Customer</p>
                        <p class="font-medium">{{ payment.customer.name }}</p>
                    </div>
                    <div v-if="payment.sale">
                        <p class="text-sm text-muted-foreground">Sale</p>
                        <Link :href="`/admin/sales/${payment.sale.id}`" class="font-medium text-primary hover:underline">
                            {{ payment.sale.sale_number }}
                        </Link>
                    </div>
                    <div v-if="payment.repair">
                        <p class="text-sm text-muted-foreground">Repair</p>
                        <Link :href="`/admin/repairs/${payment.repair.id}`" class="font-medium text-primary hover:underline">
                            {{ payment.repair.repair_number }}
                        </Link>
                    </div>
                    <div v-if="payment.reference_number">
                        <p class="text-sm text-muted-foreground">Reference Number</p>
                        <p class="font-medium font-mono">{{ payment.reference_number }}</p>
                    </div>
                    <div v-if="payment.received_by">
                        <p class="text-sm text-muted-foreground">Received By</p>
                        <p class="font-medium">{{ payment.received_by }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Date</p>
                        <p class="font-medium">{{ payment.created_at }}</p>
                    </div>
                    <div v-if="payment.notes">
                        <p class="text-sm text-muted-foreground">Notes</p>
                        <p class="font-medium">{{ payment.notes }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
