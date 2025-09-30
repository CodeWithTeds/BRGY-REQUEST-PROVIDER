<script setup lang="ts">
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
    form.post(route('resident.certificate-of-residency.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
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