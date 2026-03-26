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

type RepairData = {
    id: number;
    repair_number: string;
    customer_id: number;
    device_name: string;
    device_brand: string | null;
    device_model: string | null;
    issue_description: string;
    diagnosis: string | null;
    resolution: string | null;
    status: string;
    priority: string;
    estimated_cost: string;
    actual_cost: string;
    assigned_to: number | null;
    estimated_completion: string | null;
    notes: string | null;
};

const props = defineProps<{
    repair: RepairData;
    technicians: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Repairs', href: '/admin/repairs' },
    { title: props.repair.repair_number, href: `/admin/repairs/${props.repair.id}` },
    { title: 'Edit', href: `/admin/repairs/${props.repair.id}/edit` },
];

const form = useForm({
    diagnosis: props.repair.diagnosis || '',
    resolution: props.repair.resolution || '',
    status: props.repair.status,
    priority: props.repair.priority,
    estimated_cost: parseFloat(props.repair.estimated_cost) || 0,
    actual_cost: parseFloat(props.repair.actual_cost) || 0,
    assigned_to: props.repair.assigned_to ? String(props.repair.assigned_to) : '' as string | number,
    estimated_completion: props.repair.estimated_completion || '',
    notes: props.repair.notes || '',
});

function submit() {
    form.put(`/admin/repairs/${props.repair.id}`);
}
</script>

<template>
    <Head :title="`Edit Repair ${repair.repair_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" w-full max-w-4xl space-y-6 p-6">
            <h1 class="text-2xl font-bold tracking-tight">Edit Repair: {{ repair.repair_number }}</h1>

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <!-- Device Info (read-only) -->
                        <div class="rounded-lg border bg-muted/50 p-4">
                            <h3 class="mb-3 font-semibold">Device Info</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-3">
                                <div>
                                    <span class="text-muted-foreground">Device:</span>
                                    <span class="ml-2 font-medium">{{ repair.device_name }}</span>
                                </div>
                                <div v-if="repair.device_brand">
                                    <span class="text-muted-foreground">Brand:</span>
                                    <span class="ml-2 font-medium">{{ repair.device_brand }}</span>
                                </div>
                                <div v-if="repair.device_model">
                                    <span class="text-muted-foreground">Model:</span>
                                    <span class="ml-2 font-medium">{{ repair.device_model }}</span>
                                </div>
                            </div>
                            <div class="mt-3 text-sm">
                                <span class="text-muted-foreground">Issue:</span>
                                <p class="mt-1">{{ repair.issue_description }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="grid gap-2">
                                <Label>Status *</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="received">Received</SelectItem>
                                        <SelectItem value="diagnosing">Diagnosing</SelectItem>
                                        <SelectItem value="waiting_parts">Waiting Parts</SelectItem>
                                        <SelectItem value="in_repair">In Repair</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="delivered">Delivered</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</p>
                            </div>
                            <div class="grid gap-2">
                                <Label>Priority *</Label>
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
                        </div>

                        <div class="grid gap-2">
                            <Label>Diagnosis</Label>
                            <Textarea v-model="form.diagnosis" rows="3" placeholder="Diagnosis findings..." />
                        </div>

                        <div class="grid gap-2">
                            <Label>Resolution</Label>
                            <Textarea v-model="form.resolution" rows="3" placeholder="How the issue was resolved..." />
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="grid gap-2">
                                <Label>Estimated Cost ($)</Label>
                                <Input v-model.number="form.estimated_cost" type="number" step="0.01" min="0" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Actual Cost ($)</Label>
                                <Input v-model.number="form.actual_cost" type="number" step="0.01" min="0" />
                            </div>
                            <div class="grid gap-2">
                                <Label>Est. Completion</Label>
                                <Input v-model="form.estimated_completion" type="date" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Notes</Label>
                            <Textarea v-model="form.notes" rows="2" placeholder="Additional notes..." />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link :href="`/admin/repairs/${repair.id}`">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">Update Repair</Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
