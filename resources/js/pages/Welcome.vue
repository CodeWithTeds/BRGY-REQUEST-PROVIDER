<template>
    <div class="min-h-screen relative overflow-hidden bg-[#2C4854]">
        <!-- Content -->
        <div class="relative z-10 grid min-h-[600px] grid-cols-1 items-center gap-8 px-10 py-14 md:grid-cols-2 md:px-16 lg:px-24">
            <div class="text-[#efeeed]">
                <p class="mb-8 text-sm font-semibold tracking-widest text-[#efeeed]/80">Web <span class="font-normal">Apps</span></p>
                <h1 class="leading-none">
                    <span class="block text-[13vw] font-extrabold leading-[0.9] md:text-[7rem] lg:text-[8rem] animate__animated animate__fadeIn transition-all duration-1000 ease-in-out">BRGY REQUEST</span>
                     <span class="block text-[13vw] font-extrabold leading-[0.9] md:text-[7rem] lg:text-[8rem] animate__animated animate__fadeIn transition-all duration-1000 ease-in-out">PROVIDER</span>
                </h1>
                <TypingText text="CityConnect — Your all‑in‑one mobile hub for city services, real‑time alerts, local updates, and community engagement—right at your fingertips." />
                <div class="mt-8">
                    <button class="group inline-flex items-center gap-3 rounded-full border-2 border-[#efeeed]/80 bg-[#efeeed] text-[#2C4854] transition hover:bg-transparent hover:text-[#efeeed]">
                        <span class="px-6 py-3 text-sm font-semibold tracking-wide">LEARN MORE</span>
                        <span class="mr-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#2C4854] text-[#efeeed] group-hover:bg-[#efeeed] group-hover:text-[#2C4854]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M3.75 12a.75.75 0 0 1 .75-.75h12.69l-3.22-3.22a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H4.5A.75.75 0 0 1 3.75 12Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Phone Mockup -->
            <div class="relative mx-auto hidden md:block md:mx-0 md:ml-auto md:h-[600px] md:w-[320px] lg:h-[660px] lg:w-[340px] animate__animated animate__fadeInRight transition-all duration-1000 ease-in-out">
                <!-- Glow circle -->
                
                <!-- Device -->
                <div class="absolute left-1/2 top-1/2 z-10 h-full w-full -translate-x-1/2 -translate-y-1/2 rounded-[3rem] bg-[#efeeed] shadow-[0_20px_80px_rgba(44,72,84,0.35)] ring-8 ring-[#2C4854]">
                    <!-- Notch -->
                    <div class="mx-auto mt-4 h-6 w-40 rounded-b-2xl bg-[#2C4854]"></div>
                    <!-- Screen content -->
                    <div class="p-6">
                        <div class="mt-10 space-y-4">
                            <div class="h-11 rounded-lg border border-[#2C4854]/20 bg-[#efeeed]"></div>
                            <div class="h-11 rounded-lg border border-[#2C4854]/20 bg-[#efeeed]"></div>
                            <button class="mt-2 w-full rounded-lg bg-[#2C4854] py-3 text-sm font-medium text-[#efeeed]">Sign In</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import TypingText from '@/components/TypingText.vue';
</script>