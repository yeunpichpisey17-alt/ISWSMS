import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Auth } from '@/types';

export function usePermissions() {
    const page = usePage<{ auth: Auth }>();
    const user = computed(() => page.props.auth.user);

    function can(permission: string): boolean {
        return user.value?.permissions?.includes(permission) ?? false;
    }

    function hasRole(role: string): boolean {
        return user.value?.roles?.includes(role) ?? false;
    }

    function hasAnyRole(roles: string[]): boolean {
        return roles.some((r) => hasRole(r));
    }

    function hasAnyPermission(permissions: string[]): boolean {
        return permissions.some((p) => can(p));
    }

    return { can, hasRole, hasAnyRole, hasAnyPermission, user };
}
