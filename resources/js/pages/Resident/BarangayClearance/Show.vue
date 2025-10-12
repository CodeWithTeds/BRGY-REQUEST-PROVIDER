<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

type Doc = { id: number; document_type: string; file_path: string | null; verified: boolean }
type Address = {
  id: number
  type: string | null
  line: string | null
  barangay?: string | null
  city?: string | null
  province?: string | null
  region?: string | null
  zip_code?: string | null
}

const props = defineProps<{
  clearance: {
    id: number
    full_name?: string | null
    application_date?: string | null
    status: 'pending' | 'processing' | 'approved' | 'rejected'
    purpose?: string | null
    remarks?: string | null
    applicant_profile?: Record<string, any>
    addresses?: Address[]
    supporting_documents?: Doc[]
  }
}>()

const statusLabel = (s: string) => {
  switch (s) {
    case 'pending': return 'Pending'
    case 'processing': return 'Processing'
    case 'approved': return 'Approved'
    case 'rejected': return 'Rejected'
    default: return s
  }
}

// Build breadcrumbs safely to avoid runtime errors if id is missing
const breadcrumbs = [
  { title: 'Barangay Clearance', href: route('resident.barangay-clearance') },
  { title: 'Details', href: (props.clearance && props.clearance.id) ? route('barangay-clearance.show', props.clearance.id) : '#' },
]
</script>

<template>
  <Head title="Barangay Clearance Details" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-semibold text-[#2C4854]">Application Details</h1>
          <Link :href="route('resident.barangay-clearance')">
            <Button variant="outline">Back to List</Button>
          </Link>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Summary</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3 text-[#2C4854]">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <div class="text-xs opacity-70">Applicant</div>
                <div class="text-sm">{{ props.clearance.full_name || '—' }}</div>
              </div>
              <div>
                <div class="text-xs opacity-70">Application Date</div>
                <div class="text-sm">{{ props.clearance.application_date || '—' }}</div>
              </div>
              <div>
                <div class="text-xs opacity-70">Status</div>
                <span :class="{
                  'bg-yellow-100 text-yellow-800': props.clearance.status === 'pending',
                  'bg-blue-100 text-blue-800': props.clearance.status === 'processing',
                  'bg-green-100 text-green-800': props.clearance.status === 'approved',
                  'bg-red-100 text-red-800': props.clearance.status === 'rejected',
                }" class="inline-block px-2 py-1 rounded text-xs">{{ statusLabel(props.clearance.status) }}</span>
              </div>
              <div>
                <div class="text-xs opacity-70">Purpose</div>
                <div class="text-sm">{{ props.clearance.purpose || '—' }}</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Address</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="props.clearance.addresses && props.clearance.addresses.length" class="space-y-2">
              <div v-for="addr in props.clearance.addresses" :key="addr.id" class="rounded border border-[#2C4854]/20 p-3">
                <div class="text-sm text-[#2C4854]">{{ addr.line || '—' }}</div>
                <div class="text-xs text-[#2C4854] opacity-70">{{ [addr.barangay, addr.city, addr.province, addr.region].filter(Boolean).join(', ') }}</div>
                <div class="text-xs text-[#2C4854] opacity-70">Zip: {{ addr.zip_code || '—' }}</div>
              </div>
            </div>
            <p v-else class="text-sm text-[#2C4854] opacity-70">No address on record.</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Supporting Documents</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="props.clearance.supporting_documents && props.clearance.supporting_documents.length" class="space-y-2">
              <div v-for="doc in props.clearance.supporting_documents" :key="doc.id" class="flex items-center justify-between rounded border border-[#2C4854]/20 p-3">
                <div>
                  <div class="text-sm text-[#2C4854]">{{ doc.document_type }}</div>
                  <div class="text-xs text-[#2C4854] opacity-70">{{ doc.file_path || '—' }}</div>
                </div>
                <div>
                  <a v-if="doc.file_path" :href="route('admin.barangay-clearances.documents.view', { id: props.clearance.id, docId: doc.id })" target="_blank" rel="noopener" class="text-xs text-[#2C4854] hover:underline">View</a>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-[#2C4854] opacity-70">No supporting documents uploaded.</p>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>