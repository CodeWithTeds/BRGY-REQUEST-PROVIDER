<script setup lang="ts">
import StatusMessageBase from '@/components/StatusMessageBase.vue'
import { Link } from '@inertiajs/vue3'
import { Calendar } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{ indigency: { id?: number; status: string; remarks?: string; application_date?: string; appointment_at?: string | null }, rescheduleAllowed?: boolean }>()

const referenceNo = computed(() => (props.indigency.id ? `COI-${props.indigency.id}` : '—'))
const appointmentDisplay = computed(() => {
  if (!props.indigency.appointment_at) return null
  try {
    const d = new Date(props.indigency.appointment_at as string)
    return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short', hour12: true })
  } catch { return props.indigency.appointment_at as string }
})

const details = computed(() => [
  { label: 'Indigency ID', value: props.indigency.id ? `#${props.indigency.id}` : null },
  { label: 'Application Date', value: props.indigency.application_date || null },
])

const requirements = [
  'Valid government-issued ID (e.g., PhilSys ID, Driver’s License, Passport).',
  'Application reference number or Indigency ID.',
  'Any additional documents requested in the remarks.',
]
</script>

<template>
  <StatusMessageBase
    pageTitle="Certificate of Indigency Status"
    :status="props.indigency.status"
    approvedText="Congratulations! Your certificate of indigency application has been approved. Please take note of the details below."
    rejectedText="Unfortunately, your application has been rejected. Please review the remarks below for more information."
    :referenceNo="referenceNo"
    :details="details"
    :remarks="props.indigency.remarks"
    :requirements="requirements"
  >
    <template #actions>
      <div class="flex items-center gap-2 text-[#2C4854]">
        <Calendar class="size-5" />
        <h2 class="font-semibold">Schedule Pickup</h2>
      </div>
      <p class="mt-2 text-[#2C4854]">You may now schedule a time to pick up your printed certificate of indigency at your barangay office.</p>
      <ul class="mt-2 pl-5 space-y-2 text-[#2C4854]/80 list-disc">
        <li>Bring a valid government-issued ID and your reference number shown above.</li>
        <li>Office hours are Monday–Friday, 8:00 AM–5:00 PM. Confirm availability with your barangay office.</li>
        <li>Processing and printing may take up to 2–3 business days depending on queue.</li>
      </ul>
      <div class="mt-4 flex items-center gap-2">
        <Link v-if="props.rescheduleAllowed !== false" :href="route('resident.certificate-of-indigency.schedule')" class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
          {{ appointmentDisplay ? 'Reschedule Appointment' : 'Schedule Appointment' }}
        </Link>
        <a v-if="props.indigency.status === 'approved' && props.indigency.id" :href="route('resident.certificate-of-indigency.pdf', props.indigency.id)" target="_blank" rel="noopener" class="inline-flex items-center rounded bg-neutral-800 px-4 py-2 text-white hover:bg-neutral-900">
          Download PDF
        </a>
      </div>
      <div v-if="appointmentDisplay" class="mt-2 text-sm text-[#2C4854]/80">Current appointment: <span class="font-medium">{{ appointmentDisplay }}</span></div>
    </template>
  </StatusMessageBase>
</template>