<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, FileCheck, UserCheck, Calendar, CheckCircle2, Clock, AlertTriangle, ChevronRight, XCircle, Loader2, HeartHandshake, BadgeCheck, IdCard } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
  counts: {
    permits: { pending: number; approved: number; rejected: number; processing?: number; pre_approved?: number };
    clearances: { pending: number; approved: number; rejected: number; processing?: number; pre_approved?: number };
    residencies: { pending: number; approved: number; rejected: number; processing?: number; pre_approved?: number };
    indigencies: { pending: number; approved: number; rejected: number; processing?: number; pre_approved?: number };
  };
  recentApplications: Array<{ id: number; type: string; status: string; application_date: string | null; route?: string }>;
  applicantProfile?: { first_name?: string | null; middle_name?: string | null; last_name?: string | null; suffix?: string | null } | null;
}>();

const page = usePage();
const displayName = computed(() => {
  const ap = props.applicantProfile;
  if (ap && (ap.first_name || ap.last_name)) {
    return [ap.first_name, ap.last_name].filter(Boolean).join(' ');
  }
  return page.props.auth?.user?.name || 'Resident';
});

const totals = computed(() => ({
  permits:
    (props.counts?.permits?.pending || 0) +
    (props.counts?.permits?.approved || 0) +
    (props.counts?.permits?.rejected || 0) +
    (props.counts?.permits?.processing || 0) +
    (props.counts?.permits?.pre_approved || 0),
  clearances:
    (props.counts?.clearances?.pending || 0) +
    (props.counts?.clearances?.approved || 0) +
    (props.counts?.clearances?.rejected || 0) +
    (props.counts?.clearances?.processing || 0) +
    (props.counts?.clearances?.pre_approved || 0),
  residencies:
    (props.counts?.residencies?.pending || 0) +
    (props.counts?.residencies?.approved || 0) +
    (props.counts?.residencies?.rejected || 0) +
    (props.counts?.residencies?.processing || 0) +
    (props.counts?.residencies?.pre_approved || 0),
  indigencies:
    (props.counts?.indigencies?.pending || 0) +
    (props.counts?.indigencies?.approved || 0) +
    (props.counts?.indigencies?.rejected || 0) +
    (props.counts?.indigencies?.processing || 0) +
    (props.counts?.indigencies?.pre_approved || 0),
}));

const statusTotals = computed(() => {
  const p = props.counts?.permits || { pending: 0, approved: 0, rejected: 0, processing: 0, pre_approved: 0 };
  const c = props.counts?.clearances || { pending: 0, approved: 0, rejected: 0, processing: 0, pre_approved: 0 };
  const r = props.counts?.residencies || { pending: 0, approved: 0, rejected: 0, processing: 0, pre_approved: 0 };
  const i = props.counts?.indigencies || { pending: 0, approved: 0, rejected: 0, processing: 0, pre_approved: 0 };
  return {
    pending: (p.pending || 0) + (c.pending || 0) + (r.pending || 0) + (i.pending || 0),
    approved: (p.approved || 0) + (c.approved || 0) + (r.approved || 0) + (i.approved || 0),
    rejected: (p.rejected || 0) + (c.rejected || 0) + (r.rejected || 0) + (i.rejected || 0),
    processing: (p.processing || 0) + (c.processing || 0) + (r.processing || 0) + (i.processing || 0),
    pre_approved: (p.pre_approved || 0) + (c.pre_approved || 0) + (r.pre_approved || 0) + (i.pre_approved || 0),
  };
});

// Donut chart computed pieces
const donutData = computed(() => {
  const entries = [
    { label: 'Pending', value: statusTotals.value.pending, color: '#f59e0b' },
    { label: 'Approved', value: statusTotals.value.approved, color: '#22c55e' },
    { label: 'Rejected', value: statusTotals.value.rejected, color: '#ef4444' },
    { label: 'Processing', value: statusTotals.value.processing, color: '#3b82f6' },
    { label: 'Pre-Approved', value: statusTotals.value.pre_approved, color: '#14b8a6' },
  ];
  const total = Math.max(1, entries.reduce((sum, e) => sum + e.value, 0));
  let acc = 0;
  return entries.map((e) => {
    const start = acc / total;
    acc += e.value;
    const end = acc / total;
    return { ...e, start, end };
  });
});

