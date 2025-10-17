<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PermitStatsCards from '@/components/Admin/PermitStatsCards.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Eye, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next'

interface IndigencyItem {
  id: number
  full_name: string
  status: 'approved' | 'pending' | 'rejected' | 'processing' | 'pre-approved' | string
  application_date?: string | null
  barangay?: string | null
  address_line?: string | null
  contact_number?: string | null
  remarks?: string | null
  updated_at?: string | null
}

interface StatsItem {
  total: number
  pending: number
  approved: number
  rejected: number
  processing?: number
}

interface Filters {
  name?: string;
  status?: 'approved' | 'pending' | 'processing' | 'pre-approved' | 'rejected' | '';
  date_from?: string;
  date_to?: string;
}

const props = defineProps<{ indigencies: IndigencyItem[]; stats: StatsItem; filters?: Filters; pagination?: { current_page: number; per_page: number; last_page: number; total: number }; routeGroup?: string; canDelete?: boolean }>()

const basePath = computed(() => `/${props.routeGroup ?? 'admin'}/indigency-certificates`)

const filterName = ref(props.filters?.name ?? '')
const filterStatus = ref<Filters['status']>(props.filters?.status ?? '')
const filterDateFrom = ref(props.filters?.date_from ?? '')
const filterDateTo = ref(props.filters?.date_to ?? '')

const selectedIds = ref<number[]>([])
const selectAll = ref(false)

function toggleSelectAll(items: IndigencyItem[]) {
  selectAll.value = !selectAll.value
  selectedIds.value = selectAll.value ? items.map((p) => p.id) : []
}

function toggleSelected(id: number) {
  const idx = selectedIds.value.indexOf(id)
  if (idx >= 0) selectedIds.value.splice(idx, 1)
  else selectedIds.value.push(id)
}

// Server-driven pagination
const page = ref(props.pagination?.current_page ?? 1)
const perPage = ref(props.pagination?.per_page ?? 10)
const totalItems = computed(() => props.pagination?.total ?? props.indigencies.length)
const totalPages = computed(() => props.pagination?.last_page ?? Math.max(1, Math.ceil(totalItems.value / perPage.value)))
// Server returns current page items
const paginatedIndigencies = computed(() => props.indigencies)

function gotoPage(next: number) {
  const nextPage = Math.min(Math.max(1, next), totalPages.value)
  page.value = nextPage
  router.get(basePath.value, {
    name: filterName.value || undefined,
    status: filterStatus.value || undefined,
    date_from: filterDateFrom.value || undefined,
    date_to: filterDateTo.value || undefined,
    page: nextPage,
    per_page: perPage.value,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function applyFilters() {
  page.value = 1
  router.get(basePath.value, {
    name: filterName.value || undefined,
    status: filterStatus.value || undefined,
    date_from: filterDateFrom.value || undefined,
    date_to: filterDateTo.value || undefined,
    page: 1,
    per_page: perPage.value,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function resetFilters() {
  filterName.value = ''
  filterStatus.value = ''
  filterDateFrom.value = ''
  filterDateTo.value = ''
  applyFilters()
}

function initial(name?: string | null) {
  if (!name) return '?'
  return name.trim().charAt(0).toUpperCase()
}

function deleteItem(id: number) {
  if (!props.canDelete) return
  router.delete(`${basePath.value}/${id}`, { preserveScroll: true })
}
</script>

<template>
  <Head :title="props.routeGroup === 'staff' ? 'Staff: Indigency Certificates' : 'Indigency Certificates'" />
  <AppLayout>
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="space-y-6">
        <PermitStatsCards :stats="props.stats" />

        <div class="rounded-lg border border-[#2c4454]/20 bg-white">
          <div class="p-4 border-b border-[#2c4454]/10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <input v-model="filterName" type="text" placeholder="Search name" class="border rounded px-3 py-2" />
              <select v-model="filterStatus" class="border rounded px-3 py-2">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="pre-approved">Pre-Approved</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
              <input v-model="filterDateFrom" type="date" class="border rounded px-3 py-2" />
              <input v-model="filterDateTo" type="date" class="border rounded px-3 py-2" />
            </div>
            <div class="mt-3 flex items-center gap-2">
              <button class="px-3 py-2 bg-[#2c4454] text-white rounded text-sm" @click="applyFilters">Apply filters</button>
              <button class="px-3 py-2 bg-gray-200 text-[#2c4454] rounded text-sm" @click="resetFilters">Reset</button>
            </div>
          </div>

          <div class="p-4">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3">
                      <input type="checkbox" class="rounded" :checked="selectAll" @change="toggleSelectAll(paginatedIndigencies)" />
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Applicant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Applied</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Last update</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in paginatedIndigencies" :key="item.id" class="hover:bg-gray-50">
                    <td class="px-4 py-4">
                      <input type="checkbox" class="rounded" :checked="selectedIds.includes(item.id)" @change="toggleSelected(item.id)" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full bg-[#2c4454]/10 text-[#2c4454] flex items-center justify-center font-semibold">{{ initial(item.full_name) }}</div>
                        <div>
                          <div class="text-sm font-medium text-[#2c4454]">{{ item.full_name || '—' }}</div>
                          <div class="text-xs text-[#2c4454]/70">{{ item.barangay || '—' }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      {{ item.status }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.application_date || '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.updated_at || '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-2">
                        <Link :href="`${basePath}/${item.id}`" class="inline-flex items-center gap-1 px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">
                          <Eye class="h-4 w-4" />
                          <span>View</span>
                        </Link>
                        <button v-if="props.canDelete" @click="deleteItem(item.id)" class="inline-flex items-center gap-1 px-3 py-2 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm hover:bg-red-100">
                          <Trash2 class="h-4 w-4" />
                          <span>Delete</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center gap-2 text-sm text-[#2c4454]">
                <label>Rows per page</label>
                <select v-model.number="perPage" class="border border-[#2c4454]/20 rounded px-2 py-1 text-sm" @change="applyFilters">
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                </select>
                <span class="ml-2">{{ (page - 1) * perPage + 1 }}-{{ Math.min(page * perPage, totalItems) }} of {{ totalItems }}</span>
              </div>
              <div class="inline-flex items-center gap-2">
                <button class="inline-flex items-center gap-1 px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50" :disabled="page === 1" @click="gotoPage(page - 1)">
                  <ChevronLeft class="h-4 w-4" />
                  <span>Prev</span>
                </button>
                <span class="text-sm text-[#2c4454]">{{ page }}</span>
                <button class="inline-flex items-center gap-1 px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50" :disabled="page === totalPages" @click="gotoPage(page + 1)">
                  <ChevronRight class="h-4 w-4" />
                  <span>Next</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>