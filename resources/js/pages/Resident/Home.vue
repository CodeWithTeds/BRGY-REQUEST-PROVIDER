<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
  counts: {
    permits: { pending: number; approved: number; rejected: number; processing?: number };
    clearances: { pending: number; approved: number; rejected: number; processing?: number };
    residencies: { pending: number; approved: number; rejected: number; processing?: number };
    indigencies: { pending: number; approved: number; rejected: number; processing?: number };
  };
  recentApplications: Array<{ id: number; type: string; status: string; application_date: string | null; route?: string }>;
  applicantProfile?: { first_name: string; middle_name?: string | null; last_name: string; suffix?: string | null } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Resident Dashboard', href: '/resident/dashboard' },
];
</script>

<template>
  <Head title="Resident Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto animate__animated animate__fadeIn transition-all duration-500 ease-in-out">
      <ResidentHomeUI :counts="props.counts" :recentApplications="props.recentApplications" :applicantProfile="props.applicantProfile" />
    </div>
  </AppLayout>
</template>