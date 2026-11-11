<script setup lang="ts">
import TheHeader from '~/components/layout/TheHeader.vue'
import TheFooter from '~/components/layout/TheFooter.vue'
import MobileMenu from '~/components/layout/MobileMenu.vue'
import CartDrawer from '~/components/cart/CartDrawer.vue'
import { useSiteSettings } from '~/composables/useSiteSettings'

const { fetchSettings } = useSiteSettings()
await useAsyncData('site-settings', () => fetchSettings())
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background selection:bg-accent-light selection:text-primary-dark">
    <TheHeader />
    <MobileMenu />
    <CartDrawer />

    <main class="flex-1">
      <slot />
    </main>

    <TheFooter />
  </div>
</template>

