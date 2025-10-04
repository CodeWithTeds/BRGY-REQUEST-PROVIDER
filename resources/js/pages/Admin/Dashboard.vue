<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, ChevronLeft, ChevronRight, BarChart3, FileText, Bell, Settings, Calendar, UserCheck, FileCheck, Clock, CheckCircle2, AlertTriangle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  counts: {
    permits: { pending: number; approved: number; rejected: number };
    clearances: { pending: number; approved: number; rejected: number };
    residencies: { pending: number; processing?: number; approved: number; rejected: number };
  };
  recentApplications: Array<{ id: number; type: string; status: string; application_date: string | null }>;
}>();

const totals = computed(() => ({
  permits:
    (props.counts?.permits?.pending || 0) +
    (props.counts?.permits?.approved || 0) +
    (props.counts?.permits?.rejected || 0),
  clearances:
    (props.counts?.clearances?.pending || 0) +
    (props.counts?.clearances?.approved || 0) +
    (props.counts?.clearances?.rejected || 0),
  residencies:
    (props.counts?.residencies?.pending || 0) +
    (props.counts?.residencies?.processing || 0) +
    (props.counts?.residencies?.approved || 0) +
    (props.counts?.residencies?.rejected || 0),
}));

const statusTotals = computed(() => {
  const p = props.counts?.permits || { pending: 0, approved: 0, rejected: 0 };
  const c = props.counts?.clearances || { pending: 0, approved: 0, rejected: 0 };
  const r = props.counts?.residencies || { pending: 0, processing: 0, approved: 0, rejected: 0 };
  return {
    pending: (p.pending || 0) + (c.pending || 0) + (r.pending || 0),
    approved: (p.approved || 0) + (c.approved || 0) + (r.approved || 0),
    rejected: (p.rejected || 0) + (c.rejected || 0) + (r.rejected || 0),
    processing: r.processing || 0,
  };
});
const maxBar = computed(() => Math.max(1, ...Object.values(statusTotals.value)));
const barHeight = (n: number) => `${Math.round((n / maxBar.value) * 80) + 16}px`;

const thumbFor = (t: string) => {
  if (t === 'Permit') return '/images/business.png';
  if (t === 'Clearance') return '/images/major.png';
  return '/images/about.png';
};

const cap = (s: string) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '');
const statusClass = (s: string) => {
  const map: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700 ring-yellow-200',
    approved: 'bg-green-100 text-green-700 ring-green-200',
    rejected: 'bg-red-100 text-red-700 ring-red-200',
    processing: 'bg-blue-100 text-blue-700 ring-blue-200',
  };
  return map[s] || 'bg-gray-100 text-gray-700 ring-gray-200';
};
</script>

