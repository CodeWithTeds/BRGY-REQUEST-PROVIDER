<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

function iconColors(title: string) {
    switch (title) {
        case 'Dashboard':
        case 'Home':
            return 'from-slate-400 via-blue-500 to-indigo-500';
        case 'Business Permits':
        case 'Barangay Business Permit':
            return 'from-orange-400 via-amber-500 to-yellow-500';
        case 'Barangay Clearances':
            return 'from-blue-400 via-sky-500 to-cyan-500';
        case 'Residency Certificates':
            return 'from-green-400 via-emerald-500 to-teal-500';
        case 'Indigency Certificates':
        case 'Certificate of Indigency':
            return 'from-pink-400 via-fuchsia-500 to-purple-500';
        default:
            return 'from-gray-400 via-gray-500 to-gray-600';
    }
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href" class="flex items-center gap-3">
                        <span
                            class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br text-white shadow-sm"
                            :class="[iconColors(item.title), item.href === page.url ? 'ring-2 ring-offset-2 ring-indigo-500' : '']"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                        </span>
                        <span class="text-[#2c4454]">{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
