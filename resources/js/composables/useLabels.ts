import { computed, type Ref } from 'vue'

export function makeMapLabel<T extends string | number | null | undefined>(
  getValue: () => T,
  map: Record<string, string>,
) {
  return computed(() => {
    const v = getValue()
    if (v == null) return ''
    const key = String(v)
    return map[key] ?? ''
  })
}

export function makeListLabel<T extends Record<string, any>>(
  getValue: () => string | null | undefined,
  listRef: Ref<T[]>,
  valueKey: keyof T,
  labelKey: keyof T,
) {
  return computed(() => {
    const v = getValue()
    if (!v) return ''
    const found = listRef.value.find((item) => String(item[valueKey]) === String(v))
    return found ? String(found[labelKey] ?? '') : ''
  })
}