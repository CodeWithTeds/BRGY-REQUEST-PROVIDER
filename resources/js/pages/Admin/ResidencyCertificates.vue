<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Eye, Trash2, FileText, Search } from 'lucide-vue-next';
import Toastify from 'toastify-js';

interface CertificateListItem {
  id: number;
  full_name: string;
  application_date?: string | null;
  status: 'approved' | 'pending' | 'rejected' | 'processing' | 'pre-approved' | string;
}

interface Pagination {
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{ certificates: CertificateListItem[]; pagination: Pagination; routeGroup?: string; canDelete?: boolean }>();

const basePath = computed(() => `/${props.routeGroup ?? 'admin'}/residency-certificates`);

const query = ref('');
const statusFilter = ref<string>('');
const currentPage = ref<number>(1);

onMounted(() => {
  // Initial fetch already provided by server; keep filters in sync
});

function notify(message: string, variant: 'success' | 'error' | 'info' = 'info') {
  const bg = variant === 'success'
    ? 'linear-gradient(to right, #00b09b, #96c93d)'
    : variant === 'error'
      ? 'linear-gradient(to right, #ef4444, #b91c1c)'
      : 'linear-gradient(to right, #3b82f6, #2563eb)';
  Toastify({
    text: message,
    duration: 2500,
    close: true,
    gravity: 'top',
    position: 'right',
    backgroundColor: bg,
    stopOnFocus: true,
  }).showToast();
}

function applyFilters(page: number = 1) {
  currentPage.value = page;
  const params: Record<string, string> = {};
  if (query.value) params.search = query.value;
  if (statusFilter.value) params.status = statusFilter.value;
  router.get(basePath.value, params, { preserveScroll: true, preserveState: true });
}

function parsePage(url: string | null): number {
  if (!url) return 1;
  try {
    const u = new window.URL(url, window.location.origin);
    const p = u.searchParams.get('page');
    return p ? parseInt(p) : 1;
  } catch {
    return 1;
  }
}

function viewCertificate(id: number) {
  router.get(`${basePath.value}/${id}`);
}

function deleteCertificate(id: number) {
  if (!props.canDelete) return;
  if (!confirm('Delete this certificate?')) return;
  router.delete(`${basePath.value}/${id}`, {
    onSuccess: () => notify('Certificate deleted.', 'success'),
    onError: () => notify('Failed to delete certificate.', 'error'),
  });
}

const statusChip = (s: string) => {
  switch (s) {
    case 'approved': return 'bg-green-100 text-green-700 ring-green-200';
    case 'pending': return 'bg-yellow-100 text-yellow-700 ring-yellow-200';
    case 'processing': return 'bg-blue-100 text-blue-700 ring-blue-200';
    case 'pre-approved': return 'bg-indigo-100 text-indigo-700 ring-indigo-200';
    case 'rejected': return 'bg-red-100 text-red-700 ring-red-200';
    default: return 'bg-gray-100 text-gray-700 ring-gray-200';
  }
};

const breadcrumbs = computed(() => [
  { title: props.routeGroup === 'staff' ? 'Staff' : 'Admin', href: props.routeGroup === 'staff' ? '/staff/dashboard' : '/admin/dashboard' },
  { title: 'Residency Certificates', href: basePath.value },
]);
</script>

<template>
  <Head :title="props.routeGroup === 'staff' ? 'Staff: Residency Certificates' : 'Residency Certificates'" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-[#2c4454]">Residency Certificates</h1>
          <p class="text-sm text-[#2c4454] opacity-70">Manage residency certificate applications</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="basePath" class="inline-flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50">
            <FileText class="h-4 w-4" />
            List
          </Link>
        </div>
      </div>
    </template>

    <div class="p-6 space-y-4">
      <div class="flex items-center gap-2">
        <div class="relative flex-1">
          <Search class="absolute left-2 top-2.5 h-4 w-4 text-[#2c4454] opacity-60" />
          <input v-model="query" @keyup.enter="applyFilters()" type="text" placeholder="Search by name" class="w-full rounded-md border border-[#2c4454]/20 pl-8 pr-3 py-2 text-sm text-[#2c4454] focus:outline-none focus:ring-2 focus:ring-[#2c4454]/30" />
        </div>
        <select v-model="statusFilter" @change="applyFilters()" class="rounded-md border border-[#2c4454]/20 px-3 py-2 text-sm text-[#2c4454] focus:outline-none focus:ring-2 focus:ring-[#2c4454]/30">
          <option value="">All statuses</option>
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="pre-approved">Pre-Approved</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <button @click="applyFilters()" class="px-3 py-2 rounded-md bg-[#2c4454] text-white text-sm hover:opacity-90">Apply</button>
      </div>

      <div class="overflow-hidden rounded-lg border border-[#2c4454]/20">
        <table class="min-w-full divide-y divide-[#2c4454]/20">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider">ID</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider">Name</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider">Applied</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-[#2c4454] uppercase tracking-wider">Status</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-[#2c4454] uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-[#2c4454]/10">
            <tr v-for="c in props.certificates" :key="c.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 text-sm text-[#2c4454]">#{{ c.id }}</td>
              <td class="px-4 py-2 text-sm text-[#2c4454]">{{ c.full_name }}</td>
              <td class="px-4 py-2 text-sm text-[#2c4454]">{{ c.application_date || '—' }}</td>
              <td class="px-4 py-2">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium ring-1" :class="statusChip(c.status)">
                  <span class="capitalize">{{ c.status }}</span>
                </span>
              </td>
              <td class="px-4 py-2">
                <div class="flex items-center justify-end gap-2">
                  <button @click="viewCertificate(c.id)" class="inline-flex items-center gap-1 rounded-md border border-[#2c4454]/20 bg-white px-3 py-1 text-xs text-[#2c4454] hover:bg-gray-50">
                    <Eye class="h-4 w-4" />
                    View
                  </button>
                  <button v-if="props.canDelete" @click="deleteCertificate(c.id)" class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-3 py-1 text-xs text-red-700 hover:bg-red-100">
                    <Trash2 class="h-4 w-4" />
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between">
        <div class="text-xs text-[#2c4454] opacity-70">Page {{ currentPage }}</div>
        <div class="flex items-center gap-1">
          <button
            v-for="link in props.pagination.links"
            :key="link.label"
            :disabled="!link.url"
            @click="link.url ? applyFilters(parsePage(link.url)) : null"
            class="px-3 py-1 rounded-md border border-[#2c4454]/20 bg-white text-xs text-[#2c4454] hover:bg-gray-50 disabled:opacity-50"
          >
            {{ link.label }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>