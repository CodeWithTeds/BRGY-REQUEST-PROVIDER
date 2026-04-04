<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useForm, Head, router } from '@inertiajs/vue3'
import Toastify from 'toastify-js'
import type { BreadcrumbItem } from '@/types'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import PsgcAddressSelector from '@/components/address/PsgcAddressSelector.vue'

const props = defineProps<{ regions?: any[]; applicantProfile?: any }>()

// Explicitly typing the form avoids deep type instantiation issues
type IndigencyForm = {
    purpose: string
    valid_government_id_document: File | null
    proof_of_income_document: File | null
    first_name: string
    middle_name: string
    last_name: string
    suffix: string
    date_of_birth: string
    place_of_birth: string
    civil_status: string
    gender: string
    citizenship: string
    contact_number: string
    address_type: string
    region_code: string
    province_code: string
    city_code: string
    barangay_code: string
    house_no: string
    street: string
    purok: string
    zip_code: string
}

const form = useForm<IndigencyForm>({
    purpose: '',
    // bind file directly to the form so Inertia can serialize it
    valid_government_id_document: null,
    proof_of_income_document: null,
    // Personal info (prefill from applicantProfile if available)
    first_name: props.applicantProfile?.first_name || '',
    middle_name: props.applicantProfile?.middle_name || '',
    last_name: props.applicantProfile?.last_name || '',
    suffix: props.applicantProfile?.suffix || '',
    date_of_birth: props.applicantProfile?.date_of_birth || '',
    place_of_birth: props.applicantProfile?.place_of_birth || '',
    civil_status: props.applicantProfile?.civil_status || '',
    gender: props.applicantProfile?.gender || '',
    citizenship: props.applicantProfile?.citizenship || '',
    contact_number: props.applicantProfile?.contact_number || '',
    // PSGC address
    address_type: 'present',
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

// Wizard step state for indigency create
const steps = ['Personal Information', 'PSGC Address', 'Supporting Documents']
const currentStep = ref(0)
const isFirstStep = computed(() => currentStep.value === 0)
const isLastStep = computed(() => currentStep.value === steps.length - 1)
function requiredFieldsForStep(step: number): string[] {
    if (step === 2) {
        return ['purpose', 'valid_government_id_document']
    }
    return []
}
const canProceed = computed(() => {
    const required = requiredFieldsForStep(currentStep.value)
    return required.every((field) => {
        const value = (form as any)[field]
        return value !== undefined && value !== null && value !== ''
    })
})

// Map of form fields per wizard step to help error routing
function stepFieldKeys(step: number): string[] {
  if (step === 0) {
    return ['first_name','middle_name','last_name','suffix','date_of_birth','place_of_birth','civil_status','gender','citizenship','contact_number']
  } else if (step === 1) {
    return ['address_type','region_code','province_code','city_code','barangay_code','house_no','street','purok','zip_code']
  }
  return ['purpose','valid_government_id_document','proof_of_income_document']
}

// Filter errors to those relevant to the current step
const visibleErrorKeys = computed(() => {
  const errs = form.errors as Record<string, string>
  return stepFieldKeys(currentStep.value).filter(k => !!errs[k])
})

// Show a concise summary of the first few errors at the top
const firstThreeErrors = computed(() => {
  const errs = form.errors as Record<string, string>
  return Object.values(errs).slice(0, 3)
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
            text: 'Please complete required fields to proceed.',
            duration: 3000,
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
const MAX_FILE_BYTES = 5 * 1024 * 1024
const localErrors = ref<Record<string, string>>({})
const errorFor = (key: string) => localErrors.value[key] || (form.errors as Record<string, string>)[key]
function setLocalError(key: string, msg: string) { localErrors.value[key] = msg }
function clearLocalError(key: string) { delete localErrors.value[key] }
function handleFileChange(key: string, file: File | null, assign: (f: File | null) => void) {
  if (!file) { assign(null); clearLocalError(key); return }
  if (file.size > MAX_FILE_BYTES) { assign(null); setLocalError(key, 'File must be 5MB or smaller') } else { assign(file); clearLocalError(key) }
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Certificate of Indigency',
        href: route('resident.certificate-of-indigency.create'),
    },
    {
        title: 'Apply',
        href: route('resident.certificate-of-indigency.create'),
    },
];

function submit() {
    form.clearErrors()
    form.post(route('resident.certificate-of-indigency.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            Toastify({
                text: 'Application submitted successfully! We\'ll notify you once processed.',
                duration: 4000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#16a34a',
                close: true,
            }).showToast()
            // Navigate back to create route; controller will show Pending if applicable
            router.get(route('resident.certificate-of-indigency.create'))
        },
        onError: (errors) => {
            // Automatically jump to the first step that has errors
            const stepOrder = [0, 1, 2]
            for (const s of stepOrder) {
                const keys = stepFieldKeys(s)
                if (keys.some(k => !!(errors as Record<string, string>)[k])) {
                    currentStep.value = s
                    break
                }
            }
            const firstMsg = Object.values(errors as Record<string, string>)[0] || 'Please correct the highlighted fields.'
            Toastify({
                text: firstMsg,
                duration: 3500,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#dc2626',
                close: true,
            }).showToast()
        },
        onFinish: () => {
            if (!form.hasErrors) {
                form.reset('purpose', 'valid_government_id_document', 'proof_of_income_document')
            }
        }
    })
}
</script>

