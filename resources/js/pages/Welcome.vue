<template>
    <div class="min-h-screen relative overflow-hidden bg-[#2C4854]">
        <ResidentHeader />
        <!-- Content -->
        <div
            class="relative z-10 grid min-h-[600px] grid-cols-1 items-center gap-8 px-10 py-14 md:grid-cols-2 md:px-16 lg:px-24">
            <div class="text-[#efeeed]">
                <p class="mb-8 text-sm font-semibold tracking-widest text-[#efeeed]/80">Web <span
                        class="font-normal">Apps</span></p>
                <h1 class="leading-none">
                    <span
                        class="block text-[13vw] font-extrabold leading-[0.9] md:text-[7rem] lg:text-[8rem] animate__animated animate__fadeIn transition-all duration-1000 ease-in-out">BRGY
                        REQUEST</span>
                    <span
                        class="block text-[13vw] font-extrabold leading-[0.9] md:text-[7rem] lg:text-[8rem] animate__animated animate__fadeIn transition-all duration-1000 ease-in-out">PROVIDER</span>
                </h1>
                <TypingText
                    text="BRGY Request Provider A web-based system that simplifies barangay service requests and document processing. Residents can easily submit requests online, while barangay officials can track, manage, and process applications more efficiently." />
                <div class="mt-8">
                    <Link href="#features-section"
                        class="group inline-flex items-center gap-3 rounded-full border-2 border-[#efeeed]/80 bg-[#efeeed] text-[#2C4854] transition hover:bg-transparent hover:text-[#efeeed]">
                    <span class="px-6 py-3 text-sm font-semibold tracking-wide">LEARN MORE</span>
                    <span
                        class="mr-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#2C4854] text-[#efeeed] group-hover:bg-[#efeeed] group-hover:text-[#2C4854]">
                        <i class="pi pi-arrow-right" style="font-size: 1rem"></i>
                    </span>
                    </Link>
                </div>
            </div>

            <!-- Phone Mockup -->
            <div
                class="relative mx-auto hidden md:block md:mx-0 md:ml-auto md:h-[600px] md:w-[320px] lg:h-[660px] lg:w-[340px] animate__animated animate__fadeInRight transition-all duration-1000 ease-in-out">
                <!-- Glow circle -->

                <!-- Device -->
                <div
                    class="absolute left-1/2 top-[60%] z-10 h-full w-full -translate-x-1/2 -translate-y-1/2 rounded-[3rem] bg-[#efeeed] shadow-[0_20px_80px_rgba(44,72,84,0.35)] ring-8 ring-[#2C4854]">
                    <!-- Notch -->
                    <div class="mx-auto mt-4 h-6 w-40 rounded-b-2xl bg-[#2C4854]"></div>
                    <!-- Screen content -->
                    <div class="p-6">
                        <div class="mt-10 space-y-4">
                            <div class="h-11 rounded-lg border border-[#2C4854]/20 bg-[#efeeed]"></div>
                            <div class="h-11 rounded-lg border border-[#2C4854]/20 bg-[#efeeed]"></div>
                            <button
                                class="mt-2 w-full rounded-lg bg-[#2C4854] py-3 text-sm font-medium text-[#efeeed]">Sign
                                In</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <FeaturesSection id="features-section" />
    <AboutSection id="about-section" />
</template>


<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import TypingText from '@/components/TypingText.vue';
import ResidentHeader from '@/components/ResidentHeader.vue';
import FeaturesSection from '@/components/resident/FeaturesSection.vue';
import AboutSection from '@/components/resident/AboutSection.vue';
import 'primeicons/primeicons.css';

</script>