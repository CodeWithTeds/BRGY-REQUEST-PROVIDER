<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ labels: string[]; values: number[]; width?: number; height?: number; stroke?: string }>();

const width = computed(() => props.width ?? 520);
const height = computed(() => props.height ?? 160);
const padding = 24;
const max = computed(() => Math.max(1, ...props.values.map(v => v || 0)));

const points = computed(() => {
  const n = props.values.length;
  if (n === 0) return '';
  const stepX = (width.value - padding * 2) / Math.max(1, n - 1);
  return props.values.map((v, i) => {
    const x = padding + i * stepX;
    const y = padding + (1 - (v || 0) / max.value) * (height.value - padding * 2);
    return `${x},${y}`;
  }).join(' ');
});
</script>

<template>
  <div>
    <svg :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`">
      <defs>
        <linearGradient id="lineFill" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25" />
          <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
        </linearGradient>
      </defs>
      <g>
        <rect :x="padding" :y="padding" :width="width - padding*2" :height="height - padding*2" rx="8" class="fill-[#f9fafb] stroke-[#e5e7eb]" />
        <polyline :points="points" :fill="'url(#lineFill)'" :stroke="props.stroke ?? '#3b82f6'" stroke-width="2" />
      </g>
    </svg>
    <div class="flex justify-between px-2 mt-1">
      <span v-for="(l,i) in props.labels" :key="i" class="text-[10px] text-secondary">{{ l }}</span>
    </div>
  </div>
</template>