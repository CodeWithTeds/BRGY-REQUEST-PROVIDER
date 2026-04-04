<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, AlertTriangle, Clock, FileText, FileCheck } from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';
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

const basePath = computed(() => `/${props.routeGroup ?? 'admin'}/residency-certificates`);

function goBack() {
  history.back();
}

// Actions form & state (mirror BusinessPermitView)
const showEdit = ref(false);
const form = useForm({
  status: props.certificate.status,
  remarks: props.certificate.remarks || '',
});

onMounted(() => {
  notify(`Current status: ${props.certificate.status}`, 'info');
});

function notify(message: string, variant: 'success' | 'error' | 'info' = 'info') {
  const bg = variant === 'success'
    ? 'linear-gradient(to right, #00b09b, #96c93d)'
    : variant === 'error'
      ? 'linear-gradient(to right, #ef4444, #b91c1c)'
      : 'linear-gradient(to right, #3b82f6, #2563eb)';
  Toastify({
    text: message,
    duration: 3000,
    close: true,
    gravity: 'top',
    position: 'right',
    backgroundColor: bg,
    stopOnFocus: true,
  }).showToast();
}

function updateStatus(status: string) {
  form.status = status;
  form.post(`${basePath.value}/${props.certificate.id}/status`, {
    onSuccess: () => notify(`Residency certificate status updated to ${status}.`, 'success'),
    onError: () => notify('Failed to update residency certificate status.', 'error'),
  });
}

function toggleEdit() {
  showEdit.value = !showEdit.value;
}

function saveDetails() {
  form.post(`${basePath.value}/${props.certificate.id}/status`, {
    onSuccess: () => {
      notify('Residency certificate details saved.', 'success');
      showEdit.value = false;
    },
    onError: () => notify('Failed to save residency certificate details.', 'error'),
  });
}

const statusIcon = (s: string) => {
  switch (s) {
    case 'approved': return CheckCircle2;
    case 'pending': return Clock;
    case 'processing': return Clock;
    case 'rejected': return AlertTriangle;
    default: return Clock;
  }
}

