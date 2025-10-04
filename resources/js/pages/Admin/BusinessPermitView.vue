<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, AlertTriangle, Clock, FileText, FileCheck, FileWarning } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
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

interface Permit {
  id: number;
  full_name: string;
  application_date: string | null;
  status: 'approved' | 'pending' | 'rejected' | string;
  created_at: string | null;
  updated_at: string | null;
  gender?: string | null;
  citizenship?: string | null;
  contact_number?: string | null;
  remarks?: string | null;
  applicant_profile?: ApplicantProfileItem;
  addresses?: AddressItem[];
  supporting_documents?: DocumentItem[];
  user?: UserItem;
}

const props = defineProps<{ permit: Permit }>();

// Actions form & state
const showEdit = ref(false);
const form = useForm({
  status: props.permit.status,
  remarks: props.permit.remarks || '',
});

onMounted(() => {
  notify(`Current status: ${props.permit.status}`, 'info');
});

function notify(message: string, variant: 'success' | 'error' | 'info' = 'info') {
  const bg = variant === 'success' ? 'linear-gradient(to right, #00b09b, #96c93d)' : variant === 'error' ? 'linear-gradient(to right, #ef4444, #b91c1c)' : 'linear-gradient(to right, #3b82f6, #2563eb)';
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
  form.post(`/admin/business-permits/${props.permit.id}/status`, {
    onSuccess: () => notify(`Business permit status updated to ${status}.`, 'success'),
    onError: () => notify('Failed to update business permit status.', 'error'),
  });
}

function toggleEdit() {
  showEdit.value = !showEdit.value;
}

function saveDetails() {
  form.post(`/admin/business-permits/${props.permit.id}/status`, {
    onSuccess: () => {
      notify('Business permit details saved.', 'success');
      showEdit.value = false;
    },
    onError: () => notify('Failed to save business permit details.', 'error'),
  });
}

const statusChip = (s: string) => {
  switch (s) {
    case 'approved':
      return 'bg-green-100 text-green-700 ring-green-200';
    case 'pending':
      return 'bg-yellow-100 text-yellow-700 ring-yellow-200';
    case 'processing':
      return 'bg-blue-100 text-blue-700 ring-blue-200';
    case 'rejected':
      return 'bg-red-100 text-red-700 ring-red-200';
    default:
      return 'bg-gray-100 text-gray-700 ring-gray-200';
  }
};
</script>

