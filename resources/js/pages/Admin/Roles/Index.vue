<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type RoleItem = {
    id: number;
    name: string;
    permissions_count: number;
    users_count: number;
    created_at: string;
};

type Props = {
    roles: RoleItem[];
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Roles', href: '/admin/roles' },
];

const deleteDialog = ref(false);
const roleToDelete = ref<RoleItem | null>(null);

function confirmDelete(role: RoleItem) {
    roleToDelete.value = role;
    deleteDialog.value = true;
}

function deleteRole() {
    if (!roleToDelete.value) {
return;
}

    router.delete(`/admin/roles/${roleToDelete.value.id}`, {
        onFinish: () => {
            deleteDialog.value = false;
            roleToDelete.value = null;
        },
    });
}
</script>

<template>
    <Head title="Role Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-7xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <Heading
                    title="Role Management"
                    description="Manage roles and their permissions"
                />
                <Link href="/admin/roles/create">
                    <Button>Create Role</Button>
                </Link>
            </div>

            <div class="overflow-hidden rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Role
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Permissions
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Users
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Created
                            </th>
                            <th
                                class="px-4 py-3 text-right font-medium text-muted-foreground"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="role in roles"
                            :key="role.id"
                            class="border-b transition-colors hover:bg-muted/50"
                        >
                            <td class="px-4 py-3 font-medium">
                                <Badge
                                    :variant="
                                        role.name === 'Admin'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{ role.name }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ role.permissions_count }} permissions
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ role.users_count }} users
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ role.created_at }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        :href="`/admin/roles/${role.id}/edit`"
                                    >
                                        <Button variant="outline" size="sm">
                                            Edit
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="role.name !== 'Admin'"
                                        variant="destructive"
                                        size="sm"
                                        @click="confirmDelete(role)"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="deleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Role</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete the
                        <strong>{{ roleToDelete?.name }}</strong> role? Users
                        with this role will lose its permissions.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="deleteRole">
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
