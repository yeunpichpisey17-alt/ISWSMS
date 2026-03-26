<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ClaimData = {
    id: number;
    claim_number: string;
    status: string;
    resolution: string | null;
    repair_id: number | null;
    notes: string | null;
};

const props = defineProps<{ claim: ClaimData }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Warranty Claims', href: '/admin/warranty-claims' },
    { title: props.claim.claim_number, href: `/admin/warranty-claims/${props.claim.id}` },
    { title: 'Edit', href: `/admin/warranty-claims/${props.claim.id}/edit` },
];

const form = useForm({
    status: props.claim.status,
    resolution: props.claim.resolution || '',
    notes: props.claim.notes || '',
});

function submit() {
    form.put(`/admin/warranty-claims/${props.claim.id}`);
}
</script>

<template>
    <Head :title="`Edit Warranty ${claim.claim_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-3xl space-y-6 p-6">
            <h1 class="text-2xl font-bold tracking-tight">Edit Claim: {{ claim.claim_number }}</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <div class="grid gap-2">
                            <Label>Status *</Label>
                            <Select v-model="form.status">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="submitted">Submitted</SelectItem>
                                    <SelectItem value="under_review">Under Review</SelectItem>
                                    <SelectItem value="approved">Approved</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
                                    <SelectItem value="in_repair">In Repair</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                        </div>
                        <div class="grid gap-2">
                            <Label>Resolution</Label>
                            <Textarea v-model="form.resolution" rows="4" placeholder="Resolution details..." />
                        </div>
                        <div class="grid gap-2">
                            <Label>Notes</Label>
                            <Textarea v-model="form.notes" rows="2" placeholder="Additional notes..." />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link :href="`/admin/warranty-claims/${claim.id}`">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">Update Claim</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