const statusLabel = (s: string) => {
  switch (s) {
    case 'approved': return 'Approved';
    case 'pending': return 'Pending';
    case 'processing': return 'Processing';
    case 'rejected': return 'Rejected';
    default: return s;
  }
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

const breadcrumbs = computed(() => [
  { title: props.routeGroup === 'staff' ? 'Staff' : 'Admin', href: props.routeGroup === 'staff' ? '/staff/dashboard' : '/admin/dashboard' },
  { title: 'Residency Certificates', href: basePath.value },
  { title: 'Details', href: `${basePath.value}/${props.certificate.id}` },
]);
</script>

<template>
  <Head :title="`Residency Certificate #${props.certificate.id}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <button aria-label="Go back" @click="goBack" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white p-2 text-sm text-[#2c4454] hover:bg-gray-50">
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

    <div class="p-6">
      <div class="flex items-center mb-4">
        <Link :href="basePath" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50">
          <ArrowLeft class="h-4 w-4" />
          Back to list
        </Link>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
          <!-- Applicant summary -->
          <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
              <h2 class="text-xl font-semibold text-[#2c4454]">{{ props.certificate.full_name }}</h2>
              <p class="text-sm text-[#2c4454] opacity-80">Contact: {{ props.certificate.contact_number || '—' }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs text-[#2c4454] opacity-70">Application Date</p>
              <p class="text-sm font-medium text-[#2c4454]">{{ props.certificate.application_date || '—' }}</p>
              <p class="mt-2 text-xs text-[#2c4454] opacity-70">Last update</p>
              <p class="text-sm font-medium text-[#2c4454]">{{ props.certificate.updated_at || '—' }}</p>
            </div>
          </div>

          <div class="rounded-lg border border-[#2c4454]/20 p-4">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-semibold text-[#2c4454]">Application</h2>
              <div class="inline-flex items-center gap-2">
                <component :is="statusIcon(props.certificate.status)" class="h-4 w-4" :class="{
                  'text-green-600': props.certificate.status === 'approved',
                  'text-yellow-600': props.certificate.status === 'pending' || props.certificate.status === 'processing',
                  'text-red-600': props.certificate.status === 'rejected',
                }" />
                <span class="text-xs text-[#2c4454]">{{ statusLabel(props.certificate.status) }}</span>
              </div>
            </div>
            <div class="mt-3 text-sm text-[#2c4454]">
              <p><span class="opacity-70">Applicant:</span> {{ props.certificate.full_name || '—' }}</p>
              <p><span class="opacity-70">Applied on:</span> {{ props.certificate.application_date || '—' }}</p>
              <p><span class="opacity-70">Contact:</span> {{ props.certificate.contact_number || '—' }}</p>
              <p><span class="opacity-70">Remarks:</span> {{ props.certificate.remarks || '—' }}</p>
            </div>
          </div>

          <div class="rounded-lg border border-[#2c4454]/20 p-4">
            <h3 class="text-sm font-semibold text-[#2c4454]">Applicant Profile</h3>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-[#2c4454]">
              <p><span class="opacity-70">First Name:</span> {{ props.certificate.applicant_profile?.first_name || '—' }}</p>
              <p><span class="opacity-70">Middle Name:</span> {{ props.certificate.applicant_profile?.middle_name || '—' }}</p>
              <p><span class="opacity-70">Last Name:</span> {{ props.certificate.applicant_profile?.last_name || '—' }}</p>
              <p><span class="opacity-70">Suffix:</span> {{ props.certificate.applicant_profile?.suffix || '—' }}</p>
              <p><span class="opacity-70">Date of Birth:</span> {{ props.certificate.applicant_profile?.date_of_birth || '—' }}</p>
              <p><span class="opacity-70">Place of Birth:</span> {{ props.certificate.applicant_profile?.place_of_birth || '—' }}</p>
              <p><span class="opacity-70">Civil Status:</span> {{ props.certificate.applicant_profile?.civil_status || '—' }}</p>
            </div>
          </div>

          <div class="rounded-lg border border-[#2c4454]/20 p-4">
            <h3 class="text-sm font-semibold text-[#2c4454]">Addresses</h3>
            <div class="mt-3 space-y-3" v-if="props.certificate.addresses && props.certificate.addresses.length">
              <div v-for="addr in props.certificate.addresses" :key="addr.id" class="rounded border border-[#2c4454]/10 p-3">
                <div class="text-sm text-[#2c4454]">
                  <span class="inline-block px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs mr-2">{{ addr.type }}</span>
                  <span>{{ addr.line || '—' }}</span>
                </div>
                <div class="mt-1 text-xs text-[#2c4454] opacity-80">
                  <span v-if="addr.barangay">Barangay: {{ addr.barangay }}</span>
                  <span v-if="addr.city"> • City: {{ addr.city }}</span>
                  <span v-if="addr.province"> • Province: {{ addr.province }}</span>
                  <span v-if="addr.region"> • Region: {{ addr.region }}</span>
                  <span v-if="addr.zip_code"> • ZIP: {{ addr.zip_code }}</span>
                </div>
              </div>
            </div>
            <p v-else class="mt-2 text-sm text-[#2c4454] opacity-80">No addresses on file.</p>
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
                  <a
                    :href="`${basePath}/${props.certificate.id}/documents/${doc.id}`"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-1 text-xs text-[#2c4454] hover:bg-gray-50"
                  >
                    View
                  </a>
                </div>
              </div>
            </div>
            <p v-else class="mt-2 text-sm text-[#2c4454] opacity-80">No supporting documents uploaded.</p>
          </div>

          <div class="rounded-lg border border-[#2c4454]/20 p-4">
            <h3 class="text-sm font-semibold text-[#2c4454]">Remarks</h3>
            <p class="mt-2 text-sm text-[#2c4454] opacity-80">{{ props.certificate.remarks || '—' }}</p>
          </div>
        </div>

        <!-- Actions / Meta -->
        <div class="space-y-6">
          <div class="rounded-lg border border-[#2c4454]/20 p-4">
            <h3 class="text-sm font-semibold text-[#2c4454]">Actions</h3>
            <div class="mt-3">
              <template v-if="props.certificate.status === 'pending'">
                <div class="grid grid-cols-2 gap-2">
                  <button class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90" @click="updateStatus('processing')">Accept</button>
                  <button class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:opacity-90" @click="updateStatus('rejected')">Reject</button>
                </div>
              </template>
              <template v-else-if="props.certificate.status === 'processing'">
                <button v-if="props.canApprove" class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90 w-full" @click="updateStatus('approved')">Mark as Approved</button>
                <button v-else-if="props.routeGroup === 'staff'" class="mt-2 px-3 py-2 rounded-md bg-indigo-600 text-white text-sm hover:opacity-90 w-full" @click="updateStatus('pre-approved')">Mark as Pre-Approved</button>
              </template>
              <template v-else-if="props.certificate.status === 'approved'">
                <p class="text-sm text-[#2c4454] opacity-80">No further actions. This certificate is already approved.</p>
              </template>
              <template v-else-if="props.certificate.status === 'pre-approved'">
                <div v-if="props.canApprove" class="grid grid-cols-2 gap-2">
                  <button class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90" @click="updateStatus('approved')">Mark as Approved</button>
                  <button class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:opacity-90" @click="updateStatus('rejected')">Reject</button>
                </div>
                <p v-else class="text-sm text-[#2c4454] opacity-80">This certificate is pre-approved by staff, awaiting admin approval.</p>
              </template>
              <template v-else-if="props.certificate.status === 'rejected'">
                <p class="text-sm text-[#2c4454] opacity-80">This certificate was rejected.</p>
              </template>
              <button class="mt-3 px-3 py-2 rounded-md bg-[#2c4454] text-white text-sm hover:opacity-90" @click="toggleEdit">Edit details</button>
            </div>
            <div v-if="showEdit" class="mt-3 space-y-2">
              <label class="text-xs font-medium text-[#2c4454]">Remarks</label>
              <textarea v-model="form.remarks" rows="3" class="w-full rounded-md border border-[#2c4454]/20 p-2 text-sm text-[#2c4454] focus:outline-none focus:ring-2 focus:ring-[#2c4454]/30"></textarea>
              <div class="flex items-center gap-2">
                <button class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90" @click="saveDetails">Save</button>
                <button class="px-3 py-2 rounded-md bg-gray-200 text-[#2c4454] text-sm hover:bg-gray-300" @click="toggleEdit">Cancel</button>
              </div>
              <p v-if="form.errors && (form.errors as any).remarks" class="text-xs text-red-600">{{ (form.errors as any).remarks }}</p>
            </div>
          </div>
          <div class="rounded-lg border border-[#2c4454]/20 p-4">
            <h3 class="text-sm font-semibold text-[#2c4454]">Account</h3>
            <div class="mt-3 text-sm text-[#2c4454]">
              <p><span class="opacity-70">Name:</span> {{ props.certificate.user?.name || '—' }}</p>
              <p><span class="opacity-70">Email:</span> {{ props.certificate.user?.email || '—' }}</p>
              <p><span class="opacity-70">Updated:</span> {{ props.certificate.updated_at || '—' }}</p>
            </div>
          </div>
          <div class="rounded-lg border border-[#2c4454]/20 p-4">
            <h3 class="text-sm font-semibold text-[#2c4454]">Meta</h3>
            <p class="mt-2 text-xs text-[#2c4454] opacity-70">Created: {{ props.certificate.created_at || '—' }}</p>
            <p class="text-xs text-[#2c4454] opacity-70">Updated: {{ props.certificate.updated_at || '—' }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>