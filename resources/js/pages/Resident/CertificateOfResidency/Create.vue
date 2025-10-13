<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useForm, Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'

const form = useForm({
    purpose: '',
})

// Local file refs for supporting documents
const validGovernmentId = ref<File | null>(null)
const proofOfResidenceDocument = ref<File | null>(null)
const leaseContractDocument = ref<File | null>(null)
const authorizationLetterDocument = ref<File | null>(null)

// Use a generic index signature for dynamic error keys in template
const extraErrors = computed(() => form.errors as Record<string, string>)

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
                            <CardDescription>Fill out the form to apply for a certificate of residency.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div v-if="Object.keys(form.errors).length > 0"
                                class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
                                Please check the form for errors and try again.
                            </div>

                            <div class="space-y-4">
                                <div class="sm:col-span-2">
                                    <Label for="purpose">Purpose of Certificate</Label>
                                    <Input id="purpose" v-model="form.purpose" type="text"
                                        :error="form.errors.purpose" />
                                    <div v-if="form.errors.purpose" class="text-sm text-red-600">{{ form.errors.purpose
                                    }}</div>
                                </div>
                            </div>

                            <!-- Supporting Documents -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium">Supporting Documents</h3>
                                <p class="text-sm text-muted-foreground">Please attach the following documents to help us verify your residency. If a document does not apply to you, you may skip it.</p>

                                <!-- Valid Government-Issued ID -->
                                <div>
                                    <Label for="valid_government_id">Valid Government-Issued ID</Label>
                                    <Input id="valid_government_id" type="file" @change="validGovernmentId = $event.target.files?.[0] || null" :error="extraErrors['valid_government_id_document']" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="extraErrors['valid_government_id_document']" class="text-sm text-red-600">{{ extraErrors['valid_government_id_document'] }}</div>
                                    <div class="mt-2 text-xs text-muted-foreground">
                                        Examples: PhilID (National ID), Voter’s ID, Driver’s License, Passport, Postal ID, SSS/GSIS ID, PRC ID
                                    </div>
                                </div>

                                <!-- Proof that you live in the barangay -->
                                <div>
                                    <Label for="proof_of_residence">Proof that you live in the barangay</Label>
                                    <Input id="proof_of_residence" type="file" @change="proofOfResidenceDocument = $event.target.files?.[0] || null" :error="extraErrors['proof_of_residence_document']" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="extraErrors['proof_of_residence_document']" class="text-sm text-red-600">{{ extraErrors['proof_of_residence_document'] }}</div>
                                    <div class="mt-2 text-xs text-muted-foreground">Examples: Utility bill, barangay certificate, or similar proof.</div>
                                </div>

                                <!-- Lease/rental agreement, if renting -->
                                <div>
                                    <Label for="lease_contract">Lease/rental agreement (if renting)</Label>
                                    <Input id="lease_contract" type="file" @change="leaseContractDocument = $event.target.files?.[0] || null" :error="extraErrors['lease_contract_document']" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="extraErrors['lease_contract_document']" class="text-sm text-red-600">{{ extraErrors['lease_contract_document'] }}</div>
                                </div>

                                <!-- Authorization letter from the homeowner -->
                                <div>
                                    <Label for="authorization_letter">Authorization letter from the homeowner (if staying with relatives or friends)</Label>
                                    <Input id="authorization_letter" type="file" @change="authorizationLetterDocument = $event.target.files?.[0] || null" :error="extraErrors['authorization_letter_document']" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="extraErrors['authorization_letter_document']" class="text-sm text-red-600">{{ extraErrors['authorization_letter_document'] }}</div>
                                </div>

                                
                            </div>

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