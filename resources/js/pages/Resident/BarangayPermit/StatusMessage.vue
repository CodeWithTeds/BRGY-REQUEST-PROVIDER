<script setup lang="ts">
import StatusMessageBase from '@/components/StatusMessageBase.vue'
import { Link } from '@inertiajs/vue3'
import { Calendar } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{ permit: { id?: number; status: string; remarks?: string; application_date?: string; appointment_at?: string | null; appointment_status?: string | null }, rescheduleAllowed?: boolean }>()

const referenceNo = computed(() => (props.permit.id ? `BP-${props.permit.id}` : '—'))
const appointmentDisplay = computed(() => {
  if (!props.permit.appointment_at) return null
  try {
    const d = new Date(props.permit.appointment_at as string)
    return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short', hour12: true })
  } catch { return props.permit.appointment_at as string }
})

const appointmentStatusLabel = computed(() => {
  const s = props.permit.appointment_status as string | undefined | null
  if (!s) return null
  const map: Record<string, string> = { scheduled: 'Scheduled', completed: 'Completed', cancelled: 'Cancelled', no_show: 'No-show' }
  return map[s] || s
})

const details = computed(() => [
  { label: 'Permit ID', value: props.permit.id ? `#${props.permit.id}` : null },
  { label: 'Application Date', value: props.permit.application_date || null },
])

const requirements = [
  'Valid government-issued ID (e.g., PhilSys ID, Driver’s License, Passport).',
  'Proof of payment, if applicable.',
  'Application reference number or Permit ID.',
  'Any additional documents requested in the remarks.',
]
</script>

<template>
  <StatusMessageBase
    pageTitle="Barangay Permit Status"
    :status="props.permit.status"
    approvedText="Congratulations! Your business permit application has been approved. You will be able to download your permit from your dashboard soon."
    rejectedText="Unfortunately, your application has been rejected. Please review the remarks below for more information."
    :referenceNo="referenceNo"
    :details="details"
    :remarks="props.permit.remarks"
    :requirements="requirements"
  >
    <template #actions>
      <div class="flex items-center gap-2 text-[#2C4854]">
        <Calendar class="size-5" />
        <h2 class="font-semibold">Schedule Pickup</h2>
      </div>
      <p class="mt-2 text-[#2C4854]">You may now schedule a time to pick up your printed business permit at your barangay office.</p>
      <ul class="mt-2 pl-5 space-y-2 text-[#2C4854]/80 list-disc">
        <li>Bring a valid government-issued ID and your reference number shown above.</li>
        <li>Office hours are Monday–Friday, 8:00 AM–5:00 PM. Confirm availability with your barangay office.</li>
        <li>Processing and printing may take up to 2–3 business days depending on queue.</li>
      </ul>
      <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:gap-2">
        <Link v-if="props.rescheduleAllowed !== false" :href="route('barangay-permit.schedule')" class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
          {{ appointmentDisplay ? 'Reschedule Appointment' : 'Schedule Appointment' }}
        </Link>
        <span v-else class="inline-flex items-center rounded bg-neutral-300 px-4 py-2 text-neutral-700 cursor-not-allowed">
          Reschedule limit reached
        </span>
        <a v-if="props.permit.id" :href="route('barangay-permit.pdf', props.permit.id)" target="_blank" rel="noopener" class="inline-flex items-center rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
          Download PDF
        </a>
        <div v-if="appointmentDisplay || appointmentStatusLabel" class="text-[#2C4854] sm:ml-2 mt-3 sm:mt-0 w-full text-left flex flex-wrap items-start sm:items-center gap-3">
          <div v-if="appointmentDisplay"><span class="opacity-70">Current:</span> <span class="font-medium">{{ appointmentDisplay }}</span></div>
          <div v-if="appointmentStatusLabel"><span class="opacity-70">Status:</span> <span class="font-medium">{{ appointmentStatusLabel }}</span></div>
        </div>
      </div>
    </template>
  </StatusMessageBase>
</template>

<style scoped></style>