<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserPlus, Edit3, Trash2, Save, X, Search, CheckCircle2, XCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
  clerks: Array<{ id: number; name: string; email?: string | null; contact_number?: string | null; position?: string | null; active: boolean; created_at?: string; }>;
  filters: { search?: string | null; per_page?: number };
  pagination: { current_page: number; per_page: number; last_page: number; total: number };
}>();

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.per_page || 10);
const page = ref(props.pagination.current_page || 1);

const showCreate = ref(false);
const createForm = useForm({
  name: '',
  email: '',
  contact_number: '',
  position: '',
  active: true,
  password: '',
  password_confirmation: '',
});

function openCreate() { showCreate.value = true; }
function closeCreate() { showCreate.value = false; createForm.reset(); }

function submitCreate() {
  createForm.post(route('admin.clerks.store'), {
    preserveScroll: true,
    onSuccess: () => closeCreate(),
  });
}

const editingId = ref<number | null>(null);
const editForm = useForm({
  name: '',
  email: '',
  contact_number: '',
  position: '',
  active: true,
  password: '',
  password_confirmation: '',
});

function startEdit(c: any) {
  editingId.value = c.id;
  editForm.name = c.name || '';
  editForm.email = c.email || '';
  editForm.contact_number = c.contact_number || '';
  editForm.position = c.position || '';
  editForm.active = !!c.active;
}

function cancelEdit() { editingId.value = null; }
function submitUpdate(id: number) {
  editForm.patch(route('admin.clerks.update', id), {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null; },
  });
}

function deleteClerk(id: number) {
  if (!confirm('Delete this clerk?')) return;
  router.delete(route('admin.clerks.destroy', id), { preserveScroll: true });
}

function applyFilters(nextPage?: number) {
  router.get(route('admin.clerks'), { search: search.value, per_page: perPage.value, page: nextPage || page.value }, { preserveState: true, replace: true });
}

function gotoPage(p: number) { page.value = p; applyFilters(p); }
</script>

