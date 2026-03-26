<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type PurchaseOrderDetail = {
    id: number;
    po_number: string;
    supplier: string;
    status: string;
    order_date: string;
    expected_date: string | null;
    received_date: string | null;
    total_amount: string;
    notes: string | null;
    created_by: string;
    items: Array<{
        id: number;
        product_name: string;
        product_sku: string;
        quantity: number;
        unit_price: string;
        total_price: string;
    }>;
};

const props = defineProps<{ purchaseOrder: PurchaseOrderDetail }>();

const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Purchase Orders', href: '/admin/purchase-orders' },
    { title: props.purchaseOrder.po_number, href: `/admin/purchase-orders/${props.purchaseOrder.id}` },
];

const processing = ref(false);
const showSerialInputs = ref(false);

const serialNumbers: Record<number, string[]> = reactive({});

function initSerialNumbers() {
    props.purchaseOrder.items.forEach((item) => {
        serialNumbers[item.id] = Array.from({ length: item.quantity }, () => '');
    });
    showSerialInputs.value = true;
}

function updateStatus(status: string) {
    if (status === 'received' && props.purchaseOrder.status === 'pending') {
        if (!showSerialInputs.value) {
            initSerialNumbers();

            return;
        }
    }

    processing.value = true;
    const data: Record<string, unknown> = { status };

    if (status === 'received') {
        data.serial_numbers = serialNumbers;
    }

    router.patch(`/admin/purchase-orders/${props.purchaseOrder.id}/status`, data, {
        onFinish: () => {
            processing.value = false;
        },
    });
}

function cancelOrder() {
    updateStatus('cancelled');
}

function statusVariant(status: string) {
    const map: Record<string, 'outline' | 'secondary' | 'default' | 'destructive'> = {
        draft: 'outline',
        pending: 'secondary',
        received: 'default',
        cancelled: 'destructive',
    };

    return map[status] ?? 'outline';
}
</script>

<template>
    <Head :title="`PO ${purchaseOrder.po_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading :title="`Purchase Order: ${purchaseOrder.po_number}`" description="View purchase order details" />
                <Link href="/admin/purchase-orders">
                    <Button variant="outline">Back to List</Button>
                </Link>
            </div>

            <Card>
                <CardContent class="grid grid-cols-2 gap-4 pt-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-muted-foreground">PO Number</p>
                        <p class="font-medium">{{ purchaseOrder.po_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Supplier</p>
                        <p class="font-medium">{{ purchaseOrder.supplier }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        <Badge :variant="statusVariant(purchaseOrder.status)">{{ purchaseOrder.status }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Order Date</p>
                        <p class="font-medium">{{ purchaseOrder.order_date }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Expected Date</p>
                        <p class="font-medium">{{ purchaseOrder.expected_date || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Received Date</p>
                        <p class="font-medium">{{ purchaseOrder.received_date || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Total Amount</p>
                        <p class="font-medium">{{ purchaseOrder.total_amount }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Created By</p>
                        <p class="font-medium">{{ purchaseOrder.created_by }}</p>
                    </div>
                    <div v-if="purchaseOrder.notes">
                        <p class="text-sm text-muted-foreground">Notes</p>
                        <p class="font-medium">{{ purchaseOrder.notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Product Name</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">SKU</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Quantity</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Unit Price</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in purchaseOrder.items" :key="item.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="px-4 py-3 font-medium">{{ item.product_name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ item.product_sku }}</td>
                            <td class="px-4 py-3 text-right text-muted-foreground">{{ item.quantity }}</td>
                            <td class="px-4 py-3 text-right text-muted-foreground">{{ item.unit_price }}</td>
                            <td class="px-4 py-3 text-right text-muted-foreground">{{ item.total_price }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Serial number inputs when receiving -->
            <Card v-if="showSerialInputs">
                <CardContent class="space-y-6 pt-6">
                    <h3 class="text-lg font-semibold">Enter Serial Numbers</h3>
                    <div v-for="item in purchaseOrder.items" :key="item.id" class="space-y-3">
                        <p class="font-medium">{{ item.product_name }} ({{ item.product_sku }}) &mdash; {{ item.quantity }} unit(s)</p>
                        <div v-for="(_, idx) in item.quantity" :key="idx" class="flex items-center gap-2">
                            <Label class="w-20 shrink-0">Unit {{ idx + 1 }}</Label>
                            <Input v-model="serialNumbers[item.id][idx]" :placeholder="`Serial number ${idx + 1}`" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button @click="updateStatus('received')" :disabled="processing">Confirm Received</Button>
                        <Button variant="outline" @click="showSerialInputs = false">Cancel</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Status action buttons -->
            <div v-if="can('purchases.edit') && !showSerialInputs" class="flex gap-2">
                <template v-if="purchaseOrder.status === 'draft'">
                    <Button @click="updateStatus('pending')" :disabled="processing">Mark as Pending</Button>
                    <Button variant="destructive" @click="cancelOrder" :disabled="processing">Cancel</Button>
                </template>
                <template v-if="purchaseOrder.status === 'pending'">
                    <Button @click="updateStatus('received')" :disabled="processing">Mark as Received</Button>
                    <Button variant="destructive" @click="cancelOrder" :disabled="processing">Cancel</Button>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
