<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

type ServiceKey = 'permit' | 'clearance' | 'residency' | 'indigency';

const services: { key: ServiceKey; label: string; path: string }[] = [
  { key: 'permit', label: 'Barangay Business Permit', path: '/resident/barangay-permit/availability' },
  { key: 'clearance', label: 'Barangay Clearance', path: '/resident/barangay-clearance/availability' },
  { key: 'residency', label: 'Certificate of Residency', path: '/resident/certificate-of-residency/availability' },
  { key: 'indigency', label: 'Certificate of Indigency', path: '/resident/certificate-of-indigency/availability' },
];

function formatTime24To12(hhmm: string) {
  const [hStr, m] = hhmm.split(':');
  const h = parseInt(hStr, 10);
  const suffix = h >= 12 ? 'PM' : 'AM';
  const h12 = h % 12 === 0 ? 12 : h % 12;
  return `${h12}:${m} ${suffix}`;
}

const todayStr = computed(() => {
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
});

const selectedDate = ref<string>(todayStr.value);

const timeSlots = computed<string[]>(() => {
  const slots: string[] = [];
  let hour = 8;
  let minute = 0;
  while (hour < 17 || (hour === 17 && minute === 0)) {
    slots.push(`${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`);
    minute += 30;
    if (minute >= 60) {
      minute = 0;
      hour += 1;
    }
  }
  return slots;
});

const loading = ref<Record<ServiceKey, boolean>>({ permit: false, clearance: false, residency: false, indigency: false });
const occupied = ref<Record<ServiceKey, Set<string>>>({
  permit: new Set<string>(),
  clearance: new Set<string>(),
  residency: new Set<string>(),
  indigency: new Set<string>(),
});
const error = ref<Record<ServiceKey, string | null>>({ permit: null, clearance: null, residency: null, indigency: null });

async function loadAvailability(service: { key: ServiceKey; path: string }) {
  loading.value[service.key] = true;
  error.value[service.key] = null;
  try {
    const url = `${service.path}?date=${encodeURIComponent(selectedDate.value)}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    const occ: string[] = Array.isArray(data?.occupied) ? data.occupied : [];
    occupied.value[service.key] = new Set(occ);
  } catch (e: any) {
    error.value[service.key] = e?.message || 'Failed to load availability';
  } finally {
    loading.value[service.key] = false;
  }
}

function refreshAll() {
  services.forEach(s => loadAvailability(s));
}

onMounted(() => {
  refreshAll();
});

watch(selectedDate, () => {
  refreshAll();
});
</script>

<template>
  <AppLayout title="Availability">
    <div class="p-6 max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Appointment Availability</h1>
        <div class="flex items-center gap-3">
          <label for="avail-date" class="text-sm font-medium">Select Date</label>
          <input
            id="avail-date"
            type="date"
            v-model="selectedDate"
            :min="todayStr"
            class="border rounded px-3 py-2"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="svc in services" :key="svc.key" class="border rounded-lg">
          <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <div class="font-medium">{{ svc.label }}</div>
            <div v-if="loading[svc.key]" class="text-sm text-gray-500">Loading…</div>
          </div>
          <div class="p-4">
            <div v-if="error[svc.key]" class="text-red-600 text-sm mb-3">{{ error[svc.key] }}</div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
              <div v-for="t in timeSlots" :key="t"
                   class="flex items-center justify-between border rounded px-3 py-2"
                   :class="occupied[svc.key].has(t) ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'">
                <span class="font-medium">{{ formatTime24To12(t) }}</span>
                <span class="text-xs" v-if="occupied[svc.key].has(t)">Full</span>
                <span class="text-xs" v-else>Available</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>