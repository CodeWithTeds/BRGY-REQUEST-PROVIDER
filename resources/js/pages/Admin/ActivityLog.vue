<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { ScrollText, Search, Info, Hash, User, FileText, Calendar, ListFilter } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface LogItem {
  id: number;
  action: string;
  subject_type?: string | null;
  subject_id?: number | null;
  description?: string | null;
  metadata?: Record<string, any> | null;
  ip_address?: string | null;
  user_agent?: string | null;
  created_at?: string | null;
  user?: { id: number; name: string; email?: string | null } | null;
  clerk?: { id: number; name?: string | null } | null;
}

const props = defineProps<{
  logs: { data: LogItem[]; current_page: number; last_page: number; total: number; per_page: number };
  filters?: { id?: number | null; action?: string | null; subject_type?: string | null; clerk_id?: number | null; permit_id?: number | null; date_from?: string | null; date_to?: string | null };
}>();

const page = usePage();
const id = ref(props.filters?.id ? String(props.filters?.id) : '');
const action = ref(props.filters?.action || '');
const subjectType = ref(props.filters?.subject_type || '');
const clerkId = ref(props.filters?.clerk_id ? String(props.filters?.clerk_id) : '');
const permitId = ref(props.filters?.permit_id ? String(props.filters?.permit_id) : '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const revertingId = ref<number | null>(null);
const isLoading = ref(false);
const showFilters = ref(true);

function resetFilters() {
  id.value = '';
  action.value = '';
  subjectType.value = '';
  clerkId.value = '';
  permitId.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  submitFilters();
}

function buildParams() {
  return {
    id: id.value || undefined,
    action: action.value || undefined,
    subject_type: subjectType.value || undefined,
    clerk_id: clerkId.value || undefined,
    permit_id: permitId.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  } as Record<string, string | number | undefined>;
}

function submitFilters() {
  const params = buildParams();
  isLoading.value = true;
  router.get(route('admin.activity-log'), params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    only: ['logs', 'filters'],
    onFinish: () => { isLoading.value = false; },
  });
}

function goToPage(page: number) {
  const params = { ...buildParams(), page } as Record<string, string | number | undefined>;
  isLoading.value = true;
  router.get(route('admin.activity-log'), params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    only: ['logs', 'filters'],
    onFinish: () => { isLoading.value = false; },
  });
}

function subjectRoute(item: LogItem): string | null {
  if (!item.subject_type || !item.subject_id) return null;
  switch (item.subject_type) {
    case 'App\\Models\\BarangayPermit':
      return route('admin.business-permits.show', item.subject_id);
    case 'App\\Models\\BarangayClearance':
      return route('admin.barangay-clearances.show', item.subject_id);
    case 'App\\Models\\CertificateOfResidency':
      return route('admin.residency-certificates.show', item.subject_id);
    case 'App\\Models\\IndigencyCertificate':
      return route('admin.indigency-certificates.show', item.subject_id);
    default:
      return null;
  }
}

function revert(item: LogItem) {
  if (item.action !== 'status_updated') return;
  if (!confirm('Revert this status change?')) return;
  revertingId.value = item.id;
  router.post(route('admin.activity-log.revert', item.id), {}, {
    preserveScroll: true,
    onFinish: () => { revertingId.value = null; },
  });
}

const userName = computed(() => page.props.auth?.user?.name || 'Admin');
</script>

