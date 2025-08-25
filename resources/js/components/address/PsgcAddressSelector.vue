<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps<{
  regions: Array<{ code: string; name: string }>
  regionCode: string | null
  provinceCode: string | null
  cityCode: string | null
  barangayCode: string | null
}>()

const emit = defineEmits<{
  (e: 'update:regionCode', v: string | null): void
  (e: 'update:provinceCode', v: string | null): void
  (e: 'update:cityCode', v: string | null): void
  (e: 'update:barangayCode', v: string | null): void
}>()

// Local stores
const provinces = ref<Array<{ code: string; name: string }>>([])
const cities = ref<Array<{ code: string; name: string }>>([])
const barangays = ref<Array<{ code: string; name: string }>>([])

// Search inputs
const searchRegion = ref('')
const searchProvince = ref('')
const searchCity = ref('')
const searchBarangay = ref('')

// Filters
const regionItems = computed(() => {
  const q = searchRegion.value.trim().toLowerCase()
  if (!q) return props.regions
  return props.regions.filter(r => r.name.toLowerCase().includes(q) || r.code.toLowerCase().includes(q))
})
const provinceItems = computed(() => {
  const q = searchProvince.value.trim().toLowerCase()
  if (!q) return provinces.value
  return provinces.value.filter(p => p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q))
})
const cityItems = computed(() => {
  const q = searchCity.value.trim().toLowerCase()
  if (!q) return cities.value
  return cities.value.filter(c => c.name.toLowerCase().includes(q) || c.code.toLowerCase().includes(q))
})
const filteredBarangays = computed(() => {
  const q = searchBarangay.value.trim().toLowerCase()
  const items = barangays.value
  if (!q) return items.slice(0, 200)
  const list = items.filter(b => b.name.toLowerCase().includes(q) || b.code.toLowerCase().includes(q))
  return list.slice(0, 200)
})

const hasProvinces = computed(() => provinces.value.length > 0)

// Fetchers
async function loadProvinces(regionCode: string) {
  try {
    const res = await fetch(`/psgc/provinces?region_code=${encodeURIComponent(regionCode)}`)
    const json = await res.json()
    provinces.value = Array.isArray(json?.data) ? json.data : []
  } catch {
    provinces.value = []
  }
}
async function loadCitiesByProvince(provinceCode: string) {
  try {
    const res = await fetch(`/psgc/cities?province_code=${encodeURIComponent(provinceCode)}`)
    const json = await res.json()
    cities.value = Array.isArray(json?.data) ? json.data : []
  } catch {
    cities.value = []
  }
}
async function loadCitiesByRegion(regionCode: string) {
  try {
    const res = await fetch(`/psgc/cities?region_code=${encodeURIComponent(regionCode)}`)
    const json = await res.json()
    cities.value = Array.isArray(json?.data) ? json.data : []
  } catch {
    cities.value = []
  }
}
async function loadBarangays(cityCode: string) {
  try {
    const res = await fetch(`/psgc/barangays?city_code=${encodeURIComponent(cityCode)}`)
    const json = await res.json()
    barangays.value = Array.isArray(json?.data) ? json.data : []
  } catch {
    barangays.value = []
  }
}

// Selection handlers
async function setRegion(val: string | null) {
  emit('update:regionCode', val)
  emit('update:provinceCode', null)
  emit('update:cityCode', null)
  emit('update:barangayCode', null)
  provinces.value = []
  cities.value = []
  barangays.value = []
  const r = props.regions.find(r => r.code === val)
  searchRegion.value = r?.name ?? ''
  searchProvince.value = ''
  searchCity.value = ''
  searchBarangay.value = ''
  if (val) {
    await loadProvinces(val)
    if (provinces.value.length === 0) {
      await loadCitiesByRegion(val)
    }
  }
}
async function setProvince(val: string | null) {
  emit('update:provinceCode', val)
  emit('update:cityCode', null)
  emit('update:barangayCode', null)
  cities.value = []
  barangays.value = []
  const p = provinces.value.find(p => p.code === val)
  searchProvince.value = p?.name ?? ''
  searchCity.value = ''
  searchBarangay.value = ''
  if (val) await loadCitiesByProvince(val)
}
async function setCity(val: string | null) {
  emit('update:cityCode', val)
  emit('update:barangayCode', null)
  barangays.value = []
  const c = cities.value.find(c => c.code === val)
  searchCity.value = c?.name ?? ''
  searchBarangay.value = ''
  if (val) await loadBarangays(val)
}
function setBarangay(val: string | null) {
  emit('update:barangayCode', val)
  const b = barangays.value.find(b => b.code === val)
  searchBarangay.value = b?.name ?? ''
}

// Watch for external changes
watch(() => props.regionCode, (v, o) => { if (v !== o) setRegion(v) })
watch(() => props.provinceCode, (v, o) => { if (v !== o) setProvince(v) })
watch(() => props.cityCode, (v, o) => { if (v !== o) setCity(v) })
watch(() => props.barangayCode, (v, o) => { if (v !== o) setBarangay(v) })
</script>

<template>
  <!-- Region -->
  <div>
    <Label for="region_code">Region</Label>
    <Input class="mt-1" v-model="searchRegion" placeholder="Type to search regions..." />
    <Select :modelValue="props.regionCode" @update:modelValue="setRegion">
      <SelectTrigger>
        <SelectValue placeholder="Select region">Select region</SelectValue>
      </SelectTrigger>
      <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
        <SelectItem v-for="region in regionItems" :key="region.code" :value="region.code" :textValue="region.name">
          {{ region.name }}
        </SelectItem>
      </SelectContent>
    </Select>
  </div>

  <!-- Province -->
  <div v-if="props.regionCode">
    <div v-if="hasProvinces">
      <Label for="province_code">Province</Label>
      <Input class="mt-1" v-model="searchProvince" placeholder="Type to search provinces..." />
      <Select :modelValue="props.provinceCode" @update:modelValue="setProvince">
        <SelectTrigger>
          <SelectValue placeholder="Select province">Select province</SelectValue>
        </SelectTrigger>
        <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
          <SelectItem v-for="province in provinceItems" :key="province.code" :value="province.code" :textValue="province.name">
            {{ province.name }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
    <div v-else>
      <Label for="province_code">Province</Label>
      <p class="mt-2 text-sm text-muted-foreground">This region has no provinces. Please select a city/municipality directly.</p>
    </div>
  </div>

  <!-- City -->
  <div>
    <Label for="city_code">City / Municipality</Label>
    <Input class="mt-1" v-model="searchCity" placeholder="Type to search cities..." />
    <Select :modelValue="props.cityCode" @update:modelValue="setCity">
      <SelectTrigger>
        <SelectValue placeholder="Select city / municipality">Select city / municipality</SelectValue>
      </SelectTrigger>
      <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
        <SelectItem v-for="city in cityItems" :key="city.code" :value="city.code" :textValue="city.name">
          {{ city.name }}
        </SelectItem>
      </SelectContent>
    </Select>
  </div>

  <!-- Barangay -->
  <div>
    <Label for="barangay_code">Barangay</Label>
    <Input class="mt-1" v-model="searchBarangay" placeholder="Type to search barangays..." />
    <Select :modelValue="props.barangayCode" @update:modelValue="setBarangay">
      <SelectTrigger>
        <SelectValue placeholder="Select barangay">Select barangay</SelectValue>
      </SelectTrigger>
      <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
        <SelectItem v-for="barangay in filteredBarangays" :key="barangay.code" :value="barangay.code" :textValue="barangay.name">
          {{ barangay.name }}
        </SelectItem>
      </SelectContent>
    </Select>
  </div>
</template>