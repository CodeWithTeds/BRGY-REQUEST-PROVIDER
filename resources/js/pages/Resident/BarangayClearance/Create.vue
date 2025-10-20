<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useBarangayClearanceForm } from '@/composables/useBarangayClearanceForm'
import PsgcAddressSelector from '@/components/address/PsgcAddressSelector.vue'
import { Head, router } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { ref, computed } from 'vue'
import Toastify from 'toastify-js'

const {
    form,
    civilStatusLabel,
    genderLabel,
    documentTypeLabel,
    addressTypeLabel,
} = useBarangayClearanceForm()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Barangay Clearance',
        href: route('resident.barangay-clearance'),
    },
    {
        title: 'Apply',
        href: route('barangay-clearance.create'),
    },
];

// Define lightweight ApplicantProfile type and accept from backend
type ApplicantProfileProps = {
    first_name?: string | null;
    middle_name?: string | null;
    last_name?: string | null;
    suffix?: string | null;
    date_of_birth?: string | null;
    place_of_birth?: string | null;
    civil_status?: 'single' | 'married' | 'widowed' | 'separated' | string | null;
    gender?: 'male' | 'female' | 'other' | string | null;
    citizenship?: string | null;
    contact_number?: string | null;
} | null

const props = defineProps<{
    barangays: Array<{ code: string; name: string }>,
    regions: Array<{ code: string; name: string }>,
    applicantProfile?: ApplicantProfileProps,
}>()

// Prefill form from applicantProfile if available
if (props.applicantProfile) {
    const ap = props.applicantProfile
    form.first_name = ap?.first_name ?? ''
    form.middle_name = ap?.middle_name ?? ''
    form.last_name = ap?.last_name ?? ''
    // suffix not part of this form state
    form.date_of_birth = ap?.date_of_birth ?? ''
    form.place_of_birth = ap?.place_of_birth ?? ''
    form.civil_status = ap?.civil_status ? String(ap.civil_status).toLowerCase() : form.civil_status
    form.gender = ap?.gender ? String(ap.gender).toLowerCase() : form.gender
    form.citizenship = ap?.citizenship ?? ''
    form.contact_number = ap?.contact_number ?? ''
}

// Local ref for additional Valid ID document
const validIdDocument = ref<File | null>(null)
// Use a generic index signature for dynamic error keys in template
const extraErrors = computed(() => form.errors as Record<string, string>)

// Wizard step state similar to Permit
const steps = ['Personal Information', 'PSGC Address', 'Supporting Documents']
const currentStep = ref(0)
const isFirstStep = computed(() => currentStep.value === 0)
const isLastStep = computed(() => currentStep.value === steps.length - 1)

function requiredFieldsForStep(step: number): string[] {
    if (step === 0) {
        return [
            'first_name',
            'last_name',
            'date_of_birth',
            'place_of_birth',
            'civil_status',
            'gender',
            'citizenship',
            'contact_number',
            'purpose',
        ]
    }
    if (step === 1) {
        return [
            'address_type',
            'region_code',
            'province_code',
            'city_code',
            'barangay_code',
            'house_no',
            'street',
            'zip_code',
        ]
    }
    return ['document_type', 'document']
}

const canProceed = computed(() => {
    const required = requiredFieldsForStep(currentStep.value)
    return required.every((field) => {
        const value = (form as any)[field]
        return value !== undefined && value !== null && value !== ''
    })
})

function markMissingErrors(fields: string[]) {
    const errs = form.errors as Record<string, string>
    fields.forEach((f) => {
        const value = (form as any)[f]
        if (value === undefined || value === null || value === '') {
            errs[f] = 'This field is required'
        } else {
            delete errs[f]
        }
    })
}

