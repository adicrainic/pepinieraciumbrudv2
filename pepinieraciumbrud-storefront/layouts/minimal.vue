<script setup lang="ts">
import { useSiteSettings } from '~/composables/useSiteSettings'

const { settings, fetchSettings } = useSiteSettings()
await useAsyncData('site-settings-minimal', () => fetchSettings())
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background">
    <header class="py-4 border-b border-border bg-white">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-4">
        <NuxtLink
          to="/cos"
          class="inline-flex items-center gap-1.5 text-xs font-medium text-text-muted hover:text-primary transition-colors"
        >
          <span>←</span>
          <span>Înapoi la coș</span>
        </NuxtLink>

        <NuxtLink to="/" class="inline-flex items-center space-x-2.5">
          <img
            v-if="settings.logo_url"
            :src="settings.logo_url"
            :alt="settings.site_title"
            class="h-10 w-auto object-contain inline-block mix-blend-multiply"
          />
          <span v-else class="text-2xl">🌿</span>
          <span class="font-serif text-xl sm:text-2xl font-bold text-primary-dark">{{ settings.site_title }}</span>
        </NuxtLink>

        <a
          v-if="settings.contact_phone"
          :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary-dark transition-colors"
        >
          <span>📞</span>
          <span class="hidden sm:inline">Suport:</span>
          <span>{{ settings.contact_phone }}</span>
        </a>
      </div>
    </header>

    <main class="flex-1 py-8 sm:py-12">
      <slot />
    </main>

    <footer class="py-6 border-t border-border bg-white text-center text-xs text-text-muted">
      <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <span>© {{ new Date().getFullYear() }} {{ settings.footer_copyright || `${settings.site_title}. Plăți securizate & Date protejate.` }}</span>
        <div class="flex items-center gap-4">
          <NuxtLink to="/livrare-si-plata" class="hover:text-primary underline">Livrare și Plată</NuxtLink>
          <NuxtLink to="/termeni-si-conditii" class="hover:text-primary underline">Termeni și Condiții</NuxtLink>
        </div>
      </div>
    </footer>
  </div>
</template>

