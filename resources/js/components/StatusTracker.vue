<template>
  <div class="flex items-center justify-center w-full mt-8">
    <div class="w-full max-w-4xl">
      <div class="flex items-center justify-between">
        <div v-for="(status, index) in statuses" :key="status" class="flex-1 text-center">
          <div class="flex items-center justify-center">
            <div class="flex-1 h-1" :class="getLineClass(index)"></div>
            <div class="relative">
              <div class="flex items-center justify-center w-10 h-10 rounded-full" :class="getStepClass(index)">
                <span class="text-lg font-semibold">{{ index + 1 }}</span>
              </div>
            </div>
            <div class="flex-1 h-1" :class="getLineClass(index, true)"></div>
          </div>
          <p class="mt-2 text-sm font-medium" :class="getTextClass(index)">{{ status }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  currentStatus: {
    type: String,
    required: true,
    validator: (value: string) => ['pending', 'processing', 'approved', 'rejected'].includes(value),
  },
});

const statuses = ['pending', 'processing', 'approved'];
const statusIndex = computed(() => statuses.indexOf(props.currentStatus));

const getStepClass = (index: number) => {
  if (index < statusIndex.value) {
    return 'bg-primary text-primary-foreground';
  }
  if (index === statusIndex.value) {
    return 'bg-primary text-primary-foreground scale-110';
  }
  return 'bg-gray-200 text-gray-500';
};

const getLineClass = (index: number, isAfter = false) => {
  if (isAfter) {
    if (index < statusIndex.value) {
      return 'bg-primary';
    }
    return 'bg-gray-200';
  }
  if (index <= statusIndex.value) {
    return 'bg-primary';
  }
  return 'bg-gray-200';
};

const getTextClass = (index: number) => {
  if (index <= statusIndex.value) {
    return 'text-primary';
  }
  return 'text-gray-500';
};
</script>

<style scoped>
.bg-primary {
  background-color: hsl(0 0% 9%);
}
.text-primary {
  color: hsl(0 0% 9%);
}
.text-primary-foreground {
  color: hsl(0 0% 98%);
}
</style>