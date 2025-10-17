<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, BarChart3, FileText, Settings, Calendar, UserCheck, FileCheck, Clock, CheckCircle2, AlertTriangle, LineChart as LineIcon, BarChart2, PieChart } from 'lucide-vue-next';
import { computed } from 'vue';
import DonutChart from '@/components/charts/DonutChart.vue';
import BarChart from '@/components/charts/BarChart.vue';
import LineChart from '@/components/charts/LineChart.vue';
import KpiCard from '@/components/Admin/KpiCard.vue';

const props = defineProps<{
  counts: {
    permits: { pending: number; approved: number; rejected: number; processing?: number; pre_approved?: number };
    clearances: { pending: number; approved: number; rejected: number; processing?: number; pre_approved?: number };
    residencies: { pending: number; processing?: number; approved: number; rejected: number; pre_approved?: number };
  };
  recentApplications: Array<{ id: number; type: string; status: string; application_date: string | null }>;
  statusDistribution: { pending: number; processing: number; approved: number; rejected: number };
  typeBreakdown: { permits: number; clearances: number; residencies: number; indigencies: number };
  timeSeries: { daily: { labels: string[]; values: number[] }; weekly: { labels: string[]; values: number[] } };
  kpis: { totalRequests: number; pending: number; approved: number; todaysAppointments: number };
  quickActions: Array<{ label: string; href: string }>;
}>();

const totals = computed(() => ({
  permits:
    (props.counts?.permits?.pending || 0) +
    (props.counts?.permits?.processing || 0) +
    (props.counts?.permits?.pre_approved || 0) +
    (props.counts?.permits?.approved || 0) +
    (props.counts?.permits?.rejected || 0),
  clearances:
    (props.counts?.clearances?.pending || 0) +
    (props.counts?.clearances?.processing || 0) +
    (props.counts?.clearances?.pre_approved || 0) +
    (props.counts?.clearances?.approved || 0) +
    (props.counts?.clearances?.rejected || 0),
  residencies:
    (props.counts?.residencies?.pending || 0) +
    (props.counts?.residencies?.processing || 0) +
    (props.counts?.residencies?.pre_approved || 0) +
    (props.counts?.residencies?.approved || 0) +
    (props.counts?.residencies?.rejected || 0),
}));

const statusTotals = computed(() => {
  const p = props.counts?.permits || { pending: 0, processing: 0, pre_approved: 0, approved: 0, rejected: 0 };
  const c = props.counts?.clearances || { pending: 0, processing: 0, pre_approved: 0, approved: 0, rejected: 0 };
  const r = props.counts?.residencies || { pending: 0, processing: 0, pre_approved: 0, approved: 0, rejected: 0 };
  return {
    pending: (p.pending || 0) + (c.pending || 0) + (r.pending || 0),
    approved: (p.approved || 0) + (c.approved || 0) + (r.approved || 0),
    rejected: (p.rejected || 0) + (c.rejected || 0) + (r.rejected || 0),
    processing: (p.processing || 0) + (c.processing || 0) + (r.processing || 0),
    pre_approved: (p.pre_approved || 0) + (c.pre_approved || 0) + (r.pre_approved || 0),
  };
});
const maxBar = computed(() => Math.max(1, ...Object.values(statusTotals.value)));
const barHeight = (n: number) => `${Math.round((n / maxBar.value) * 80) + 16}px`;

const donutSegments = computed(() => [
  { label: 'Pending', value: props.statusDistribution?.pending || 0, color: '#f59e0b' },
  { label: 'Processing', value: props.statusDistribution?.processing || 0, color: '#3b82f6' },
  { label: 'Approved', value: props.statusDistribution?.approved || 0, color: '#22c55e' },
  { label: 'Rejected', value: props.statusDistribution?.rejected || 0, color: '#ef4444' },
]);

