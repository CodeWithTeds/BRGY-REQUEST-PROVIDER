<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useForm, Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import PsgcAddressSelector from '@/components/address/PsgcAddressSelector.vue'
import Toastify from 'toastify-js'

// removed duplicate initial form (merged with extended form below)

// Local file refs for supporting documents
const validGovernmentId = ref<File | null>(null)
const proofOfResidenceDocument = ref<File | null>(null)
const leaseContractDocument = ref<File | null>(null)
const authorizationLetterDocument = ref<File | null>(null)

// Use a generic index signature for dynamic error keys in template
// extraErrors moved below after the extended form initialization

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Certificate of Residency',
        href: route('resident.certificate-of-residency.create'),
    },
    {
        title: 'Apply',
        href: route('resident.certificate-of-residency.create'),
    },
];

function submit() {
    form.clearErrors()
    // Attach additional files via transform so they are included in FormData
    form.transform((data) => ({
        ...data,
        valid_government_id_document: validGovernmentId.value,
        proof_of_residence_document: proofOfResidenceDocument.value,
        lease_contract_document: leaseContractDocument.value,
        authorization_letter_document: authorizationLetterDocument.value,
    }))
    form.post(route('resident.certificate-of-residency.store'), {
        preserveScroll: true,
        forceFormData: true,
        headers: {
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json'
        },
        onSuccess: () => {
            form.reset()
            validGovernmentId.value = null
            proofOfResidenceDocument.value = null
            leaseContractDocument.value = null
            authorizationLetterDocument.value = null
        },
    })
}

// Extend residency form with Personal Info and PSGC address fields
const form = useForm({
    purpose: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    date_of_birth: '',
    place_of_birth: '',
    civil_status: '',
    gender: '',
    citizenship: '',
    contact_number: '',
    address_type: '',
    region_code: '',
    province_code: '',
    city_code: '',
    barangay_code: '',
    house_no: '',
    street: '',
    purok: '',
    zip_code: '',
})

// Use a generic index signature for dynamic error keys in template
const extraErrors = computed(() => form.errors as Record<string, string>)

// Prefill from backend props if available
// Define lightweight ApplicantProfile type
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
    regions?: Array<{ code: string; name: string }>,
    applicantProfile?: ApplicantProfileProps,
}>()

if (props.applicantProfile) {
    const ap = props.applicantProfile
    form.first_name = ap?.first_name ?? ''
    form.middle_name = ap?.middle_name ?? ''
    form.last_name = ap?.last_name ?? ''
    form.suffix = ap?.suffix ?? ''
    form.date_of_birth = ap?.date_of_birth ?? ''
    form.place_of_birth = ap?.place_of_birth ?? ''
    form.civil_status = ap?.civil_status ? String(ap.civil_status).toLowerCase() : ''
    form.gender = ap?.gender ? String(ap.gender).toLowerCase() : ''
    form.citizenship = ap?.citizenship ?? ''
    form.contact_number = ap?.contact_number ?? ''
}

// Wizard state
const steps = ['Personal Information', 'PSGC Address', 'Supporting Documents']
const currentStep = ref(0)
const isFirstStep = computed(() => currentStep.value === 0)
const isLastStep = computed(() => currentStep.value === steps.length - 1)

