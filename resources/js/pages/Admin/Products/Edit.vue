<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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

type ProductMedia = {
    id: number;
    url: string;
};

const props = defineProps<{
    product: {
        id: number;
        category_id: number;
        name: string;
        description: string | null;
        sku: string;
        barcode: string | null;
        cost_price: string;
        selling_price: string;
        stock_quantity: number;
        min_stock_level: number;
        unit: string;
        is_active: boolean;
        media: ProductMedia[];
    };
    categories: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Products', href: '/admin/products' },
    { title: 'Edit Product', href: `/admin/products/${props.product.id}/edit` },
];

const form = useForm({
    _method: 'put' as const,
    category_id: String(props.product.category_id),
    name: props.product.name,
    description: props.product.description ?? '',
    sku: props.product.sku,
    barcode: props.product.barcode ?? '',
    cost_price: props.product.cost_price,
    selling_price: props.product.selling_price,
    stock_quantity: String(props.product.stock_quantity),
    min_stock_level: String(props.product.min_stock_level),
    unit: props.product.unit,
    is_active: props.product.is_active,
    images: [] as File[],
    remove_images: [] as number[],
}, { forceFormData: true });

const existingMedia = ref<ProductMedia[]>([...props.product.media]);

function removeExistingImage(media: ProductMedia) {
    existingMedia.value = existingMedia.value.filter(m => m.id !== media.id);
    form.remove_images.push(media.id);
}

function handleImages(event: Event) {
    const target = event.target as HTMLInputElement;
    form.images = target.files ? Array.from(target.files) : [];
}

function submit() {
    router.post(`/admin/products/${props.product.id}`, form.data(), {
        onSuccess: () => form.reset(),
        onError: (errors) => {
            form.errors = errors as any;
        },
    });
}
</script>

<template>
    <Head title="Edit Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" w-full max-w-2xl space-y-6 p-6">
            <Heading title="Edit Product" description="Update product information" />

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <div class="grid gap-2">
                            <Label for="category_id">Category</Label>
                            <Select v-model="form.category_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="cat in categories" :key="cat.id" :value="String(cat.id)">{{ cat.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.category_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" placeholder="Product name" required />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Input id="description" v-model="form.description" placeholder="Optional description" />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="sku">SKU</Label>
                                <Input id="sku" v-model="form.sku" placeholder="SKU" />
                                <p class="text-xs text-muted-foreground">Auto-generate if left empty</p>
                                <InputError :message="form.errors.sku" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="barcode">Barcode</Label>
                                <Input id="barcode" v-model="form.barcode" placeholder="Barcode" />
                                <InputError :message="form.errors.barcode" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="cost_price">Cost Price</Label>
                                <Input id="cost_price" v-model="form.cost_price" type="number" step="0.01" placeholder="0.00" />
                                <InputError :message="form.errors.cost_price" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="selling_price">Selling Price</Label>
                                <Input id="selling_price" v-model="form.selling_price" type="number" step="0.01" placeholder="0.00" />
                                <InputError :message="form.errors.selling_price" />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="grid gap-2">
                                <Label for="stock_quantity">Stock Quantity</Label>
                                <Input id="stock_quantity" v-model="form.stock_quantity" type="number" placeholder="0" />
                                <InputError :message="form.errors.stock_quantity" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="min_stock_level">Min Stock Level</Label>
                                <Input id="min_stock_level" v-model="form.min_stock_level" type="number" placeholder="0" />
                                <InputError :message="form.errors.min_stock_level" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="unit">Unit</Label>
                                <Input id="unit" v-model="form.unit" placeholder="pcs" />
                                <InputError :message="form.errors.unit" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox id="is_active" :checked="form.is_active" @update:checked="form.is_active = $event" />
                            <Label for="is_active">Active</Label>
                        </div>

                        <div v-if="existingMedia.length > 0" class="grid gap-2">
                            <Label>Existing Images</Label>
                            <div class="flex flex-wrap gap-3">
                                <div v-for="media in existingMedia" :key="media.id" class="relative">
                                    <img :src="media.url" class="h-20 w-20 rounded object-cover border" />
                                    <Button type="button" variant="destructive" size="sm" class="absolute -right-2 -top-2 h-6 w-6 rounded-full p-0" @click="removeExistingImage(media)">
                                        &times;
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="images">Add Images</Label>
                            <input id="images" type="file" multiple accept="image/*" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" @change="handleImages" />
                            <InputError :message="form.errors.images" />
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link href="/admin/products"><Button type="button" variant="outline">Cancel</Button></Link>
                        <Button type="submit" :disabled="form.processing">Update Product</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
