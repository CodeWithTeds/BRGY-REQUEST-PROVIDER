<template>
    <div class="relative min-h-screen overflow-hidden bg-[#2C4854]" ref="sectionRef">
        <section class="relative z-10 px-6 py-20 text-[#F5F5F5] md:px-10 lg:px-16">
            <div
                class="mx-auto max-w-6xl transform transition-all duration-700 ease-out"
                :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div class="animate__animated animate__fadeIn">
                        <h1 class="text-4xl font-extrabold tracking-tight md:text-6xl mb-8 font-['Playfair_Display'] italic text-center md:text-left leading-tight">A Message from the Mayor</h1>
                        <div class="prose prose-lg text-[#F5F5F5]/90">
                            <p class="mb-4">A place called home by over 30,000 individuals – Tibiao is a haven of abundant flora and fauna co-existing with the crystal-clear waters of Tibiao River that touches the white sands in Seco Island while traversing the blue sea of the municipal waters.</p>
                            
                            <p class="mb-4">As the working mayor of this humble municipality since 2022, I've set my sights on transforming Tibiao from a lowly 4th class municipality to a bustling environment-friendly 1st class municipality. We are on track to achieve such milestone and with the continued support and love from all of you, my dearest kasimanwas – our town will soon become the education hub and the eco-tourism capital of northern Antique.</p>
                            
                            <p class="mb-4">Anchored on my campaign catchphrase "Labaw sa Tanan Gugma", I nested my leadership style and centered my way of governance in GUGMA. Gugma serves as my needle to thread Tibiaoanons from all walks of life under one banner – to elevate Tibiao to a higher altitude tungod ang Tibiao padayon nga mangibabaw amidst the adversities.</p>
                            
                            <p class="mb-4">As the world shifts towards a digitalized terrain of advanced technology, let us not shy away from these advancements. Let us embrace it and incorporate such innovations in our daily lives to further our livelihoods – farming, fishing, weaving, and pottery making.</p>
                            
                            <p class="mb-4">I dreamed Tibiao as a melting pot of abundance in agricultural production, a home healthy populace, a knowledge bank, nature's refuge, and love-centered endeavors. May you join me in this crusade for the continuous and rapid growth of our town.</p>
                            
                            <p class="mb-4 font-semibold">Labaw sa Tanan Gugma – I love you all, Palangga ko gd kmo mga Kasimanwa!!!</p>
                            
                            <p class="text-sm font-medium text-[#F5F5F5]/70 italic">#MayorWengineeredProgress</p>
                        </div>
                    </div>
                    <div class="relative mx-auto w-full max-w-lg animate__animated animate__fadeInRight">
                        <img
                            src="/images/about.png"
                            alt="Mayor's Message"
                            class="w-full h-auto rounded-2xl shadow-xl ring-1 ring-gray-400/10"
                        />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
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