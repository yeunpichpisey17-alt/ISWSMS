<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    FolderGit2,
    KeyRound,
    LayoutGrid,
    Shield,
    Users,
    Tag,
    Package,
    UserCheck,
    ShoppingBag,
    ShoppingCart,
    ClipboardList,
    Wrench,
    ShieldCheck,
    CreditCard,
    BarChart3,
    FileText,
    DollarSign,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const { hasRole, can } = usePermissions();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const adminNavItems = computed<NavItem[]>(() => {
    if (!hasRole('Admin')) {
return [];
}

    return [
        { title: 'Users', href: '/admin/users', icon: Users },
        { title: 'Roles', href: '/admin/roles', icon: Shield },
        { title: 'Permissions', href: '/admin/permissions', icon: KeyRound },
    ];
});

const inventoryNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (can('products.view')) {
        items.push({
            title: 'Categories',
            href: '/admin/categories',
            icon: Tag,
        });
        items.push({
            title: 'Products',
            href: '/admin/products',
            icon: Package,
        });
    }

    if (can('suppliers.view')) {
        items.push({
            title: 'Suppliers',
            href: '/admin/suppliers',
            icon: UserCheck,
        });
    }

    if (can('purchases.view')) {
        items.push({
            title: 'Purchase Orders',
            href: '/admin/purchase-orders',
            icon: ShoppingCart,
        });
    }

    if (can('inventory.view')) {
        items.push({
            title: 'Stock Adjustments',
            href: '/admin/stock-adjustments',
            icon: ClipboardList,
        });
    }

    return items;
});

const salesNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (can('customers.view')) {
        items.push({
            title: 'Customers',
            href: '/admin/customers',
            icon: Users,
        });
    }

    if (can('sales.view')) {
        items.push({ title: 'Sales', href: '/admin/sales', icon: ShoppingBag });
    }

    if (can('payments.view')) {
        items.push({
            title: 'Payments',
            href: '/admin/payments',
            icon: CreditCard,
        });
    }

    return items;
});

const serviceNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (can('repairs.view')) {
        items.push({ title: 'Repairs', href: '/admin/repairs', icon: Wrench });
    }

    if (can('warranty.view')) {
        items.push({
            title: 'Warranty Claims',
            href: '/admin/warranty-claims',
            icon: ShieldCheck,
        });
    }

    return items;
});

const reportNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (can('reports.sales') || can('reports.view')) {
        items.push({
            title: 'Sales Report',
            href: '/admin/reports/sales',
            icon: BarChart3,
        });
    }

    if (can('reports.inventory') || can('reports.view')) {
        items.push({
            title: 'Inventory Report',
            href: '/admin/reports/inventory',
            icon: FileText,
        });
    }

    if (can('reports.financial')) {
        items.push({
            title: 'Financial Report',
            href: '/admin/reports/financial',
            icon: DollarSign,
        });
    }

    return items;
});

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Repository',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: FolderGit2,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain
                v-if="adminNavItems.length"
                :items="adminNavItems"
                label="Administration"
            />
            <NavMain
                v-if="inventoryNavItems.length"
                :items="inventoryNavItems"
                label="Inventory"
            />
            <NavMain
                v-if="salesNavItems.length"
                :items="salesNavItems"
                label="Sales & Customers"
            />
            <NavMain
                v-if="serviceNavItems.length"
                :items="serviceNavItems"
                label="Service & Warranty"
            />
            <NavMain
                v-if="reportNavItems.length"
                :items="reportNavItems"
                label="Reports"
            />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
