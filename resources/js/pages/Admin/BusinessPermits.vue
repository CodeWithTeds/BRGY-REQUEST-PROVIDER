<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { Eye, Trash2, Settings, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import PermitStatsCards from '@/components/Admin/PermitStatsCards.vue';
import { ref, computed } from 'vue';
import Toastify from 'toastify-js';

interface Permit {
  id: number;
  full_name: string;
  application_date: string;
  status: 'approved' | 'pending' | 'processing' | 'rejected';
  created_at: string;
  updated_at: string;
  gender?: string | null;
  citizenship?: string | null;
  contact_number?: string | null;
  barangay?: string | null;
  address_line?: string | null;
  remarks?: string | null;
}

interface Stats {
  total: number;
  approved: number;
  pending: number;
  rejected: number;
}

// Use props inside script
const props = defineProps<{ permits: Permit[]; stats: Stats }>();

// Selection state
const selectedIds = ref<number[]>([]);
const selectAll = ref(false);

function toggleSelectAll(items: Permit[]) {
  selectAll.value = !selectAll.value;
  selectedIds.value = selectAll.value ? items.map((p) => p.id) : [];
}

function toggleSelected(id: number) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((x) => x !== id);
  } else {
    selectedIds.value.push(id);
  }
}

// Pagination state
const page = ref(1);
const perPage = ref(10);

const totalItems = computed(() => props.permits.length);
const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / perPage.value)));

const paginatedPermits = computed(() => {
  const start = (page.value - 1) * perPage.value;
  return props.permits.slice(start, start + perPage.value);
});

function gotoPage(next: number) {
  page.value = Math.min(Math.max(1, next), totalPages.value);
}

function initial(name: string) {
  return (name?.trim()?.charAt(0) || '?').toUpperCase();
}


function deletePermit(id: number) {
  if (!confirm('Delete this Barangay Business Permit?')) return;
  router.delete(`/admin/business-permits/${id}`, {
    onSuccess: () => {
      Toastify({
        text: 'Deleted Barangay Business Permit!',
        duration: 3000,
        gravity: 'top',
        position: 'right',
        backgroundColor: '#16a34a',
        close: true,
      }).showToast();
    },
    onError: () => {
      Toastify({
        text: 'Delete failed. Please try again.',
        duration: 3000,
        gravity: 'top',
        position: 'right',
        backgroundColor: '#dc2626',
        close: true,
      }).showToast();
    },
  });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Business Permits', href: '/admin/business-permits' },
];
</script>

<template>

  <Head title="Business Permits" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="text-sm text-[#2c4454]">All Permits: <span class="font-semibold">{{ props.stats.total }}</span>
        </div>
        <div class="flex items-center gap-4">
          <button
            class="inline-flex items-center gap-2 px-3 py-2 bg-[#2c4454] text-white text-sm rounded-md hover:opacity-90">
            <span class="text-white">+ Add new permit</span>
          </button>
          <button
            class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] text-sm rounded-md hover:bg-gray-50">
            <Settings class="h-4 w-4" />
            <span>Table settings</span>
          </button>
        </div>
      </div>
      <div class="mt-2 text-lg font-semibold text-[#2c4454]">Barangay Business Permit</div>
      <div class="mt-4 flex items-center gap-2">
        <button
          class="px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">Approve
          selected</button>
        <button
          class="px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">Reject
          selected</button>
        <button
          class="px-3 py-2 bg-white border border-[#2c4454]/20 text-[#2c4454] rounded-md text-sm hover:bg-gray-50">Delete
          selected</button>
      </div>
      <!-- Modern Thin Stat Cards moved to main content below -->
    </template>

    <div class="bp-theme">
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <!-- Thin Stat Cards (now a reusable component) -->
          <PermitStatsCards :stats="props.stats" class="mb-6" />
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3">
                        <input type="checkbox" class="rounded" :checked="selectAll"
                          @change="toggleSelectAll(paginatedPermits)" />
                      </th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        User</th>
                      <th scope="col"
                        class="w-24 px-3 py-2 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Status</th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Application Date</th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Barangay</th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Address</th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Contact</th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Citizenship</th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Remarks</th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Last update</th>
                      <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider opacity-70">
                        Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="permit in paginatedPermits" :key="permit.id" class="hover:bg-gray-50">
                      <td class="px-4 py-4">
                        <input type="checkbox" class="rounded" :checked="selectedIds.includes(permit.id)"
                          @change="toggleSelected(permit.id)" />
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                          <div
                            class="h-8 w-8 rounded-full bg-[#2c4454]/10 text-[#2c4454] flex items-center justify-center font-semibold">
                            {{ initial(permit.full_name) }}
                          </div>
                          <div>
                            <div class="text-sm font-medium text-[#2c4454]">{{ permit.full_name }}</div>
                            <div class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                              :class="{
                                'bg-purple-100 text-purple-800': permit.gender === 'Female',
                                'bg-blue-100 text-blue-800': permit.gender === 'Male',
                                'bg-gray-100 text-[#2c4454]': !permit.gender,
                              }">
                              {{ permit.gender || 'Unknown' }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-3 py-2 whitespace-nowrap">
                        <div class="flex items-center gap-1">
                          <span class="h-2 w-2 rounded-full" :class="{
                            'bg-green-500': permit.status === 'approved',
                            'bg-yellow-500': permit.status === 'pending',
                            'bg-blue-500': permit.status === 'processing',
                            'bg-red-500': permit.status === 'rejected',
                          }" />
                          <span class="text-xs text-[#2c4454] capitalize">{{ permit.status }}</span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{
                        permit.application_date
                        }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ permit.barangay ||
                        '—' }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ permit.address_line
                        ||
                        '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ permit.contact_number
                        ||
                        '—' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ permit.citizenship ||
                        '—'
                        }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ permit.remarks || '—'
                        }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2c4454] opacity-80">{{ permit.updated_at }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <a :href="`/admin/business-permits/${permit.id}`" class="text-[#2c4454] hover:opacity-80"
                            title="View">
                            <Eye class="h-5 w-5" />
                          </a>
                          <button class="text-red-600 hover:opacity-80" title="Delete" @click="deletePermit(permit.id)">
                            <Trash2 class="h-5 w-5" />
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
                  <select v-model.number="perPage" class="border border-[#2c4454]/20 rounded px-2 py-1 text-sm">
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                  </select>
                  <span class="ml-2">{{ (page - 1) * perPage + 1 }}-{{ Math.min(page * perPage, totalItems) }} of {{
                    totalItems }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <button class="px-3 py-2 border border-[#2c4454]/20 rounded hover:bg-gray-50"
                    @click="gotoPage(page - 1)">
                    <ChevronLeft class="h-4 w-4 text-[#2c4454]" />
                  </button>
                  <span class="text-sm text-[#2c4454]">{{ page }}</span>
                  <button class="px-3 py-2 border border-[#2c4454]/20 rounded hover:bg-gray-50"
                    @click="gotoPage(page + 1)">
                    <ChevronRight class="h-4 w-4 text-[#2c4454]" />
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

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

.bp-theme {
  font-family: 'Space Grotesk', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
}
</style>