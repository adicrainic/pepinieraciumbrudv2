<script setup lang="ts">
import TheHeader from '~/components/layout/TheHeader.vue'
import TheFooter from '~/components/layout/TheFooter.vue'
import MobileMenu from '~/components/layout/MobileMenu.vue'
import CartDrawer from '~/components/cart/CartDrawer.vue'
import LogoMark from '~/components/ui/LogoMark.vue'
import { useSiteSettings } from '~/composables/useSiteSettings'

const { fetchSettings } = useSiteSettings()
await useAsyncData('site-settings', () => fetchSettings())

const route = useRoute()
const isHomePage = computed(() => route.path === '/')
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background selection:bg-accent selection:text-white font-sans relative">
    <TheHeader />
    <MobileMenu />
    <CartDrawer />

    <!-- Centered Brand Watermark Motif across all pages (full page height & wider) -->
    <div
      class="pointer-events-none fixed inset-0 flex items-center justify-center overflow-hidden z-0 select-none"
      aria-hidden="true"
    >
      <div class="h-screen w-auto flex items-center justify-center">
        <LogoMark
          :rose="false"
          class="h-full w-auto scale-x-125 sm:scale-x-135 text-primary/[0.05] drop-shadow-[0_2px_20px_rgba(178,17,17,0.06)]"
        />
      </div>
    </div>

    <main class="flex-1 relative z-10">
      <slot />
    </main>

    <TheFooter />
  </div>
</template>

