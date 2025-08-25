<script setup lang="ts">
import { SelectLabel as RekaSelectLabel } from 'reka-ui'

defineOptions({
  inheritAttrs: false,
})
</script>

<template>
  <RekaSelectLabel class="px-2 py-1.5 text-sm font-semibold" v-bind="$attrs">
    <slot />
  </RekaSelectLabel>
</template>
