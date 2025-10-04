<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { CheckCircle2, XCircle, Info, Calendar, ClipboardList, FileCheck } from 'lucide-vue-next';
import Toastify from 'toastify-js';
import { onMounted, computed } from 'vue';
import StatusTracker from '@/components/StatusTracker.vue';

const props = defineProps<{ permit: { id?: number; status: string; remarks?: string; application_date?: string } }>();

const isApproved = props.permit.status === 'approved';
const isRejected = props.permit.status === 'rejected';

const referenceNo = computed(() => props.permit.id ? `BP-${props.permit.id}` : '—');

function notify() {
  const text = isApproved
    ? 'Your business permit has been approved.'
    : 'Your business permit has been rejected.';
  Toastify({
    text,
    duration: 4000,
    gravity: 'top',
    position: 'right',
    backgroundColor: isApproved ? '#16a34a' : '#dc2626',
    close: true,
  }).showToast();
}

function handleScheduleClick() {
  Toastify({
    text: 'Scheduling feature will be available soon. Please proceed to your barangay office to set an appointment.',
    duration: 4500,
    gravity: 'top',
    position: 'right',
    backgroundColor: '#2563eb',
    close: true,
  }).showToast();
}

onMounted(() => {
  notify();
});
</script>

<template>
  <AppLayout title="Barangay Permit Status">
    <!-- Sticky top tracker -->
    <div class="sticky top-16 z-30 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60">
      <div class="max-w-6xl mx-auto px-4 md:px-6 py-3 flex items-center gap-4">
     
        <StatusTracker :current-status="props.permit.status" :noMargin="true" class="flex-1" />
      </div>
    </div>

    <div class="container mx-auto py-8 px-4 md:px-6">
      <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto mb-12 items-start min-h-[calc(100vh-150px)]">
        <!-- Left: Status message and guidance -->
        <div>
          <div class="flex items-center gap-4" :class="isApproved ? 'text-green-600' : 'text-red-600'">
            <component :is="isApproved ? CheckCircle2 : XCircle" class="size-10" />
            <h1 class="text-3xl font-bold">{{ isApproved ? 'Application Approved' : 'Application Rejected' }}</h1>
          </div>

          <p class="mt-4 text-[#2C4854]" v-if="isApproved">
            Congratulations! Your business permit application has been approved. You will be able to download your permit from your dashboard soon.
          </p>
          <p class="mt-4 text-[#2C4854]" v-else>
            Unfortunately, your application has been rejected. Please review the remarks below for more information.
          </p>

          <!-- Permit details -->
          <div class="mt-6 rounded border border-[#2C4854]/20 bg-white p-4">
            <div class="text-[#2C4854] font-semibold">Permit Details</div>
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-[#2C4854]">
              <p><span class="opacity-70">Reference No.:</span> <span class="font-medium">{{ referenceNo }}</span></p>
              <p v-if="props.permit.id"><span class="opacity-70">Permit ID:</span> <span class="font-medium">#{{ props.permit.id }}</span></p>
              <p v-if="props.permit.application_date"><span class="opacity-70">Application Date:</span> <span class="font-medium">{{ props.permit.application_date }}</span></p>
            </div>
          </div>

          <!-- Next steps for approved -->
          <div v-if="isApproved" class="mt-6 rounded border border-[#2C4854]/20 bg-[#2C4854]/5 p-4">
            <div class="flex items-center gap-2 text-[#2C4854]">
              <Calendar class="size-5" />
              <h2 class="font-semibold">Schedule Pickup</h2>
            </div>
            <p class="mt-2 text-[#2C4854]">You may now schedule a time to pick up your printed business permit at your barangay office.</p>
            <ul class="mt-2 pl-5 space-y-2 text-[#2C4854]/80 list-disc">
              <li>Bring a valid government-issued ID and your reference number shown above.</li>
              <li>Office hours are typically Monday–Friday, 8:00 AM–5:00 PM. Confirm availability with your barangay office.</li>
              <li>Processing and printing may take up to 2–3 business days depending on queue.</li>
            </ul>
            <div class="mt-4">
              <button type="button" @click="handleScheduleClick" class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                Schedule Pickup
              </button>
            </div>
          </div>

          <!-- Requirements -->
          <div v-if="isApproved" class="mt-6 rounded border border-[#2C4854]/20 bg-white p-4">
            <div class="flex items-center gap-2 text-[#2C4854]">
              <ClipboardList class="size-5" />
              <h2 class="font-semibold">Requirements</h2>
            </div>
            <ul class="mt-2 pl-5 space-y-2 text-[#2C4854]/80 list-disc">
              <li>Valid government-issued ID (e.g., PhilSys ID, Driver’s License, Passport).</li>
              <li>Proof of payment, if applicable.</li>
              <li>Application reference number or Permit ID.</li>
              <li>Any additional documents requested in the remarks.</li>
            </ul>
            <p class="mt-3 text-xs text-[#2C4854]/70">Note: Requirements may vary by barangay. Please follow any additional instructions provided by your local office.</p>
          </div>

          <!-- Remarks / Description -->
          <div v-if="props.permit.remarks" class="mt-6 rounded border border-[#2C4854]/20 bg-[#2C4854]/5 p-4">
            <div class="flex items-center gap-2 text-[#2C4854]">
              <Info class="size-5" />
              <h2 class="font-semibold">Remarks & Description</h2>
            </div>
            <p class="mt-2 text-[#2C4854]">{{ props.permit.remarks }}</p>
          </div>

          <!-- Guidance for rejected -->
          <div v-if="isRejected" class="mt-6 rounded border border-[#2C4854]/20 bg-white p-4">
            <div class="flex items-center gap-2 text-[#2C4854]">
              <FileCheck class="size-5" />
              <h2 class="font-semibold">How to proceed</h2>
            </div>
            <ul class="mt-2 pl-5 space-y-2 text-[#2C4854]/80 list-disc">
              <li>Review the remarks and correct any missing or invalid information.</li>
              <li>Prepare the required documents and re-submit your application.</li>
              <li>If you need help, contact your barangay office for assistance.</li>
            </ul>
          </div>

          <div class="mt-8">
            <a :href="route('resident.dashboard')" class="inline-flex items-center rounded bg-main px-4 py-2 text-white hover:bg-main/90">Back to Dashboard</a>
          </div>
        </div>

        <!-- Right: Visual guidance (kept simple since sticky header already shows image + tracker) -->
        <div class="flex flex-col items-center justify-start md:sticky md:top-24">
          <img src="/images/thankyou.png" alt="Status" class="w-full max-w-md" />

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Ensure sticky header overlays content cleanly */
</style>