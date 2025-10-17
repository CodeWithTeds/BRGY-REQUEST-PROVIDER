<script setup lang="ts">
import { ClipboardList, Clock, CheckCircle2, AlertTriangle, BadgeCheck } from 'lucide-vue-next';
import { computed } from 'vue';

interface Stats {
  total: number;
  approved: number;
  pending: number;
  rejected: number;
  processing?: number;
  pre_approved?: number;
}

const props = defineProps<{ stats: Stats }>();

// Thin card metrics: percentage shares and progress ring
const totalCount = computed(() => props.stats.total || 0);
const circleR = 18;
const circleC = 2 * Math.PI * circleR;
function offsetFor(p: number) {
  const clamped = Math.max(0, Math.min(100, p || 0));
  return circleC - (circleC * clamped) / 100;
}
const shares = computed(() => {
  const t = totalCount.value || 1;
  return {
    pending: Math.round(((props.stats.pending || 0) / t) * 100),
    approved: Math.round(((props.stats.approved || 0) / t) * 100),
    rejected: Math.round(((props.stats.rejected || 0) / t) * 100),
    pre_approved: Math.round(((props.stats.pre_approved || 0) / t) * 100),
  };
});
</script>

<template>
  <!-- Grid root: parent-supplied classes (e.g., mb-6) are merged here -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    <!-- Total -->
    <div class="relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur ring-1 ring-black/5 shadow-sm">
      <div class="absolute right-0 top-1/2 -translate-y-1/2 h-16 w-6 rounded-l-full bg-gradient-to-b from-sky-500 to-indigo-600 opacity-30"></div>
      <div class="p-4">
        <div class="flex items-start justify-between">
          <div class="inline-flex items-center gap-2 text-[#2c4454]">
            <ClipboardList class="h-6 w-6" />
            <span class="text-sm">Total permits</span>
          </div>
          <div class="relative">
            <svg viewBox="0 0 42 42" class="h-10 w-10">
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#ffffff55" stroke-width="4" />
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#ffffff" stroke-width="4" :stroke-dasharray="circleC" :stroke-dashoffset="offsetFor(100)" stroke-linecap="round" />
            </svg>
            <span class="absolute inset-0 grid place-items-center text-[10px] text-[#2c4454]">100%</span>
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <div class="text-2xl font-semibold text-[#2c4454]">{{ props.stats.total }}</div>
          <div class="text-sm text-[#2c4454]/70">—</div>
        </div>
        <p class="mt-1 text-xs text-[#2c4454]/70">In system</p>
      </div>
    </div>

    <!-- Pending -->
    <div class="relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur ring-1 ring-black/5 shadow-sm">
      <div class="absolute right-0 top-1/2 -translate-y-1/2 h-16 w-6 rounded-l-full bg-gradient-to-b from-amber-400 to-yellow-600 opacity-30"></div>
      <div class="p-4">
        <div class="flex items-start justify-between">
          <div class="inline-flex items-center gap-2 text-[#2c4454]">
            <Clock class="h-6 w-6" />
            <span class="text-sm">Pending</span>
          </div>
          <div class="relative">
            <svg viewBox="0 0 42 42" class="h-10 w-10">
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#00000015" stroke-width="4" />
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#f59e0b" stroke-width="4" :stroke-dasharray="circleC" :stroke-dashoffset="offsetFor(shares.pending)" stroke-linecap="round" />
            </svg>
            <span class="absolute inset-0 grid place-items-center text-[10px] text-[#2c4454]">{{ shares.pending }}%</span>
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <div class="text-2xl font-semibold text-[#2c4454]">{{ props.stats.pending }}</div>
          <div class="text-sm text-[#2c4454]/70">- {{ Math.max(0, totalCount - props.stats.pending) }}</div>
        </div>
        <p class="mt-1 text-xs text-[#2c4454]/70">Awaiting review</p>
      </div>
    </div>

    <!-- Approved -->
    <div class="relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur ring-1 ring-black/5 shadow-sm">
      <div class="absolute right-0 top-1/2 -translate-y-1/2 h-16 w-6 rounded-l-full bg-gradient-to-b from-emerald-500 to-green-700 opacity-30"></div>
      <div class="p-4">
        <div class="flex items-start justify-between">
          <div class="inline-flex items-center gap-2 text-[#2c4454]">
            <CheckCircle2 class="h-6 w-6" />
            <span class="text-sm">Approved</span>
          </div>
          <div class="relative">
            <svg viewBox="0 0 42 42" class="h-10 w-10">
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#00000015" stroke-width="4" />
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#10b981" stroke-width="4" :stroke-dasharray="circleC" :stroke-dashoffset="offsetFor(shares.approved)" stroke-linecap="round" />
            </svg>
            <span class="absolute inset-0 grid place-items-center text-[10px] text-[#2c4454]">{{ shares.approved }}%</span>
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <div class="text-2xl font-semibold text-[#2c4454]">{{ props.stats.approved }}</div>
          <div class="text-sm text-[#2c4454]/70">- {{ Math.max(0, totalCount - props.stats.approved) }}</div>
        </div>
        <p class="mt-1 text-xs text-[#2c4454]/70">Approved records</p>
      </div>
    </div>

    <!-- Pre-Approved -->
    <div class="relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur ring-1 ring-black/5 shadow-sm">
      <div class="absolute right-0 top-1/2 -translate-y-1/2 h-16 w-6 rounded-l-full bg-gradient-to-b from-teal-500 to-teal-700 opacity-30"></div>
      <div class="p-4">
        <div class="flex items-start justify-between">
          <div class="inline-flex items-center gap-2 text-[#2c4454]">
            <BadgeCheck class="h-6 w-6" />
            <span class="text-sm">Pre-Approved</span>
          </div>
          <div class="relative">
            <svg viewBox="0 0 42 42" class="h-10 w-10">
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#00000015" stroke-width="4" />
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#14b8a6" stroke-width="4" :stroke-dasharray="circleC" :stroke-dashoffset="offsetFor(shares.pre_approved)" stroke-linecap="round" />
            </svg>
            <span class="absolute inset-0 grid place-items-center text-[10px] text-[#2c4454]">{{ shares.pre_approved }}%</span>
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <div class="text-2xl font-semibold text-[#2c4454]">{{ props.stats.pre_approved || 0 }}</div>
          <div class="text-sm text-[#2c4454]/70">- {{ Math.max(0, totalCount - (props.stats.pre_approved || 0)) }}</div>
        </div>
        <p class="mt-1 text-xs text-[#2c4454]/70">Staff pre-approved</p>
      </div>
    </div>

    <!-- Rejected -->
    <div class="relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur ring-1 ring-black/5 shadow-sm">
      <div class="absolute right-0 top-1/2 -translate-y-1/2 h-16 w-6 rounded-l-full bg-gradient-to-b from-rose-500 to-red-700 opacity-30"></div>
      <div class="p-4">
        <div class="flex items-start justify-between">
          <div class="inline-flex items-center gap-2 text-[#2c4454]">
            <AlertTriangle class="h-6 w-6" />
            <span class="text-sm">Rejected</span>
          </div>
          <div class="relative">
            <svg viewBox="0 0 42 42" class="h-10 w-10">
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#00000015" stroke-width="4" />
              <circle cx="21" cy="21" :r="circleR" class="fill-none" stroke="#ef4444" stroke-width="4" :stroke-dasharray="circleC" :stroke-dashoffset="offsetFor(shares.rejected)" stroke-linecap="round" />
            </svg>
            <span class="absolute inset-0 grid place-items-center text-[10px] text-[#2c4454]">{{ shares.rejected }}%</span>
          </div>
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <div class="text-2xl font-semibold text-[#2c4454]">{{ props.stats.rejected }}</div>
          <div class="text-sm text-[#2c4454]/70">- {{ Math.max(0, totalCount - props.stats.rejected) }}</div>
        </div>
        <p class="mt-1 text-xs text-[#2c4454]/70">Declined this period</p>
      </div>
    </div>
  </div>
</template>