const selectedDate = ref<string>('');
const selectedType = ref<string>('clearance');

const cap = (s: string) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '');
const overallTotal = computed(() => (
  statusTotals.value.pending +
  statusTotals.value.approved +
  statusTotals.value.rejected +
  statusTotals.value.processing +
  statusTotals.value.pre_approved
));

const statusCards = computed(() => [
  { key: 'pending', label: 'Pending', value: statusTotals.value.pending, icon: Clock, iconClass: 'text-amber-600', bgClass: 'bg-amber-100', barClass: 'bg-amber-500' },
  { key: 'approved', label: 'Approved', value: statusTotals.value.approved, icon: CheckCircle2, iconClass: 'text-green-600', bgClass: 'bg-green-100', barClass: 'bg-green-500' },
  { key: 'pre_approved', label: 'Pre-Approved', value: statusTotals.value.pre_approved, icon: BadgeCheck, iconClass: 'text-teal-600', bgClass: 'bg-teal-100', barClass: 'bg-teal-500' },
  { key: 'rejected', label: 'Rejected', value: statusTotals.value.rejected, icon: XCircle, iconClass: 'text-red-600', bgClass: 'bg-red-100', barClass: 'bg-red-500' },
  { key: 'processing', label: 'Processing', value: statusTotals.value.processing, icon: Loader2, iconClass: 'text-blue-600', bgClass: 'bg-blue-100', barClass: 'bg-blue-500' },
]);
</script>

