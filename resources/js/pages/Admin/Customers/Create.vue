<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
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

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    status: 'active' as 'active' | 'inactive',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Customers', href: '/admin/customers' },
    { title: 'Create', href: '/admin/customers/create' },
];

const submit = () => {
    form.post('/admin/customers', {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Customer" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-3xl space-y-6 p-6">
            <h1 class="text-2xl font-bold tracking-tight">New Customer</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="name">Name *</Label>
                                <Input id="name" v-model="form.name" placeholder="Customer name" />
                                <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="phone">Phone</Label>
                                <Input id="phone" v-model="form.phone" placeholder="Phone number" />
                                <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="email">Email</Label>
                                <Input id="email" v-model="form.email" type="email" placeholder="Email address" />
                                <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label for="address">Address</Label>
                            <Textarea id="address" v-model="form.address" rows="3" placeholder="Customer address" />
                            <p v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</p>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Button type="button" variant="outline" @click="router.visit('/admin/customers')">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Create Customer</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
