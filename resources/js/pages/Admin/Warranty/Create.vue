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

defineProps<{
    customers: Array<{ id: number; name: string }>;
    products: Array<{ id: number; name: string; sku: string }>;
    sales: Array<{ id: number; sale_number: string; customer_id: number }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Warranty Claims', href: '/admin/warranty-claims' },
    { title: 'New Claim', href: '/admin/warranty-claims/create' },
];

const form = useForm({
    customer_id: '' as string | number,
    sale_id: '' as string | number,
    product_id: '' as string | number,
    serial_number: '',
    issue_description: '',
    warranty_start: '',
    warranty_end: '',
    notes: '',
});

function submit() {
    form.post('/admin/warranty-claims');
}
</script>

<template>
    <Head title="New Warranty Claim" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-3xl space-y-6 p-6">
            <h1 class="text-2xl font-bold tracking-tight">New Warranty Claim</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label>Customer *</Label>
                                <Select v-model="form.customer_id">
                                    <SelectTrigger><SelectValue placeholder="Select customer" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="c in customers" :key="c.id" :value="String(c.id)">{{ c.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.customer_id" class="text-sm text-destructive">{{ form.errors.customer_id }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label>Related Sale (optional)</Label>
                                <Select v-model="form.sale_id">
                                    <SelectTrigger><SelectValue placeholder="Select sale" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="s in sales" :key="s.id" :value="String(s.id)">{{ s.sale_number }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label>Product *</Label>
                                <Select v-model="form.product_id">
                                    <SelectTrigger><SelectValue placeholder="Select product" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="p in products" :key="p.id" :value="String(p.id)">{{ p.name }} ({{ p.sku }})</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.product_id" class="text-sm text-destructive">{{ form.errors.product_id }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label>Serial Number</Label>
                                <Input v-model="form.serial_number" placeholder="Device serial number" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Warranty Start *</Label>
                                <Input v-model="form.warranty_start" type="date" />
                                <p v-if="form.errors.warranty_start" class="text-sm text-destructive">{{ form.errors.warranty_start }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label>Warranty End *</Label>
                                <Input v-model="form.warranty_end" type="date" />
                                <p v-if="form.errors.warranty_end" class="text-sm text-destructive">{{ form.errors.warranty_end }}</p>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label>Issue Description *</Label>
                            <Textarea v-model="form.issue_description" rows="4" placeholder="Describe the warranty issue..." />
                            <p v-if="form.errors.issue_description" class="text-sm text-destructive">{{ form.errors.issue_description }}</p>
                        </div>
                        <div class="grid gap-2">
                            <Label>Notes</Label>
                            <Textarea v-model="form.notes" rows="2" placeholder="Additional notes..." />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link href="/admin/warranty-claims"><Button type="button" variant="outline">Cancel</Button></Link>
                        <Button type="submit" :disabled="form.processing">Submit Claim</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
