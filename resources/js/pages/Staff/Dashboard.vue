<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { LayoutGrid, FileText, FileCheck, UserCheck, IdCard, BarChart3, Clock, CheckCircle2, AlertTriangle, ClipboardList } from 'lucide-vue-next';

const props = defineProps<{
  stats: {
    barangay_permits: { pending: number; processing: number; approved: number; rejected: number };
    barangay_clearances: { pending: number; processing: number; approved: number; rejected: number };
    residency_certificates: { pending: number; processing: number; approved: number; rejected: number };
    indigency_certificates: { pending: number; processing: number; approved: number; rejected: number };
  };
  auth_user: { id: number; name: string } | null;
}>();

const page = usePage();
const userName = computed(() => props.auth_user?.name || page.props.auth?.user?.name || 'Clerk');

const totals = computed(() => ({
  permits:
    (props.stats?.barangay_permits?.pending || 0) +
    (props.stats?.barangay_permits?.processing || 0) +
    (props.stats?.barangay_permits?.approved || 0) +
    (props.stats?.barangay_permits?.rejected || 0),
  clearances:
    (props.stats?.barangay_clearances?.pending || 0) +
    (props.stats?.barangay_clearances?.processing || 0) +
    (props.stats?.barangay_clearances?.approved || 0) +
    (props.stats?.barangay_clearances?.rejected || 0),
  residencies:
    (props.stats?.residency_certificates?.pending || 0) +
    (props.stats?.residency_certificates?.processing || 0) +
    (props.stats?.residency_certificates?.approved || 0) +
    (props.stats?.residency_certificates?.rejected || 0),
  indigency:
    (props.stats?.indigency_certificates?.pending || 0) +
    (props.stats?.indigency_certificates?.processing || 0) +
    (props.stats?.indigency_certificates?.approved || 0) +
    (props.stats?.indigency_certificates?.rejected || 0),
}));

const percent = (part: number, total: number) => (total > 0 ? Math.round((part / total) * 100) : 0);

const cards = computed(() => [
  { title: 'Permits', total: totals.value.permits, icon: FileText, stats: props.stats?.barangay_permits || { pending: 0, processing: 0, approved: 0, rejected: 0 } },
  { title: 'Clearances', total: totals.value.clearances, icon: FileCheck, stats: props.stats?.barangay_clearances || { pending: 0, processing: 0, approved: 0, rejected: 0 } },
  { title: 'Residency', total: totals.value.residencies, icon: UserCheck, stats: props.stats?.residency_certificates || { pending: 0, processing: 0, approved: 0, rejected: 0 } },
  { title: 'Indigency', total: totals.value.indigency, icon: IdCard, stats: props.stats?.indigency_certificates || { pending: 0, processing: 0, approved: 0, rejected: 0 } },
]);
</script>

