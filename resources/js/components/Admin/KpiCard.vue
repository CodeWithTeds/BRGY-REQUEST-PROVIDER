<script setup lang="ts">
import type { Component } from 'vue';
const props = defineProps<{ title: string; value: number|string; icon?: string; iconComponent?: Component; progress?: number; accentClass?: string }>();
</script>

<template>
  <div class="rounded-xl bg-white shadow-sm border border-gray-200 p-4">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-xs text-secondary">{{ props.title }}</div>
        <div class="text-2xl font-semibold text-main">{{ props.value }}</div>
      </div>
      <div class="h-10 w-10 rounded-full flex items-center justify-center" :class="props.accentClass ?? 'bg-brand/10 text-brand'">
        <component v-if="props.iconComponent" :is="props.iconComponent" class="h-5 w-5" />
        <i v-else :class="props.icon ?? 'i-tabler-gauge'" class="text-xl"></i>
      </div>
    </div>
    <div class="mt-3">
      <div class="h-2 w-full rounded-full bg-secondary/20">
        <div class="h-2 rounded-full bg-brand" :style="{ width: (props.progress ?? 0) + '%' }"></div>
      </div>
    </div>
  </div>
</template>