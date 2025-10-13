<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PermitStatsCards from '@/components/Admin/PermitStatsCards.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Eye, Trash2, Settings, AlertTriangle, ChevronLeft, ChevronRight } from 'lucide-vue-next'

interface ResidencyItem {
  id: number
  full_name: string
  status: 'approved' | 'pending' | 'rejected' | 'processing' | string
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
  status?: 'approved' | 'pending' | 'processing' | 'rejected' | '';
  date_from?: string;
  date_to?: string;
}

const props = defineProps<{ residencies: ResidencyItem[]; stats: StatsItem; filters?: Filters }>()

// Server-driven filters (mirroring BarangayClearances)
const filterName = ref(props.filters?.name ?? '')
const filterStatus = ref<Filters['status']>(props.filters?.status ?? '')
const filterDateFrom = ref(props.filters?.date_from ?? '')
const filterDateTo = ref(props.filters?.date_to ?? '')

// Selection state
const selectedIds = ref<number[]>([])
const selectAll = ref(false)
function toggleSelectAll(items: ResidencyItem[]) {
  selectAll.value = !selectAll.value
  selectedIds.value = selectAll.value ? items.map((r) => r.id) : []
}
function toggleSelected(id: number) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((x) => x !== id)
  } else {
    selectedIds.value.push(id)
  }
}

// Pagination state
const page = ref(1)
const perPage = ref(10)
const totalItems = computed(() => props.residencies.length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / perPage.value)))
const paginatedResidencies = computed(() => {
  const start = (page.value - 1) * perPage.value
  return props.residencies.slice(start, start + perPage.value)
})

function gotoPage(next: number) {
  page.value = Math.min(Math.max(1, next), totalPages.value)
}

function initial(name: string) {
  return (name?.trim()?.charAt(0) || '?').toUpperCase()
}

function applyFilters() {
  page.value = 1
  router.get('/admin/residency-certificates', {
    name: filterName.value || undefined,
    status: filterStatus.value || undefined,
    date_from: filterDateFrom.value || undefined,
    date_to: filterDateTo.value || undefined,
  }, { preserveState: true, replace: true })
}

function resetFilters() {
  filterName.value = ''
  filterStatus.value = ''
  filterDateFrom.value = ''
  filterDateTo.value = ''
  applyFilters()
}

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Residency Certificates', href: '/admin/residency-certificates' },
]

// legacy currentPage pagination is replaced by gotoPage above

function deleteResidency(id: number) {
  const ok = window.confirm('Are you sure you want to delete this residency certificate? This action cannot be undone.')
  if (!ok) return
  router.delete(`/admin/residency-certificates/${id}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Residency Certificates" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="text-sm text-[#2c4454]">All Certificates: <span class="font-semibold">{{ props.stats.total }}</span></div>
        <div class="flex items-center gap-4">
          <button class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] text-sm rounded-md hover:bg-gray-50">
            <Settings class="h-4 w-4" />
            <span>Table settings</span>
          </button>
        </div>
      </div>
      <div class="mt-2 text-lg font-semibold text-[#2c4454]">Certificate of Residency Applications</div>
      <div class="mt-4 flex items-center gap-2">
        <button class="px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">Approve selected</button>
        <button class="px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">Reject selected</button>
        <button class="px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">Delete selected</button>
      </div>
    </template>

    <div class="bp-theme">
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <PermitStatsCards :stats="props.stats" class="mb-6" />
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <!-- Filters Toolbar -->
              <div class="mb-4 rounded-md border border-[#2c4454]/20 bg-white p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                  <input v-model="filterName" type="text" placeholder="Search by name" class="w-full rounded-md border border-[#2c4454]/20 p-2 text-sm text-[#2c4454] focus:outline-none focus:ring-2 focus:ring-[#2c4454]/30" />
                  <select v-model="filterStatus" class="w-full rounded-md border border-[#2c4454]/20 p-2 text-sm text-[#2c4454] focus:outline-none focus:ring-2 focus:ring-[#2c4454]/30">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                  </select>
                  <input v-model="filterDateFrom" type="date" class="w-full rounded-md border border-[#2c4454]/20 p-2 text-sm text-[#2c4454] focus:outline-none focus:ring-2 focus:ring-[#2c4454]/30" />
                  <input v-model="filterDateTo" type="date" class="w-full rounded-md border border-[#2c4454]/20 p-2 text-sm text-[#2c4454] focus:outline-none focus:ring-2 focus:ring-[#2c4454]/30" />
                </div>
                <div class="mt-3 flex items-center gap-2">
                  <button class="px-3 py-2 rounded-md bg-[#2c4454] text-white text-sm hover:opacity-90" @click="applyFilters">Apply filters</button>
                  <button class="px-3 py-2 rounded-md bg-gray-200 text-[#2c4454] text-sm hover:bg-gray-300" @click="resetFilters">Reset</button>
                </div>
              </div>

              <!-- Data Table -->
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3">
                        <input type="checkbox" class="rounded" :checked="selectAll"
                          @change="toggleSelectAll(paginatedResidencies)" />
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">User</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Status</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Application Date</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Barangay</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Address</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Contact</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Remarks</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Last update</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in paginatedResidencies" :key="item.id" class="hover:bg-gray-50">
                      <td class="px-4 py-4">
                        <input type="checkbox" class="rounded" :checked="selectedIds.includes(item.id)"
                          @change="toggleSelected(item.id)" />
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                          <div class="h-8 w-8 rounded-full bg-[#2c4454]/10 text-[#2c4454] flex items-center justify-center font-semibold">
                            {{ initial(item.full_name) }}
                          </div>
                          <div>
                            <div class="text-sm font-medium text-[#2c4454]">{{ item.full_name || '—' }}</div>
                            <div class="text-xs text-[#2c4454]/70">{{ item.barangay || '—' }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="inline-flex items-center gap-2">
                          <span class="h-2 w-2 rounded-full" :class="{
                            'bg-green-500': item.status === 'approved',
                            'bg-yellow-500': item.status === 'pending',
                            'bg-blue-500': item.status === 'processing',
                            'bg-red-500': item.status === 'rejected',
                          }" />
                          <span class="text-xs text-[#2c4454] capitalize">{{ item.status }}</span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454]">{{ item.application_date || '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ item.barangay || '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ item.address_line || '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ item.contact_number || '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454]">{{ item.remarks || '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454]">{{ item.updated_at || '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <Link :href="`/admin/residency-certificates/${item.id}`" class="text-[#2c4454] hover:opacity-80" title="View">
                            <Eye class="h-5 w-5" />
                          </Link>
                          <button class="text-red-600 hover:opacity-80" title="Delete" @click="deleteResidency(item.id)">
                            <Trash2 class="h-5 w-5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-[#2c4454]">Page {{ page }} of {{ totalPages }}</div>
                <div class="flex items-center gap-2">
                  <button
                    class="inline-flex items-center gap-1 px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50"
                    :disabled="page === 1" @click="gotoPage(page - 1)"
                  >
                    <ChevronLeft class="h-4 w-4" />
                    <span>Prev</span>
                  </button>
                  <button
                    class="inline-flex items-center gap-1 px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50"
                    :disabled="page === totalPages" @click="gotoPage(page + 1)"
                  >
                    <ChevronRight class="h-4 w-4" />
                    <span>Next</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>