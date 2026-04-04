<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, AlertTriangle, Clock, FileText, FileCheck } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import Toastify from 'toastify-js';

interface AddressItem {
  id: number;
  type: 'present' | 'permanent' | string;
  line: string | null;
  barangay?: string | null;
  city?: string | null;
  province?: string | null;
  region?: string | null;
  zip_code?: string | null;
}

interface DocumentItem {
  id: number;
  document_type: string;
  file_path?: string | null;
  verified: boolean;
}

interface ApplicantProfileItem {
  first_name?: string | null;
  middle_name?: string | null;
  last_name?: string | null;
  suffix?: string | null;
  date_of_birth?: string | null;
  place_of_birth?: string | null;
  civil_status?: string | null;
}

interface UserItem {
  id?: number;
  name?: string | null;
  email?: string | null;
}

interface Certificate {
  id: number;
  full_name: string;
  application_date: string | null;
  status: 'approved' | 'pending' | 'rejected' | 'processing' | string;
  created_at: string | null;
  updated_at: string | null;
  contact_number?: string | null;
  remarks?: string | null;
  applicant_profile?: ApplicantProfileItem;
  addresses?: AddressItem[];
  user?: UserItem;
  supporting_documents?: DocumentItem[];
}

const props = defineProps<{ certificate: Certificate; routeGroup?: string; canApprove?: boolean }>();

const basePath = computed(() => `/${props.routeGroup ?? 'admin'}/indigency-certificates`);

// Initialize form with current values so changes persist
const initialStatus = ref(props.certificate.status);
const initialRemarks = ref(props.certificate.remarks ?? '');

const form = useForm({
  status: initialStatus.value,
  remarks: initialRemarks.value,
});

const hasChanges = computed(() => form.status !== initialStatus.value || (form.remarks ?? '') !== initialRemarks.value);

function statusIcon(s: string) {
  switch (s) {
    case 'approved': return CheckCircle2
    case 'rejected': return AlertTriangle
    case 'processing': return Clock
    default: return Clock
  }
}

function resetForm() {
  form.status = initialStatus.value
  form.remarks = initialRemarks.value
}

function submitStatus() {
  form.post(`${basePath.value}/${props.certificate.id}/status`, {
    preserveScroll: true,
    onSuccess: () => {
      initialStatus.value = form.status;
      initialRemarks.value = form.remarks ?? '';
      router.get(`${basePath.value}/${props.certificate.id}`, {}, { preserveState: true, replace: true });
    },
  })
}

function notify(message: string, variant: 'success' | 'error' | 'info' = 'info') {
  const bg = variant === 'success'
    ? 'linear-gradient(to right, #00b09b, #96c93d)'
    : variant === 'error'
      ? 'linear-gradient(to right, #ef4444, #b91c1c)'
      : 'linear-gradient(to right, #3b82f6, #2563eb)';
  Toastify({ text: message, duration: 3000, close: true, gravity: 'top', position: 'right', backgroundColor: bg, stopOnFocus: true }).showToast();
}

onMounted(() => {
  notify(`Current status: ${props.certificate.status}`, 'info');
});

function goBack() {
  window.history.back();
}

function updateStatus(status: string) {
  form.status = status;
  form.post(`${basePath.value}/${props.certificate.id}/status`, {
    preserveScroll: true,
    onSuccess: () => {
      notify(`Certificate status updated to ${status}.`, 'success');
      router.get(`${basePath.value}/${props.certificate.id}`, {}, { preserveState: true, replace: true });
    },
    onError: () => notify('Failed to update certificate status.', 'error'),
  });
}

const showEdit = ref(false);
function toggleEdit() { showEdit.value = !showEdit.value; }
function saveDetails() {
  form.post(`${basePath.value}/${props.certificate.id}/status`, {
    preserveScroll: true,
    onSuccess: () => { notify('Certificate details saved.', 'success'); showEdit.value = false; },
    onError: () => notify('Failed to save certificate details.', 'error'),
  });
}

