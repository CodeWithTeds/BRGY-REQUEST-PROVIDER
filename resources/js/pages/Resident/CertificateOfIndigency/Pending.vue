<template>
  <app-layout>
    <div class="flex items-center justify-center min-h-[calc(100vh-150px)] bg-gray-100 px-4 sm:px-6 lg:px-8">
      <div class="w-full max-w-6xl p-8 bg-white rounded-lg shadow-lg">
        <div class="flex flex-col md:flex-row">
          <div class="md:w-1/2 md:pr-8">
            <h1 class="mb-4 text-3xl font-bold text-main sm:text-4xl">Application Pending</h1>
            <p class="mb-4 text-lg text-gray-600">Your certificate of indigency application is currently being processed.
              Please wait for it to be approved.</p>
            <p class="mb-4 text-gray-500">We are working diligently to review your application. This process typically
              takes 1-2 business days. You will receive a notification once your application has been approved.</p>
            <h2 class="mt-6 mb-2 text-xl font-semibold text-gray-700 sm:text-2xl">What to expect next:</h2>
            <ul class="pl-5 space-y-2 text-gray-500 list-disc">
              <li>Our team will verify the documents you submitted.</li>
              <li>We may contact you if we need additional information.</li>
              <li>Once approved, you will be able to download your Certificate of Indigency from your dashboard.</li>
              <li>If your application is rejected, we will provide the reason for rejection.</li>
            </ul>
            <h2 class="mt-6 mb-2 text-xl font-semibold text-gray-700 sm:text-2xl">Please note:</h2>
            <p class="text-gray-500">Any attempt to submit multiple applications while one is already pending may result
              in delays or rejection of all applications. Please be patient. We appreciate your understanding.</p>
          </div>
          <div class="flex flex-col items-center justify-center mt-8 md:w-1/2 md:mt-0">
            <img src="/images/thankyou.png" alt="Thank You"
              class="max-w-xs mx-auto h-auto rounded-lg sm:max-w-sm md:max-w-md">
            <status-tracker :current-status="indigency.status" />
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import StatusTracker from '@/components/StatusTracker.vue';

defineProps({
  indigency: {
    type: Object,
    required: true,
  },
});
</script>