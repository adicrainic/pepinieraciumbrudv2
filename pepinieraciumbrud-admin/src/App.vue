<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { onAdminRequestCountChange } from '@/services/api'

const activeRequests = ref(0)
const showSpinner = ref(false)
let timer: ReturnType<typeof setTimeout> | null = null

watch(activeRequests, (count) => {
  if (timer) {
    clearTimeout(timer)
    timer = null
  }
  if (count > 0) {
    timer = setTimeout(() => {
      if (activeRequests.value > 0) {
        showSpinner.value = true
      }
    }, 100)
  } else {
    showSpinner.value = false
  }
})

onMounted(() => {
  onAdminRequestCountChange((count) => {
    activeRequests.value = count
  })
})
</script>

<template>
  <!-- Global Floating Loading Spinner Pill for any in-flight Admin API request -->
  <Transition
    enter-active-class="transition duration-150 ease-out"
    enter-from-class="opacity-0 -translate-y-2 scale-95"
    enter-to-class="opacity-100 translate-y-0 scale-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 translate-y-0 scale-100"
    leave-to-class="opacity-0 -translate-y-2 scale-95"
  >
    <div
      v-if="showSpinner"
      class="fixed top-4 right-4 z-50 pointer-events-none flex items-center gap-2.5 bg-white/95 backdrop-blur-md border border-primary/25 text-gray-900 px-3.5 py-2 rounded-full shadow-lg"
      role="status"
      aria-live="polite"
    >
      <span
        class="animate-spin inline-block w-4 h-4 border-2 border-primary border-t-transparent rounded-full shrink-0"
      ></span>
      <span class="text-xs font-semibold tracking-wide whitespace-nowrap">Se încarcă...</span>
    </div>
  </Transition>

  <RouterView />
</template>
