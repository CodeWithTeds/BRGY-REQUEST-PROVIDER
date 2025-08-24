<template>
    <div class="relative min-h-screen overflow-hidden bg-white" ref="sectionRef">
        <section class="relative z-10 px-6 py-20 text-[#2C4854] md:px-10 lg:px-16">
            <div
                class="mx-auto max-w-6xl transform transition-all duration-700 ease-out"
                :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
            >
                <h1 class="animate__animated animate__fadeIn text-4xl font-extrabold tracking-tight md:text-6xl">BRGY Request Provider</h1>
                <p class="mt-4 max-w-3xl text-base text-[#2C4854]/90 md:text-lg">
                    A web-based system that simplifies barangay service requests and document processing. Residents can easily submit requests online,
                    while barangay officials can track, manage, and process applications more efficiently.
                </p>

                <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-gray-300 bg-white/5 p-6 backdrop-blur">
                        <h2 class="text-xl font-bold">Online Requests</h2>
                        <p class="mt-2 text-sm text-[#2C4854]/90">
                            Residents submit barangay clearances, permits, and certificates online—anytime, anywhere.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-gray-300 bg-white/5 p-6 backdrop-blur">
                        <h2 class="text-xl font-bold">Tracking & Updates</h2>
                        <p class="mt-2 text-sm text-[#2C4854]/90">Real-time status tracking, notifications, and timelines for each application.</p>
                    </div>
                    <div class="rounded-2xl border border-gray-300 bg-white/5 p-6 backdrop-blur">
                        <h2 class="text-xl font-bold">Admin Dashboard</h2>
                        <p class="mt-2 text-sm text-[#2C4854]/90">Streamlined tools for barangay staff to review, approve, and archive requests.</p>
                    </div>
                    <div class="rounded-2xl border border-gray-300 bg-white/5 p-6 backdrop-blur">
                        <h2 class="text-xl font-bold">Secure Document Handling</h2>
                        <p class="mt-2 text-sm text-[#2C4854]/90">Digitally manage supporting documents with validation and audit trails.</p>
                    </div>
                    <div class="rounded-2xl border border-gray-300 bg-white/5 p-6 backdrop-blur">
                        <h2 class="text-xl font-bold">Mobile-Friendly</h2>
                        <p class="mt-2 text-sm text-[#2C4854]/90">Responsive UI for residents and officials across devices.</p>
                    </div>
                    <div class="rounded-2xl border border-gray-300 bg-white/5 p-6 backdrop-blur">
                        <h2 class="text-xl font-bold">Analytics & Reports</h2>
                        <p class="mt-2 text-sm text-[#2C4854]/90">Insights on processing times, request volumes, and service performance.</p>
                    </div>
                </div>

                <div class="mt-12">
                    <Link
                        href="/register"
                        class="group inline-flex items-center gap-3 rounded-full border-2 border-[#2C4854]/80 bg-[#2C4854] text-[#efeeed] transition hover:bg-transparent hover:text-[#2C4854]"
                    >
                        <span class="px-6 py-3 text-sm font-semibold tracking-wide">Get Started</span>
                        <span
                            class="mr-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#efeeed] text-[#2C4854] group-hover:bg-[#2C4854] group-hover:text-[#efeeed]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                <path
                                    fill-rule="evenodd"
                                    d="M3.75 12a.75.75 0 0 1 .75-.75h12.69l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H4.5A.75.75 0 0 1 3.75 12Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </span>
                    </Link>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const sectionRef = ref<HTMLElement | null>(null);
const isVisible = ref(false);

onMounted(() => {
    if (!sectionRef.value) return;
    const observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                isVisible.value = true;
                observer.unobserve(entry.target);
            }
        },
        { threshold: 0.15 }
    );
    observer.observe(sectionRef.value);
});
</script>
