<template>
    <div class="relative min-h-screen overflow-hidden bg-white" ref="sectionRef">
        <section class="relative z-10 px-6 py-20 text-[#2C4854] md:px-10 lg:px-16">
            <div class="mx-auto max-w-6xl transform transition-all duration-700 ease-out"
                :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                <h1 class="animate__animated animate__fadeIn text-4xl font-extrabold tracking-tight md:text-6xl">Tibiao
                    Municipal Theme Song</h1>
                <p class="mt-4 max-w-3xl text-base text-[#2C4854]/90 md:text-lg">
                    Experience the heart and soul of our beloved municipality through its official anthem.
                </p>
                <div class="mx-auto mt-10 max-w-full grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div class="aspect-w-16 aspect-h-9 lg:order-2">
                        <iframe width="1280" height="720"
                            src="https://www.youtube.com/embed/91TR91NTLfY?list=RD91TR91NTLfY"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            class="rounded-xl shadow-2xl w-full h-full"></iframe>
                    </div>
                    <p class="text-lg leading-8 text-[#2C4854]/90 lg:order-1 lg:pr-8">
                        This song celebrates the spirit, culture, and unity of Tibiao, showcasing the pride and identity
                        of
                        its people.
                        Through its melody and lyrics, the theme song highlights the town's rich heritage, natural
                        beauty,
                        and aspirations for progress.
                        Enjoy listening and let the music remind us of our shared commitment to build a brighter future
                        for
                        Tibiao.
                    </p>
                </div>
                <div class="mt-12">
                    <svg viewBox="0 0 500 50" class="mx-auto w-full text-[#2C4854]/50">
                        <path d="M0,25 C150,50 350,0 500,25" fill="none" stroke="currentColor" stroke-width="2" />
                    </svg>
                </div>

                <div class="overflow-hidden">
                    <p class="mt-6 text-center text-lg text-[#2C4854]/80 italic animate-marquee whitespace-nowrap">
                        A theme song serves as a powerful symbol of unity and identity, bringing together our community
                        through shared musical heritage and cultural pride.
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
@keyframes marquee {
    0% {
        transform: translateX(100%);
    }

    100% {
        transform: translateX(-100%);
    }
}

.animate-marquee {
    animation: marquee 12s linear infinite;
}
</style>

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
