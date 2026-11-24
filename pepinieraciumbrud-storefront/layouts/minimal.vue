<script setup lang="ts">
import { useSiteSettings } from '~/composables/useSiteSettings'
import { useCookieConsent } from '~/composables/useCookieConsent'
import CookieConsentBanner from '~/components/layout/CookieConsentBanner.vue'

const { settings, fetchSettings } = useSiteSettings()
const { openPreferences } = useCookieConsent()
await useAsyncData('site-settings-minimal', () => fetchSettings())
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background relative">
    <header class="py-4 border-b border-border bg-white relative z-10">
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

    <main class="flex-1 py-8 sm:py-12 relative z-10">
      <slot />
    </main>

    <footer class="py-6 border-t border-border bg-white/90 backdrop-blur-sm text-xs text-text-muted relative z-10">
      <div class="max-w-5xl mx-auto px-4 space-y-3">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="text-center sm:text-left">
            <span class="font-semibold text-text">{{ settings.company_legal_name || 'S.C. Pepiniera Ciumbrud S.R.L.' }}</span>
            <span v-if="settings.company_cui"> · CUI: {{ settings.company_cui }}</span>
            <span v-if="settings.company_reg_com"> · Reg. Com.: {{ settings.company_reg_com }}</span>
            <span v-if="settings.company_authorization"> · Autorizație: {{ settings.company_authorization }}</span>
          </div>
          <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
            <NuxtLink to="/garantie" class="hover:text-primary underline min-h-[44px] inline-flex items-center">Garanția Plantelor</NuxtLink>
            <NuxtLink to="/livrare-si-plata" class="hover:text-primary underline min-h-[44px] inline-flex items-center">Livrare și Plată</NuxtLink>
            <NuxtLink to="/termeni-si-conditii" class="hover:text-primary underline min-h-[44px] inline-flex items-center">Termeni și Condiții</NuxtLink>
            <NuxtLink to="/politica-de-confidentialitate" class="hover:text-primary underline min-h-[44px] inline-flex items-center">Confidențialitate</NuxtLink>
            <button type="button" @click="openPreferences" class="hover:text-primary underline min-h-[44px] inline-flex items-center">Cookies</button>
          </div>
        </div>

        <div class="pt-2 border-t border-border/60 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-text-muted">
          <span>© {{ new Date().getFullYear() }} {{ settings.footer_copyright || `${settings.site_title}. Toate drepturile rezervate.` }}</span>
          <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1">
            <a href="https://anpc.ro/" target="_blank" rel="noopener noreferrer" class="hover:text-primary underline min-h-[44px] inline-flex items-center">ANPC</a>
            <a href="https://anpc.ro/ce-este-sal/" target="_blank" rel="noopener noreferrer" class="hover:text-primary underline min-h-[44px] inline-flex items-center">ANPC – SAL</a>
            <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener noreferrer" class="hover:text-primary underline min-h-[44px] inline-flex items-center">SOL / ODR</a>
          </div>
        </div>
      </div>
    </footer>

    <CookieConsentBanner />
  </div>
</template>