function requiredFieldsForStep(step: number): string[] {
    if (step === 0) {
        return ['first_name','last_name','date_of_birth','place_of_birth','civil_status','citizenship','contact_number']
    }
    if (step === 1) {
        return ['address_type','region_code','province_code','city_code','barangay_code','house_no','street','zip_code']
    }
    // Step 2 requires purpose only; documents are optional
    return ['purpose']
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
        Toastify({ text: 'Please complete required fields to proceed.', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#dc2626', close: true }).showToast()
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
function onNameInput(key: 'first_name'|'middle_name'|'last_name'|'suffix', e: Event) {
  const target = e.target as HTMLInputElement
  ;(form as any)[key] = sanitizeName(target.value)
}
function preventDigitKeydown(e: KeyboardEvent) { if (/\d/.test(e.key)) e.preventDefault() }
function onContactInput(e: Event) { const t = e.target as HTMLInputElement; form.contact_number = enforceDigits(t.value, 11) }
function onZipInput(e: Event) { const t = e.target as HTMLInputElement; form.zip_code = enforceDigits(t.value, 4) }
</script>

<template>

    <Head title="Apply for Certificate of Residency" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Certificate of Residency Application</CardTitle>
                            <CardDescription>Fill out the form to apply for a certificate of residency.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Wizard Progress -->
                            <div class="mt-2">
                                <div class="flex items-center justify-between">
                                    <div v-for="(label, idx) in steps" :key="label" class="flex-1 text-center">
                                        <div class="flex items-center justify-center">
                                            <div class="flex-1 h-1" :class="idx > 0 ? (idx <= currentStep ? 'bg-emerald-500' : 'bg-neutral-300') : 'bg-transparent'"></div>
                                            <div class="relative">
                                                <div :class="['w-10 h-10 rounded-full flex items-center justify-center border text-sm font-semibold', idx < currentStep ? 'bg-emerald-500 text-white border-emerald-500' : idx === currentStep ? 'border-emerald-500 text-emerald-600' : 'border-neutral-300 text-neutral-400']">{{ idx + 1 }}</div>
                                            </div>
                                            <div class="flex-1 h-1" :class="idx < steps.length - 1 ? (idx < currentStep ? 'bg-emerald-500' : 'bg-neutral-300') : 'bg-transparent'"></div>
                                        </div>
                                        <p :class="['mt-2 text-sm font-medium', idx <= currentStep ? 'text-emerald-700' : 'text-neutral-400']">{{ label }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="Object.keys(form.errors).length > 0" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">Please check the form for errors and try again.</div>

                            <!-- Step 1: Personal Information -->
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
                                        <Label for="suffix">Suffix</Label>
                                        <Input id="suffix" v-model="form.suffix" type="text" :error="errorFor('suffix')" @input="onNameInput('suffix', $event)" @keydown="preventDigitKeydown" />
                                        <div v-if="errorFor('suffix')" class="text-sm text-red-600">{{ errorFor('suffix') }}</div>
                                    </div>
                                    <div>
                                        <Label for="date_of_birth">Date of Birth</Label>
                                        <Input id="date_of_birth" v-model="form.date_of_birth" type="date" :error="form.errors.date_of_birth" />
                                        <div v-if="form.errors.date_of_birth" class="text-sm text-red-600">{{ form.errors.date_of_birth }}</div>
                                    </div>
                                    <div>
                                        <Label for="place_of_birth">Place of Birth</Label>
                                        <Input id="place_of_birth" v-model="form.place_of_birth" type="text" :error="errorFor('place_of_birth')" @keydown="preventDigitKeydown" />
                                        <div v-if="errorFor('place_of_birth')" class="text-sm text-red-600">{{ errorFor('place_of_birth') }}</div>
                                    </div>
                                    <div>
                                        <Label for="civil_status">Civil Status</Label>
                                        <Select v-model="form.civil_status">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select civil status">{{ form.civil_status || 'Select civil status' }}</SelectValue>
                                            </SelectTrigger>
                                            <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
                                                <SelectItem value="single">Single</SelectItem>
                                                <SelectItem value="married">Married</SelectItem>
                                                <SelectItem value="widowed">Widowed</SelectItem>
                                                <SelectItem value="separated">Separated</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label for="gender">Gender</Label>
                                        <Select v-model="form.gender">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select gender">{{ form.gender || 'Select gender' }}</SelectValue>
                                            </SelectTrigger>
                                            <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
                                                <SelectItem value="male">Male</SelectItem>
                                                <SelectItem value="female">Female</SelectItem>
                                                <SelectItem value="other">Other</SelectItem>
                                            </SelectContent>
                                        </Select>
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
                                </div>
                            </div>

                            <!-- Step 2: PSGC Address -->
                            <div v-if="currentStep === 1">
                                <Label for="address_type">Address Type</Label>
                                <Select v-model="form.address_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select address type">{{ form.address_type || 'Select address type' }}</SelectValue>
                                    </SelectTrigger>
                                    <SelectContent position="popper" side="bottom" :sideOffset="4" align="start" :alignOffset="0" :avoidCollisions="true">
                                        <SelectItem value="present">Present</SelectItem>
                                        <SelectItem value="permanent">Permanent</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.address_type" class="text-sm text-red-600">{{ form.errors.address_type }}</div>
                            </div>
                            <div v-if="currentStep === 1" class="sm:col-span-2">
                                <div class="space-y-2">
                                    <PsgcAddressSelector :regions="props.regions || []" v-model:regionCode="form.region_code" v-model:provinceCode="form.province_code" v-model:cityCode="form.city_code" v-model:barangayCode="form.barangay_code" />
                                    <div v-if="form.errors.region_code" class="text-sm text-red-600">{{ form.errors.region_code }}</div>
                                    <div v-if="form.errors.province_code" class="text-sm text-red-600">{{ form.errors.province_code }}</div>
                                    <div v-if="form.errors.city_code" class="text-sm text-red-600">{{ form.errors.city_code }}</div>
                                    <div v-if="form.errors.barangay_code" class="text-sm text-red-600">{{ form.errors.barangay_code }}</div>
                                </div>
                            </div>
                            <div v-if="currentStep === 1">
                                <Label for="house_no">House No.</Label>
                                <Input id="house_no" v-model="form.house_no" type="text" :error="form.errors.house_no" />
                                <div v-if="form.errors.house_no" class="text-sm text-red-600">{{ form.errors.house_no }}</div>
                            </div>
                            <div v-if="currentStep === 1">
                                <Label for="street">Street</Label>
                                <Input id="street" v-model="form.street" type="text" :error="form.errors.street" />
                                <div v-if="form.errors.street" class="text-sm text-red-600">{{ form.errors.street }}</div>
                            </div>
                            <div v-if="currentStep === 1">
                                <Label for="purok">Purok</Label>
                                <Input id="purok" v-model="form.purok" type="text" :error="form.errors.purok" />
                                <div v-if="form.errors.purok" class="text-sm text-red-600">{{ form.errors.purok }}</div>
                            </div>
                            <div v-if="currentStep === 1">
                                <Label for="zip_code">Zip Code</Label>
                                <Input id="zip_code" v-model="form.zip_code" type="text" inputmode="numeric" maxlength="4" @input="onZipInput" :error="errorFor('zip_code')" />
                                <div v-if="errorFor('zip_code')" class="text-sm text-red-600">{{ errorFor('zip_code') }}</div>
                            </div>

                            <!-- Step 3: Purpose and Supporting Documents -->
                            <div v-if="currentStep === 2" class="space-y-6">
                                <div class="space-y-4">
                                    <div class="sm:col-span-2">
                                        <Label for="purpose">Purpose of Certificate</Label>
                                        <Input id="purpose" v-model="form.purpose" type="text" :error="form.errors.purpose" />
                                        <div v-if="form.errors.purpose" class="text-sm text-red-600">{{ form.errors.purpose }}</div>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <h3 class="text-lg font-medium">Supporting Documents</h3>
                                    <p class="text-sm text-muted-foreground">Attach any documents that help verify your residency. These are optional unless specifically requested.</p>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-blue-600">Optional</span>
                                            <Label for="valid_government_id">Valid Government-Issued ID</Label>
                                        </div>
                                        <Input id="valid_government_id" type="file" @change="handleFileChange('valid_government_id_document', ($event.target as HTMLInputElement).files?.[0] || null, f => validGovernmentId = f)" :error="errorFor('valid_government_id_document')" accept=".jpg,.jpeg,.png,.pdf" />
                                        <div v-if="errorFor('valid_government_id_document')" class="text-sm text-red-600">{{ errorFor('valid_government_id_document') }}</div>
                                        <div class="mt-2 text-xs text-muted-foreground">Examples: PhilID (National ID), Voter’s ID, Driver’s License, Passport, Postal ID, SSS/GSIS ID, PRC ID</div>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-blue-600">Optional</span>
                                            <Label for="proof_of_residence">Proof that you live in the barangay</Label>
                                        </div>
                                        <Input id="proof_of_residence" type="file" @change="handleFileChange('proof_of_residence_document', ($event.target as HTMLInputElement).files?.[0] || null, f => proofOfResidenceDocument = f)" :error="errorFor('proof_of_residence_document')" accept=".jpg,.jpeg,.png,.pdf" />
                                        <div v-if="errorFor('proof_of_residence_document')" class="text-sm text-red-600">{{ errorFor('proof_of_residence_document') }}</div>
                                        <div class="mt-2 text-xs text-muted-foreground">Examples: Utility bill, barangay certificate, or similar proof.</div>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-blue-600">Optional</span>
                                            <Label for="authorization_letter">Authorization letter from the homeowner (if staying with relatives or friends)</Label>
                                        </div>
                                        <Input id="authorization_letter" type="file" @change="handleFileChange('authorization_letter_document', ($event.target as HTMLInputElement).files?.[0] || null, f => authorizationLetterDocument = f)" :error="errorFor('authorization_letter_document')" accept=".jpg,.jpeg,.png,.pdf" />
                                        <div v-if="errorFor('authorization_letter_document')" class="text-sm text-red-600">{{ errorFor('authorization_letter_document') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-between pt-6">
                                <Button v-if="!isFirstStep" type="button" variant="secondary" @click="goBack">Back</Button>
                                <div>
                                    <Button v-if="!isLastStep" type="button" :disabled="!canProceed" @click="goNext">Next</Button>
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