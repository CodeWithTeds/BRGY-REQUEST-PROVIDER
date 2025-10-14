<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Personal Information',
        href: '/settings/personal-information',
    },
];

type ApplicantProfile = {
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
};

const page = usePage();
const ap = page.props.applicantProfile as ApplicantProfile | null;

// Local state for Selects to ensure chosen value displays and submits
const civilStatus = ref<string>(ap?.civil_status ?? 'single');
const gender = ref<string>(ap?.gender ?? 'male');

const civilStatusLabels: Record<string, string> = {
    single: 'Single',
    married: 'Married',
    widowed: 'Widowed',
    separated: 'Separated',
};

const genderLabels: Record<string, string> = {
    male: 'Male',
    female: 'Female',
    other: 'Other',
};

const civilStatusLabel = computed(() => civilStatusLabels[civilStatus.value] ?? '');
const genderLabel = computed(() => genderLabels[gender.value] ?? '');
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Personal Information" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Personal Information" description="Update your personal details used for applications" />

                <Form method="patch" :action="route('profile.personal.update')" class="space-y-6" v-slot="{ errors, processing, recentlySuccessful }">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="first_name">First Name</Label>
                            <Input id="first_name" name="first_name" :default-value="ap?.first_name ?? ''" placeholder="First Name" />
                            <InputError class="mt-2" :message="errors.first_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="middle_name">Middle Name</Label>
                            <Input id="middle_name" name="middle_name" :default-value="ap?.middle_name ?? ''" placeholder="Middle Name" />
                            <InputError class="mt-2" :message="errors.middle_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="last_name">Last Name</Label>
                            <Input id="last_name" name="last_name" :default-value="ap?.last_name ?? ''" placeholder="Last Name" />
                            <InputError class="mt-2" :message="errors.last_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="suffix">Suffix</Label>
                            <Input id="suffix" name="suffix" :default-value="ap?.suffix ?? ''" placeholder="(e.g., Jr., Sr., III)" />
                            <InputError class="mt-2" :message="errors.suffix" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="date_of_birth">Date of Birth</Label>
                            <Input id="date_of_birth" type="date" name="date_of_birth" :default-value="ap?.date_of_birth ?? ''" />
                            <InputError class="mt-2" :message="errors.date_of_birth" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="place_of_birth">Place of Birth</Label>
                            <Input id="place_of_birth" name="place_of_birth" :default-value="ap?.place_of_birth ?? ''" placeholder="City, Province" />
                            <InputError class="mt-2" :message="errors.place_of_birth" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="civil_status">Civil Status</Label>
                            <Select v-model="civilStatus" name="civil_status">
                                <SelectTrigger id="civil_status">
                                    <SelectValue placeholder="Select status">{{ civilStatusLabel }}</SelectValue>
                                </SelectTrigger>
                                <SelectContent position="popper" :side-offset="4" align="start">
                                    <SelectItem value="single" text-value="Single">Single</SelectItem>
                                    <SelectItem value="married" text-value="Married">Married</SelectItem>
                                    <SelectItem value="widowed" text-value="Widowed">Widowed</SelectItem>
                                    <SelectItem value="separated" text-value="Separated">Separated</SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="civil_status" :value="civilStatus" />
                            <InputError class="mt-2" :message="errors.civil_status" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="gender">Gender</Label>
                            <Select v-model="gender" name="gender">
                                <SelectTrigger id="gender">
                                    <SelectValue placeholder="Select gender">{{ genderLabel }}</SelectValue>
                                </SelectTrigger>
                                <SelectContent position="popper" :side-offset="4" align="start">
                                    <SelectItem value="male" text-value="Male">Male</SelectItem>
                                    <SelectItem value="female" text-value="Female">Female</SelectItem>
                                    <SelectItem value="other" text-value="Other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="gender" :value="gender" />
                            <InputError class="mt-2" :message="errors.gender" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="citizenship">Citizenship</Label>
                            <Input id="citizenship" name="citizenship" :default-value="ap?.citizenship ?? ''" placeholder="Citizenship" />
                            <InputError class="mt-2" :message="errors.citizenship" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="contact_number">Contact Number</Label>
                            <Input id="contact_number" name="contact_number" :default-value="ap?.contact_number ?? ''" placeholder="Contact Number" />
                            <InputError class="mt-2" :message="errors.contact_number" />
                        </div>
                    </div>

                    <div>
                        <button :disabled="processing" type="submit" class="inline-flex items-center rounded-md bg-neutral-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-neutral-700 disabled:pointer-events-none disabled:opacity-50">
                            Save Personal Information
                        </button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>