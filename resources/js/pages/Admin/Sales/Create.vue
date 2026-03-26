<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
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
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Customer = { id: number; name: string };
type Product = { id: number; name: string; selling_price: string; stock_quantity: number; sku: string };

const props = defineProps<{
    customers: Customer[];
    products: Product[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sales', href: '/admin/sales' },
    { title: 'Create Sale', href: '/admin/sales/create' },
];

const form = useForm({
    customer_id: '' as string | number,
    notes: '',
    status: 'pending' as 'pending' | 'completed',
    discount: 0,
    tax_amount: 0,
    items: [
        { product_id: '' as string | number, quantity: 1, unit_price: 0 },
    ] as Array<{
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
        item.unit_price = parseFloat(product.selling_price || '0');
    }
}

function getProductStock(productId: string | number): number {
    const product = props.products.find((p) => p.id === Number(productId));

    return product?.stock_quantity ?? 0;
}

function lineTotal(item: { quantity: number; unit_price: number }): string {
    return (item.quantity * item.unit_price).toFixed(2);
}

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + item.quantity * item.unit_price, 0);
});

const grandTotal = computed(() => {
    return Math.max(0, subtotal.value - form.discount + form.tax_amount);
});

function submit() {
    form.post('/admin/sales');
}
</script>

<template>
    <Head title="Create Sale" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-4xl space-y-6 p-6">
            <h1 class="text-2xl font-bold tracking-tight">Create Sale</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="customer_id">Customer</Label>
                                <Select v-model="form.customer_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Walk-in customer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="customer in props.customers"
                                            :key="customer.id"
                                            :value="String(customer.id)"
                                        >
                                            {{ customer.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.customer_id" class="text-sm text-destructive">{{ form.errors.customer_id }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                            </div>
                            <div class="grid gap-2 md:col-span-2">
                                <Label for="notes">Notes</Label>
                                <Textarea id="notes" v-model="form.notes" placeholder="Optional notes" rows="2" />
                                <p v-if="form.errors.notes" class="text-sm text-destructive">{{ form.errors.notes }}</p>
                            </div>
                        </div>

                        <!-- Sale Items -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold">Sale Items</h3>
                                <Button type="button" variant="outline" size="sm" @click="addItem">
                                    <Plus class="mr-1 h-4 w-4" />
                                    Add Item
                                </Button>
                            </div>
                            <p v-if="form.errors.items" class="text-sm text-destructive">{{ form.errors.items }}</p>

                            <div
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="grid grid-cols-12 items-end gap-2 rounded-lg border p-3"
                            >
                                <div class="col-span-4 grid gap-2">
                                    <Label>Product</Label>
                                    <Select v-model="item.product_id" @update:model-value="onProductChange(index)">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select product" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="product in props.products"
                                                :key="product.id"
                                                :value="String(product.id)"
                                            >
                                                {{ product.name }} ({{ product.stock_quantity }} in stock)
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="(form.errors as any)[`items.${index}.product_id`]" class="text-sm text-destructive">
                                        {{ (form.errors as any)[`items.${index}.product_id`] }}
                                    </p>
                                </div>
                                <div class="col-span-2 grid gap-2">
                                    <Label>Qty</Label>
                                    <Input
                                        v-model.number="item.quantity"
                                        type="number"
                                        min="1"
                                        :max="getProductStock(item.product_id)"
                                    />
                                    <p v-if="(form.errors as any)[`items.${index}.quantity`]" class="text-sm text-destructive">
                                        {{ (form.errors as any)[`items.${index}.quantity`] }}
                                    </p>
                                </div>
                                <div class="col-span-2 grid gap-2">
                                    <Label>Unit Price</Label>
                                    <Input v-model.number="item.unit_price" type="number" step="0.01" min="0" />
                                </div>
                                <div class="col-span-2 grid gap-2">
                                    <Label>Line Total</Label>
                                    <Input :model-value="lineTotal(item)" readonly class="bg-muted" />
                                </div>
                                <div class="col-span-2 flex justify-end pt-6">
                                    <Button
                                        v-if="form.items.length > 1"
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="removeItem(index)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="space-y-3 rounded-lg border bg-muted/50 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <Label class="text-muted-foreground">Discount</Label>
                                <Input v-model.number="form.discount" type="number" step="0.01" min="0" class="w-40 text-right" />
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <Label class="text-muted-foreground">Tax</Label>
                                <Input v-model.number="form.tax_amount" type="number" step="0.01" min="0" class="w-40 text-right" />
                            </div>
                            <div class="flex items-center justify-between border-t pt-3">
                                <span class="text-lg font-bold">Grand Total</span>
                                <span class="text-lg font-bold">${{ grandTotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link href="/admin/sales">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">Create Sale</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
