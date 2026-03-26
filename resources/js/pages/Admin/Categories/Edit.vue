<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    category: { id: number; name: string; description: string | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Categories', href: '/admin/categories' },
    {
        title: 'Edit Category',
        href: `/admin/categories/${props.category.id}/edit`,
    },
];

const form = useForm({
    name: props.category.name,
    description: props.category.description ?? '',
});

function submit() {
    form.put(`/admin/categories/${props.category.id}`);
}
</script>

<template>
    <Head title="Edit Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-2xl space-y-6 p-6">
            <Heading
                title="Edit Category"
                description="Update category information"
            />

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Category name"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Input
                                id="description"
                                v-model="form.description"
                                placeholder="Optional description"
                            />
                            <InputError :message="form.errors.description" />
                        </div>
                    </CardContent>

                    <CardFooter
                        class="flex justify-end gap-2 border-t px-6 py-4"
                    >
                        <Link href="/admin/categories"
                            ><Button type="button" variant="outline"
                                >Cancel</Button
                            ></Link
                        >
                        <Button type="submit" :disabled="form.processing"
                            >Update Category</Button
                        >
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
