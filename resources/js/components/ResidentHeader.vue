<template>
    <header class="absolute inset-x-0 top-0 z-20 bg-gray-100">
        <nav class="flex h-20 items-center justify-between p-6 lg:px-8" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href="#" class="-m-1.5 p-1.5">
                    <span class="sr-only">Your Company</span>
                    <img class="h-16 w-auto" src="/images/brg.png" alt="BRGY REQUEST PROVIDER Logo" />
                </a>
            </div>
            <div class="flex gap-x-8 lg:gap-x-12">
                <Link :href="route('home')" class="text-sm leading-6 font-semibold text-gray-900 hover:text-gray-700">
                Home</Link>
                <Link :href="route('home') + '#features-section'" @click="scrollToFeatures"
                    class="text-sm leading-6 font-semibold text-gray-900 hover:text-gray-700">Features</Link>
                <Link :href="route('home') + '#about-section'" @click="scrollToAbout"
                    class="text-sm leading-6 font-semibold text-gray-900 hover:text-gray-700">About</Link>
            </div>
        </nav>
    </header>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const smoothScroll = (elementId: string) => {
    const element = document.getElementById(elementId);
    if (element) {
        const startPosition = window.pageYOffset;
        const targetPosition = element.getBoundingClientRect().top + window.pageYOffset;
        const distance = targetPosition - startPosition;
        const duration = 100; // milliseconds
        let start: number | null = null;

        window.requestAnimationFrame(function step(timestamp) {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            const percentage = Math.min(progress / duration, 1);
            window.scrollTo(0, startPosition + distance * percentage);

            if (progress < duration) {
                window.requestAnimationFrame(step);
            }
        });
    }
};

const scrollToFeatures = () => smoothScroll('features-section');
const scrollToAbout = () => smoothScroll('about-section');
</script>
