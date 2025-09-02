<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowRight, FileText, UserCheck, MapPin, FileCheck2, AlertCircle } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Barangay Clearance',
        href: route('resident.barangay-clearance'),
    },
];
</script>

<template>
    <Head title="Barangay Clearance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-8 px-4 md:px-6">
            <!-- Hero Section with Text and Image -->
            <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto mb-12 items-center">
                <div>
                    <h1 class="text-4xl font-bold mb-8 text-[#2C4854]">Barangay Clearance</h1>
                    <p class="text-[#2C4854] mb-6">
                        To obtain a Barangay Clearance, applicants must provide their personal information, proof
                        of residence, and any required supporting documents. The clearance will be issued after
                        verification of all submitted requirements and confirmation of good standing in the community.
                    </p>
                    <div class="bg-warning/5 border border-warning/20 p-6 rounded-lg mb-6">
                        <div class="flex items-start gap-3">
                            <AlertCircle class="size-5 text-warning flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="font-medium text-warning mb-1">Important Note:</p>
                                <p class="text-muted-foreground text-sm text-justify">
                                    The Barangay Clearance is an official document that must include the barangay's
                                    seal and authorized signatures to be considered valid.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-12">
                        <svg viewBox="0 0 500 50" class="mx-auto w-full text-[#2C4854]/50">
                            <path d="M0,25 C150,50 350,0 500,25" fill="none" stroke="currentColor" stroke-width="2" />
                        </svg>
                    </div>
                </div>
                <div class="flex justify-center">
                    <img src="/images/brg.png" alt="Barangay Clearance" class="w-full max-w-md" />
                </div>
            </div>

            <!-- Requirements Grid -->
            <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto mb-12">
                <!-- Personal Information Box -->
                <Card class="bg-card hover:bg-[#2C4854]/10 hover:border-[#2C4854] transition-colors cursor-pointer">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <UserCheck class="size-5 text-[#2C4854]" />
                            <CardTitle class="text-lg text-[#2C4854]">Personal Information</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CardDescription class="text-[#2C4854]/80">
                            Basic personal details including full name, contact information, and purpose of clearance
                            for proper documentation.
                        </CardDescription>
                    </CardContent>
                </Card>

                <!-- Proof of Residence Box -->
                <Card class="bg-card hover:bg-[#2C4854]/5 hover:border-[#2C4854]/20 transition-colors cursor-pointer">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <MapPin class="size-5 text-[#2C4854]" />
                            <CardTitle class="text-lg text-[#2C4854]">Proof of Residence</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CardDescription class="text-[#2C4854]/80">
                            Documentation proving your residence within the barangay's jurisdiction, such as utility
                            bills or lease agreement.
                        </CardDescription>
                    </CardContent>
                </Card>

                <!-- Supporting Documents Box -->
                <Card class="bg-card hover:bg-[#2C4854]/5 hover:border-[#2C4854]/20 transition-colors cursor-pointer">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <FileCheck2 class="size-5 text-[#2C4854]" />
                            <CardTitle class="text-lg text-[#2C4854]">Supporting Documents</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CardDescription class="text-[#2C4854]/80">
                            Additional requirements such as valid ID and other relevant documents as specified by
                            the barangay.
                        </CardDescription>
                    </CardContent>
                </Card>
            </div>

            <!-- CTA Section -->
            <div class="flex justify-end">
                <Link :href="route('barangay-clearance.create')">
                    <Button size="lg" class="gap-2 px-8">
                        Start Application
                        <ArrowRight class="size-4" />
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>