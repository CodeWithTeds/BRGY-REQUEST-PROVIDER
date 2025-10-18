<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, CheckCircle2, AlertTriangle, Clock } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { BreadcrumbItem } from '@/types';
import Toastify from 'toastify-js';

interface AppointmentItem {
  id: number;
  status: 'scheduled' | 'completed' | 'cancelled' | 'no_show' | string;
  remarks?: string | null;
  appointment_at: string | null; // ISO local to Asia/Manila from server
  type: string;
  appointable_type: string;
  appointable_id?: number | null;
  applicant_name?: string | null;
}

const props = defineProps<{ appointment: AppointmentItem }>();

const basePath = computed(() => `/admin/appointments`);
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Appointments', href: basePath.value },
];

function statusChip(s: string) {
  switch (s) {
    case 'scheduled': return 'bg-indigo-100 text-indigo-700 ring-indigo-200';
    case 'completed': return 'bg-green-100 text-green-700 ring-green-200';
    case 'cancelled': return 'bg-red-100 text-red-700 ring-red-200';
    case 'no_show': return 'bg-yellow-100 text-yellow-700 ring-yellow-200';
    default: return 'bg-gray-100 text-gray-700 ring-gray-200';
  }
}

function formatDate(iso?: string | null) {
  if (!iso) return '—';
  try {
    const d = new Date(iso);
    return d.toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
  } catch { return iso || '—'; }
}

function recordHref() {
  const id = props.appointment.appointable_id;
  const type = props.appointment.appointable_type;
  if (!id || !type) return '';
  switch (type) {
    case 'App\\Models\\BarangayPermit':
      return `/admin/business-permits/${id}`;
    case 'App\\Models\\BarangayClearance':
      return `/admin/barangay-clearances/${id}`;
    case 'App\\Models\\CertificateOfResidency':
      return `/admin/residency-certificates/${id}`;
    case 'App\\Models\\CertificateOfIndigency':
      return `/admin/indigency-certificates/${id}`;
    default:
      return '';
  }
}
const rescheduleForm = useForm({
  date: '',
  time: '',
  remarks: '',
});

const statusForm = useForm({ status: props.appointment.status });

function toast(msg: string, type: 'success' | 'error' | 'info' = 'info') {
  Toastify({ text: msg, duration: 2500, gravity: 'top', position: 'right', backgroundColor: type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#334155' }).showToast();
}

function submitReschedule() {
  rescheduleForm.post(`${basePath.value}/${props.appointment.id}/reschedule`, {
    preserveScroll: true,
    onSuccess: () => toast('Appointment rescheduled', 'success'),
  });
}

function submitStatus() {
  statusForm.post(`${basePath.value}/${props.appointment.id}/status`, {
    preserveScroll: true,
    onSuccess: () => toast('Appointment status updated', 'success'),
  });
}
</script>

<template>
  <Head title="Appointment" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link :href="basePath" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50">
            <ArrowLeft class="h-4 w-4" /> Back
          </Link>
          <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium ring-1" :class="statusChip(props.appointment.status)">
            <component :is="props.appointment.status === 'scheduled' ? Clock : props.appointment.status === 'completed' ? CheckCircle2 : AlertTriangle" class="h-3.5 w-3.5" />
            <span class="capitalize">{{ props.appointment.status.replace('_', ' ') }}</span>
          </div>
        </div>
      </div>
      <div class="mt-2 text-lg font-semibold text-[#2c4454]">Appointment Details</div>
    </template>

    <div class="bp-theme">
      <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Details -->
                <div class="md:col-span-2 space-y-4">
                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Summary</h3>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-[#2c4454]">
                      <p><span class="opacity-70">Appointment ID:</span> #{{ props.appointment.id }}</p>
                      <p><span class="opacity-70">Applicant:</span> {{ props.appointment.applicant_name || '—' }}</p>
                      <p><span class="opacity-70">Type:</span> {{ props.appointment.type }}</p>
                      <p><span class="opacity-70">Appointable ID:</span> {{ props.appointment.appointable_id || '—' }}</p>
                      <p><span class="opacity-70">Schedule:</span> {{ formatDate(props.appointment.appointment_at) }}</p>
                      <p v-if="props.appointment.appointable_id && recordHref()"><span class="opacity-70">Record:</span> <Link :href="recordHref()" class="hover:underline">Open {{ props.appointment.type }} #{{ props.appointment.appointable_id }}</Link></p>
                    </div>
                  </div>

                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Remarks</h3>
                    <p class="mt-2 text-sm text-[#2c4454] opacity-80">{{ props.appointment.remarks || '—' }}</p>
                  </div>
                </div>

                <!-- Actions -->
                <div class="space-y-4">
                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Update Status</h3>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                      <button class="px-3 py-2 rounded-md bg-indigo-600 text-white text-sm hover:opacity-90" @click="statusForm.status = 'scheduled'; submitStatus()">Mark Scheduled</button>
                      <button class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90" @click="statusForm.status = 'completed'; submitStatus()">Mark Completed</button>
                      <button class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:opacity-90" @click="statusForm.status = 'cancelled'; submitStatus()">Mark Cancelled</button>
                      <button class="px-3 py-2 rounded-md bg-yellow-600 text-white text-sm hover:opacity-90" @click="statusForm.status = 'no_show'; submitStatus()">Mark No-show</button>
                    </div>
                    <p v-if="statusForm.errors && statusForm.errors.status" class="mt-2 text-xs text-red-600">{{ statusForm.errors.status }}</p>
                  </div>

                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Reschedule</h3>
                    <div class="mt-3 space-y-3">
                      <div class="grid grid-cols-2 gap-2">
                        <div>
                          <label class="block text-xs text-[#2c4454] opacity-70">Date</label>
                          <input v-model="rescheduleForm.date" type="date" class="mt-1 w-full rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2" />
                        </div>
                        <div>
                          <label class="block text-xs text-[#2c4454] opacity-70">Time</label>
                          <input v-model="rescheduleForm.time" type="time" class="mt-1 w-full rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2" />
                        </div>
                      </div>
                      <div>
                        <label class="block text-xs text-[#2c4454] opacity-70">Remarks</label>
                        <textarea v-model="rescheduleForm.remarks" rows="3" class="mt-1 w-full rounded-md border border-[#2c4454]/20 text-sm text-[#2c4454] py-2 px-2"></textarea>
                      </div>
                      <button @click="submitReschedule" class="px-3 py-2 bg-[#2c4454] text-white rounded-md text-sm hover:opacity-90 inline-flex items-center gap-2">
                        <CalendarDays class="h-4 w-4" />
                        Reschedule
                      </button>
                      <p v-if="rescheduleForm.errors && (rescheduleForm.errors.date || rescheduleForm.errors.time)" class="text-xs text-red-600">
                        {{ rescheduleForm.errors.date || rescheduleForm.errors.time }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');
.bp-theme { font-family: 'Space Grotesk', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
</style>