<template>
  <div class="px-2 sm:px-4 py-4 space-y-6">
    <!-- Hero Banner -->
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#356cd2] to-[#2c4854] text-white">
      <div class="p-6 md:p-8">
        <div class="flex items-start justify-between">
          <div class="max-w-xl">
            <p class="text-xs uppercase tracking-wider/5 opacity-80">Welcome back</p>
            <h1 class="mt-2 text-2xl md:text-3xl font-semibold leading-tight">
              {{ displayName }}
            </h1>
            <p class="mt-2 text-sm opacity-90">Schedule your pickup or document issuing appointment.</p>
            <div class="mt-4 flex flex-wrap gap-2">
              <Link :href="route('barangay-permit.create')" class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-medium text-main hover:bg-white">
                <FileCheck class="h-4 w-4 text-brand" /> New Permit
              </Link>
              <Link :href="route('barangay-clearance.create')" class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-medium text-main hover:bg-white">
                <ClipboardList class="h-4 w-4 text-brand" /> Barangay Clearance
              </Link>
              <Link :href="route('resident.certificate-of-residency.create')" class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-medium text-main hover:bg-white">
                <UserCheck class="h-4 w-4 text-brand" /> Residency Certificate
              </Link>
              <Link :href="route('resident.certificate-of-indigency.create')" class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-medium text-main hover:bg-white">
                <UserCheck class="h-4 w-4 text-brand" /> Indigency Certificate
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Quick type chips -->
    <section class="flex flex-wrap gap-3">
      <div class="flex items-center gap-3 rounded-xl bg-white shadow-sm ring-1 ring-black/5 px-4 py-3">
        <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-main text-white">
          <FileCheck class="h-4 w-4" />
        </div>
        <div>
          <p class="text-xs text-secondary">{{ totals.permits }} requests</p>
          <p class="text-sm font-medium">Permits</p>
        </div>
      </div>
      <div class="flex items-center gap-3 rounded-xl bg-white shadow-sm ring-1 ring-black/5 px-4 py-3">
        <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-secondary text-white">
          <BadgeCheck class="h-4 w-4" />
        </div>
        <div>
          <p class="text-xs text-secondary">{{ totals.clearances }} requests</p>
          <p class="text-sm font-medium">Clearances</p>
        </div>
      </div>
      <div class="flex items-center gap-3 rounded-xl bg-white shadow-sm ring-1 ring-black/5 px-4 py-3">
        <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-main text-white">
          <IdCard class="h-4 w-4" />
        </div>
        <div>
          <p class="text-xs text-secondary">{{ totals.residencies }} requests</p>
          <p class="text-sm font-medium">Residency Certificates</p>
        </div>
      </div>
      <div class="flex items-center gap-3 rounded-xl bg-white shadow-sm ring-1 ring-black/5 px-4 py-3">
        <div class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
          <HeartHandshake class="h-4 w-4" />
        </div>
        <div>
          <p class="text-xs text-secondary">{{ totals.indigencies }} requests</p>
          <p class="text-sm font-medium">Indigency Certificates</p>
        </div>
      </div>
    </section>

    <!-- KPI cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
      <div v-for="card in statusCards" :key="card.key" class="relative overflow-hidden rounded-2xl bg-white p-4 ring-1 ring-black/5 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
          <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl" :class="card.bgClass">
            <component :is="card.icon" class="h-5 w-5" :class="card.iconClass" />
          </div>
          <div>
            <p class="text-xs text-secondary">{{ card.label }}</p>
            <p class="mt-1 text-2xl font-semibold text-main">{{ card.value }}</p>
          </div>
        </div>
        <div class="mt-4 h-2 w-full rounded-full bg-secondary/20">
          <div class="h-2 rounded-full" :class="card.barClass" :style="{ width: `${Math.min(100, Math.round((card.value / Math.max(1, overallTotal)) * 100))}%` }"></div>
        </div>
      </div>
    </section>

    <!-- Appointment scheduling + Donut chart -->
    <section class="grid grid-cols-12 gap-6">
      <!-- Appointment scheduling -->
      <div class="col-span-12 xl:col-span-8 space-y-4">
        <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-main">Schedule an appointment</h3>
          </div>
          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div>
              <label class="text-xs text-secondary">Appointment type</label>
              <select v-model="selectedType" class="mt-1 w-full rounded-lg border border-secondary/30 bg-white px-3 py-2 text-sm">
                <option value="clearance">Barangay Clearance</option>
                <option value="permit">Barangay Business Permit</option>
                <option value="residency">Residency Certificate</option>
                <option value="indigency">Indigency Certificate</option>
              </select>
            </div>
            <div>
              <label class="text-xs text-secondary">Preferred date</label>
              <input v-model="selectedDate" type="date" class="mt-1 w-full rounded-lg border border-secondary/30 bg-white px-3 py-2 text-sm" />
            </div>
          </div>
          <div class="mt-4 flex flex-wrap gap-2">
            <Link :href="route(selectedType === 'clearance' ? 'barangay-clearance.schedule' : selectedType === 'permit' ? 'barangay-permit.schedule' : selectedType === 'residency' ? 'resident.certificate-of-residency.schedule' : 'resident.certificate-of-indigency.schedule')" class="inline-flex items-center gap-2 rounded-full bg-brand px-4 py-2 text-xs font-medium text-white shadow hover:bg-brand/90">
              Proceed to scheduling
              <ChevronRight class="h-4 w-4" />
            </Link>
            <Link v-if="selectedType === 'clearance'" :href="route('barangay-clearance.availability')" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10">Check availability</Link>
            <Link v-if="selectedType === 'permit'" :href="route('barangay-permit.availability')" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10">Check availability</Link>
            <Link v-if="selectedType === 'residency'" :href="route('resident.certificate-of-residency.availability')" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10">Check availability</Link>
            <Link v-if="selectedType === 'indigency'" :href="route('resident.certificate-of-indigency.availability')" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-medium text-main ring-1 ring-black/5 hover:bg-secondary/10">Check availability</Link>
          </div>
        </div>
      </div>

      <!-- Donut chart -->
      <div class="col-span-12 xl:col-span-4 space-y-4">
        <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-main">Status distribution</h4>
            <Calendar class="h-5 w-5 text-secondary" />
          </div>
          <div class="mt-4 grid grid-cols-2 gap-4 items-center">
            <svg viewBox="0 0 100 100" class="w-32 h-32 mx-auto">
              <g transform="translate(50,50)">
                <circle r="30" fill="#f3f4f6"></circle>
                <template v-for="(arc, idx) in donutData" :key="idx">
                  <path :d="(() => {
                    const rs = 30; // radius
                    const start = arc.start * Math.PI * 2 - Math.PI / 2;
                    const end = arc.end * Math.PI * 2 - Math.PI / 2;
                    const x1 = Math.cos(start) * rs;
                    const y1 = Math.sin(start) * rs;
                    const x2 = Math.cos(end) * rs;
                    const y2 = Math.sin(end) * rs;
                    const largeArc = arc.end - arc.start > 0.5 ? 1 : 0;
                    return `M ${x1} ${y1} A ${rs} ${rs} 0 ${largeArc} 1 ${x2} ${y2} L 0 0 Z`;
                  })()" :fill="arc.color" />
                </template>
                <circle r="18" fill="white"></circle>
                <text text-anchor="middle" dominant-baseline="middle" font-size="8" fill="#111827">{{ statusTotals.pending + statusTotals.approved + statusTotals.rejected + statusTotals.processing + statusTotals.pre_approved }}</text>
              </g>
            </svg>
            <ul class="space-y-1">
              <li class="flex items-center gap-2 text-sm"><span class="inline-block h-3 w-3 rounded-full" style="background:#f59e0b"></span> Pending: {{ statusTotals.pending }}</li>
              <li class="flex items-center gap-2 text-sm"><span class="inline-block h-3 w-3 rounded-full" style="background:#22c55e"></span> Approved: {{ statusTotals.approved }}</li>
              <li class="flex items-center gap-2 text-sm"><span class="inline-block h-3 w-3 rounded-full" style="background:#14b8a6"></span> Pre-Approved: {{ statusTotals.pre_approved }}</li>
              <li class="flex items-center gap-2 text-sm"><span class="inline-block h-3 w-3 rounded-full" style="background:#ef4444"></span> Rejected: {{ statusTotals.rejected }}</li>
              <li class="flex items-center gap-2 text-sm"><span class="inline-block h-3 w-3 rounded-full" style="background:#3b82f6"></span> Processing: {{ statusTotals.processing }}</li>
            </ul>
          </div>
        </div>

        <!-- Start a request -->
        <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
          <h4 class="text-sm font-semibold text-main">Start a request</h4>
          <div class="mt-3 grid gap-2">
            <Link :href="route('barangay-clearance.create')" class="inline-flex items-center justify-between rounded-lg bg-secondary/20 px-3 py-2 text-sm font-medium text-main hover:bg-secondary/30">
              <span>Apply for Barangay Clearance</span>
              <ChevronRight class="h-4 w-4 text-secondary" />
            </Link>
            <Link :href="route('resident.certificate-of-residency.create')" class="inline-flex items-center justify-between rounded-lg bg-secondary/20 px-3 py-2 text-sm font-medium text-main hover:bg-secondary/30">
              <span>Apply for Residency Certificate</span>
              <ChevronRight class="h-4 w-4 text-secondary" />
            </Link>
            <Link :href="route('resident.certificate-of-indigency.create')" class="inline-flex items-center justify-between rounded-lg bg-secondary/20 px-3 py-2 text-sm font-medium text-main hover:bg-secondary/30">
              <span>Apply for Indigency Certificate</span>
              <ChevronRight class="h-4 w-4 text-secondary" />
            </Link>
            <Link :href="route('barangay-permit.create')" class="inline-flex items-center justify-between rounded-lg bg-secondary/20 px-3 py-2 text-sm font-medium text-main hover:bg-secondary/30">
              <span>Apply for Barangay Business Permit</span>
              <ChevronRight class="h-4 w-4 text-secondary" />
            </Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
