<template>
  <header class="fixed inset-x-0 top-0 z-30">
    <nav
      class="mx-auto flex items-center justify-between px-4 md:px-8 py-3 bg-gradient-to-r from-[#1F5E88] via-[#0B4F6C] to-[#063B52] text-white shadow-md"
      aria-label="Global"
    >
      <!-- Brand -->
      <div class="flex items-center gap-3">
        <Link :href="route('home')" class="inline-flex items-center gap-3">
          <img class="h-10 w-auto" src="/images/brg.png" alt="BRGY Request Provider Logo" />
          <span class="hidden sm:block text-sm font-semibold tracking-wide">BRGY Request Provider</span>
        </Link>
      </div>

      <!-- Desktop Nav -->
      <div class="hidden md:flex items-center gap-6">
        <Link :href="route('home')" class="text-sm font-medium text-white/90 hover:text-white transition">Home</Link>
        <a href="#features-section" @click.prevent="scrollTo('features-section')" class="text-sm font-medium text-white/90 hover:text-white transition">Features</a>
        <a href="#about-section" @click.prevent="scrollTo('about-section')" class="text-sm font-medium text-white/90 hover:text-white transition">About</a>
        <Link :href="loginHref" class="inline-flex items-center gap-2 rounded-full border-2 border-white/70 bg-white px-4 py-2 text-[#0B4F6C] transition hover:bg-transparent hover:text-white">
          Get Started
          <ChevronRight class="h-4 w-4" />
        </Link>
      </div>

      <!-- Mobile Trigger -->
      <button
        class="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-white hover:bg-white/30 transition"
        @click="open = !open"
        aria-label="Toggle Menu"
      >
        <component :is="open ? X : Menu" class="h-5 w-5" />
      </button>
    </nav>

    <!-- Mobile Menu -->
    <div v-if="open" class="md:hidden px-4 py-4 bg-gradient-to-r from-[#0B4F6C] to-[#063B52] text-white shadow-md">
      <div class="flex flex-col gap-3">
        <Link :href="route('home')" class="text-sm font-medium" @click="open=false">Home</Link>
        <a href="#features-section" class="text-sm font-medium" @click.prevent="scrollTo('features-section'); open=false">Features</a>
        <a href="#about-section" class="text-sm font-medium" @click.prevent="scrollTo('about-section'); open=false">About</a>
        <Link :href="loginHref" class="inline-flex items-center gap-2 rounded-lg border-2 border-white/70 bg-white px-4 py-2 text-[#0B4F6C] transition hover:bg-transparent hover:text-white" @click="open=false">
          Get Started
          <ChevronRight class="h-4 w-4" />
        </Link>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Menu, X, ChevronRight } from 'lucide-vue-next';

const open = ref(false);

const scrollTo = (elementId: string) => {
  const el = document.getElementById(elementId);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const loginHref = computed(() => {
  try {
    return (window as any).route ? (window as any).route('login') : '/login';
  } catch {
    return '/login';
  }
});
</script>
