<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

defineOptions({
    inheritAttrs: false,
})

interface Props {
    className?: HTMLAttributes['class']
}

defineProps<Props>()
</script>

<template>
    <img
        src="/images/brg.png"
        alt="Logo"
        class="h-15 w-20 rounded-full"
        v-bind="$attrs"
    />
</template>
