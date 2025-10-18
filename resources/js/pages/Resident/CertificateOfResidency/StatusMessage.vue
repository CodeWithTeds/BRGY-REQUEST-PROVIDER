<script setup lang="ts">
import StatusMessageBase from '@/components/StatusMessageBase.vue'
import { Link } from '@inertiajs/vue3'
import { Calendar } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{ residency: { id?: number; status: string; remarks?: string; application_date?: string; appointment_at?: string | null }, rescheduleAllowed?: boolean }>()

const referenceNo = computed(() => (props.residency.id ? `COR-${props.residency.id}` : '—'))
const appointmentDisplay = computed(() => {
  if (!props.residency.appointment_at) return null
  try {
    const d = new Date(props.residency.appointment_at as string)
    return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short', hour12: true })
  } catch { return props.residency.appointment_at as string }
})

const details = computed(() => [
  { label: 'Residency ID', value: props.residency.id ? `#${props.residency.id}` : null },
  { label: 'Application Date', value: props.residency.application_date || null },
])

const requirements = [
  'Valid government-issued ID (e.g., PhilSys ID, Driver’s License, Passport).',
  'Application reference number or Residency ID.',
  'Any additional documents requested in the remarks.',
]

// Only allow initial scheduling if approved; disallow rescheduling after approval
const canScheduleOrReschedule = computed(() => {
  if (props.residency.status === 'approved') {
    return !appointmentDisplay.value // allow scheduling only if not yet scheduled
  }
  return props.rescheduleAllowed !== false
})

const lockMessage = computed(() => {
  if (props.residency.status === 'approved' && appointmentDisplay.value) {
    return 'Rescheduling is disabled after approval.'
  }
  return 'Reschedule limit reached'
})
</script>

<template>
  <StatusMessageBase
    pageTitle="Certificate of Residency Status"
    :status="props.residency.status"
    approvedText="Congratulations! Your certificate of residency application has been approved. Please take note of the details below."
    rejectedText="Unfortunately, your application has been rejected. Please review the remarks below for more information."
    :referenceNo="referenceNo"
    :details="details"
    :remarks="props.residency.remarks"
    :requirements="requirements"
  >
    <template #actions>
      <div class="flex items-center gap-2 text-[#2C4854]">
        <Calendar class="size-5" />
        <h2 class="font-semibold">Schedule Pickup</h2>
      </div>
      <p class="mt-2 text-[#2C4854]">You may now schedule a time to pick up your printed certificate of residency at your barangay office.</p>
      <ul class="mt-2 pl-5 space-y-2 text-[#2C4854]/80 list-disc">
        <li>Bring a valid government-issued ID and your reference number shown above.</li>
        <li>Office hours are Monday–Friday, 8:00 AM–5:00 PM. Confirm availability with your barangay office.</li>
        <li>Processing and printing may take up to 2–3 business days depending on queue.</li>
      </ul>
      <div class="mt-4 flex items-center gap-2">
        <Link v-if="canScheduleOrReschedule" :href="route('resident.certificate-of-residency.schedule')" class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
          {{ appointmentDisplay ? 'Reschedule Appointment' : 'Schedule Appointment' }}
        </Link>
        <span v-else class="inline-flex items-center rounded bg-neutral-300 px-4 py-2 text-neutral-700 cursor-not-allowed">
          {{ lockMessage }}
        </span>
        <a v-if="props.residency.id" :href="route('resident.certificate-of-residency.pdf', props.residency.id)" target="_blank" rel="noopener" class="inline-flex items-center rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
          Download PDF
        </a>
        <div v-if="appointmentDisplay" class="text-[#2C4854] ml-2">
          <span class="opacity-70">Current:</span> <span class="font-medium">{{ appointmentDisplay }}</span>
        </div>
      </div>
    </template>
  </StatusMessageBase>
</template>

<style scoped></style>