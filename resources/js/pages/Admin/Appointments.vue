<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { ref, computed, onMounted } from 'vue';
import { CalendarDays, Search, ChevronLeft, ChevronRight, Eye } from 'lucide-vue-next';
import Toastify from 'toastify-js';
import OccupiedCalendar from '@/components/OccupiedCalendar.vue';

interface AppointmentItem {
  id: number;
  type: string; // Permit, Clearance, Residency, Indigency
  appointable_type?: string;
  status: 'scheduled' | 'completed' | 'cancelled' | 'no_show' | string;
  appointment_at: string | null; // ISO string localized to Asia/Manila
  appointable_id?: number | null;
  applicant_name?: string | null;
}

interface Stats {
  total: number;
  scheduled: number;
  completed: number;
  cancelled: number;
  no_show: number;
}

interface Pagination {
  current_page: number;
  per_page: number;
  last_page: number;
  total: number;
}

interface FiltersProp {
  statusOptions: string[];
  typeOptions: { value: string; label: string }[];
}

interface QueryProp {
  status?: string | null;
  type?: string | null;
  date_from?: string | null;
  date_to?: string | null;
  q?: string | null;
}

interface CalendarProp {
  busyDates: string[];
  year: number;
}

const props = defineProps<{
  items: AppointmentItem[];
  stats: Stats;
  pagination: Pagination;
  filters: FiltersProp;
  query: QueryProp;
  calendar: CalendarProp;
}>();

const basePath = computed(() => `/admin/appointments`);

// Filters (server-driven)
const status = ref(props.query.status || '');
const type = ref(props.query.type || '');
const dateFrom = ref(props.query.date_from || '');
const dateTo = ref(props.query.date_to || '');
const q = ref(props.query.q || '');

function applyFilters(page?: number) {
  const data: Record<string, string> = {};
  if (status.value) data.status = status.value;
  if (type.value) data.type = type.value;
  if (dateFrom.value) data.date_from = dateFrom.value;
  if (dateTo.value) data.date_to = dateTo.value;
  if (q.value) data.q = q.value;
  if (page && page > 0) data.page = String(page);
  router.visit(basePath.value, {
    method: 'get',
    data,
    preserveScroll: true,
    preserveState: true,
  });
}

function clearFilters() {
  status.value = '';
  type.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  q.value = '';
  applyFilters();
}

function statusChip(s: string) {
  switch (s) {
    case 'scheduled':
      return 'bg-indigo-100 text-indigo-700 ring-indigo-200';
    case 'completed':
      return 'bg-green-100 text-green-700 ring-green-200';
    case 'cancelled':
      return 'bg-red-100 text-red-700 ring-red-200';
    case 'no_show':
      return 'bg-yellow-100 text-yellow-700 ring-yellow-200';
    default:
      return 'bg-gray-100 text-gray-700 ring-gray-200';
  }
}

function formatDate(iso?: string | null) {
  if (!iso) return '—';
  try {
    const d = new Date(iso);
    return d.toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
  } catch {
    return iso || '—';
  }
}

function appointableHref(item: AppointmentItem) {
  const id = item.appointable_id;
  if (!id) return '';
  // Prefer FQCN mapping when available
  switch (item.appointable_type) {
    case 'App\\Models\\BarangayPermit':
      return `/admin/business-permits/${id}`;
    case 'App\\Models\\BarangayClearance':
      return `/admin/barangay-clearances/${id}`;
    case 'App\\Models\\CertificateOfResidency':
      return `/admin/residency-certificates/${id}`;
    case 'App\\Models\\CertificateOfIndigency':
      return `/admin/indigency-certificates/${id}`;
  }
  // Fallback based on label
  const t = (item.type || '').toLowerCase();
  if (t.includes('permit')) return `/admin/business-permits/${id}`;
  if (t.includes('clearance')) return `/admin/barangay-clearances/${id}`;
  if (t.includes('residency')) return `/admin/residency-certificates/${id}`;
  if (t.includes('indigency')) return `/admin/indigency-certificates/${id}`;
  return '';
}

