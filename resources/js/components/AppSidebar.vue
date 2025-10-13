<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, IdCard, FileCheck, Building2, UserCheck, FileText } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Barangay Business Permit',
        href: route('resident.barangay-business-permit'),
        icon: Building2,
    },
    {
        title: 'Barangay Clearance',
        href: route('resident.barangay-clearance'),
        icon: FileCheck,
    },
    {
        title: 'Certificate of Residency',
        href: route('resident.certificate-of-residency.create'),
        icon: UserCheck,
    },
    {
        title: 'Certificate of Indigency',
        href: route('resident.certificate-of-indigency.create'),
        icon: IdCard,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Business Permits',
        href: '/admin/business-permits',
        icon: FileText,
    },
    {
        title: 'Barangay Clearances',
        href: '/admin/barangay-clearances',
        icon: FileCheck,
    },
    {
        title: 'Residency Certificates',
        href: '/admin/residency-certificates',
        icon: UserCheck,
    },
    {
        title: 'Indigency Certificates',
        href: '/admin/indigency-certificates',
        icon: IdCard,
    },
    {
        title: 'Profile Settings',
        href: '/settings/profile',
        icon: UserCheck,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

const isAdmin = computed(() => {
    const path = window.location.pathname;
    return path.startsWith('/admin') || path.includes('/admin/');
});

const navItems = computed(() => {
    return isAdmin.value ? adminNavItems : mainNavItems;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="navItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
