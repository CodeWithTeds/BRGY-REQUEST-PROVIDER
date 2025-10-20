<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { computed, ref, watch } from 'vue'

const props = defineProps<{ clearance: { id: number; status: string; application_date?: string | null; appointment_at?: string | null; appointment_status?: string | null }, rescheduleAllowed?: boolean }>()

const form = useForm({
  clearance_id: props.clearance.id,
  date: '',
  time: ''
})

// Track occupied time slots for selected date
const occupied = ref<string[]>([])

async function loadOccupied(date: string) {
  try {
    const base = route('barangay-clearance.availability')
    const res = await fetch(`${base}?date=${encodeURIComponent(date)}`)
    if (!res.ok) return (occupied.value = [])
    const json = await res.json()
    occupied.value = Array.isArray(json.occupied) ? json.occupied : []
  } catch { occupied.value = [] }
}

watch(() => form.date, (d) => {
  if (d) loadOccupied(d); else occupied.value = []
})

const referenceNo = computed(() => (props.clearance?.id ? `BCL-${props.clearance.id}` : '—'))

// Add client-side date constraints: today, weekend, past-date
const today = computed(() => {
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${dd}`;
});
const isWeekend = computed(() => {
  if (!form.date) return false;
  const d = new Date(`${form.date}T00:00:00`);
  const day = d.getDay();
  return day === 0 || day === 6;
});
const isPast = computed(() => {
  if (!form.date) return false;
  return form.date < today.value;
});
const clientDateError = computed(() => {
  if (!form.date) return '';
  if (isPast.value) return 'Date cannot be in the past.';
  if (isWeekend.value) return 'Appointments are only Monday–Friday.';
  return '';
});

// Timeslots between 08:00 and 17:00 in 30-min increments (display in 12-hour)
const timeSlots = computed(() => {
  const slots: string[] = []
  const start = 8 * 60 // minutes
  const end = 17 * 60 // minutes
  for (let m = start; m <= end; m += 30) {
    const hh = String(Math.floor(m / 60)).padStart(2, '0')
    const mm = String(m % 60).padStart(2, '0')
    slots.push(`${hh}:${mm}`)
  }
  return slots
})

function formatTime24To12(t: string | null | undefined) {
  if (!t) return ''
  const [hStr, mStr] = t.split(':')
  let h = parseInt(hStr, 10)
  const ampm = h >= 12 ? 'PM' : 'AM'
  h = h % 12
  if (h === 0) h = 12
  return `${h}:${mStr} ${ampm}`
}

const appointmentDisplay = computed(() => {
  if (!props.clearance.appointment_at) return null
  try {
    const d = new Date(props.clearance.appointment_at)
    return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short', hour12: true })
  } catch { return props.clearance.appointment_at }
})

function submit() {
  if (props.rescheduleAllowed === false && appointmentDisplay.value) {
    return;
  }
  form.post(route('barangay-clearance.schedule.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    }
  })
}

const breadcrumbs = [
  { title: 'Barangay Clearance', href: route('resident.barangay-clearance') },
  { title: 'Schedule Pickup', href: route('barangay-clearance.schedule') },
]
</script>

<template>
  <Head title="Schedule Appointment" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="max-w-4xl mx-auto">
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Schedule Appointment</CardTitle>
                <CardDescription>Pick a date and time to claim your clearance.</CardDescription>
              </div>
              <img src="/images/thankyou.png" alt="Barangay Clearance" class="hidden sm:block h-12 w-auto rounded-md" />
            </div>
          </CardHeader>
          <CardContent class="space-y-6 text-[#2C4854]">
            <div class="rounded border border-neutral-200 bg-white p-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <div class="text-xs opacity-70">Clearance ID</div>
                  <div class="text-sm">#{{ props.clearance.id }}</div>
                </div>
                <div>
                  <div class="text-xs opacity-70">Status</div>
                  <div class="text-sm font-medium">{{ props.clearance.status }}</div>
                </div>
                <div v-if="props.clearance.application_date">
                  <div class="text-xs opacity-70">Application Date</div>
                  <div class="text-sm">{{ props.clearance.application_date }}</div>
                </div>
                <div v-if="appointmentDisplay">
                  <div class="text-xs opacity-70">Current Appointment</div>
                  <div class="text-sm">{{ appointmentDisplay }}</div>
                </div>
                <div v-if="props.clearance.appointment_status">
                  <div class="text-xs opacity-70">Appointment Status</div>
                  <div class="text-sm capitalize">{{ (props.clearance.appointment_status as string).replace('_', ' ') }}</div>
                </div>
              </div>
            </div>

            <div class="rounded border border-neutral-200 bg-white p-4">
              <div class="font-semibold">Requirements & Instructions</div>
              <p class="mt-2 text-sm">Please bring the following items when you visit the barangay office for pickup:</p>
              <ul class="mt-2 pl-5 space-y-1 list-disc text-sm">
                <li>Valid government-issued ID (e.g., PhilSys ID, Driver’s License, Passport).</li>
                <li>Your reference number: <span class="font-medium">{{ referenceNo }}</span>.</li>
                <li>Any supporting documents used during your application.</li>
                <li>Printed or screenshot of your appointment confirmation.</li>
              </ul>
              <div class="mt-3 text-sm">
                Office hours: <span class="font-medium">Monday–Friday, 8:00 AM–5:00 PM</span>.
              </div>
              <div class="mt-2 text-xs text-neutral-600">
                If you cannot make your appointment, you can return here to reschedule. Processing and printing may take 2–3 business days depending on queue.
              </div>
            </div>

            <div class="rounded border border-neutral-200 bg-[#f8fafc] p-4">
              <div class="font-semibold">Important Reminders</div>
              <ul class="mt-2 pl-5 list-disc text-sm space-y-1">
                <li>Present your reference number <span class="font-medium">{{ referenceNo }}</span> upon arrival.</li>
                <li>Arrive 10 minutes before your slot to avoid delays.</li>
                <li>Only the applicant or an authorized representative with valid ID may claim.</li>
              </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
              <div>
                <Label for="date">Date</Label>
                <Input id="date" type="date" v-model="form.date" :min="today" :error="form.errors.date" />
                <div v-if="form.errors.date" class="text-sm text-red-600">{{ form.errors.date }}</div>
                <div v-if="clientDateError" class="text-sm text-red-600">{{ clientDateError }}</div>
              </div>

              <div>
                <Label for="time">Time (8:00 AM–5:00 PM)</Label>
                <Select v-model="form.time">
                  <SelectTrigger id="time" class="w-full">
                    <SelectValue placeholder="Select time">{{ form.time ? formatTime24To12(form.time) : 'Select time' }}</SelectValue>
                  </SelectTrigger>
                  <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
                    <SelectItem v-for="t in timeSlots" :key="t" :value="t" :disabled="occupied.includes(t)">{{ formatTime24To12(t) }}</SelectItem>
                  </SelectContent>
                </Select>
                <div v-if="form.errors.time" class="text-sm text-red-600">{{ form.errors.time }}</div>
                <div v-if="form.date && occupied.length" class="text-xs text-neutral-600 mt-1">Unavailable slots are disabled based on current bookings.</div>
              </div>

              <input type="hidden" name="clearance_id" :value="form.clearance_id" />

              <div class="flex items-center gap-2">
                <Button type="submit" :disabled="(props.rescheduleAllowed === false && !!appointmentDisplay) || !!clientDateError || !form.date || !form.time">Save Appointment</Button>
                <Link :href="route('barangay-clearance.create')">
                  <Button type="button" variant="outline">Back</Button>
                </Link>
              </div>
              <div v-if="props.rescheduleAllowed === false && !!appointmentDisplay" class="text-sm text-red-600 mt-2">Rescheduling is allowed only once. Please contact your barangay office for further changes.</div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
  
</template>