<template>

  <Head title="Activity Log" />
  <AppLayout>
    <div class="px-6 py-6 space-y-6">
      <section class="rounded-2xl bg-gradient-to-r from-[#2c4454] to-[#356cd2] text-white">
        <div class="p-6 md:p-8">
          <div class="flex items-start justify-between">
            <div class="max-w-xl">
              <p class="text-xs uppercase tracking-wider/5 opacity-80">Admin Portal</p>
              <h1 class="mt-2 text-2xl md:text-3xl font-semibold leading-tight">
                Activity Log
              </h1>
              <p class="mt-2 text-sm opacity-90">Audit of actions performed by staff and admins.</p>
            </div>
          </div>
        </div>
      </section>

      <Card class="border-none shadow-none">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-main">
            <ScrollText class="h-5 w-5 text-secondary" /> Recent Activity
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="mb-4 rounded-xl bg-white ring-1 ring-black/5 shadow-sm">
            <div class="flex items-center justify-between gap-2 p-3">
              <div class="flex items-center gap-2 text-sm text-neutral-700">
                <ListFilter class="h-4 w-4 text-secondary" />
                <span class="font-medium">Filters</span>
              </div>
              <div class="flex items-center gap-2">
                <Button variant="ghost" size="sm" @click="showFilters = !showFilters">
                  {{ showFilters ? 'Hide' : 'Show' }}
                </Button>
              </div>
            </div>

            <div v-show="showFilters" class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 border-t border-neutral-100">
              <div>
                <Label class="text-xs text-neutral-600">ID</Label>
                <div class="relative">
                  <Hash class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                  <Input v-model="id" placeholder="e.g. 123" class="h-9 w-full pl-9" @keydown.enter="submitFilters" />
                </div>
              </div>

              <div>
                <Label class="text-xs text-neutral-600">Action</Label>
                <div class="relative">
                  <ListFilter class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                  <Input v-model="action" placeholder="e.g. status_updated" class="h-9 w-full pl-9" @keydown.enter="submitFilters" />
                </div>
              </div>

              <div>
                <Label class="text-xs text-neutral-600">Subject Type</Label>
                <div class="relative">
                  <ScrollText class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                  <Input v-model="subjectType" placeholder="e.g. App\\Models\\BarangayPermit" class="h-9 w-full pl-9" @keydown.enter="submitFilters" />
                </div>
              </div>

              <div>
                <Label class="text-xs text-neutral-600">Clerk ID</Label>
                <div class="relative">
                  <User class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                  <Input v-model="clerkId" placeholder="e.g. 45" class="h-9 w-full pl-9" @keydown.enter="submitFilters" />
                </div>
              </div>

              <div>
                <Label class="text-xs text-neutral-600">Permit ID</Label>
                <div class="relative">
                  <FileText class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                  <Input v-model="permitId" placeholder="e.g. 789" class="h-9 w-full pl-9" @keydown.enter="submitFilters" />
                </div>
              </div>

              <div>
                <Label class="text-xs text-neutral-600">Date Range</Label>
                <div class="grid grid-cols-2 gap-2">
                  <div class="relative">
                    <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                    <Input v-model="dateFrom" type="date" placeholder="From" class="h-9 w-full pl-9" />
                  </div>
                  <div class="relative">
                    <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                    <Input v-model="dateTo" type="date" placeholder="To" class="h-9 w-full pl-9" />
                  </div>
                </div>
              </div>
            </div>

            <div class="p-3 flex items-center justify-end gap-2 border-t border-neutral-100">
              <Button variant="outline" size="sm" @click="resetFilters">Reset</Button>
              <Button variant="secondary" size="sm" :disabled="isLoading" @click="submitFilters">
                <Search class="mr-2 h-4 w-4" /> {{ isLoading ? 'Applying…' : 'Apply' }}
              </Button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-xl ring-1 ring-black/5 bg-white">
            <table class="min-w-full text-sm">
              <thead class="bg-neutral-50/80 text-neutral-600">
                <tr>
                  <th class="px-4 py-3 text-left">Time</th>
                  <th class="px-4 py-3 text-left">Action</th>
                  <th class="px-4 py-3 text-left">Subject</th>
                  <th class="px-4 py-3 text-left">Actor</th>
                  <th class="px-4 py-3 text-left">Description</th>
                  <th class="px-4 py-3 text-left">Actions</th>
                  <th class="px-4 py-3 text-left">Details</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.logs.data" :key="item.id" class="border-t border-neutral-100">
                  <td class="px-4 py-3 whitespace-nowrap text-neutral-600">{{ item.created_at }}</td>
                  <td class="px-4 py-3">
                    <span
                      class="inline-flex items-center rounded-full bg-secondary/10 px-2 py-0.5 text-xs font-medium text-secondary">{{
                        item.action }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <span class="text-neutral-800">{{ item.subject_type || '—' }}</span>
                      <template v-if="subjectRoute(item)">
                        <Link :href="subjectRoute(item)!" class="text-secondary hover:underline">#{{ item.subject_id }}
                        </Link>
                      </template>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-neutral-700">
                    <div class="flex flex-col">
                      <span class="text-neutral-800">{{ item.clerk?.name || item.user?.name || '—' }}</span>
                      <span class="text-[11px] text-neutral-500">User: {{ item.user?.email || '—' }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-neutral-700">{{ item.description || '—' }}</td>
                  <td class="px-4 py-3">
                    <Button v-if="item.action === 'status_updated' && !(item.metadata && item.metadata.reverted)" size="sm" variant="outline"
                      :disabled="revertingId === item.id" @click="revert(item)">
                      {{ revertingId === item.id ? 'Reverting…' : 'Revert' }}
                    </Button>
                    <span v-else-if="item.action === 'status_updated' && (item.metadata && item.metadata.reverted)"
                      class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Reverted</span>
                  </td>
                  <td class="px-4 py-3">
                    <details class="text-neutral-700">
                      <summary class="cursor-pointer inline-flex items-center gap-2 text-secondary">
                        <Info class="h-4 w-4" /> Metadata
                      </summary>
                      <pre class="mt-2 max-w-xl overflow-x-auto rounded bg-neutral-50 p-2 text-xs">{{ JSON.stringify(item.metadata || {},
                        null, 2) }}</pre>
                      <div class="mt-2 text-[11px] text-neutral-500">IP: {{ item.ip_address || '—' }}</div>
                      <div class="text-[11px] text-neutral-500">UA: {{ item.user_agent || '—' }}</div>
                    </details>
                  </td>
                </tr>
                <tr v-if="props.logs.data.length === 0">
                  <td colspan="7" class="px-4 py-6 text-center text-neutral-500">No activity recorded yet.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex items-center justify-between text-xs text-neutral-600">
            <div>
              Page {{ props.logs.current_page }} of {{ props.logs.last_page }} • {{ props.logs.total }} total
            </div>
            <div class="flex items-center gap-2">
              <Button size="sm" variant="outline" :disabled="props.logs.current_page <= 1 || isLoading"
                @click="goToPage(props.logs.current_page - 1)">Prev</Button>
              <Button size="sm" variant="outline"
                :disabled="props.logs.current_page >= props.logs.last_page || isLoading"
                @click="goToPage(props.logs.current_page + 1)">Next</Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>