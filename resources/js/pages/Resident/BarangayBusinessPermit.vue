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
        title: 'Barangay Business Permit',
        href: route('resident.barangay-business-permit'),
    },
];
</script>

<template>
    <Head title="Barangay Business Permit" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-8 px-4 md:px-6">
            <!-- Hero Section with Text and Image -->
            <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto mb-12 items-center">
                <div>
                    <h1 class="text-4xl font-bold mb-8 text-[#2C4854]">Barangay Business Permit</h1>
                    <p class="text-[#2C4854] mb-6">
                        To apply for a Barangay Business Permit, applicants must provide their personal profile, proof
                        of address, and supporting documents as required by the barangay. All submitted documents will
                        be subject to verification. Only after the requirements have been completed and verified will
                        the Barangay Business Clearance be officially issued.
                    </p>
                    <div class="bg-warning/5 border border-warning/20 p-6 rounded-lg mb-6">
                        <div class="flex items-start gap-3">
                            <AlertCircle class="size-5 text-warning flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="font-medium text-warning mb-1">Important Note:</p>
                                <p class="text-muted-foreground text-sm">
                                    The Barangay Business Clearance is a mandatory requirement as directed by the Mayor's
                                    Office. It must be presented when applying for the Mayor's/Business Permit at the City
                                    or Municipal Hall.
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
                    <img src="/images/major.png" alt="3D Paper Folder" class="w-full max-w-md" />
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
                            Basic details including full name, contact information, and business details for proper identification and communication.
                        </CardDescription>
                    </CardContent>
                </Card>

                <!-- Proof of Address Box -->
                <Card class="bg-card hover:bg-[#2C4854]/5 hover:border-[#2C4854]/20 transition-colors cursor-pointer">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <MapPin class="size-5 text-[#2C4854]" />
                            <CardTitle class="text-lg text-[#2C4854]">Proof of Address</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CardDescription class="text-[#2C4854]/80">
                            Recent utility bill or lease contract to verify your business location within the barangay's jurisdiction.
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
                            Additional requirements such as valid ID, business registration, and other relevant permits or certificates.
                        </CardDescription>
                    </CardContent>
                </Card>
                
            </div>

            <!-- CTA Section -->
            <div class="flex justify-end">
                <Link :href="route('barangay-permit.create')">
                    <Button size="lg" class="gap-2 px-8">
                        Start Application
                        <ArrowRight class="size-4" />
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>