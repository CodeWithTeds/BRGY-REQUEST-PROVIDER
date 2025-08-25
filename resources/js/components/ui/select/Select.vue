<script setup lang="ts">
import { SelectRoot as RekaSelect } from 'reka-ui'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<{
  modelValue?: any
  defaultValue?: any
  name?: string
  disabled?: boolean
}>()

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <RekaSelect
    :model-value="modelValue"
    :default-value="defaultValue"
    :name="name"
    :disabled="disabled"
    @update:model-value="emit('update:modelValue', $event)"
    v-bind="$attrs"
  >
    <slot />
  </RekaSelect>
</template>
