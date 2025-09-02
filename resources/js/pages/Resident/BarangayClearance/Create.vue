<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useBarangayClearanceForm } from '@/composables/useBarangayClearanceForm'
import PsgcAddressSelector from '@/components/address/PsgcAddressSelector.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'

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

const props = defineProps<{
    barangays: Array<{ code: string; name: string }>,
    regions: Array<{ code: string; name: string }>,
}>()

function submit() {
    form.clearErrors()
    form.post(route('barangay-clearance.store'), {
        preserveScroll: true,
        forceFormData: true,
        headers: {
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json'
        },
        onError: (errors) => {
            console.error('Form submission failed:', errors)
        },
        onSuccess: () => {
            console.log('Form submitted successfully')
        },
        onFinish: () => {
            if (!form.hasErrors) {
                form.reset()
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
                            <div v-if="Object.keys(form.errors).length > 0" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
                                Please check the form for errors and try again.
                            </div>

                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Personal Information</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <Label for="first_name">First Name</Label>
                                        <Input id="first_name" v-model="form.first_name" type="text" :error="form.errors.first_name" />
                                        <div v-if="form.errors.first_name" class="text-sm text-red-600">{{ form.errors.first_name }}</div>
                                    </div>

                                    <div>
                                        <Label for="middle_name">Middle Name</Label>
                                        <Input id="middle_name" v-model="form.middle_name" type="text" :error="form.errors.middle_name" />
                                        <div v-if="form.errors.middle_name" class="text-sm text-red-600">{{ form.errors.middle_name }}</div>
                                    </div>

                                    <div>
                                        <Label for="last_name">Last Name</Label>
                                        <Input id="last_name" v-model="form.last_name" type="text" :error="form.errors.last_name" />
                                        <div v-if="form.errors.last_name" class="text-sm text-red-600">{{ form.errors.last_name }}</div>
                                    </div>

                                    <div>
                                        <Label for="birth_date">Birth Date</Label>
                                        <Input id="birth_date" v-model="form.birth_date" type="date" :error="form.errors.birth_date" />
                                        <div v-if="form.errors.birth_date" class="text-sm text-red-600">{{ form.errors.birth_date }}</div>
                                    </div>

                                    <div>
                                        <Label for="civil_status">Civil Status</Label>
                                        <Select v-model="form.civil_status">
                                            <SelectTrigger :error="form.errors.civil_status">
                                                <SelectValue placeholder="Select civil status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="(label, value) in civilStatusLabel" :key="value" :value="value">
                                                    {{ label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="form.errors.civil_status" class="text-sm text-red-600">{{ form.errors.civil_status }}</div>
                                    </div>

                                    <div>
                                        <Label for="gender">Gender</Label>
                                        <Select v-model="form.gender">
                                            <SelectTrigger :error="form.errors.gender">
                                                <SelectValue placeholder="Select gender" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="(label, value) in genderLabel" :key="value" :value="value">
                                                    {{ label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="form.errors.gender" class="text-sm text-red-600">{{ form.errors.gender }}</div>
                                    </div>

                                    <div>
                                        <Label for="contact_number">Contact Number</Label>
                                        <Input id="contact_number" v-model="form.contact_number" type="tel" :error="form.errors.contact_number" />
                                        <div v-if="form.errors.contact_number" class="text-sm text-red-600">{{ form.errors.contact_number }}</div>
                                    </div>

                                    <div>
                                        <Label for="email">Email Address</Label>
                                        <Input id="email" v-model="form.email" type="email" :error="form.errors.email" />
                                        <div v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <Label for="purpose">Purpose of Clearance</Label>
                                        <Input id="purpose" v-model="form.purpose" type="text" :error="form.errors.purpose" />
                                        <div v-if="form.errors.purpose" class="text-sm text-red-600">{{ form.errors.purpose }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Address Information</h3>
                                <div>
                                    <Label for="address_type">Address Type</Label>
                                    <Select v-model="form.address_type">
                                        <SelectTrigger :error="form.errors.address_type">
                                            <SelectValue placeholder="Select address type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="(label, value) in addressTypeLabel" :key="value" :value="value">
                                                {{ label }}
                                            </SelectItem>
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
                                        <div v-if="form.errors.region_code" class="text-sm text-red-600">{{ form.errors.region_code }}</div>
                                        <div v-if="form.errors.province_code" class="text-sm text-red-600">{{ form.errors.province_code }}</div>
                                        <div v-if="form.errors.city_code" class="text-sm text-red-600">{{ form.errors.city_code }}</div>
                                        <div v-if="form.errors.barangay_code" class="text-sm text-red-600">{{ form.errors.barangay_code }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                                </div>
                            </div>

                            <!-- Supporting Documents -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Supporting Documents</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <Label for="document_type">Document Type</Label>
                                        <Select v-model="form.document_type">
                                            <SelectTrigger :error="form.errors.document_type">
                                                <SelectValue placeholder="Select document type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="(label, value) in documentTypeLabel" :key="value" :value="value">
                                                    {{ label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="form.errors.document_type" class="text-sm text-red-600">{{ form.errors.document_type }}</div>
                                    </div>

                                    <div>
                                        <Label for="document_file">Upload Document</Label>
                                        <Input
                                            id="document_file"
                                            type="file"
                                            @input="(e: Event) => form.document_file = (e.target as HTMLInputElement)?.files?.[0] || null"
                                            :error="form.errors.document_file"
                                        />
                                        <div v-if="form.errors.document_file" class="text-sm text-red-600">{{ form.errors.document_file }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-6">
                                <Button type="submit" :disabled="form.processing" :loading="form.processing">
                                    Submit Application
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </div>
    </AppLayout>
</template>