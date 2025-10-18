<script setup lang="ts">
import { ref, computed } from 'vue'
import { Calendar } from 'v-calendar'
import 'v-calendar/style.css'

/**
 * Props
 * - busyDates: array of ISO local dates (YYYY-MM-DD) that are occupied
 * - year: target year to render (defaults to current year)
 */
const props = defineProps<{ busyDates?: string[]; year?: number }>()
const emit = defineEmits<{ (e: 'select-date', dateStr: string): void }>()

const currentYear = props.year || new Date().getFullYear()
const firstOfYear = new Date(currentYear, 0, 1)
const today = new Date()

// v-calendar uses "attributes" to decorate dates
const attributes = computed(() => {
  // Mark busy dates
  const busy = (props.busyDates || []).map(d => ({
    key: `busy-${d}`,
    dates: [new Date(d)],
    highlight: { class: 'is-busy' },
    dot: { class: 'is-busy-dot' },
    popover: { label: 'Occupied' }
  }))

  const todayAttr = {
    key: 'today',
    dates: [today],
    highlight: { class: 'is-today' },
    popover: { label: 'Today' }
  }

  return [
    // style for busy dates
    ...busy,
    todayAttr,
  ]
})

function onDayClick(payload: any) {
  try {
    const d = payload?.date instanceof Date ? payload.date : new Date(payload?.date)
    const y = d.getFullYear()
    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    emit('select-date', `${y}-${m}-${day}`)
  } catch {}
}

// Panel grid to mimic a year view (4 rows x 3 columns)
const rows = 4
const columns = 3
</script>

<template>
  <div class="bp-theme max-h-[calc(100vh-8rem)] overflow-auto">
    <div class="flex items-center justify-between">
      <h3 class="text-base font-semibold text-[#2c4454]">Calendar – {{ currentYear }}</h3>
    </div>

    <Calendar
          :first-day-of-week="1"
          :rows="rows"
          :columns="columns"
          :title-position="'center'"
          :min-date="firstOfYear"
          :attributes="attributes"
          expanded
          @dayclick="onDayClick"
        />
    <div class="mt-3 text-[11px] text-[#2c4454] opacity-70">
      Occupied dates are highlighted with dots.
    </div>
  </div>
</template>

<style scoped>
.bp-theme { font-family: 'Space Grotesk', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
/* Accent for occupied dates */
:deep(.vc-day .vc-highlight) { background: rgba(239, 68, 68, 0.12); }
:deep(.vc-day .vc-dot) { background-color: #ef4444; }
</style>