<template>
  <Head :title="`Permit #${props.permit.id}`" />
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link :href="'/admin/business-permits'" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50">
            <ArrowLeft class="h-4 w-4" />
            Back to list
          </Link>
          <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium ring-1" :class="statusChip(props.permit.status)">
            <component :is="props.permit.status === 'approved' ? CheckCircle2 : (props.permit.status === 'pending' || props.permit.status === 'processing') ? Clock : AlertTriangle" class="h-3.5 w-3.5" />
            <span class="capitalize">{{ props.permit.status }}</span>
          </div>
        </div>
        <div class="text-sm text-[#2c4454]">Permit ID: <span class="font-semibold">#{{ props.permit.id }}</span></div>
      </div>
    </template>

    <div class="bp-theme">
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 space-y-6">
              <!-- Applicant summary -->
              <div class="flex flex-wrap items-start justify-between gap-6">
                <div>
                  <h2 class="text-xl font-semibold text-[#2c4454]">{{ props.permit.full_name }}</h2>
                  <p class="text-sm text-[#2c4454] opacity-80">Contact: {{ props.permit.contact_number || '—' }}</p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-[#2c4454] opacity-70">Application Date</p>
                  <p class="text-sm font-medium text-[#2c4454]">{{ props.permit.application_date || '—' }}</p>
                  <p class="mt-2 text-xs text-[#2c4454] opacity-70">Last update</p>
                  <p class="text-sm font-medium text-[#2c4454]">{{ props.permit.updated_at || '—' }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Details -->
                <div class="md:col-span-2 space-y-4">
                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Applicant Details</h3>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-[#2c4454]">
                      <p><span class="opacity-70">Gender:</span> {{ props.permit.gender || '—' }}</p>
                      <p><span class="opacity-70">Citizenship:</span> {{ props.permit.citizenship || '—' }}</p>
                      <p><span class="opacity-70">Civil Status:</span> {{ props.permit.applicant_profile?.civil_status || '—' }}</p>
                      <p><span class="opacity-70">Date of Birth:</span> {{ props.permit.applicant_profile?.date_of_birth || '—' }}</p>
                      <p><span class="opacity-70">Place of Birth:</span> {{ props.permit.applicant_profile?.place_of_birth || '—' }}</p>
                      <p><span class="opacity-70">Account:</span> {{ props.permit.user?.name || '—' }} <span v-if="props.permit.user?.email" class="opacity-60">({{ props.permit.user?.email }})</span></p>
                    </div>
                  </div>

                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Addresses</h3>
                    <div class="mt-3 space-y-3" v-if="props.permit.addresses && props.permit.addresses.length">
                      <div v-for="addr in props.permit.addresses" :key="addr.id" class="rounded border border-[#2c4454]/10 p-3">
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
                    <div class="mt-3 space-y-2" v-if="props.permit.supporting_documents && props.permit.supporting_documents.length">
                      <div v-for="doc in props.permit.supporting_documents" :key="doc.id" class="flex items-center justify-between rounded border border-[#2c4454]/10 p-3">
                        <div class="flex items-center gap-3">
                          <component :is="doc.verified ? FileCheck : FileText" class="h-4 w-4" :class="doc.verified ? 'text-green-600' : 'text-[#2c4454]'" />
                          <div>
                            <div class="text-sm text-[#2c4454]">{{ doc.document_type }}</div>
                            <div class="text-xs text-[#2c4454] opacity-70">{{ doc.file_path || '—' }}</div>
                          </div>
                        </div>
                        <div class="flex items-center gap-2">
                          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium" :class="doc.verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                            {{ doc.verified ? 'Verified' : 'Pending verification' }}
                          </span>
                          <a v-if="doc.file_path" :href="`/admin/business-permits/${props.permit.id}/documents/${doc.id}`" target="_blank" rel="noopener" class="text-xs text-[#2c4454] hover:underline">View</a>
                        </div>
                      </div>
                    </div>
                    <p v-else class="mt-2 text-sm text-[#2c4454] opacity-80">No supporting documents uploaded.</p>
                  </div>

                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Remarks</h3>
                    <p class="mt-2 text-sm text-[#2c4454] opacity-80">{{ props.permit.remarks || '—' }}</p>
                  </div>
                </div>

                <!-- Actions / Meta -->
                <div class="space-y-4">
                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Actions</h3>
                    <div class="mt-3">
                      <template v-if="props.permit.status === 'pending'">
                        <div class="grid grid-cols-2 gap-2">
                          <button class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90" @click="updateStatus('processing')">Accept</button>
                          <button class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:opacity-90" @click="updateStatus('rejected')">Reject</button>
                        </div>
                      </template>
                      <template v-else-if="props.permit.status === 'processing'">
                        <button class="px-3 py-2 rounded-md bg-green-600 text-white text-sm hover:opacity-90 w-full" @click="updateStatus('approved')">Mark as Approved</button>
                      </template>
                      <template v-else-if="props.permit.status === 'approved'">
                        <p class="text-sm text-[#2c4454] opacity-80">No further actions. This permit is already approved.</p>
                      </template>
                      <template v-else-if="props.permit.status === 'rejected'">
                        <p class="text-sm text-[#2c4454] opacity-80">This permit was rejected.</p>
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
                      <p v-if="form.errors && form.errors.remarks" class="text-xs text-red-600">{{ form.errors.remarks }}</p>
                    </div>
                  </div>
                  <div class="rounded-lg border border-[#2c4454]/20 p-4">
                    <h3 class="text-sm font-semibold text-[#2c4454]">Meta</h3>
                    <p class="mt-2 text-xs text-[#2c4454] opacity-70">Created: {{ props.permit.created_at || '—' }}</p>
                    <p class="text-xs text-[#2c4454] opacity-70">Updated: {{ props.permit.updated_at || '—' }}</p>
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
.bp-theme {
  font-family: 'Space Grotesk', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
}
</style>
