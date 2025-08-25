<script setup lang="ts">
import { SelectGroup as RekaSelectGroup } from 'reka-ui'

defineOptions({
  inheritAttrs: false,
})
</script>

<template>
  <RekaSelectGroup class="p-1 py-1.5" v-bind="$attrs">
    <slot />
  </RekaSelectGroup>
</template>
