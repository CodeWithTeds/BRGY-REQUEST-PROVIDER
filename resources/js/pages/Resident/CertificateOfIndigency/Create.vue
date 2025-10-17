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
        return ['valid_government_id_document']
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
                            <div v-if="Object.keys(form.errors).length > 0"
                                class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
                                Please check the form for errors and try again.
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
                                        <Input id="first_name" v-model="form.first_name" type="text" :error="form.errors.first_name" />
                                        <div v-if="form.errors.first_name" class="text-sm text-red-600">{{ form.errors.first_name }}</div>
                                    </div>
                                    <div>
                                        <Label for="middle_name">Middle Name</Label>
                                        <Input id="middle_name" v-model="form.middle_name" type="text" />
                                    </div>
                                    <div>
                                        <Label for="last_name">Last Name</Label>
                                        <Input id="last_name" v-model="form.last_name" type="text" :error="form.errors.last_name" />
                                        <div v-if="form.errors.last_name" class="text-sm text-red-600">{{ form.errors.last_name }}</div>
                                    </div>
                                    <div>
                                        <Label for="suffix">Suffix</Label>
                                        <Input id="suffix" v-model="form.suffix" type="text" />
                                    </div>
                                    <div>
                                        <Label for="date_of_birth">Date of Birth</Label>
                                        <Input id="date_of_birth" v-model="form.date_of_birth" type="date" :error="form.errors.date_of_birth" />
                                        <div v-if="form.errors.date_of_birth" class="text-sm text-red-600">{{ form.errors.date_of_birth }}</div>
                                    </div>
                                    <div>
                                        <Label for="place_of_birth">Place of Birth</Label>
                                        <Input id="place_of_birth" v-model="form.place_of_birth" type="text" :error="form.errors.place_of_birth" />
                                        <div v-if="form.errors.place_of_birth" class="text-sm text-red-600">{{ form.errors.place_of_birth }}</div>
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
                                        <div v-if="form.errors.civil_status" class="text-sm text-red-600">{{ form.errors.civil_status }}</div>
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
                                        <Input id="citizenship" v-model="form.citizenship" type="text" :error="form.errors.citizenship" />
                                        <div v-if="form.errors.citizenship" class="text-sm text-red-600">{{ form.errors.citizenship }}</div>
                                    </div>
                                    <div>
                                        <Label for="contact_number">Contact Number</Label>
                                        <Input id="contact_number" v-model="form.contact_number" type="text" :error="form.errors.contact_number" />
                                        <div v-if="form.errors.contact_number" class="text-sm text-red-600">{{ form.errors.contact_number }}</div>
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
                                    <div v-if="form.errors.address_type" class="text-sm text-red-600">{{ form.errors.address_type }}</div>
                                </div>
                                <div class="sm:col-span-2 space-y-2">
                                    <PsgcAddressSelector :regions="props.regions || []" v-model:regionCode="form.region_code" v-model:provinceCode="form.province_code" v-model:cityCode="form.city_code" v-model:barangayCode="form.barangay_code" />
                                    <div v-if="form.errors.region_code" class="text-sm text-red-600">{{ form.errors.region_code }}</div>
                                    <div v-if="form.errors.province_code" class="text-sm text-red-600">{{ form.errors.province_code }}</div>
                                    <div v-if="form.errors.city_code" class="text-sm text-red-600">{{ form.errors.city_code }}</div>
                                    <div v-if="form.errors.barangay_code" class="text-sm text-red-600">{{ form.errors.barangay_code }}</div>
                                </div>
                                <div>
                                    <Label for="house_no">House No.</Label>
                                    <Input id="house_no" v-model="form.house_no" type="text" :error="form.errors.house_no" />
                                    <div v-if="form.errors.house_no" class="text-sm text-red-600">{{ form.errors.house_no }}</div>
                                </div>
                                <div>
                                    <Label for="street">Street</Label>
                                    <Input id="street" v-model="form.street" type="text" :error="form.errors.street" />
                                    <div v-if="form.errors.street" class="text-sm text-red-600">{{ form.errors.street }}</div>
                                </div>
                                <div>
                                    <Label for="purok">Purok</Label>
                                    <Input id="purok" v-model="form.purok" type="text" :error="form.errors.purok" />
                                    <div v-if="form.errors.purok" class="text-sm text-red-600">{{ form.errors.purok }}</div>
                                </div>
                                <div>
                                    <Label for="zip_code">Zip Code</Label>
                                    <Input id="zip_code" v-model="form.zip_code" type="text" :error="form.errors.zip_code" />
                                    <div v-if="form.errors.zip_code" class="text-sm text-red-600">{{ form.errors.zip_code }}</div>
                                </div>
                            </div>

                            <!-- Purpose -->
                            <div v-if="currentStep === 2" class="space-y-4">
                                <div class="sm:col-span-2">
                                    <Label for="purpose">Purpose of Certificate</Label>
                                    <Input id="purpose" v-model="form.purpose" type="text" :error="form.errors.purpose" />
                                    <div v-if="form.errors.purpose" class="text-sm text-red-600">{{ form.errors.purpose }}</div>
                                </div>
                            </div>

                            <!-- Supporting Documents -->
                            <div v-if="currentStep === 2" class="space-y-6">
                                <h3 class="text-lg font-medium">Supporting Documents</h3>
                                <p class="text-sm text-muted-foreground">Attach the required documents to process your application.</p>

                                <div>
                                    <Label for="valid_government_id">Valid Government-Issued ID</Label>
                                    <Input id="valid_government_id" type="file" @change="form.valid_government_id_document = ($event.target as HTMLInputElement)?.files?.[0] || null" :error="form.errors.valid_government_id_document" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="form.errors.valid_government_id_document" class="text-sm text-red-600">{{ form.errors.valid_government_id_document }}</div>
                                    <div class="mt-2 text-xs text-muted-foreground">
                                        Examples: PhilID (National ID), Voter’s ID, Driver’s License, Passport, Postal ID, SSS/GSIS ID, PRC ID
                                    </div>
                                </div>

                                <div>
                                    <Label for="proof_of_income">Proof of Income (optional)</Label>
                                    <Input id="proof_of_income" type="file" @change="form.proof_of_income_document = ($event.target as HTMLInputElement)?.files?.[0] || null" :error="form.errors.proof_of_income_document" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="form.errors.proof_of_income_document" class="text-sm text-red-600">{{ form.errors.proof_of_income_document }}</div>
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