const typeLabels = ['Permits', 'Clearances', 'Residencies', 'Indigencies'];
const typeValues = computed(() => [
  props.typeBreakdown?.permits || 0,
  props.typeBreakdown?.clearances || 0,
  props.typeBreakdown?.residencies || 0,
  props.typeBreakdown?.indigencies || 0,
]);
const typeColors = ['bg-brand', 'bg-secondary', 'bg-main', 'bg-rose-500'];


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

          
          <button class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10"><Settings class="h-4 w-4 text-secondary" />Settings</button>
        </div>
      </section>

      <!-- KPI cards -->
      <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <KpiCard title="Total Requests" :value="kpis.totalRequests" :progress="Math.min(100, Math.round((kpis.totalRequests/Math.max(1,kpis.totalRequests))*100))" :icon-component="BarChart3" accentClass="bg-brand/10 text-brand" />
        <KpiCard title="Pending" :value="kpis.pending" :progress="Math.min(100, Math.round((kpis.pending/Math.max(1,kpis.totalRequests))*100))" :icon-component="AlertTriangle" accentClass="bg-amber-100 text-amber-600" />
        <KpiCard title="Approved" :value="kpis.approved" :progress="Math.min(100, Math.round((kpis.approved/Math.max(1,kpis.totalRequests))*100))" :icon-component="CheckCircle2" accentClass="bg-emerald-100 text-emerald-600" />
        <KpiCard title="Today’s Appointments" :value="kpis.todaysAppointments" :progress="Math.min(100, Math.round((kpis.todaysAppointments/Math.max(1,kpis.totalRequests))*100))" :icon-component="Calendar" accentClass="bg-blue-100 text-blue-600" />
      </section>

      <!-- Main grid -->
      <section class="grid grid-cols-12 gap-6">
        <!-- Left column -->
        <div class="col-span-12 xl:col-span-8 space-y-4">
          <!-- Charts -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
              <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-main">Status distribution</h4>
                <PieChart class="h-5 w-5 text-secondary" />
              </div>
              <div class="mt-3">
                <DonutChart :segments="donutSegments" :center-text="String(kpis.totalRequests)" />
              </div>
            </div>
            <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
              <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-main">By document type</h4>
                <BarChart2 class="h-5 w-5 text-secondary" />
              </div>
              <div class="mt-3">
                <BarChart :labels="typeLabels" :values="typeValues" :colors="typeColors" :height="140" />
              </div>
            </div>
          </div>

          <!-- Time series -->
          <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-semibold text-main">Requests over time (Daily)</h4>
              <LineIcon class="h-5 w-5 text-secondary" />
            </div>
            <div class="mt-3">
              <LineChart :labels="props.timeSeries.daily.labels" :values="props.timeSeries.daily.values" :height="160" />
            </div>
          </div>


        </div>

        <!-- Right column -->
        <div class="col-span-12 xl:col-span-4 space-y-4">
          <!-- Status overview bars (legacy quick view) -->
          <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-semibold text-main">Status overview</h4>
              <BarChart3 class="h-5 w-5 text-secondary" />
            </div>
            <div class="mt-4">
              <div class="grid grid-cols-5 gap-3">
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-amber-500" :style="{ height: barHeight(statusTotals.pending) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Pending</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-blue-500" :style="{ height: barHeight(statusTotals.processing) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Processing</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-emerald-500" :style="{ height: barHeight(statusTotals.approved) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Approved</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-red-500" :style="{ height: barHeight(statusTotals.rejected) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Rejected</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                  <div class="h-24 w-10 rounded-md bg-brand/20">
                    <div class="w-10 rounded-b-md bg-teal-500" :style="{ height: barHeight(statusTotals.pre_approved) }"></div>
                  </div>
                  <span class="text-[11px] text-secondary">Pre-Approved</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick actions -->
          <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-semibold text-main">Quick Actions</h4>
              <ClipboardList class="h-5 w-5 text-secondary" />
            </div>
            <ul class="mt-3 space-y-2">
              <li v-for="qa in props.quickActions" :key="qa.href" class="flex items-center justify-between gap-2 rounded-lg bg-secondary/10 px-3 py-2">
                <div>
                  <p class="text-sm font-medium text-main">{{ qa.label }}</p>
                  <p class="text-xs text-secondary">Navigate</p>
                </div>
                <Link :href="qa.href" class="rounded-full border border-secondary px-3 py-1 text-xs font-medium text-main hover:bg-secondary/20">Open</Link>
              </li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>