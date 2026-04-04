import { ref } from 'vue';

/**
 * Returns a debounced version of the given function.
 * The returned function, when called, will only execute the original
 * function after the specified delay has elapsed since the last call.
 */
export function useDebounce<T extends (...args: unknown[]) => void>(fn: T, delay = 400): T {
  const timer = ref<ReturnType<typeof setTimeout> | null>(null);

  return ((...args: unknown[]) => {
    if (timer.value !== null) clearTimeout(timer.value);
    timer.value = setTimeout(() => fn(...args), delay);
  }) as T;
}
