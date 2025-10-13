<script setup lang="ts">
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useForm, Head, router } from '@inertiajs/vue3'
import Toastify from 'toastify-js'
import type { BreadcrumbItem } from '@/types'

const form = useForm({
    purpose: '',
    // bind file directly to the form so Inertia can serialize it
    valid_government_id_document: null as File | null,
})

// Use a generic index signature for dynamic error keys in template
const extraErrors = computed(() => form.errors as Record<string, string>)

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
                form.reset('purpose', 'valid_government_id_document')
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

                            <div class="space-y-4">
                                <div class="sm:col-span-2">
                                    <Label for="purpose">Purpose of Certificate</Label>
                                    <Input id="purpose" v-model="form.purpose" type="text" :error="form.errors.purpose" />
                                    <div v-if="form.errors.purpose" class="text-sm text-red-600">{{ form.errors.purpose }}</div>
                                </div>
                            </div>

                            <!-- Single Valid Government-Issued ID -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium">Supporting Document</h3>
                                <p class="text-sm text-muted-foreground">Please attach one valid government-issued ID. This is required to process your application.</p>

                                <div>
                                    <Label for="valid_government_id">Valid Government-Issued ID</Label>
                                    <Input id="valid_government_id" type="file" @change="form.valid_government_id_document = ($event.target as HTMLInputElement)?.files?.[0] || null" :error="form.errors.valid_government_id_document" accept=".jpg,.jpeg,.png,.pdf" />
                                    <div v-if="form.errors.valid_government_id_document" class="text-sm text-red-600">{{ form.errors.valid_government_id_document }}</div>
                                    <div class="mt-2 text-xs text-muted-foreground">
                                        Examples: PhilID (National ID), Voter’s ID, Driver’s License, Passport, Postal ID, SSS/GSIS ID, PRC ID
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="mt-6 flex justify-end">
                        <Button type="submit" class="gap-2 px-8">Submit Application</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>