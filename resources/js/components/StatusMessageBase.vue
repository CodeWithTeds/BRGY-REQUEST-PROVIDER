<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import StatusTracker from '@/components/StatusTracker.vue'
import { CheckCircle2, XCircle } from 'lucide-vue-next'
import Toastify from 'toastify-js'
import { onMounted, computed } from 'vue'

type DetailItem = { label: string; value?: string | null }

const props = defineProps<{
  pageTitle: string
  status: string
  approvedText: string
  rejectedText: string
  referenceNo: string
  details: DetailItem[]
  remarks?: string | null
  requirements?: string[]
  rightImageSrc?: string
}>()

const isApproved = computed(() => props.status === 'approved')
const isRejected = computed(() => props.status === 'rejected')

function notify() {
  const text = isApproved.value ? props.approvedText : props.rejectedText
  Toastify({
    text,
    duration: 4000,
    gravity: 'top',
    position: 'right',
    backgroundColor: isApproved.value ? '#16a34a' : '#dc2626',
    close: true,
  }).showToast()
}

onMounted(() => {
  notify()
})
</script>

<template>
  <AppLayout :title="pageTitle">
    <!-- Sticky top tracker -->
    <div class="sticky top-16 z-30 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60">
      <div class="max-w-6xl mx-auto px-4 md:px-6 py-3 flex items-center gap-4">
        <StatusTracker :current-status="status" :noMargin="true" class="flex-1" />
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

          <p class="mt-4 text-[#2C4854]" v-if="isApproved">{{ approvedText }}</p>
          <p class="mt-4 text-[#2C4854]" v-else>{{ rejectedText }}</p>

          <!-- Details -->
          <div class="mt-6 rounded border border-[#2C4854]/20 bg-white p-4">
            <div class="text-[#2C4854] font-semibold">Details</div>
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-[#2C4854]">
              <p><span class="opacity-70">Reference No.:</span> <span class="font-medium">{{ referenceNo }}</span></p>
              <template v-for="(d, idx) in details" :key="idx">
                <p v-if="d.value"><span class="opacity-70">{{ d.label }}:</span> <span class="font-medium">{{ d.value }}</span></p>
              </template>
            </div>
          </div>

          <!-- Next steps for approved -->
          <div v-if="isApproved" class="mt-6 rounded border border-[#2C4854]/20 bg-[#2C4854]/5 p-4">
            <slot name="actions" />
          </div>

          <!-- Requirements -->
          <div v-if="isApproved && (requirements && requirements.length)" class="mt-6 rounded border border-[#2C4854]/20 bg-white p-4">
            <div class="flex items-center gap-2 text-[#2C4854]">
              <h2 class="font-semibold">Requirements</h2>
            </div>
            <ul class="mt-2 pl-5 space-y-2 text-[#2C4854]/80 list-disc">
              <li v-for="(req, idx) in requirements" :key="idx">{{ req }}</li>
            </ul>
            <p class="mt-3 text-xs text-[#2C4854]/70">Note: Requirements may vary by barangay.</p>
          </div>

          <!-- Remarks / Description -->
          <div v-if="remarks" class="mt-6 rounded border border-[#2C4854]/20 bg-[#2C4854]/5 p-4">
            <div class="flex items-center gap-2 text-[#2C4854]">
              <h2 class="font-semibold">Remarks & Description</h2>
            </div>
            <p class="mt-2 text-[#2C4854]">{{ remarks }}</p>
          </div>

          <!-- Guidance for rejected -->
          <div v-if="isRejected" class="mt-6 rounded border border-[#2C4854]/20 bg-white p-4">
            <div class="flex items-center gap-2 text-[#2C4854]">
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

        <!-- Right visual -->
        <div class="flex flex-col items-center justify-start md:sticky md:top-24">
          <img :src="rightImageSrc || '/images/thankyou.png'" alt="Status" class="w-full max-w-md" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
</style>