<template>
  <Head title="Admin Dashboard" />
  <AppLayout>
    <div class="px-6 py-6 space-y-6">
      <!-- Hero Banner -->
      <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#356cd2] to-[#2c4454] text-white">
        <div class="p-6 md:p-8">
          <div class="flex items-start justify-between">
            <div class="max-w-xl">
              <p class="text-xs uppercase tracking-wider/5 opacity-80">Barangay Requests</p>
              <h1 class="mt-2 text-2xl md:text-3xl font-semibold leading-tight">
                Manage and review resident applications
              </h1>
              <Link href="#" class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-medium text-main hover:bg-white">
                <FileText class="h-4 w-4 text-brand" />
                View requests
              </Link>
            </div>
          </div>
        </div>
      </section>

      <!-- Quick type chips -->
      <section class="flex flex-wrap gap-3">
        <div class="flex items-center gap-3 rounded-xl bg-white shadow-sm ring-1 ring-black/5 px-4 py-3">
          <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand/90 text-white">PR</div>
          <div>
            <p class="text-xs text-secondary">{{ totals.permits }} requests</p>
            <p class="text-sm font-medium">Permits</p>
          </div>
        </div>
        <div class="flex items-center gap-3 rounded-xl bg-white shadow-sm ring-1 ring-black/5 px-4 py-3">
          <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-secondary text-white">CL</div>
          <div>
            <p class="text-xs text-secondary">{{ totals.clearances }} requests</p>
            <p class="text-sm font-medium">Clearances</p>
          </div>
        </div>
        <div class="flex items-center gap-3 rounded-xl bg-white shadow-sm ring-1 ring-black/5 px-4 py-3">
          <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-main text-white">CR</div>
          <div>
            <p class="text-xs text-secondary">{{ totals.residencies }} requests</p>
            <p class="text-sm font-medium">Residency Certificates</p>
          </div>
        </div>
      </section>

      <!-- Action toolbar: quick filters & actions -->
      <section class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
          <button class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10"><Calendar class="h-4 w-4 text-secondary" />This week</button>
          <button class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10"><Clock class="h-4 w-4 text-secondary" />Pending</button>
          <button class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10"><CheckCircle2 class="h-4 w-4 text-secondary" />Approved</button>
          <button class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10"><AlertTriangle class="h-4 w-4 text-secondary" />Rejected</button>
        </div>
        <div class="flex flex-wrap gap-2">
          <button class="inline-flex items-center gap-2 rounded-full bg-brand px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-brand/90"><FileCheck class="h-4 w-4" />New permit</button>
          <button class="inline-flex items-center gap-2 rounded-full bg-main/90 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-main"><UserCheck class="h-4 w-4" />Issue certificate</button>
          <button class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10"><Bell class="h-4 w-4 text-secondary" />Alerts</button>
          <button class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10"><Settings class="h-4 w-4 text-secondary" />Settings</button>
        </div>
      </section>

      <!-- KPI cards -->
      <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
          <p class="text-xs text-secondary">Pending</p>
          <p class="mt-1 text-2xl font-semibold text-main">{{ statusTotals.pending }}</p>
          <div class="mt-3 h-2 w-full rounded-full bg-secondary/20"><div class="h-2 rounded-full bg-brand" :style="{ width: `${Math.min(100, Math.round((statusTotals.pending / maxBar) * 100))}%` }"></div></div>
        </div>
        <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
          <p class="text-xs text-secondary">Approved</p>
          <p class="mt-1 text-2xl font-semibold text-main">{{ statusTotals.approved }}</p>
          <div class="mt-3 h-2 w-full rounded-full bg-secondary/20"><div class="h-2 rounded-full bg-green-500" :style="{ width: `${Math.min(100, Math.round((statusTotals.approved / maxBar) * 100))}%` }"></div></div>
        </div>
        <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
          <p class="text-xs text-secondary">Rejected</p>
          <p class="mt-1 text-2xl font-semibold text-main">{{ statusTotals.rejected }}</p>
          <div class="mt-3 h-2 w-full rounded-full bg-secondary/20"><div class="h-2 rounded-full bg-red-500" :style="{ width: `${Math.min(100, Math.round((statusTotals.rejected / maxBar) * 100))}%` }"></div></div>
        </div>
        <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
          <p class="text-xs text-secondary">Processing</p>
          <p class="mt-1 text-2xl font-semibold text-main">{{ statusTotals.processing }}</p>
          <div class="mt-3 h-2 w-full rounded-full bg-secondary/20"><div class="h-2 rounded-full bg-blue-500" :style="{ width: `${Math.min(100, Math.round((statusTotals.processing / maxBar) * 100))}%` }"></div></div>
        </div>
      </section>

      <!-- Main grid -->
      <section class="grid grid-cols-12 gap-6">
        <!-- Left column -->
        <div class="col-span-12 xl:col-span-8 space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-main">Recent Applications</h3>
            <div class="flex items-center gap-2">
              <button class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white ring-1 ring-black/5 hover:bg-secondary/10">
                <ChevronLeft class="h-4 w-4 text-main" />
              </button>
              <button class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white ring-1 ring-black/5 hover:bg-secondary/10">
                <ChevronRight class="h-4 w-4 text-main" />
              </button>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article v-for="a in props.recentApplications" :key="`${a.type}-${a.id}`" class="overflow-hidden rounded-xl bg-white ring-1 ring-black/5">
              <div class="relative">
                <img :src="thumbFor(a.type)" alt="" class="aspect-video w-full object-cover" />
                <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-xs font-medium text-main ring-1 ring-black/5">{{ cap(a.type) }}</span>
              </div>
              <div class="space-y-3 p-4">
                <h4 class="line-clamp-2 text-sm font-semibold text-gray-900">{{ cap(a.status) }} • {{ a.application_date || '—' }}</h4>
                <div class="flex items-center justify-between">
                  <div class="text-xs text-secondary">ID #{{ a.id }}</div>
                  <Link href="#" class="inline-flex items-center gap-1 rounded-full bg-brand px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-brand/90">
                    <ClipboardList class="h-3.5 w-3.5" />
                    View
                  </Link>
                </div>
              </div>
            </article>
          </div>
        </div>

        <!-- Right column -->
        <div class="col-span-12 xl:col-span-4 space-y-4">
          <!-- Stats card -->
          <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-semibold text-main">Status overview</h4>
              <BarChart3 class="h-5 w-5 text-secondary" />
            </div>
            <div class="mt-4">
              <div class="grid grid-cols-4 gap-3">
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-brand" :style="{ height: barHeight(statusTotals.pending) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Pending</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-brand" :style="{ height: barHeight(statusTotals.approved) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Approved</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-brand" :style="{ height: barHeight(statusTotals.rejected) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Rejected</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-brand" :style="{ height: barHeight(statusTotals.processing) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Processing</span>
                </div>
              </div>
            </div>
          </div>

          <!-- By type card -->
          <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-semibold text-main">By type</h4>
              <ClipboardList class="h-5 w-5 text-secondary" />
            </div>
            <ul class="mt-3 space-y-2">
              <li class="flex items-center justify-between gap-2 rounded-lg bg-secondary/10 px-3 py-2">
                <div>
                  <p class="text-sm font-medium text-main">Permits</p>
                  <p class="text-xs text-secondary">{{ totals.permits }} total</p>
                </div>
                <Link href="#" class="rounded-full border border-secondary px-3 py-1 text-xs font-medium text-main hover:bg-secondary/20">Manage</Link>
              </li>
              <li class="flex items-center justify-between gap-2 rounded-lg bg-secondary/10 px-3 py-2">
                <div>
                  <p class="text-sm font-medium text-main">Clearances</p>
                  <p class="text-xs text-secondary">{{ totals.clearances }} total</p>
                </div>
                <Link href="#" class="rounded-full border border-secondary px-3 py-1 text-xs font-medium text-main hover:bg-secondary/20">Manage</Link>
              </li>
              <li class="flex items-center justify-between gap-2 rounded-lg bg-secondary/10 px-3 py-2">
                <div>
                  <p class="text-sm font-medium text-main">Residency Certificates</p>
                  <p class="text-xs text-secondary">{{ totals.residencies }} total</p>
                </div>
                <Link href="#" class="rounded-full border border-secondary px-3 py-1 text-xs font-medium text-main hover:bg-secondary/20">Manage</Link>
              </li>
            </ul>
            <div class="mt-3 text-center">
              <Link href="#" class="inline-flex w-full items-center justify-center rounded-full bg-secondary/20 px-3 py-1.5 text-xs font-medium text-main hover:bg-secondary/30">See All</Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>