<script setup lang="ts">
import { SelectItem as RekaSelectItem } from 'reka-ui'

defineOptions({
  inheritAttrs: false,
})

defineProps<{
  value: string
  disabled?: boolean
  textValue?: string
}>()
</script>

<template>
  <RekaSelectItem
    class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
    v-bind="$attrs"
    :value="value"
    :disabled="disabled"
    :text-value="textValue"
  >
    <slot />
  </RekaSelectItem>
</template>
