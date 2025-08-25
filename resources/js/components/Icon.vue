<script setup lang="ts">
import { cn } from '@/lib/utils';
import * as icons from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    name: string;
    class?: string;
    size?: number | string;
    color?: string;
    strokeWidth?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    class: '',
    size: 16,
    strokeWidth: 2,
});

const className = computed(() => cn('h-4 w-4', props.class));

// Convert names like "chevron-down" or "chevron_down" to "ChevronDown"
function toPascalCase(input: string) {
    return input
        .split(/[-_\s]+/)
        .filter(Boolean)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');
}

const icon = computed(() => {
    const pascal = toPascalCase(props.name);
    // Prefer PascalCase lookup (e.g., ChevronDown). Fallback to exact name.
    return (icons as Record<string, any>)[pascal] ?? (icons as Record<string, any>)[props.name];
});
</script>

<template>
    <component :is="icon" :class="className" :size="size" :stroke-width="strokeWidth" :color="color" />
</template>
