<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type SaleData = {
    id: number;
    sale_number: string;
    customer_id: number | null;
    total_amount: string;
    discount: string;
    total_tax: string;
    grand_total: string;
    status: string;
    notes: string | null;
    items: Array<{
        id: number;
        product_id: number;
        quantity: number;
        unit_price: string;
        serial_number: string | null;
    }>;
};

const props = defineProps<{
    sale: SaleData;
    customers: Array<{ id: number; name: string }>;
    products: Array<{ id: number; name: string; selling_price: string; stock_quantity: number; sku: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sales', href: '/admin/sales' },
    { title: props.sale.sale_number, href: `/admin/sales/${props.sale.id}` },
    { title: 'Edit', href: `/admin/sales/${props.sale.id}/edit` },
];

const form = useForm({
    status: props.sale.status as 'pending' | 'completed' | 'cancelled',
    notes: props.sale.notes || '',
});

function submit() {
    form.put(`/admin/sales/${props.sale.id}`);
}
</script>

<template>
    <Head :title="`Edit Sale ${sale.sale_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" w-full max-w-3xl space-y-6 p-6">
            <h1 class="text-2xl font-bold tracking-tight">Edit Sale: {{ sale.sale_number }}</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <!-- Sale Summary (read-only) -->
                        <div class="rounded-lg border bg-muted/50 p-4">
                            <h3 class="mb-3 font-semibold">Sale Summary</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-muted-foreground">Sale Number:</span>
                                    <span class="ml-2 font-mono font-medium">{{ sale.sale_number }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Items:</span>
                                    <span class="ml-2 font-medium">{{ sale.items.length }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Total:</span>
                                    <span class="ml-2 font-medium">${{ Number(sale.grand_total).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6">
                            <div class="grid gap-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="notes">Notes</Label>
                                <Textarea id="notes" v-model="form.notes" rows="3" placeholder="Sale notes" />
                                <p v-if="form.errors.notes" class="text-sm text-destructive">{{ form.errors.notes }}</p>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link :href="`/admin/sales/${sale.id}`">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">Update Sale</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
