<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    permissions: Record<string, string[]>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Roles', href: '/admin/roles' },
    { title: 'Create Role', href: '/admin/roles/create' },
];

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const modules = computed(() => Object.keys(props.permissions));

function isModuleAllSelected(module: string): boolean {
    return props.permissions[module].every((p) =>
        form.permissions.includes(p),
    );
}

function toggleModule(module: string) {
    const modulePerms = props.permissions[module];

    if (isModuleAllSelected(module)) {
        form.permissions = form.permissions.filter(
            (p) => !modulePerms.includes(p),
        );
    } else {
        const newPerms = modulePerms.filter(
            (p) => !form.permissions.includes(p),
        );
        form.permissions = [...form.permissions, ...newPerms];
    }
}

function togglePermission(permission: string) {
    const index = form.permissions.indexOf(permission);

    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permission);
    }
}

function submit() {
    form.post('/admin/roles');
}
</script>

<template>
    <Head title="Create Role" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-3xl space-y-6 p-6">
            <Heading
                title="Create Role"
                description="Create a new role and assign permissions"
            />

            <form @submit.prevent="submit">
                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <div class="grid gap-2">
                            <Label for="name">Role Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Enter role name"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-4">
                            <Label>Permissions</Label>
                            <InputError :message="form.errors.permissions" />

                            <div
                                v-for="module in modules"
                                :key="module"
                                class="rounded-lg border p-4"
                            >
                                <div class="mb-3 flex items-center gap-2">
                                    <Checkbox
                                        :id="`module-${module}`"
                                        :checked="isModuleAllSelected(module)"
                                        @update:checked="toggleModule(module)"
                                    />
                                    <Label
                                        :for="`module-${module}`"
                                        class="cursor-pointer text-sm font-semibold capitalize"
                                    >
                                        {{ module }}
                                    </Label>
                                </div>
                                <div
                                    class="ml-6 grid grid-cols-2 gap-2 md:grid-cols-4"
                                >
                                    <div
                                        v-for="permission in permissions[
                                            module
                                        ]"
                                        :key="permission"
                                        class="flex items-center gap-2"
                                    >
                                        <Checkbox
                                            :id="permission"
                                            :checked="
                                                form.permissions.includes(
                                                    permission,
                                                )
                                            "
                                            @update:checked="
                                                togglePermission(permission)
                                            "
                                        />
                                        <Label
                                            :for="permission"
                                            class="cursor-pointer text-xs text-muted-foreground"
                                        >
                                            {{
                                                permission.split('.').pop()
                                            }}
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
                        <Link href="/admin/roles">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            Create Role
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
