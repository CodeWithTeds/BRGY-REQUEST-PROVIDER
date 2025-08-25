<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useBarangayPermitForm } from '@/composables/useBarangayPermitForm'
import PsgcAddressSelector from '@/components/address/PsgcAddressSelector.vue'

const {
    form,
    civilStatusLabel,
    genderLabel,
} = useBarangayPermitForm()


const props = defineProps<{
    barangays: Array<{ code: string; name: string }>,
    regions: Array<{ code: string; name: string }>,
}>()

function submit() {
    form.post(route('barangay-permit.store'))
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
                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Personal Information</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <Label for="first_name">First Name</Label>
                                        <Input id="first_name" v-model="form.first_name" type="text" />
                                    </div>
                                    <div>
                                        <Label for="middle_name">Middle Name</Label>
                                        <Input id="middle_name" v-model="form.middle_name" type="text" />
                                    </div>
                                    <div>
                                        <Label for="last_name">Last Name</Label>
                                        <Input id="last_name" v-model="form.last_name" type="text" />
                                    </div>
                                    <div>
                                        <Label for="suffix">Suffix</Label>
                                        <Input id="suffix" v-model="form.suffix" type="text" />
                                    </div>
                                    <div>
                                        <Label for="date_of_birth">Date of Birth</Label>
                                        <Input id="date_of_birth" v-model="form.date_of_birth" type="date" />
                                    </div>
                                    <div>
                                        <Label for="place_of_birth">Place of Birth</Label>
                                        <Input id="place_of_birth" v-model="form.place_of_birth" type="text" />
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
                                        <Input id="citizenship" v-model="form.citizenship" type="text" />
                                    </div>
                                    <div>
                                        <Label for="contact_number">Contact Number</Label>
                                        <Input id="contact_number" v-model="form.contact_number" type="text" />
                                    </div>
                                </div>
                            </div>
                            <!-- PSGC Cascading Address Selector -->
                            <div class="sm:col-span-2">
                                <PsgcAddressSelector :regions="props.regions" v-model:regionCode="form.region_code"
                                    v-model:provinceCode="form.province_code" v-model:cityCode="form.city_code"
                                    v-model:barangayCode="form.barangay_code" />
                            </div>

                            <div>
                                <Label for="house_no">House No.</Label>
                                <Input id="house_no" v-model="form.house_no" type="text" />
                            </div>
                            <div>
                                <Label for="street">Street</Label>
                                <Input id="street" v-model="form.street" type="text" />
                            </div>
                            <div>
                                <Label for="purok">Purok</Label>
                                <Input id="purok" v-model="form.purok" type="text" />
                            </div>
                            <div>
                                <Label for="zip_code">Zip Code</Label>
                                <Input id="zip_code" v-model="form.zip_code" type="text" />
                            </div>

                            <!-- Supporting Document -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Supporting Document</h3>
                                <div>
                                    <Label for="document_type">Document Type</Label>
                                    <Select v-model="form.document_type">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select document type">Select document type
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent position="popper" side="bottom" :sideOffset="4" align="start"
                                            :alignOffset="0" :avoidCollisions="true">
                                            <SelectItem value="certificate_of_residency">Certificate of Residency
                                            </SelectItem>
                                            <SelectItem value="lease_contract">Lease Contract</SelectItem>
                                            <SelectItem value="utility_bill">Utility Bill</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <Label for="document">Upload Document</Label>
                                    <Input id="document" type="file" @input="form.document = $event.target.files[0]" />
                                </div>
                            </div>

                            <Button type="submit" :disabled="form.processing">
                                Submit Application
                            </Button>

                        </CardContent>
                    </Card>
                </form>
            </div>
        </div>

    </AppLayout>
</template>
