<script setup lang="ts">
import { useSiteSettings } from '~/composables/useSiteSettings'
import { onRequestCountChange, trackRequestStart, trackRequestEnd } from '~/services/storeApi'

const { fetchSettings } = useSiteSettings()
await useAsyncData('site-settings', () => fetchSettings())

const nuxtApp = useNuxtApp()
const activeRequests = ref(0)
const isPageNavigating = ref(false)
const showGlobalSpinner = ref(false)
let spinnerTimer: ReturnType<typeof setTimeout> | null = null

const isBusy = computed(() => activeRequests.value > 0 || isPageNavigating.value)

watch(
  isBusy,
  (busy) => {
    if (spinnerTimer) {
      clearTimeout(spinnerTimer)
      spinnerTimer = null
    }
    if (busy) {
      // Slight 120ms delay prevents sub-100ms flashes while showing immediately on any real request
      spinnerTimer = setTimeout(() => {
        if (isBusy.value) {
          showGlobalSpinner.value = true
        }
      }, 120)
    } else {
      showGlobalSpinner.value = false
    }
  },
  { immediate: true }
)

if (import.meta.client) {
  nuxtApp.hook('page:start', () => {
    isPageNavigating.value = true
  })
  nuxtApp.hook('page:finish', () => {
    isPageNavigating.value = false
  })
  nuxtApp.hook('app:error', () => {
    isPageNavigating.value = false
  })
}

onMounted(() => {
  onRequestCountChange((count) => {
    activeRequests.value = count
  })

  // Also intercept global window.fetch for any direct $fetch calls outside storeApi (e.g. contact form, settings)
  if (typeof window !== 'undefined' && typeof window.fetch === 'function' && !(window as any).__pcsFetchHooked) {
    ;(window as any).__pcsFetchHooked = true
    const originalFetch = window.fetch.bind(window)
    window.fetch = async (...args: Parameters<typeof fetch>) => {
      const urlStr = typeof args[0] === 'string' ? args[0] : (args[0] as Request)?.url || ''
      // Skip background traffic analytics beacon so it doesn't trigger a visible UI spinner
      const isSilentBeacon = urlStr.includes('/pcs/v1/traffic')
      if (!isSilentBeacon) trackRequestStart()
      try {
        return await originalFetch(...args)
      } finally {
        if (!isSilentBeacon) trackRequestEnd()
      }
    }
  }
})
</script>

<template>
  <NuxtLoadingIndicator color="#B21111" :height="3" :throttle="80" />

  <!-- Global Floating Loading Spinner Pill for any in-flight request or page navigation -->
  <Transition
    enter-active-class="transition duration-150 ease-out"
    enter-from-class="opacity-0 -translate-y-2 scale-95"
    enter-to-class="opacity-100 translate-y-0 scale-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 translate-y-0 scale-100"
    leave-to-class="opacity-0 -translate-y-2 scale-95"
  >
    <div
      v-if="showGlobalSpinner"
      class="fixed top-20 sm:top-24 right-3.5 sm:right-6 z-50 pointer-events-none flex items-center gap-2.5 bg-white/95 backdrop-blur-md border border-primary/25 text-primary-dark px-3.5 py-2 rounded-full shadow-lg"
      role="status"
      aria-live="polite"
    >
      <span
        class="animate-spin inline-block w-4 h-4 border-2 border-primary border-t-transparent rounded-full shrink-0"
      ></span>
      <span class="text-xs font-semibold tracking-wide whitespace-nowrap">Se încarcă...</span>
    </div>
  </Transition>

  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>
