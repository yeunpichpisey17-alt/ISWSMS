<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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

const props = defineProps<{
    customers: Array<{ id: number; name: string; phone?: string }>;
    products: Array<{ id: number; name: string; sku: string }>;
    technicians: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Repairs', href: '/admin/repairs' },
    { title: 'New Repair', href: '/admin/repairs/create' },
];

const form = useForm({
    customer_id: '' as string | number,
    product_id: '' as string | number,
    serial_number: '',
    device_name: '',
    device_brand: '',
    device_model: '',
    issue_description: '',
    priority: 'normal' as string,
    estimated_cost: 0,
    assigned_to: '' as string | number,
    estimated_completion: '',
    notes: '',
});

function submit() {
    form.post('/admin/repairs');
}
</script>

<template>
    <Head title="New Repair" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" w-full max-w-4xl space-y-6 p-6">
            <h1 class="text-2xl font-bold tracking-tight">New Repair Ticket</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <!-- Customer & Device Info -->
                        <h3 class="font-semibold">Customer Information</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label>Customer *</Label>
                                <Select v-model="form.customer_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select customer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="c in customers" :key="c.id" :value="String(c.id)">
                                            {{ c.name }} {{ c.phone ? `(${c.phone})` : '' }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.customer_id" class="text-sm text-destructive">{{ form.errors.customer_id }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label>Related Product (optional)</Label>
                                <Select v-model="form.product_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select product" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="p in products" :key="p.id" :value="String(p.id)">
                                            {{ p.name }} ({{ p.sku }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <h3 class="font-semibold">Device Information</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="grid gap-2">
                                <Label>Device Name *</Label>
                                <Input v-model="form.device_name" placeholder="e.g. Laptop, Phone" />
                                <p v-if="form.errors.device_name" class="text-sm text-destructive">{{ form.errors.device_name }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label>Brand</Label>
                                <Input v-model="form.device_brand" placeholder="e.g. Dell, Apple" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Model</Label>
                                <Input v-model="form.device_model" placeholder="e.g. XPS 15" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label>Serial Number</Label>
                                <Input v-model="form.serial_number" placeholder="Device serial number" />
                            </div>
                        </div>

                        <h3 class="font-semibold">Issue & Assignment</h3>
                        <div class="grid gap-2">
                            <Label>Issue Description *</Label>
                            <Textarea v-model="form.issue_description" rows="4" placeholder="Describe the issue in detail..." />
                            <p v-if="form.errors.issue_description" class="text-sm text-destructive">{{ form.errors.issue_description }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="grid gap-2">
                                <Label>Priority</Label>
                                <Select v-model="form.priority">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="low">Low</SelectItem>
                                        <SelectItem value="normal">Normal</SelectItem>
                                        <SelectItem value="high">High</SelectItem>
                                        <SelectItem value="urgent">Urgent</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label>Assign To</Label>
                                <Select v-model="form.assigned_to">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select technician" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="t in technicians" :key="t.id" :value="String(t.id)">
                                            {{ t.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label>Estimated Completion</Label>
                                <Input v-model="form.estimated_completion" type="date" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label>Estimated Cost ($)</Label>
                                <Input v-model.number="form.estimated_cost" type="number" step="0.01" min="0" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Notes</Label>
                            <Textarea v-model="form.notes" rows="2" placeholder="Additional notes..." />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link href="/admin/repairs">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">Create Repair Ticket</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
