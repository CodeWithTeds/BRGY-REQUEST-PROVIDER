<script setup lang="ts">
import { SelectValue as RekaSelectValue } from 'reka-ui'

defineOptions({
  inheritAttrs: false,
})

defineProps<{
  placeholder?: string
}>()
</script>

<template>
  <RekaSelectValue :placeholder="placeholder" v-bind="$attrs">
    <slot />
  </RekaSelectValue>
</template>