<template>
  <Head title="Clerks" />
  <AppLayout>
    <div class="px-6 py-6 space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-main">Clerks</h1>
          <p class="text-xs text-secondary">Manage barangay clerks and staff.</p>
        </div>
        <button class="inline-flex items-center gap-2 rounded-md bg-brand px-3 py-2 text-sm font-medium text-white shadow hover:bg-brand/90" @click="openCreate">
          <UserPlus class="h-4 w-4" />
          <span>Add Clerk</span>
        </button>
      </div>

      <div class="rounded-xl bg-white p-4 ring-1 ring-black/5">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-2 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2">
            <Search class="h-4 w-4 text-secondary" />
            <input v-model="search" type="text" placeholder="Search name, email, position" class="w-64 border-none p-0 text-sm focus:ring-0" @keyup.enter="applyFilters()" />
            <button class="rounded-md bg-secondary/20 px-3 py-1 text-xs text-main hover:bg-secondary/30" @click="applyFilters()">Apply</button>
          </div>
          <div class="flex items-center gap-2">
            <label class="text-sm text-secondary">Rows</label>
            <select v-model.number="perPage" class="rounded border border-[#2c4454]/20 px-2 py-1 text-sm" @change="applyFilters()">
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
        </div>

        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full divide-y divide-[#2c4454]/10">
            <thead>
              <tr class="bg-secondary/10">
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary">Position</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary">Active</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-secondary">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#2c4454]/10">
              <tr v-for="c in props.clerks" :key="c.id">
                <td class="px-6 py-3 whitespace-nowrap text-xs text-secondary">#{{ c.id }}</td>
                <td class="px-6 py-3">
                  <template v-if="editingId === c.id">
                    <input v-model="editForm.name" type="text" class="w-full rounded border border-[#2c4454]/20 px-2 py-1 text-sm" />
                  </template>
                  <template v-else>
                    <div class="text-sm font-medium text-main">{{ c.name }}</div>
                  </template>
                </td>
                <td class="px-6 py-3">
                  <template v-if="editingId === c.id">
                    <input v-model="editForm.email" type="email" class="w-full rounded border border-[#2c4454]/20 px-2 py-1 text-sm" />
                    <div class="mt-2 grid grid-cols-2 gap-2">
                      <div>
                        <label class="text-xs text-secondary">New Password</label>
                        <input v-model="editForm.password" type="password" class="mt-1 w-full rounded border border-[#2c4454]/20 px-2 py-1 text-sm" />
                        <div v-if="editForm.errors.password" class="mt-1 text-xs text-red-600">{{ editForm.errors.password }}</div>
                      </div>
                      <div>
                        <label class="text-xs text-secondary">Confirm Password</label>
                        <input v-model="editForm.password_confirmation" type="password" class="mt-1 w-full rounded border border-[#2c4454]/20 px-2 py-1 text-sm" />
                      </div>
                    </div>
                  </template>
                  <template v-else>
                    <div class="text-sm text-[#2c4454]">{{ c.email || '—' }}</div>
                  </template>
                </td>
                <td class="px-6 py-3">
                  <template v-if="editingId === c.id">
                    <input v-model="editForm.contact_number" type="text" class="w-full rounded border border-[#2c4454]/20 px-2 py-1 text-sm" />
                  </template>
                  <template v-else>
                    <div class="text-sm text-[#2c4454]">{{ c.contact_number || '—' }}</div>
                  </template>
                </td>
                <td class="px-6 py-3">
                  <template v-if="editingId === c.id">
                    <input v-model="editForm.position" type="text" class="w-full rounded border border-[#2c4454]/20 px-2 py-1 text-sm" />
                  </template>
                  <template v-else>
                    <div class="text-sm text-[#2c4454]">{{ c.position || '—' }}</div>
                  </template>
                </td>
                <td class="px-6 py-3">
                  <template v-if="editingId === c.id">
                    <label class="inline-flex items-center gap-2 text-sm text-[#2c4454]">
                      <input type="checkbox" v-model="editForm.active" />
                      <span>Active</span>
                    </label>
                  </template>
                  <template v-else>
                    <span class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs"
                      :class="c.active ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-red-50 text-red-700 ring-1 ring-red-200'">
                      <component :is="c.active ? CheckCircle2 : XCircle" class="h-3.5 w-3.5" />
                      <span>{{ c.active ? 'Active' : 'Inactive' }}</span>
                    </span>
                  </template>
                </td>
                <td class="px-6 py-3">
                  <div class="flex items-center gap-2">
                    <template v-if="editingId === c.id">
                      <button class="inline-flex items-center gap-1 rounded bg-brand px-3 py-1.5 text-xs font-medium text-white" @click="submitUpdate(c.id)">
                        <Save class="h-4 w-4" /> Save
                      </button>
                      <button class="inline-flex items-center gap-1 rounded bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5" @click="cancelEdit">
                        <X class="h-4 w-4" /> Cancel
                      </button>
                    </template>
                    <template v-else>
                      <button class="inline-flex items-center gap-1 rounded bg-white px-3 py-1.5 text-xs font-medium text-main ring-1 ring-black/5" @click="startEdit(c)">
                        <Edit3 class="h-4 w-4" /> Edit
                      </button>
                      <button class="inline-flex items-center gap-1 rounded bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-200" @click="deleteClerk(c.id)">
                        <Trash2 class="h-4 w-4" /> Delete
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-[#2c4454]">Page {{ pagination.current_page }} of {{ pagination.last_page }}</div>
            <div class="inline-flex items-center gap-2">
              <button class="inline-flex items-center gap-1 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50" :disabled="pagination.current_page === 1" @click="gotoPage(pagination.current_page - 1)">
                Prev
              </button>
              <span class="text-sm text-[#2c4454]">{{ pagination.current_page }}</span>
              <button class="inline-flex items-center gap-1 rounded-md border border-[#2c4454]/20 bg-white px-3 py-2 text-sm text-[#2c4454] hover:bg-gray-50" :disabled="pagination.current_page === pagination.last_page" @click="gotoPage(pagination.current_page + 1)">
                Next
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create Modal -->
      <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-lg">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-main">Add Clerk</h3>
            <button class="rounded bg-white px-2 py-1 text-xs text-main ring-1 ring-black/5" @click="closeCreate">Close</button>
          </div>

          <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="text-xs text-secondary">Name</label>
              <input v-model="createForm.name" type="text" class="mt-1 w-full rounded border border-[#2c4454]/20 px-2 py-2 text-sm" />
              <div v-if="createForm.errors.name" class="mt-1 text-xs text-red-600">{{ createForm.errors.name }}</div>
            </div>
            <div>
              <label class="text-xs text-secondary">Email</label>
              <input v-model="createForm.email" type="email" class="mt-1 w-full rounded border border-[#2c4454]/20 px-2 py-2 text-sm" />
              <div v-if="createForm.errors.email" class="mt-1 text-xs text-red-600">{{ createForm.errors.email }}</div>
            </div>
            <div>
              <label class="text-xs text-secondary">Contact Number</label>
              <input v-model="createForm.contact_number" type="text" class="mt-1 w-full rounded border border-[#2c4454]/20 px-2 py-2 text-sm" />
              <div v-if="createForm.errors.contact_number" class="mt-1 text-xs text-red-600">{{ createForm.errors.contact_number }}</div>
            </div>
            <div>
              <label class="text-xs text-secondary">Position</label>
              <input v-model="createForm.position" type="text" class="mt-1 w-full rounded border border-[#2c4454]/20 px-2 py-2 text-sm" />
              <div v-if="createForm.errors.position" class="mt-1 text-xs text-red-600">{{ createForm.errors.position }}</div>
            </div>
            <div>
              <label class="text-xs text-secondary">Password</label>
              <input v-model="createForm.password" type="password" class="mt-1 w-full rounded border border-[#2c4454]/20 px-2 py-2 text-sm" />
              <div v-if="createForm.errors.password" class="mt-1 text-xs text-red-600">{{ createForm.errors.password }}</div>
            </div>
            <div>
              <label class="text-xs text-secondary">Confirm Password</label>
              <input v-model="createForm.password_confirmation" type="password" class="mt-1 w-full rounded border border-[#2c4454]/20 px-2 py-2 text-sm" />
            </div>
            <div class="flex items-center gap-2">
              <input id="active" type="checkbox" v-model="createForm.active" />
              <label for="active" class="text-xs text-secondary">Active</label>
            </div>
          </div>

          <div class="mt-6 flex items-center justify-end gap-2">
            <button class="rounded bg-white px-3 py-2 text-xs text-main ring-1 ring-black/5" @click="closeCreate">Cancel</button>
            <button class="rounded bg-brand px-3 py-2 text-xs font-medium text-white" :disabled="createForm.processing" @click="submitCreate">Save</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>