<template>

    <Head title="Apply for Certificate of Indigency" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Certificate of Indigency Application</CardTitle>
                            <CardDescription>Fill out the form to apply for a certificate of indigency.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div v-if="visibleErrorKeys.length > 0 || extraErrors['error']"
                                class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
                                {{ extraErrors['error'] || firstThreeErrors.join(' • ') || 'Please check the form for errors and try again.' }}
                            </div>

                            <!-- Wizard state moved to script setup -->

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
                                        <Label for="suffix">Suffix</Label>
                                        <Input id="suffix" v-model="form.suffix" type="text" :error="errorFor('suffix')" @input="onNameInput('suffix', $event)" @keydown="preventDigitKeydown" />
                                        <div v-if="errorFor('suffix')" class="text-sm text-red-600">{{ errorFor('suffix') }}</div>
                                    </div>
                                    <div>
                                        <Label for="date_of_birth">Date of Birth</Label>
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
                                        <div v-if="errorFor('civil_status')" class="text-sm text-red-600">{{ errorFor('civil_status') }}</div>
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

                            <!-- PSGC Address -->
                            <div v-if="currentStep === 1" class="space-y-4">
                                <h3 class="text-lg font-medium">PSGC Address</h3>
                                <div>
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
                                    <div v-if="errorFor('address_type')" class="text-sm text-red-600">{{ errorFor('address_type') }}</div>
                                </div>
                                <div class="sm:col-span-2 space-y-2">
                                    <PsgcAddressSelector :regions="props.regions || []" v-model:regionCode="form.region_code" v-model:provinceCode="form.province_code" v-model:cityCode="form.city_code" v-model:barangayCode="form.barangay_code" />
                                    <div v-if="errorFor('region_code')" class="text-sm text-red-600">{{ errorFor('region_code') }}</div>
                                    <div v-if="errorFor('province_code')" class="text-sm text-red-600">{{ errorFor('province_code') }}</div>
                                    <div v-if="errorFor('city_code')" class="text-sm text-red-600">{{ errorFor('city_code') }}</div>
                                    <div v-if="errorFor('barangay_code')" class="text-sm text-red-600">{{ errorFor('barangay_code') }}</div>
                                </div>
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

                            <!-- Purpose -->
                            <div v-if="currentStep === 2" class="space-y-4">
                                <div class="sm:col-span-2">
                                    <Label for="purpose">Purpose of Certificate</Label>
                                    <Input id="purpose" v-model="form.purpose" type="text" :error="errorFor('purpose')" />
                                    <div v-if="errorFor('purpose')" class="text-sm text-red-600">{{ errorFor('purpose') }}</div>
                                </div>
                            </div>

                            <!-- Supporting Documents -->
                            <div v-if="currentStep === 2" class="space-y-6">
                                <h3 class="text-lg font-medium">Supporting Documents</h3>
                                <p class="text-sm text-muted-foreground">Attach the required documents to process your application.</p>

                                <div>
                                    <Label for="valid_government_id">Valid Government-Issued ID</Label>
                                    <Input id="valid_government_id" type="file" @change="handleFileChange('valid_government_id_document', ($event.target as HTMLInputElement)?.files?.[0] || null, f => form.valid_government_id_document = f)" :error="errorFor('valid_government_id_document')" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="errorFor('valid_government_id_document')" class="text-sm text-red-600">{{ errorFor('valid_government_id_document') }}</div>
                                    <div class="mt-2 text-xs text-muted-foreground">
                                        Examples: PhilID (National ID), Voter’s ID, Driver’s License, Passport, Postal ID, SSS/GSIS ID, PRC ID
                                    </div>
                                </div>

                                <div>
                                    <Label for="proof_of_income">Proof of Income (optional)</Label>
                                    <Input id="proof_of_income" type="file" @change="handleFileChange('proof_of_income_document', ($event.target as HTMLInputElement)?.files?.[0] || null, f => form.proof_of_income_document = f)" :error="errorFor('proof_of_income_document')" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="errorFor('proof_of_income_document')" class="text-sm text-red-600">{{ errorFor('proof_of_income_document') }}</div>
                                    <div class="mt-2 text-xs text-muted-foreground">Examples: Income certificate, payslip, affidavit of low income.</div>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="justify-between">
                            <div>
                                <Button v-if="!isFirstStep" type="button" variant="secondary" @click="goBack">Back</Button>
                            </div>
                            <div>
                                <Button v-if="!isLastStep" type="button" :disabled="!canProceed" @click="goNext">Next</Button>
                                <Button v-else type="submit" :disabled="form.processing">Submit Application</Button>
                            </div>
                        </CardFooter>
                    </Card>
                </form>
            </div>
        </div>
    </AppLayout>
</template>