<script setup lang="ts">
import { SelectTrigger as RekaSelectTrigger } from 'reka-ui'
import Icon from '@/components/Icon.vue'

defineOptions({
  inheritAttrs: false,
})

defineProps<{
  disabled?: boolean
}>()
</script>

<template>
  <RekaSelectTrigger
    class="flex h-9 w-full items-center justify-between rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
    v-bind="$attrs"
    type="button"
    :disabled="disabled"
  >
    <slot />
    <Icon name="chevron-down" class="ml-2 h-4 w-4 opacity-50" />
  </RekaSelectTrigger>
</template>