<template>
  <Head title="Clerk Dashboard" />
  <AppLayout>
    <div class="px-6 py-6 space-y-6">
      <section class="rounded-2xl bg-gradient-to-r from-[#2c4454] to-[#356cd2] text-white">
        <div class="p-6 md:p-8">
          <div class="flex items-start justify-between">
            <div class="max-w-xl">
              <p class="text-xs uppercase tracking-wider/5 opacity-80">Staff Portal</p>
              <h1 class="mt-2 text-2xl md:text-3xl font-semibold leading-tight">
                Welcome, {{ userName }}
              </h1>
              <p class="mt-2 text-sm opacity-90">Review and process resident requests.</p>
            </div>
          </div>
        </div>
      </section>



      <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div v-for="card in cards" :key="card.title" class="group relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5">
          <div class="absolute inset-0 bg-gradient-to-b from-transparent to-secondary/10 opacity-0 group-hover:opacity-100 transition" />
          <div class="p-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <component :is="card.icon" class="h-5 w-5 text-brand" />
                <p class="text-xs text-secondary">{{ card.title }}</p>
              </div>
              <BarChart3 class="h-4 w-4 text-secondary/70" />
            </div>
            <p class="mt-2 text-3xl font-semibold text-main">{{ card.total }}</p>
            <div class="mt-3 space-y-2">
              <div class="flex items-center justify-between text-[11px] text-secondary">
                <span>Pending</span>
                <span>{{ card.stats.pending }}</span>
              </div>
              <div class="h-1.5 rounded-full bg-secondary/20">
                <div class="h-1.5 rounded-full bg-brand" :style="{ width: percent(card.stats.pending, card.total) + '%' }" />
              </div>
              <div class="flex items-center justify-between text-[11px] text-secondary">
                <span>Processing</span>
                <span>{{ card.stats.processing }}</span>
              </div>
              <div class="h-1.5 rounded-full bg-secondary/20">
                <div class="h-1.5 rounded-full bg-main" :style="{ width: percent(card.stats.processing, card.total) + '%' }" />
              </div>
              <div class="flex items-center justify-between text-[11px] text-secondary">
                <span>Approved</span>
                <span>{{ card.stats.approved }}</span>
              </div>
              <div class="h-1.5 rounded-full bg-secondary/20">
                <div class="h-1.5 rounded-full bg-emerald-500" :style="{ width: percent(card.stats.approved, card.total) + '%' }" />
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="flex flex-wrap gap-2">
        <Link href="/admin/business-permits" class="inline-flex items-center gap-2 rounded-full bg-brand px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-brand/90">Manage Permits</Link>
        <Link href="/admin/barangay-clearances" class="inline-flex items-center gap-2 rounded-full bg-main px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-main">Manage Clearances</Link>
        <Link href="/admin/residency-certificates" class="inline-flex items-center gap-2 rounded-full bg-secondary px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-secondary/90">Manage Residency</Link>
        <Link href="/admin/indigency-certificates" class="inline-flex items-center gap-2 rounded-full bg-secondary px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-secondary/90">Manage Indigency</Link>
      </section>
      <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Link href="#" class="rounded-xl bg-neutral-900 text-white p-4 hover:bg-neutral-800 transition">
          <div class="flex items-center gap-2">
            <ClipboardList class="h-4 w-4" />
            <span class="text-sm font-semibold">Review queue</span>
          </div>
          <p class="mt-2 text-xs text-neutral-300">See pending applications</p>
        </Link>
        <Link href="#" class="rounded-xl bg-indigo-600 text-white p-4 hover:bg-indigo-500 transition">
          <div class="flex items-center gap-2">
            <Clock class="h-4 w-4" />
            <span class="text-sm font-semibold">Schedule appointments</span>
          </div>
          <p class="mt-2 text-xs text-indigo-100">Manage applicant slots</p>
        </Link>
        <Link href="#" class="rounded-xl bg-emerald-600 text-white p-4 hover:bg-emerald-500 transition">
          <div class="flex items-center gap-2">
            <CheckCircle2 class="h-4 w-4" />
            <span class="text-sm font-semibold">Approve documents</span>
          </div>
          <p class="mt-2 text-xs text-emerald-100">Batch approve ready items</p>
        </Link>
        <Link :href="route('profile.edit')" class="rounded-xl bg-white p-4 ring-1 ring-black/5 hover:bg-secondary/10 transition">
    <div class="flex items-center gap-2 text-main">
      <LayoutGrid class="h-4 w-4" />
      <span class="text-sm font-semibold">Profile settings</span>
    </div>
    <p class="mt-2 text-xs text-secondary">Update your account</p>
  </Link>
      </section>
      <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
          <h4 class="text-sm font-semibold text-main">Status overview</h4>
          <ul class="mt-3 space-y-2 text-sm">
            <li class="flex items-center justify-between">
              <span class="inline-flex items-center gap-2 text-secondary"><AlertTriangle class="h-4 w-4 text-amber-500"/> Pending</span>
              <span class="font-medium">{{ (props.stats?.barangay_permits?.pending || 0) + (props.stats?.barangay_clearances?.pending || 0) + (props.stats?.residency_certificates?.pending || 0) + (props.stats?.indigency_certificates?.pending || 0) }}</span>
            </li>
            <li class="flex items-center justify-between">
              <span class="inline-flex items-center gap-2 text-secondary"><Clock class="h-4 w-4 text-blue-500"/> Processing</span>
              <span class="font-medium">{{ (props.stats?.barangay_permits?.processing || 0) + (props.stats?.barangay_clearances?.processing || 0) + (props.stats?.residency_certificates?.processing || 0) + (props.stats?.indigency_certificates?.processing || 0) }}</span>
            </li>
            <li class="flex items-center justify-between">
              <span class="inline-flex items-center gap-2 text-secondary"><CheckCircle2 class="h-4 w-4 text-emerald-600"/> Approved</span>
              <span class="font-medium">{{ (props.stats?.barangay_permits?.approved || 0) + (props.stats?.barangay_clearances?.approved || 0) + (props.stats?.residency_certificates?.approved || 0) + (props.stats?.indigency_certificates?.approved || 0) }}</span>
            </li>
          </ul>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-brand/10 to-secondary/20 p-4 ring-1 ring-black/5">
          <h4 class="text-sm font-semibold text-main">Today</h4>
          <div class="mt-3 grid grid-cols-2 gap-3">
            <div class="rounded-lg bg-white p-3 ring-1 ring-black/5">
              <p class="text-xs text-secondary">New requests</p>
              <p class="mt-1 text-lg font-semibold">{{ (props.stats?.barangay_permits?.pending || 0) + (props.stats?.barangay_clearances?.pending || 0) }}</p>
            </div>
            <div class="rounded-lg bg-white p-3 ring-1 ring-black/5">
              <p class="text-xs text-secondary">Appointments</p>
              <p class="mt-1 text-lg font-semibold">{{ (props.stats?.barangay_permits?.processing || 0) + (props.stats?.residency_certificates?.processing || 0) }}</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl bg-neutral-900 text-white p-4">
          <h4 class="text-sm font-semibold">Tips</h4>
          <p class="mt-2 text-sm text-neutral-300">Use batch approve and filters to speed up processing.</p>
          <Link href="#" class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-medium text-white hover:bg-white/20">
            <ClipboardList class="h-3.5 w-3.5" />
            Learn batch actions
          </Link>
        </div>
      </section>
    </div>
  </AppLayout>
</template>