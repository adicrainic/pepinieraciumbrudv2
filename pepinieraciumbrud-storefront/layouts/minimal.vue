<script setup lang="ts">
import { useSiteSettings } from '~/composables/useSiteSettings'

const { settings, fetchSettings } = useSiteSettings()
await useAsyncData('site-settings-minimal', () => fetchSettings())
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background">
    <header class="py-6 border-b border-border bg-white text-center">
      <NuxtLink to="/" class="inline-flex items-center space-x-2">
        <img
          v-if="settings.logo_url"
          :src="settings.logo_url"
          :alt="settings.site_title"
          class="h-8 w-auto object-contain inline-block"
        />
        <span v-else class="text-2xl">🌿</span>
        <span class="font-serif text-2xl font-bold text-primary-dark">{{ settings.site_title }}</span>
      </NuxtLink>
    </header>

    <main class="flex-1 py-12">
      <slot />
    </main>

    <footer class="py-6 border-t border-border bg-white text-center text-xs text-text-muted">
      © {{ new Date().getFullYear() }} {{ settings.footer_copyright || `${settings.site_title}. Plăți securizate & Date protejate.` }}
    </footer>
  </div>
</template>

