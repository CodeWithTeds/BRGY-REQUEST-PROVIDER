<template>
    <div class="min-h-screen relative overflow-hidden bg-main">
        <ResidentHeader />
        <!-- Content -->
        <div
            class="relative z-10 grid min-h-[600px] grid-cols-1 items-center gap-8 px-10 py-14 md:grid-cols-2 md:px-16 lg:px-24">
            <div class="text-[#efeeed]">
                <p class="mb-8 text-sm font-semibold tracking-widest text-[#efeeed]/80"><span
                        class="font-normal"></span></p>
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
                        class="group inline-flex items-center gap-3 rounded-full border-2 border-[#efeeed]/80 bg-[#efeeed] text-main transition hover:bg-transparent hover:text-[#efeeed]">
                    <span class="px-6 py-3 text-sm font-semibold tracking-wide">LEARN MORE</span>
                    <span
                        class="mr-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-main text-[#efeeed] group-hover:bg-[#efeeed] group-hover:text-main">
                        <i class="pi pi-arrow-right" style="font-size: 1rem"></i>
                    </span>
                    </Link>
                </div>
            </div>

            <!-- Phone Mockup visible on mobile -->
            <div
                class="relative mx-auto block md:mx-0 md:ml-auto h-[360px] w-[220px] sm:h-[420px] sm:w-[260px] md:h-[600px] md:w-[320px] lg:h-[660px] lg:w-[340px] animate__animated animate__fadeInRight transition-all duration-1000 ease-in-out">
                <img src="/images/phone.png" alt="Barangay Request Provider mobile preview"
                    class="absolute inset-0 m-auto w-full h-full object-contain drop-shadow-2xl" loading="lazy" />
            </div>
        </div>
    </div>
    <FeaturesSection id="features-section" />
    <AboutSection id="about-section" />
    <ThemeSongFeatures />
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import TypingText from '@/components/TypingText.vue';
import ResidentHeader from '@/components/ResidentHeader.vue';
import FeaturesSection from '@/components/resident/FeaturesSection.vue';
import AboutSection from '@/components/resident/AboutSection.vue';
import ThemeSongFeatures from '@/components/resident/ThemeSongFeatures.vue';
import 'primeicons/primeicons.css';
</script>