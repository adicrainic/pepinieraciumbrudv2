<script setup lang="ts">
import { ref } from 'vue'
import { useCookieConsent } from '~/composables/useCookieConsent'

const {
  consent,
  hasDecided,
  shouldShowBanner,
  acceptAll,
  acceptNecessaryOnly,
  closePreferences
} = useCookieConsent()

const showDetails = ref(false)
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-4"
  >
    <aside
      v-if="shouldShowBanner"
      role="region"
      aria-label="Informații privind modulele cookie și analiza traficului"
      class="fixed bottom-0 inset-x-0 z-50 p-3 sm:p-5 pointer-events-none"
    >
      <div class="max-w-4xl mx-auto bg-white/95 backdrop-blur-md border border-border rounded-2xl shadow-2xl p-4 sm:p-6 pointer-events-auto text-text">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
          <div class="space-y-2 flex-1">
            <div class="flex items-center justify-between gap-2">
              <h3 class="font-serif text-base sm:text-lg font-bold text-primary-dark flex items-center gap-2">
                <span>🍪</span>
                <span>Respectăm confidențialitatea dumneavoastră</span>
              </h3>
              <button
                v-if="hasDecided"
                type="button"
                @click="closePreferences"
                class="text-xs text-text-muted hover:text-text px-2 py-1 rounded-lg"
                aria-label="Închide preferințele cookie"
              >
                ✕
              </button>
            </div>

            <p class="text-xs sm:text-sm text-text-muted leading-relaxed">
              Folosim stocarea locală strict necesară pentru funcționarea coșului de cumpărături și, doar cu acordul dumneavoastră, un sistem intern anonimizat de analiză a traficului (fără cookie-uri publicitare terțe). Consultați
              <NuxtLink to="/politica-de-confidentialitate" class="text-primary underline hover:text-primary-dark font-semibold">
                Politica de Confidențialitate și Cookies
              </NuxtLink>.
            </p>

            <button
              type="button"
              @click="showDetails = !showDetails"
              class="text-xs font-semibold text-primary hover:text-primary-dark underline inline-flex items-center gap-1"
            >
              <span>{{ showDetails ? 'Ascunde detaliile tehnice ▲' : 'Detalii categorii utilizate ▼' }}</span>
            </button>

            <div
              v-if="showDetails"
              class="mt-3 pt-3 border-t border-border grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs"
            >
              <div class="p-3 rounded-xl bg-surface-warm border border-border space-y-1">
                <div class="flex items-center justify-between font-bold text-text">
                  <span>1. Strict Necesare (Coș &amp; Sesiune)</span>
                  <span class="text-[11px] px-2 py-0.5 rounded-full bg-leaf-50 text-leaf-800 border border-leaf-200">Mereu active</span>
                </div>
                <p class="text-text-muted leading-relaxed">
                  Păstrează produsele adăugate în coș (<code class="font-mono text-[11px]">pcs_wc_cart_token</code>) și opțiunea dumneavoastră de consimțământ (<code class="font-mono text-[11px]">pcs_cookie_consent</code>).
                </p>
              </div>

              <div class="p-3 rounded-xl bg-surface-warm border border-border space-y-1">
                <div class="flex items-center justify-between font-bold text-text">
                  <span>2. Analiză Trafic Intern (First-Party)</span>
                  <span
                    :class="[
                      'text-[11px] px-2 py-0.5 rounded-full border',
                      consent === 'all'
                        ? 'bg-leaf-50 text-leaf-800 border-leaf-200'
                        : 'bg-white text-text-muted border-border'
                    ]"
                  >
                    {{ consent === 'all' ? 'Activ' : 'Opțional' }}
                  </span>
                </div>
                <p class="text-text-muted leading-relaxed">
                  Măsoară anonim paginile vizitate în magazinul nostru (hash zilnic <code class="font-mono text-[11px]">SHA-256</code> pe serverul propriu, fără partajare cu rețele publicitare).
                </p>
              </div>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 shrink-0 md:min-w-[200px] justify-end">
            <button
              type="button"
              @click="acceptAll"
              class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs sm:text-sm font-semibold transition-colors shadow-sm text-center"
            >
              Acceptă toate
            </button>
            <button
              type="button"
              @click="acceptNecessaryOnly"
              class="px-4 py-2.5 rounded-xl bg-surface-warm hover:bg-border/50 text-text border border-border text-xs sm:text-sm font-semibold transition-colors text-center"
            >
              Doar strict necesare
            </button>
          </div>
        </div>
      </div>
    </aside>
  </Transition>
</template>
