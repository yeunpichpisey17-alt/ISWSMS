<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    customers: Array<{ id: number; name: string }>;
    sales: Array<{ id: number; sale_number: string; customer_name: string; grand_total: string }>;
    repairs: Array<{ id: number; repair_number: string; customer_name: string; actual_cost: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Record Payment', href: '/admin/payments/create' },
];

const form = useForm({
    sale_id: '' as string | number,
    repair_id: '' as string | number,
    customer_id: '' as string | number,
    amount: 0,
    payment_method: 'cash' as string,
    reference_number: '',
    notes: '',
});

function onSaleChange() {
    const sale = props.sales.find(s => s.id === Number(form.sale_id));

    if (sale) {
        form.amount = parseFloat(sale.grand_total);
        form.repair_id = '';
    }
}

function onRepairChange() {
    const repair = props.repairs.find(r => r.id === Number(form.repair_id));

    if (repair) {
        form.amount = parseFloat(repair.actual_cost);
        form.sale_id = '';
    }
}

function submit() {
    form.post('/admin/payments');
}
</script>

<template>
    <Head title="Record Payment" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-3xl space-y-6 p-6">
            <h1 class="text-2xl font-bold tracking-tight">Record Payment</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label>For Sale (optional)</Label>
                                <Select v-model="form.sale_id" @update:model-value="onSaleChange">
                                    <SelectTrigger><SelectValue placeholder="Select sale" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="s in sales" :key="s.id" :value="String(s.id)">
                                            {{ s.sale_number }} - {{ s.customer_name }} (${{ Number(s.grand_total).toFixed(2) }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label>For Repair (optional)</Label>
                                <Select v-model="form.repair_id" @update:model-value="onRepairChange">
                                    <SelectTrigger><SelectValue placeholder="Select repair" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="r in repairs" :key="r.id" :value="String(r.id)">
                                            {{ r.repair_number }} - {{ r.customer_name }} (${{ Number(r.actual_cost).toFixed(2) }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label>Customer</Label>
                                <Select v-model="form.customer_id">
                                    <SelectTrigger><SelectValue placeholder="Select customer" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="c in customers" :key="c.id" :value="String(c.id)">{{ c.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label>Payment Method *</Label>
                                <Select v-model="form.payment_method">
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="cash">Cash</SelectItem>
                                        <SelectItem value="card">Card</SelectItem>
                                        <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label>Amount ($) *</Label>
                                <Input v-model.number="form.amount" type="number" step="0.01" min="0.01" />
                                <p v-if="form.errors.amount" class="text-sm text-destructive">{{ form.errors.amount }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label>Reference Number</Label>
                                <Input v-model="form.reference_number" placeholder="Transaction reference" />
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label>Notes</Label>
                            <Textarea v-model="form.notes" rows="2" placeholder="Payment notes..." />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link href="/admin/payments"><Button type="button" variant="outline">Cancel</Button></Link>
                        <Button type="submit" :disabled="form.processing">Record Payment</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
