<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useBarangayPermitForm } from '@/composables/useBarangayPermitForm'
import PsgcAddressSelector from '@/components/address/PsgcAddressSelector.vue'
import Toastify from 'toastify-js'

const {
    form,
    civilStatusLabel,
    genderLabel,
    documentTypeLabel,
    addressTypeLabel,
} = useBarangayPermitForm()

// Local refs for additional separate documents to avoid TS errors on form typing
const validIdDocument = ref<File | null>(null)
const barangayClearanceBusinessDocument = ref<File | null>(null)
const leaseContractDocument = ref<File | null>(null)

// Use a generic index signature for dynamic error keys in template
const extraErrors = computed(() => form.errors as Record<string, string>)


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
    form.suffix = ap?.suffix ?? ''
    form.date_of_birth = ap?.date_of_birth ?? ''
    form.place_of_birth = ap?.place_of_birth ?? ''
    form.civil_status = (ap?.civil_status as string) ?? form.civil_status
    form.gender = (ap?.gender as string) ?? form.gender
    form.citizenship = ap?.citizenship ?? ''
    form.contact_number = ap?.contact_number ?? ''
}

// Wizard step state
const steps = ['Personal Information', 'PSGC Address', 'Supporting Document']
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
            'citizenship',
            'contact_number',
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

function submit() {
    form.clearErrors()
    // Attach additional files via transform so they are included in FormData
    form.transform((data) => ({
        ...data,
        valid_id_document: validIdDocument.value,
        barangay_clearance_business_document: barangayClearanceBusinessDocument.value,
        lease_contract_document: leaseContractDocument.value,
    }))
    form.post(route('barangay-permit.store'), {
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
        },
        onFinish: () => {
            if (!form.hasErrors) {
                form.reset()
                // Reset local files
                validIdDocument.value = null
                barangayClearanceBusinessDocument.value = null
                leaseContractDocument.value = null
            }
        }
    })
}
</script>

<template>
    <AppLayout title="Apply for Barangay Permit">
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle>Barangay Permit Application</CardTitle>
                            <CardDescription>Fill out the form to apply for a barangay permit.</CardDescription>
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
                            <div v-if="form.errors.error" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
                                {{ form.errors.error }}
                            </div>
                            <!-- Step 1: Personal Information -->
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
                                                <SelectValue placeholder="Select civil status">{{ civilStatusLabel ||
                                                    'Select civil status' }}</SelectValue>
                                            </SelectTrigger>
                                            <SelectContent position="popper" side="bottom" :sideOffset="4" align="start"
                                                :alignOffset="0" :avoidCollisions="true">
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
                                                <SelectValue placeholder="Select gender">{{ genderLabel || 'Select gender' }}</SelectValue>
                                            </SelectTrigger>
                                            <SelectContent position="popper" side="bottom" :sideOffset="4" align="start"
                                                :alignOffset="0" :avoidCollisions="true">
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
                            <!-- Step 2: PSGC Address -->
                            <div v-if="currentStep === 1">
                                <Label for="address_type">Address Type</Label>
                                <Select v-model="form.address_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select address type">{{ addressTypeLabel || 'Select address type' }}</SelectValue>
                                    </SelectTrigger>
                                    <SelectContent position="popper" side="bottom" :sideOffset="4" align="start"
                                        :alignOffset="0" :avoidCollisions="true">
                                        <SelectItem value="present">Present</SelectItem>
                                        <SelectItem value="permanent">Permanent</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.address_type" class="text-sm text-red-600">{{ form.errors.address_type }}</div>
                            </div>

                            <!-- PSGC Cascading Address Selector -->
                            <div v-if="currentStep === 1" class="sm:col-span-2">
                                <div class="space-y-2">
                                    <PsgcAddressSelector :regions="props.regions" v-model:regionCode="form.region_code"
                                        v-model:provinceCode="form.province_code" v-model:cityCode="form.city_code"
                                        v-model:barangayCode="form.barangay_code" />
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
                                <Input id="zip_code" v-model="form.zip_code" type="text" :error="form.errors.zip_code" />
                                <div v-if="form.errors.zip_code" class="text-sm text-red-600">{{ form.errors.zip_code }}</div>
                            </div>

                            <!-- Step 3: Supporting Document -->
                            <div v-if="currentStep === 2" class="space-y-4">
                                <h3 class="text-lg font-medium">Supporting Document</h3>
                                <div>
                                    <Label for="document_type">Document Type</Label>
                                    <Select v-model="form.document_type">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select document type">{{ documentTypeLabel || 'Select document type' }}</SelectValue>
                                        </SelectTrigger>
                                        <SelectContent position="popper" side="bottom" :sideOffset="4" align="start"
                                            :alignOffset="0" :avoidCollisions="true">
                                            <SelectItem value="certificate_of_residency">Certificate of Residency</SelectItem>
                                            <SelectItem value="lease_contract">Lease Contract</SelectItem>
                                            <SelectItem value="utility_bill">Utility Bill</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <Label for="document">Upload Document</Label>
                                    <Input id="document" type="file" @change="form.document = $event.target.files[0]" :error="form.errors.document" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="form.errors.document" class="text-sm text-red-600">{{ form.errors.document }}</div>
                                </div>

                                <!-- Additional Separate Documents -->
                                <div class="pt-2 space-y-4">
                                    <h4 class="text-base font-medium">Additional Documents</h4>
                                    <!-- Valid ID -->
                                    <div>
                                        <Label for="valid_id">Valid ID</Label>
                                        <Input id="valid_id" type="file" @change="validIdDocument = $event.target.files[0]" :error="extraErrors['valid_id_document']" accept=".jpg,.jpeg,.png,.pdf" />
                                        <div v-if="extraErrors['valid_id_document']" class="text-sm text-red-600">{{ extraErrors['valid_id_document'] }}</div>
                                    </div>

                                    <!-- Barangay Clearance for Business -->
                                    <div>
                                        <Label for="barangay_clearance_business">Barangay Clearance for Business</Label>
                                        <Input id="barangay_clearance_business" type="file" @change="barangayClearanceBusinessDocument = $event.target.files[0]" :error="extraErrors['barangay_clearance_business_document']" accept=".jpg,.jpeg,.png,.pdf" />
                                        <div v-if="extraErrors['barangay_clearance_business_document']" class="text-sm text-red-600">{{ extraErrors['barangay_clearance_business_document'] }}</div>
                                    </div>

                                    <!-- Lease Contract -->
                                    <div>
                                        <Label for="lease_contract">Lease Contract</Label>
                                        <Input id="lease_contract" type="file" @change="leaseContractDocument = $event.target.files[0]" :error="extraErrors['lease_contract_document']" accept=".jpg,.jpeg,.png,.pdf" />
                                        <div v-if="extraErrors['lease_contract_document']" class="text-sm text-red-600">{{ extraErrors['lease_contract_document'] }}</div>
                                    </div>
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
