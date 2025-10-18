<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, IdCard, FileCheck, Building2, UserCheck, FileText, Users, ScrollText } from 'lucide-vue-next';
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
        title: 'Clerks',
        href: '/admin/clerks',
        icon: Users,
    },
    {
        title: 'Activity Log',
        href: '/admin/activity-log',
        icon: ScrollText,
    },
];

// Staff/Clerk sidebar items
const staffNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/staff/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Business Permits',
        href: '/staff/business-permits',
        icon: FileText,
    },
    {
        title: 'Barangay Clearances',
        href: '/staff/barangay-clearances',
        icon: FileCheck,
    },
    {
        title: 'Residency Certificates',
        href: '/staff/residency-certificates',
        icon: UserCheck,
    },
    {
        title: 'Indigency Certificates',
        href: '/staff/indigency-certificates',
        icon: IdCard,
    },
    
    // Removed Activity Log entry from staff sidebar
];

// Footer external links removed per request

const page = usePage();
const userRole = computed(() => (page.props as any).auth?.user?.role ?? 'resident');

const navItems = computed(() => {
    if (userRole.value === 'admin') return adminNavItems;
    if (userRole.value === 'staff' || userRole.value === 'clerk') return staffNavItems;
    return mainNavItems;
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
