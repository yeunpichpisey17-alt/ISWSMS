<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
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

type Supplier = { id: number; name: string };
type Product = { id: number; name: string; sku: string; cost_price: string };

const props = defineProps<{
    suppliers: Supplier[];
    products: Product[];
    poNumber: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Purchase Orders', href: '/admin/purchase-orders' },
    { title: 'Create Purchase Order', href: '/admin/purchase-orders/create' },
];

const form = useForm({
    supplier_id: '' as string | number,
    po_number: props.poNumber,
    order_date: '',
    expected_date: '',
    notes: '',
    items: [{ product_id: '' as string | number, quantity: 1, unit_price: 0 }] as Array<{
        product_id: string | number;
        quantity: number;
        unit_price: number;
    }>,
});

function addItem() {
    form.items.push({ product_id: '', quantity: 1, unit_price: 0 });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function onProductChange(index: number) {
    const item = form.items[index];
    const product = props.products.find((p) => p.id === Number(item.product_id));

    if (product) {
        item.unit_price = parseFloat(product.cost_price);
    }
}

function lineTotal(item: { quantity: number; unit_price: number }) {
    return (item.quantity * item.unit_price).toFixed(2);
}

const grandTotal = computed(() => {
    return form.items.reduce((sum, item) => sum + item.quantity * item.unit_price, 0).toFixed(2);
});

function submit() {
    form.post('/admin/purchase-orders');
}
</script>

<template>
    <Head title="Create Purchase Order" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-4xl space-y-6 p-6">
            <Heading title="Create Purchase Order" description="Add a new purchase order" />

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="po_number">PO Number</Label>
                                <Input id="po_number" v-model="form.po_number" readonly />
                                <InputError :message="form.errors.po_number" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="supplier_id">Supplier</Label>
                                <Select v-model="form.supplier_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a supplier" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="supplier in suppliers" :key="supplier.id" :value="String(supplier.id)">
                                            {{ supplier.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.supplier_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="order_date">Order Date</Label>
                                <Input id="order_date" v-model="form.order_date" type="date" required />
                                <InputError :message="form.errors.order_date" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="expected_date">Expected Date</Label>
                                <Input id="expected_date" v-model="form.expected_date" type="date" />
                                <InputError :message="form.errors.expected_date" />
                            </div>

                            <div class="grid gap-2 md:col-span-2">
                                <Label for="notes">Notes</Label>
                                <Input id="notes" v-model="form.notes" placeholder="Optional notes" />
                                <InputError :message="form.errors.notes" />
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold">Items</h3>
                                <Button type="button" variant="outline" size="sm" @click="addItem">Add Item</Button>
                            </div>

                            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-12 items-end gap-2 rounded-lg border p-3">
                                <div class="col-span-4 grid gap-2">
                                    <Label>Product</Label>
                                    <Select v-model="item.product_id" @update:model-value="onProductChange(index)">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select product" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="product in products" :key="product.id" :value="String(product.id)">
                                                {{ product.name }} ({{ product.sku }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="(form.errors as any)[`items.${index}.product_id`]" />
                                </div>
                                <div class="col-span-2 grid gap-2">
                                    <Label>Quantity</Label>
                                    <Input v-model.number="item.quantity" type="number" min="1" required />
                                    <InputError :message="(form.errors as any)[`items.${index}.quantity`]" />
                                </div>
                                <div class="col-span-2 grid gap-2">
                                    <Label>Unit Price</Label>
                                    <Input v-model.number="item.unit_price" type="number" min="0" step="0.01" required />
                                    <InputError :message="(form.errors as any)[`items.${index}.unit_price`]" />
                                </div>
                                <div class="col-span-2 grid gap-2">
                                    <Label>Line Total</Label>
                                    <Input :model-value="lineTotal(item)" readonly />
                                </div>
                                <div class="col-span-2 flex justify-end">
                                    <Button v-if="form.items.length > 1" type="button" variant="destructive" size="sm" @click="removeItem(index)">Remove</Button>
                                </div>
                            </div>

                            <div class="flex justify-end text-lg font-semibold">
                                Grand Total: {{ grandTotal }}
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link href="/admin/purchase-orders"><Button type="button" variant="outline">Cancel</Button></Link>
                        <Button type="submit" :disabled="form.processing">Create Purchase Order</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