function toast(msg: string, type: 'info' | 'success' | 'error' = 'info') {
  Toastify({
    text: msg,
    duration: 2500,
    gravity: 'top',
    position: 'right',
    backgroundColor: type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#334155',
  }).showToast();
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Appointments', href: basePath.value },
];
const calendarFullscreen = ref(false);
// Availability panel state
const availabilityDate = ref<string>(new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' }));
const occupiedTimes = ref<string[]>([]);
const timeslots = computed<string[]>(() => {
  const slots: string[] = [];
  const startMinutes = 8 * 60; // 08:00
  const endMinutes = 17 * 60; // 17:00
  for (let m = startMinutes; m <= endMinutes; m += 30) {
    const hh = String(Math.floor(m / 60)).padStart(2, '0');
    const mm = String(m % 60).padStart(2, '0');
    slots.push(`${hh}:${mm}`);
  }
  return slots;
});
const selectedTimeslot = ref<string>('');
const filteredTimeslots = computed<string[]>(() => selectedTimeslot.value ? [selectedTimeslot.value] : timeslots.value);
function formatTime24To12(t: string) {
  const [hStr, mStr] = t.split(':');
  let h = parseInt(hStr, 10);
  const ampm = h >= 12 ? 'PM' : 'AM';
  h = h % 12;
  if (h === 0) h = 12;
  return `${h}:${mStr} ${ampm}`;
}
const counts = ref<Record<string, number>>({});
const capacity = ref<number>(10);
const totalScheduled = ref<number>(0);
const remainingPerSlot = ref<Record<string, number>>({});
async function loadAvailability() {
  try {
    const url = `${basePath.value}/availability?date=${availabilityDate.value}`;
    const res = await fetch(url);
    const data = await res.json();
    counts.value = (data?.counts) || {};
    capacity.value = (typeof data?.capacity === 'number' ? data.capacity : 10);
    totalScheduled.value = (typeof data?.totalScheduled === 'number' ? data.totalScheduled : 0);
    remainingPerSlot.value = (data?.remainingPerSlot) || {};
    occupiedTimes.value = Array.isArray(data?.occupied)
      ? data.occupied
      : Object.entries(counts.value)
          .filter(([, c]) => (c as number) >= capacity.value)
          .map(([t]) => t);
  } catch (e) {
    counts.value = {};
    totalScheduled.value = 0;
    occupiedTimes.value = [];
    remainingPerSlot.value = {};
  }
}
onMounted(() => { loadAvailability(); });

function onSelectDate(dateStr: string) {
  availabilityDate.value = dateStr;
  loadAvailability();
}
</script>

<template>
  <Head title="Appointments" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="text-sm text-[#2c4454]">All Appointments: <span class="font-semibold">{{ props.stats.total }}</span></div>
      </div>
      <div class="mt-2 text-lg font-semibold text-[#2c4454]">Appointment Management</div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="rounded-md border border-[#2c4454]/20 p-4">
          <p class="text-xs text-[#2c4454] opacity-70">Scheduled</p>
          <p class="mt-1 text-2xl font-semibold text-[#2c4454]">{{ props.stats.scheduled }}</p>
        </div>
        <div class="rounded-md border border-[#2c4454]/20 p-4">
          <p class="text-xs text-[#2c4454] opacity-70">Completed</p>
          <p class="mt-1 text-2xl font-semibold text-[#2c4454]">{{ props.stats.completed }}</p>
        </div>
        <div class="rounded-md border border-[#2c4454]/20 p-4">
          <p class="text-xs text-[#2c4454] opacity-70">Cancelled</p>
          <p class="mt-1 text-2xl font-semibold text-[#2c4454]">{{ props.stats.cancelled }}</p>
        </div>
        <div class="rounded-md border border-[#2c4454]/20 p-4">
          <p class="text-xs text-[#2c4454] opacity-70">No-show</p>
          <p class="mt-1 text-2xl font-semibold text-[#2c4454]">{{ props.stats.no_show }}</p>
        </div>
      </div>
    </template>

    <div class="bp-theme">
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Main column -->
                <div class="lg:col-span-9 space-y-6">
                  <!-- Filters -->
                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                      <div>
                        <label class="block text-xs text-[#2c4454] opacity-70">Status</label>
                        <select v-model="status" class="mt-1 w-full rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2">
                          <option value="">All</option>
                          <option v-for="s in props.filters.statusOptions" :key="s" :value="s">{{ s }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-xs text-[#2c4454] opacity-70">Type</label>
                        <select v-model="type" class="mt-1 w-full rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2">
                          <option value="">All</option>
                          <option v-for="t in props.filters.typeOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-xs text-[#2c4454] opacity-70">Date from</label>
                        <input v-model="dateFrom" type="date" class="mt-1 w-full rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2" />
                      </div>
                      <div>
                        <label class="block text-xs text-[#2c4454] opacity-70">Date to</label>
                        <input v-model="dateTo" type="date" class="mt-1 w-full rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2" />
                      </div>
                      <div class="md:col-span-2">
                        <label class="block text-xs text-[#2c4454] opacity-70">Search</label>
                        <div class="mt-1 flex items-center gap-2">
                          <div class="relative w-full">
                            <Search class="absolute left-2 top-1/2 -translate-y-1/2 h-4 w-4 text-[#2c4454] opacity-60" />
                            <input v-model="q" type="text" placeholder="Search by applicant or ID" class="w-full rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 pl-8 pr-2" />
                          </div>
                          <button @click="applyFilters()" class="px-3 py-2 bg-[#2c4454] text-white rounded-md text-sm hover:opacity-90">Apply</button>
                          <button @click="clearFilters()" class="px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">Clear</button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Table -->
                  <div class="rounded-lg border border-[#2c4454]/20 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">ID</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Applicant</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Type</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Appointment</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Status</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in props.items" :key="item.id" class="hover:bg-gray-50">
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454]">#{{ item.id }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454]">{{ item.applicant_name || '—' }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454]">
                            <template v-if="item.appointable_id && appointableHref(item)">
                              <Link :href="appointableHref(item)" class="text-[#2c4454] hover:underline">{{ item.type }} #{{ item.appointable_id }}</Link>
                            </template>
                            <template v-else>
                              {{ item.type }}
                            </template>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454]">{{ formatDate(item.appointment_at) }}</td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium ring-1" :class="statusChip(item.status)">
                              <span class="capitalize">{{ item.status.replace('_', ' ') }}</span>
                            </span>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                              <Link :href="`${basePath}/${item.id}`" class="text-[#2c4454] hover:opacity-80" title="View Appointment">
                                <Eye class="h-5 w-5" />
                              </Link>
                              <Link v-if="item.appointable_id && appointableHref(item)" :href="appointableHref(item)" class="text-[#2c4454] hover:opacity-80" title="Open Record">
                                <CalendarDays class="h-5 w-5" />
                              </Link>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Availability (full-width) -->
                  <div class="rounded-lg border border-[#2c4454]/20 overflow-x-auto mt-6">
                    <div class="bg-gray-50 px-6 py-3 flex items-center justify-between">
                      <div class="text-sm font-medium text-[#2c4454]">Timeslot Availability</div>
                      <div class="flex items-center gap-2">
                        <input v-model="availabilityDate" type="date" class="rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2" @change="loadAvailability" />
                        <select v-model="selectedTimeslot" class="rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2">
                          <option value="">All times</option>
                          <option v-for="t in timeslots" :key="t" :value="t">{{ formatTime24To12(t) }}</option>
                        </select>
                        <button @click="loadAvailability" class="px-3 py-2 bg-[#2c4454] text-white rounded-md text-sm hover:opacity-90">Check</button>
                      </div>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Time</th>
                          <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Scheduled</th>
                          <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Remaining</th>
                          <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Capacity</th>
                          <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Status</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="t in filteredTimeslots" :key="t" class="hover:bg-gray-50">
                          <td class="px-6 py-4 whitespace-nowrap text-[#2c4454]">{{ formatTime24To12(t) }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-[#2c4454] text-right">{{ counts[t] || 0 }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-[#2c4454] text-right">{{ (remainingPerSlot[t] ?? (capacity - (counts[t] || 0))) }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-[#2c4454] text-right">{{ capacity }}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium ring-1" :class="occupiedTimes.includes(t) ? 'bg-red-100 text-red-700 ring-red-200' : 'bg-green-100 text-green-700 ring-green-200'">
                              <span class="capitalize">{{ occupiedTimes.includes(t) ? 'Full' : 'Available' }}</span>
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Pagination -->
                  <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm text-[#2c4454]">
                      <label>Rows per page</label>
                      <span class="font-medium">{{ props.pagination.per_page }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <button :disabled="props.pagination.current_page <= 1" @click="applyFilters(props.pagination.current_page - 1)" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50 disabled:opacity-50">
                        <ChevronLeft class="h-4 w-4" />
                        Prev
                      </button>
                      <div class="text-sm text-[#2c4454]">Page <span class="font-semibold">{{ props.pagination.current_page }}</span> of <span class="font-semibold">{{ props.pagination.last_page }}</span></div>
                      <button :disabled="props.pagination.current_page >= props.pagination.last_page" @click="applyFilters(props.pagination.current_page + 1)" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50 disabled:opacity-50">
                        Next
                        <ChevronRight class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Sidebar column -->
                <aside class="lg:col-span-3">
                  <div class="rounded-lg border border-[#2c4454]/20 p-4 sticky lg:top-6 space-y-3">
                    <div class="flex items-center justify-end">
                      <button @click="calendarFullscreen = true" class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-[#2c4454]/20 text-[#2c4454] text-xs rounded-md hover:bg-gray-50">
                        Full Screen
                      </button>
                    </div>
                    <OccupiedCalendar :busy-dates="props.calendar?.busyDates || []" :year="props.calendar?.year || new Date().getFullYear()" @select-date="onSelectDate" />
                  </div>
                </aside>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Fullscreen Calendar Overlay -->
    <div v-if="calendarFullscreen" class="fixed inset-0 z-50 bg-white">
      <div class="flex items-center justify-between px-4 py-3 border-b border-[#2c4454]/20">
        <div class="text-lg font-semibold text-[#2c4454]">Appointments Calendar – {{ props.calendar?.year || new Date().getFullYear() }}</div>
        <button @click="calendarFullscreen = false" class="px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">Close</button>
      </div>
      <div class="p-4 max-w-7xl mx-auto">
        <OccupiedCalendar :busy-dates="props.calendar?.busyDates || []" :year="props.calendar?.year || new Date().getFullYear()" @select-date="onSelectDate" />
      </div>
    </div>
  </AppLayout>
</template>
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');
.bp-theme { font-family: 'Space Grotesk', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
</style>