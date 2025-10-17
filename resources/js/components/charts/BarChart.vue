<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ labels: string[]; values: number[]; colors?: string[]; height?: number }>();

const height = computed(() => props.height ?? 140);
const max = computed(() => Math.max(1, ...props.values.map(v => v || 0)));
function barHeight(n: number) { return Math.max(6, Math.round((n / max.value) * (height.value - 20))); }
</script>

<template>
  <div>
    <div class="flex items-end gap-3" :style="{ height: height + 'px' }">
      <div v-for="(v, i) in props.values" :key="i" class="flex flex-col items-center gap-1">
        <div class="w-6 rounded-md bg-secondary/20">
          <div class="w-6 rounded-b-md" :class="props.colors?.[i] ?? 'bg-brand'" :style="{ height: barHeight(v) + 'px' }"></div>
        </div>
        <span class="text-[10px] text-secondary">{{ props.labels[i] }}</span>
      </div>
    </div>
  </div>
</template>