function goNext() {
    const required = requiredFieldsForStep(currentStep.value)
    markMissingErrors(required)
    if (!canProceed.value) {
        Toastify({
            text: 'Please complete all required fields before proceeding.',
            duration: 3500,
            gravity: 'top',
            position: 'right',
            backgroundColor: '#dc2626',
            close: true,
        }).showToast()
        return
    }
    currentStep.value = Math.min(currentStep.value + 1, steps.length - 1)
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goBack() {
    currentStep.value = Math.max(currentStep.value - 1, 0)
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Client-side validations and helpers
const MAX_FILE_BYTES = 200000
const localErrors = ref<Record<string, string>>({})
const errorFor = (key: string) => localErrors.value[key] || (form.errors as Record<string, string>)[key]
function setLocalError(key: string, msg: string) { localErrors.value[key] = msg }
function clearLocalError(key: string) { delete localErrors.value[key] }
function handleFileChange(key: string, file: File | null, assign: (f: File | null) => void) {
  if (!file) { assign(null); clearLocalError(key); return }
  if (file.size > MAX_FILE_BYTES) { assign(null); setLocalError(key, 'File must be 200000 bytes or smaller') } else { assign(file); clearLocalError(key) }
}
function sanitizeName(value: string) { return value.replace(/\d+/g, '') }
function enforceDigits(value: string, max: number) { return value.replace(/\D/g, '').slice(0, max) }
function onNameInput(key: 'first_name'|'middle_name'|'last_name', e: Event) {
  const target = e.target as HTMLInputElement
  ;(form as any)[key] = sanitizeName(target.value)
}
function preventDigitKeydown(e: KeyboardEvent) { if (/\d/.test(e.key)) e.preventDefault() }
function onContactInput(e: Event) { const t = e.target as HTMLInputElement; form.contact_number = enforceDigits(t.value, 11) }
function onZipInput(e: Event) { const t = e.target as HTMLInputElement; form.zip_code = enforceDigits(t.value, 4) }

// Filter errors to those relevant to the current step
const visibleErrorKeys = computed(() => {
  const stepKeys: string[] = []
  if (currentStep.value === 0) {
    stepKeys.push('first_name','middle_name','last_name','date_of_birth','place_of_birth','civil_status','gender','citizenship','contact_number','email','purpose')
  } else if (currentStep.value === 1) {
    stepKeys.push('address_type','region_code','province_code','city_code','barangay_code','house_no','street','purok','zip_code')
  } else if (currentStep.value === 2) {
    stepKeys.push('document_type','document','valid_id_document')
  }
  const errs = form.errors as Record<string, string>
  return stepKeys.filter(k => !!errs[k])
})

function submit() {
    form.clearErrors()
    // Attach additional Valid ID file via transform
    form.transform((data) => ({
        ...data,
        valid_id_document: validIdDocument.value,
    }))
    form.post(route('barangay-clearance.store'), {
        preserveScroll: true,
        forceFormData: true,
        headers: {
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json'
        },
        onError: (errors) => {
            console.error('Form submission failed:', errors)
            Toastify({
                text: 'Submission failed. Please check required fields and files.',
                duration: 4500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#dc2626',
                close: true,
            }).showToast()
        },
        onSuccess: () => {
            Toastify({
                text: 'Application submitted successfully! We\'ll notify you once processed.',
                duration: 4000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#16a34a',
                close: true,
            }).showToast()

            // Navigate to resident clearance landing instead of staying on create
            router.get(route('resident.barangay-clearance'))
        },
        onFinish: () => {
            if (!form.hasErrors) {
                form.reset()
                validIdDocument.value = null
            }
        }
    })
}
</script>

<template>
    <Head title="Apply for Barangay Clearance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Barangay Clearance Application</CardTitle>
                            <CardDescription>Fill out the form to apply for a barangay clearance.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Wizard Progress -->
                            <div class="mt-2">
                                <div class="flex items-center justify-between">
                                    <div v-for="(label, idx) in steps" :key="label" class="flex-1 text-center">
                                        <div class="flex items-center justify-center">
                                            <div class="flex-1 h-1" :class="idx > 0 ? (idx <= currentStep ? 'bg-emerald-500' : 'bg-neutral-300') : 'bg-transparent'"></div>
                                            <div class="relative">
                                                <div :class="[
                                                    'w-10 h-10 rounded-full flex items-center justify-center border text-sm font-semibold',
                                                    idx < currentStep ? 'bg-emerald-500 text-white border-emerald-500' : idx === currentStep ? 'border-emerald-500 text-emerald-600' : 'border-neutral-300 text-neutral-400'
                                                ]">
                                                    {{ idx + 1 }}
                                                </div>
                                            </div>
                                            <div class="flex-1 h-1" :class="idx < steps.length - 1 ? (idx < currentStep ? 'bg-emerald-500' : 'bg-neutral-300') : 'bg-transparent'"></div>
                                        </div>
                                        <p :class="[
                                            'mt-2 text-sm font-medium',
                                            idx <= currentStep ? 'text-emerald-700' : 'text-neutral-400'
                                        ]">{{ label }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="visibleErrorKeys.length > 0 || errorFor('error')" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
    {{ errorFor('error') || 'Please check the form for errors and try again.' }}
