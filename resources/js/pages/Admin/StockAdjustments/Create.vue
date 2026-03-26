<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ProductOption = {
    id: number;
    name: string;
    sku: string;
    stock_quantity: number;
};

defineProps<{ products: ProductOption[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Stock Adjustments', href: '/admin/stock-adjustments' },
    { title: 'New Adjustment', href: '/admin/stock-adjustments/create' },
];

const form = useForm({
    product_id: '',
    type: '',
    quantity: 1,
    reason: '',
});

const adjustmentTypes = [
    { value: 'addition', label: 'Addition (+)' },
    { value: 'deduction', label: 'Deduction (-)' },
    { value: 'adjustment', label: 'Set Absolute' },
];

function submit() {
    form.post('/admin/stock-adjustments');
}
</script>

<template>
    <Head title="New Stock Adjustment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-2xl space-y-6 p-6">
            <Heading title="New Stock Adjustment" description="Manually adjust product stock quantities" />

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <div class="grid gap-2">
                            <Label for="product_id">Product</Label>
                            <Select v-model="form.product_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a product" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="product in products" :key="product.id" :value="String(product.id)">
                                        {{ product.name }} ({{ product.sku }}) — Stock: {{ product.stock_quantity }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.product_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="type">Adjustment Type</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="t in adjustmentTypes" :key="t.value" :value="t.value">
                                        {{ t.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.type" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="quantity">Quantity</Label>
                            <Input id="quantity" v-model.number="form.quantity" type="number" min="1" required />
                            <InputError :message="form.errors.quantity" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="reason">Reason</Label>
                            <Input id="reason" v-model="form.reason" placeholder="Reason for adjustment" required />
                            <InputError :message="form.errors.reason" />
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link href="/admin/stock-adjustments"><Button type="button" variant="outline">Cancel</Button></Link>
                        <Button type="submit" :disabled="form.processing">Record Adjustment</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