const statusChip = (s: string) => {
  switch (s) {
    case 'approved': return 'bg-green-100 text-green-700 ring-green-200';
    case 'pending': return 'bg-yellow-100 text-yellow-700 ring-yellow-200';
    case 'processing': return 'bg-blue-100 text-blue-700 ring-blue-200';
    case 'pre-approved': return 'bg-indigo-100 text-indigo-700 ring-indigo-200';
    case 'rejected': return 'bg-red-100 text-red-700 ring-red-200';
    default: return 'bg-gray-100 text-gray-700 ring-gray-200';
  }
};
</script>

<template>
  <Head :title="`Indigency #${props.certificate.id}`" />
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <button @click="goBack" aria-label="Go back" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white p-2 text-sm text-[#2c4454] hover:bg-gray-50">
            <ArrowLeft class="h-4 w-4" />
          </button>
          <Link :href="basePath" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50">
            <ArrowLeft class="h-4 w-4" />
            Back to list
          </Link>
          <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium ring-1" :class="statusChip(props.certificate.status)">
            <component :is="props.certificate.status === 'approved' ? CheckCircle2 : (props.certificate.status === 'pending' || props.certificate.status === 'processing' || props.certificate.status === 'pre-approved') ? Clock : AlertTriangle" class="h-3.5 w-3.5" />
            <span class="capitalize">{{ props.certificate.status }}</span>
          </div>
        </div>
        <div class="text-sm text-[#2c4454]">Certificate ID: <span class="font-semibold">#{{ props.certificate.id }}</span></div>
      </div>
    </template>
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="max-w-6xl mx-auto space-y-6">
        <!-- Two-column layout like BusinessPermitView -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left: Details -->
          <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-[#2c4454]/20 p-4">
              <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-[#2c4454]">Applicant Details</h2>
                <div class="inline-flex items-center gap-2">
                  <component :is="statusIcon(props.certificate.status)" class="h-4 w-4" :class="{
                    'text-green-600': props.certificate.status === 'approved',
                    'text-red-600': props.certificate.status === 'rejected',
                    'text-yellow-600': props.certificate.status === 'processing',
                    'text-[#2c4454]': props.certificate.status === 'pending',
                  }" />
                  <span class="text-xs text-[#2c4454] opacity-80">Updated: {{ props.certificate.updated_at || '—' }}</span>
                </div>
              </div>
              <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-[#2c4454]">
                <div>
                  <div class="opacity-70">Applicant</div>
                  <div class="font-medium">{{ props.certificate.full_name || '—' }}</div>
                </div>
                <div>
                  <div class="opacity-70">Application Date</div>
                  <div class="font-medium">{{ props.certificate.application_date || '—' }}</div>
                </div>
                <div>
                  <div class="opacity-70">Contact</div>
                  <div class="font-medium">{{ props.certificate.contact_number || '—' }}</div>
                </div>
              </div>
            </div>

            <div class="rounded-lg border border-[#2c4454]/20 p-4">
              <h3 class="text-sm font-semibold text-[#2c4454]">Addresses</h3>
              <div class="mt-3 space-y-2" v-if="props.certificate.addresses && props.certificate.addresses.length">
                <div v-for="addr in props.certificate.addresses" :key="addr.id" class="rounded border border-[#2c4454]/10 p-3">
                  <div class="text-xs text-[#2c4454] opacity-70">{{ addr.type }}</div>
                  <div class="text-sm font-medium text-[#2c4454]">{{ addr.line || '—' }}</div>
                  <div class="text-xs text-[#2c4454] opacity-70">{{ [addr.barangay, addr.city, addr.province, addr.region].filter(Boolean).join(' • ') }}</div>
                </div>
              </div>
              <p v-else class="mt-2 text-sm text-[#2c4454] opacity-80">No address information.</p>
            </div>

            <div class="rounded-lg border border-[#2c4454]/20 p-4">
              <h3 class="text-sm font-semibold text-[#2c4454]">Supporting Documents</h3>
              <div class="mt-3 space-y-2" v-if="props.certificate.supporting_documents && props.certificate.supporting_documents.length">
                <div v-for="doc in props.certificate.supporting_documents" :key="doc.id" class="flex items-center justify-between rounded border border-[#2c4454]/10 p-3">
                  <div class="flex items-center gap-3">
                    <component :is="doc.verified ? FileCheck : FileText" class="h-4 w-4" :class="doc.verified ? 'text-green-600' : 'text-[#2c4454]'" />
                    <div>
                      <div class="text-sm text-[#2c4454]">{{ doc.document_type }}</div>
                      <div class="text-xs text-[#2c4454] opacity-70">{{ doc.file_path || '—' }}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <a v-if="doc.file_path" :href="`${basePath}/${props.certificate.id}/documents/${doc.id}`" target="_blank" rel="noopener" class="text-xs text-[#2c4454] hover:underline">View</a>
                  </div>
                </div>
              </div>
              <p v-else class="mt-2 text-sm text-[#2c4454] opacity-80">No supporting documents uploaded.</p>
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="space-y-6">
            <div class="rounded-lg border border-[#2c4454]/20 p-4">
              <h3 class="text-sm font-semibold text-[#2c4454]">Actions</h3>
              <div class="mt-3">
                <template v-if="props.certificate.status === 'pending'">
                  <div class="grid grid-cols-2 gap-2">
                    <button class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm hover:opacity-90" @click="updateStatus('processing')">Accept</button>
                    <button class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:opacity-90" @click="updateStatus('rejected')">Reject</button>
                  </div>
                </template>
                <template v-else-if="props.certificate.status === 'processing'">
                  <div class="grid grid-cols-2 gap-2">
                    <button v-if="props.canApprove" class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90" @click="updateStatus('approved')">Approve</button>
                    <button v-else-if="props.routeGroup === 'staff'" class="px-3 py-2 rounded-md bg-indigo-600 text-white text-sm hover:opacity-90" @click="updateStatus('pre-approved')">Pre-Approve</button>
                    <button class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:opacity-90" @click="updateStatus('rejected')">Reject</button>
                  </div>
                </template>
                <template v-else-if="props.certificate.status === 'pre-approved'">
                  <div v-if="props.canApprove" class="grid grid-cols-2 gap-2">
                    <button class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90" @click="updateStatus('approved')">Approve</button>
                    <button class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:opacity-90" @click="updateStatus('rejected')">Reject</button>
                  </div>
                  <p v-else class="text-sm text-[#2c4454] opacity-80">No actions available for this status.</p>
                </template>
                <template v-else>
                  <p class="text-sm text-[#2c4454] opacity-80">No actions available for this status.</p>
                </template>
              </div>
            </div>

            <div class="rounded-lg border border-[#2c4454]/20 p-4">
              <h3 class="text-sm font-semibold text-[#2c4454]">Edit Details</h3>
              <div class="mt-3 space-y-2">
                <button class="px-3 py-2 rounded-md bg-white border border-[#2c4454]/20 text-[#2c4454] text-sm hover:bg-gray-50" @click="toggleEdit">{{ showEdit ? 'Cancel' : 'Edit' }}</button>
                <div v-if="showEdit" class="space-y-2">
                  <label class="block text-xs text-[#2c4454]">Remarks</label>
                  <textarea v-model="form.remarks" rows="3" class="w-full rounded-md border border-[#2c4454]/20 p-2 text-sm text-[#2c4454] focus:outline-none focus:ring-2 focus:ring-[#2c4454]/30"></textarea>
                  <button class="px-3 py-2 rounded-md bg-[#2c4454] text-white text-sm hover:opacity-90" @click="saveDetails">Save</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>