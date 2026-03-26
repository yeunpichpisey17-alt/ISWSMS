<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ClaimDetail = {
    id: number;
    claim_number: string;
    customer: { id: number; name: string; phone?: string };
    product: { id: number; name: string; sku: string };
    sale: { id: number; sale_number: string } | null;
    serial_number: string | null;
    issue_description: string;
    resolution: string | null;
    status: string;
    warranty_start: string;
    warranty_end: string;
    is_expired: boolean;
    repair: { id: number; repair_number: string; status: string } | null;
    handled_by: string | null;
    notes: string | null;
    created_at: string;
};

const props = defineProps<{ claim: ClaimDetail }>();
const { can } = usePermissions();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Warranty Claims', href: '/admin/warranty-claims' },
    { title: props.claim.claim_number, href: `/admin/warranty-claims/${props.claim.id}` },
];

function statusVariant(status: string) {
    const map: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        submitted: 'outline', under_review: 'secondary', approved: 'default',
        rejected: 'destructive', in_repair: 'secondary', completed: 'default',
    };

    return map[status] ?? 'outline';
}

function formatStatus(s: string): string {
    return s.replace(/_/g, ' ');
}
</script>

<template>
    <Head :title="`Warranty ${claim.claim_number}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading :title="`Warranty Claim: ${claim.claim_number}`" description="View warranty claim details" />
                <div class="flex gap-2">
                    <Link v-if="can('warranty.edit')" :href="`/admin/warranty-claims/${claim.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Link href="/admin/warranty-claims"><Button variant="outline">Back to List</Button></Link>
                </div>
            </div>

            <Card>
                <CardContent class="grid grid-cols-2 gap-4 pt-6 md:grid-cols-3">
                    <div>
                        <p class="text-sm text-muted-foreground">Claim Number</p>
                        <p class="font-medium font-mono">{{ claim.claim_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Customer</p>
                        <p class="font-medium">{{ claim.customer.name }}</p>
                        <p v-if="claim.customer.phone" class="text-sm text-muted-foreground">{{ claim.customer.phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        <Badge :variant="statusVariant(claim.status)" class="capitalize">{{ formatStatus(claim.status) }}</Badge>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Product</p>
                        <p class="font-medium">{{ claim.product.name }}</p>
                        <p class="text-sm font-mono text-muted-foreground">{{ claim.product.sku }}</p>
                    </div>
                    <div v-if="claim.serial_number">
                        <p class="text-sm text-muted-foreground">Serial Number</p>
                        <p class="font-medium font-mono">{{ claim.serial_number }}</p>
                    </div>
                    <div v-if="claim.sale">
                        <p class="text-sm text-muted-foreground">Related Sale</p>
                        <Link :href="`/admin/sales/${claim.sale.id}`" class="font-medium text-primary hover:underline">
                            {{ claim.sale.sale_number }}
                        </Link>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Warranty Period</p>
                        <p class="font-medium">{{ claim.warranty_start }} to {{ claim.warranty_end }}</p>
                        <p v-if="claim.is_expired" class="text-sm text-destructive">Expired</p>
                    </div>
                    <div v-if="claim.repair">
                        <p class="text-sm text-muted-foreground">Linked Repair</p>
                        <Link :href="`/admin/repairs/${claim.repair.id}`" class="font-medium text-primary hover:underline">
                            {{ claim.repair.repair_number }}
                        </Link>
                        <Badge :variant="claim.repair.status === 'completed' ? 'default' : 'secondary'" class="ml-2 capitalize">
                            {{ claim.repair.status.replace(/_/g, ' ') }}
                        </Badge>
                    </div>
                    <div v-if="claim.handled_by">
                        <p class="text-sm text-muted-foreground">Handled By</p>
                        <p class="font-medium">{{ claim.handled_by }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Submitted</p>
                        <p class="font-medium">{{ claim.created_at }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="space-y-4 pt-6">
                    <div>
                        <h3 class="font-semibold">Issue Description</h3>
                        <p class="mt-1 text-muted-foreground">{{ claim.issue_description }}</p>
                    </div>
                    <div v-if="claim.resolution">
                        <h3 class="font-semibold">Resolution</h3>
                        <p class="mt-1 text-muted-foreground">{{ claim.resolution }}</p>
                    </div>
                    <div v-if="claim.notes">
                        <h3 class="font-semibold">Notes</h3>
                        <p class="mt-1 text-muted-foreground">{{ claim.notes }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
