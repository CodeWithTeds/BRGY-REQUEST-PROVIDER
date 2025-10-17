<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { ScrollText, Search, Info } from 'lucide-vue-next';
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
}

const props = defineProps<{
  logs: { data: LogItem[]; current_page: number; last_page: number; total: number; per_page: number };
  filters?: { action?: string | null; subject_type?: string | null };
}>();

const page = usePage();
const action = ref(props.filters?.action || '');
const subjectType = ref(props.filters?.subject_type || '');

function submitFilters() {
  const base = route('staff.activity-log');
  const params = new URLSearchParams();
  if (action.value) params.set('action', action.value);
  if (subjectType.value) params.set('subject_type', subjectType.value);
  window.location.href = `${base}?${params.toString()}`;
}

function subjectRoute(item: LogItem): string | null {
  if (!item.subject_type || !item.subject_id) return null;
  switch (item.subject_type) {
    case 'App\\Models\\BarangayPermit':
      return route('staff.business-permits.show', item.subject_id);
    case 'App\\Models\\BarangayClearance':
      return route('staff.barangay-clearances.show', item.subject_id);
    case 'App\\Models\\CertificateOfResidency':
      return route('staff.residency-certificates.show', item.subject_id);
    case 'App\\Models\\IndigencyCertificate':
      return route('staff.indigency-certificates.show', item.subject_id);
    default:
      return null;
  }
}

const userName = computed(() => page.props.auth?.user?.name || 'Staff');
</script>

<template>
  <Head title="Activity Log" />
  <AppLayout>
    <div class="px-6 py-6 space-y-6">
      <section class="rounded-2xl bg-gradient-to-r from-[#2c4454] to-[#356cd2] text-white">
        <div class="p-6 md:p-8">
          <div class="flex items-start justify-between">
            <div class="max-w-xl">
              <p class="text-xs uppercase tracking-wider/5 opacity-80">Staff Portal</p>
              <h1 class="mt-2 text-2xl md:text-3xl font-semibold leading-tight">
                Activity Log
              </h1>
              <p class="mt-2 text-sm opacity-90">Audit of actions performed by {{ userName }} and colleagues.</p>
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
          <div class="flex flex-wrap items-center gap-2 mb-4">
            <div class="flex items-center gap-2">
              <Input v-model="action" placeholder="Filter by action…" class="h-9 w-48" />
              <Input v-model="subjectType" placeholder="Filter by subject type…" class="h-9 w-64" />
            </div>
            <Button variant="secondary" size="sm" @click="submitFilters">
              <Search class="mr-2 h-4 w-4" /> Apply
            </Button>
          </div>

          <div class="overflow-hidden rounded-xl ring-1 ring-black/5 bg-white">
            <table class="min-w-full text-sm">
              <thead class="bg-neutral-50/80 text-neutral-600">
                <tr>
                  <th class="px-4 py-3 text-left">Time</th>
                  <th class="px-4 py-3 text-left">Action</th>
                  <th class="px-4 py-3 text-left">Subject</th>
                  <th class="px-4 py-3 text-left">Description</th>
                  <th class="px-4 py-3 text-left">Details</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.logs.data" :key="item.id" class="border-t border-neutral-100">
                  <td class="px-4 py-3 whitespace-nowrap text-neutral-600">{{ item.created_at }}</td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full bg-secondary/10 px-2 py-0.5 text-xs font-medium text-secondary">{{ item.action }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <span class="text-neutral-800">{{ item.subject_type || '—' }}</span>
                      <template v-if="subjectRoute(item)">
                        <Link :href="subjectRoute(item)!" class="text-secondary hover:underline">#{{ item.subject_id }}</Link>
                      </template>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-neutral-700">{{ item.description || '—' }}</td>
                  <td class="px-4 py-3">
                    <details class="text-neutral-700">
                      <summary class="cursor-pointer inline-flex items-center gap-2 text-secondary">
                        <Info class="h-4 w-4" /> Metadata
                      </summary>
                      <pre class="mt-2 max-w-xl overflow-x-auto rounded bg-neutral-50 p-2 text-xs">{{ JSON.stringify(item.metadata || {}, null, 2) }}</pre>
                      <div class="mt-2 text-[11px] text-neutral-500">IP: {{ item.ip_address || '—' }}</div>
                      <div class="text-[11px] text-neutral-500">UA: {{ item.user_agent || '—' }}</div>
                    </details>
                  </td>
                </tr>
                <tr v-if="props.logs.data.length === 0">
                  <td colspan="5" class="px-4 py-6 text-center text-neutral-500">No activity recorded yet.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex items-center justify-between text-xs text-neutral-600">
            <div>
              Page {{ props.logs.current_page }} of {{ props.logs.last_page }} • {{ props.logs.total }} total
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>