</div>

                            <!-- Personal Information -->
                            <div v-if="currentStep === 0" class="space-y-4">
                                <h3 class="text-lg font-medium">Personal Information</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <Label for="first_name">First Name</Label>
                                        <Input id="first_name" v-model="form.first_name" type="text" :error="errorFor('first_name')" @input="onNameInput('first_name', $event)" @keydown="preventDigitKeydown" />
                                        <div v-if="errorFor('first_name')" class="text-sm text-red-600">{{ errorFor('first_name') }}</div>
                                    </div>

                                    <div>
                                        <Label for="middle_name">Middle Name</Label>
                                        <Input id="middle_name" v-model="form.middle_name" type="text" :error="errorFor('middle_name')" @input="onNameInput('middle_name', $event)" @keydown="preventDigitKeydown" />
                                        <div v-if="errorFor('middle_name')" class="text-sm text-red-600">{{ errorFor('middle_name') }}</div>
                                    </div>

                                    <div>
                                        <Label for="last_name">Last Name</Label>
                                        <Input id="last_name" v-model="form.last_name" type="text" :error="errorFor('last_name')" @input="onNameInput('last_name', $event)" @keydown="preventDigitKeydown" />
                                        <div v-if="errorFor('last_name')" class="text-sm text-red-600">{{ errorFor('last_name') }}</div>
                                    </div>

                                    <div>
                                        <Label for="date_of_birth">Birth Date</Label>
                                        <Input id="date_of_birth" v-model="form.date_of_birth" type="date" :error="errorFor('date_of_birth')" />
                                        <div v-if="errorFor('date_of_birth')" class="text-sm text-red-600">{{ errorFor('date_of_birth') }}</div>
                                    </div>

                                    <div>
                                        <Label for="place_of_birth">Place of Birth</Label>
                                        <Input id="place_of_birth" v-model="form.place_of_birth" type="text" :error="errorFor('place_of_birth')" @keydown="preventDigitKeydown" />
                                        <div v-if="errorFor('place_of_birth')" class="text-sm text-red-600">{{ errorFor('place_of_birth') }}</div>
                                    </div>

                                    <div>
                                        <Label for="civil_status">Civil Status</Label>
                                        <Select v-model="form.civil_status">
                                            <SelectTrigger :error="form.errors.civil_status">
                                                <SelectValue placeholder="Select civil status">{{ civilStatusLabel || 'Select civil status' }}</SelectValue>
                                            </SelectTrigger>
                                            <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
                                                <SelectItem value="single">Single</SelectItem>
                                                <SelectItem value="married">Married</SelectItem>
                                                <SelectItem value="widowed">Widowed</SelectItem>
                                                <SelectItem value="separated">Separated</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="errorFor('civil_status')" class="text-sm text-red-600">{{ errorFor('civil_status') }}</div>
                                    </div>

                                    <div>
                                        <Label for="gender">Gender</Label>
                                        <Select v-model="form.gender">
                                            <SelectTrigger :error="form.errors.gender">
                                                <SelectValue placeholder="Select gender">{{ genderLabel || 'Select gender' }}</SelectValue>
                                            </SelectTrigger>
                                            <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
                                                <SelectItem value="male">Male</SelectItem>
                                                <SelectItem value="female">Female</SelectItem>
                                                <SelectItem value="other">Other</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="errorFor('gender')" class="text-sm text-red-600">{{ errorFor('gender') }}</div>
                                    </div>

                                    <div>
                                        <Label for="citizenship">Citizenship</Label>
                                        <Input id="citizenship" v-model="form.citizenship" type="text" :error="errorFor('citizenship')" @keydown="preventDigitKeydown" />
                                        <div v-if="errorFor('citizenship')" class="text-sm text-red-600">{{ errorFor('citizenship') }}</div>
                                    </div>

                                    <div>
                                        <Label for="contact_number">Contact Number</Label>
                                        <Input id="contact_number" v-model="form.contact_number" type="text" inputmode="numeric" maxlength="11" @input="onContactInput" :error="errorFor('contact_number')" />
                                        <div v-if="errorFor('contact_number')" class="text-sm text-red-600">{{ errorFor('contact_number') }}</div>
                                    </div>

                                    <div>
                                        <Label for="email">Email Address</Label>
                                        <Input id="email" v-model="form.email" type="email" :error="errorFor('email')" />
                                        <div v-if="errorFor('email')" class="text-sm text-red-600">{{ errorFor('email') }}</div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <Label for="purpose">Purpose of Clearance</Label>
                                        <Input id="purpose" v-model="form.purpose" type="text" :error="errorFor('purpose')" />
                                        <div v-if="errorFor('purpose')" class="text-sm text-red-600">{{ errorFor('purpose') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div v-if="currentStep === 1" class="space-y-4">
                                <h3 class="text-lg font-medium">Address Information</h3>
                                <div>
                                    <Label for="address_type">Address Type</Label>
                                    <Select v-model="form.address_type">
                                        <SelectTrigger :error="form.errors.address_type">
                                            <SelectValue placeholder="Select address type">{{ addressTypeLabel || 'Select address type' }}</SelectValue>
                                        </SelectTrigger>
                                        <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
                                            <SelectItem value="present">Present Address</SelectItem>
                                            <SelectItem value="permanent">Permanent Address</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.address_type" class="text-sm text-red-600">{{ form.errors.address_type }}</div>
                                </div>

                                <!-- PSGC Cascading Address Selector -->
                                <div class="sm:col-span-2">
                                    <div class="space-y-2">
                                        <PsgcAddressSelector
                                            :regions="props.regions"
                                            v-model:regionCode="form.region_code"
                                            v-model:provinceCode="form.province_code"
                                            v-model:cityCode="form.city_code"
                                            v-model:barangayCode="form.barangay_code"
                                        />
                                        <div v-if="errorFor('region_code')" class="text-sm text-red-600">{{ errorFor('region_code') }}</div>
                                        <div v-if="errorFor('province_code')" class="text-sm text-red-600">{{ errorFor('province_code') }}</div>
                                        <div v-if="errorFor('city_code')" class="text-sm text-red-600">{{ errorFor('city_code') }}</div>
                                        <div v-if="errorFor('barangay_code')" class="text-sm text-red-600">{{ errorFor('barangay_code') }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <Label for="house_no">House No.</Label>
                                        <Input id="house_no" v-model="form.house_no" type="text" :error="errorFor('house_no')" />
                                        <div v-if="errorFor('house_no')" class="text-sm text-red-600">{{ errorFor('house_no') }}</div>
                                    </div>

                                    <div>
                                        <Label for="street">Street</Label>
                                        <Input id="street" v-model="form.street" type="text" :error="errorFor('street')" />
                                        <div v-if="errorFor('street')" class="text-sm text-red-600">{{ errorFor('street') }}</div>
                                    </div>

                                    <div>
                                        <Label for="purok">Purok</Label>
                                        <Input id="purok" v-model="form.purok" type="text" :error="errorFor('purok')" />
                                        <div v-if="errorFor('purok')" class="text-sm text-red-600">{{ errorFor('purok') }}</div>
                                    </div>

                                    <div>
                                        <Label for="zip_code">Zip Code</Label>
                                        <Input id="zip_code" v-model="form.zip_code" type="text" inputmode="numeric" maxlength="4" @input="onZipInput" :error="errorFor('zip_code')" />
                                        <div v-if="errorFor('zip_code')" class="text-sm text-red-600">{{ errorFor('zip_code') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Supporting Documents -->
                            <div v-if="currentStep === 2" class="space-y-4">
                                <h3 class="text-lg font-medium">Supporting Documents</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <Label for="document_type">Document Type</Label>
                                        <Select v-model="form.document_type">
                                            <SelectTrigger :error="form.errors.document_type">
                                                <SelectValue placeholder="Select document type">{{ documentTypeLabel || 'Select document type' }}</SelectValue>
                                            </SelectTrigger>
                                            <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
                                                <SelectItem value="certificate_of_residency">Certificate of Residency</SelectItem>
                                                <SelectItem value="lease_contract">Lease Contract</SelectItem>
                                                <SelectItem value="utility_bill">Utility Bill</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="form.errors.document_type" class="text-sm text-red-600">{{ form.errors.document_type }}</div>
                                    </div>

                                    <div>
                                        <Label for="document_file">Proof of Residence</Label>
                                        <Input
                                            id="document_file"
                                            type="file"
                                            @change="handleFileChange('document', $event.target.files?.[0] || null, (f) => form.document = f)"
                                            :error="errorFor('document')"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                        />
                                        <div v-if="errorFor('document')" class="text-sm text-red-600">{{ errorFor('document') }}</div>
                                    </div>

                                    <!-- Valid ID Upload -->
                                    <div>
                                        <Label for="valid_id_document">Valid ID</Label>
                                        <Input id="valid_id_document" type="file" @change="handleFileChange('valid_id_document', $event.target.files?.[0] || null, (f) => validIdDocument = f)" :error="errorFor('valid_id_document')" accept=".jpg,.jpeg,.png,.pdf" />
                                        <div v-if="errorFor('valid_id_document')" class="text-sm text-red-600">{{ errorFor('valid_id_document') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between pt-6">
                                <Button type="button" variant="outline" @click="goBack" :disabled="isFirstStep">Back</Button>
                                <div class="flex gap-3">
                                    <Button v-if="!isLastStep" type="button" @click="goNext">Next</Button>
                                    <Button v-else type="submit" :disabled="form.processing" :loading="form.processing">Submit Application</Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </div>
    </AppLayout>
</template>