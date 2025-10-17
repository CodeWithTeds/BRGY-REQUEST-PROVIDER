<script setup lang="ts">
import { computed } from 'vue';

interface Segment { label: string; value: number; color: string }

const props = defineProps<{ segments: Segment[]; centerText?: string; size?: number; thickness?: number }>();

const size = computed(() => props.size ?? 140);
const thickness = computed(() => props.thickness ?? 18);
const radius = computed(() => (size.value / 2) - thickness.value / 2);

const total = computed(() => (props.segments?.reduce((sum, s) => sum + (s.value || 0), 0)) || 0);

function arcPath(startFrac: number, endFrac: number) {
  const start = startFrac * Math.PI * 2 - Math.PI / 2;
  const end = endFrac * Math.PI * 2 - Math.PI / 2;
  const rOuter = radius.value + thickness.value / 2;
  const rInner = radius.value - thickness.value / 2;
  const x1o = Math.cos(start) * rOuter; const y1o = Math.sin(start) * rOuter;
  const x2o = Math.cos(end) * rOuter; const y2o = Math.sin(end) * rOuter;
  const x1i = Math.cos(end) * rInner; const y1i = Math.sin(end) * rInner;
  const x2i = Math.cos(start) * rInner; const y2i = Math.sin(start) * rInner;
  const largeArc = (end - start) % (Math.PI * 2) > Math.PI ? 1 : 0;
  return `M ${x1o} ${y1o} A ${rOuter} ${rOuter} 0 ${largeArc} 1 ${x2o} ${y2o} L ${x1i} ${y1i} A ${rInner} ${rInner} 0 ${largeArc} 0 ${x2i} ${y2i} Z`;
}

const arcs = computed(() => {
  const t = total.value || 1;
  let cum = 0;
  return props.segments.map(s => {
    const start = cum / t;
    const end = (cum + (s.value || 0)) / t;
    cum += (s.value || 0);
    return { d: arcPath(start, end), color: s.color, label: s.label, value: s.value };
  });
});
</script>

<template>
  <div class="grid grid-cols-2 gap-4 items-center">
    <svg :viewBox="`0 0 ${size} ${size}`" :width="size" :height="size" class="mx-auto">
      <g :transform="`translate(${size/2},${size/2})`">
        <circle :r="radius" fill="#f3f4f6"></circle>
        <template v-for="(arc, idx) in arcs" :key="idx">
          <path :d="arc.d" :fill="arc.color" />
        </template>
        <text text-anchor="middle" dominant-baseline="middle" class="fill-[#2c4454]" :font-size="size/8">
          {{ centerText || total }}
        </text>
      </g>
    </svg>
    <div class="space-y-2">
      <div v-for="(s, i) in props.segments" :key="i" class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <span class="inline-block h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: s.color }"></span>
          <span class="text-xs text-secondary">{{ s.label }}</span>
        </div>
        <span class="text-xs text-main font-medium">{{ s.value }}</span>
      </div>
    </div>
  </div>
</template>