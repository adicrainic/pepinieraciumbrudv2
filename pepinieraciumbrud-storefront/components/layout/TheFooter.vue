<script setup lang="ts">
import { useSiteSettings } from '~/composables/useSiteSettings'
import { useCookieConsent } from '~/composables/useCookieConsent'

const { settings } = useSiteSettings()
const { openPreferences } = useCookieConsent()
</script>

<template>
  <footer class="relative overflow-hidden bg-primary text-white mt-0">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
        <!-- Brand & Legal Identity Info -->
        <div class="space-y-4 md:col-span-1">
          <div class="flex items-center space-x-3">
            <div class="w-16 h-16 rounded-2xl bg-white text-primary flex items-center justify-center font-bold overflow-hidden shadow-lg ring-1 ring-white/20 shrink-0">
              <img
                v-if="settings.logo_url"
                :src="settings.logo_url"
                :alt="settings.site_title"
                class="mix-blend-multiply max-h-full max-w-full object-contain p-1.5"
              />
              <span v-else class="text-xl">🌿</span>
            </div>
            <span class="font-serif text-2xl font-medium tracking-wide">{{ settings.site_title || 'Pepiniera Ciumbrud' }}</span>
          </div>
          <p class="text-xs sm:text-sm uppercase tracking-wide text-rose-200">{{ settings.site_tagline || 'Pepiniera de familie din 2000' }}</p>
          <p class="text-sm text-white/80 leading-relaxed">
            {{ settings.site_description }}
          </p>

          <!-- Company Legal Data Card -->
          <div class="pt-3 border-t border-white/15 space-y-1 text-xs text-white/80">
            <div class="font-semibold text-white">
              {{ settings.company_legal_name || 'S.C. Pepiniera Ciumbrud S.R.L.' }}
            </div>
            <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-white/75">
              <span v-if="settings.company_cui">CUI: <strong class="font-medium text-white">{{ settings.company_cui }}</strong></span>
              <span v-if="settings.company_reg_com">Reg. Com.: <strong class="font-medium text-white">{{ settings.company_reg_com }}</strong></span>
            </div>
            <div v-if="settings.company_authorization" class="text-white/75">
              Autorizație fitosanitară: <strong class="font-medium text-white">{{ settings.company_authorization }}</strong>
            </div>
            <div class="text-white/70 pt-0.5">
              Sediu: {{ settings.contact_address }}
            </div>
          </div>
        </div>

        <!-- Links 1 -->
        <div>
          <h4 class="text-xs sm:text-sm font-semibold uppercase tracking-wide text-white mb-5 pb-3 border-b border-white/15 inline-block pr-8">Produse</h4>
          <ul class="space-y-2 text-sm text-white/80">
            <li><NuxtLink to="/produse?category=meri" class="hover:text-white transition-colors inline-block py-1">Meri &amp; Peri</NuxtLink></li>
            <li><NuxtLink to="/produse?category=pruni" class="hover:text-white transition-colors inline-block py-1">Pruni &amp; Cireși</NuxtLink></li>
            <li><NuxtLink to="/produse?category=caisi" class="hover:text-white transition-colors inline-block py-1">Caiși &amp; Piersici</NuxtLink></li>
            <li><NuxtLink to="/produse?category=trandafiri" class="hover:text-white transition-colors inline-block py-1">Trandafiri Teahibrizi</NuxtLink></li>
            <li><NuxtLink to="/produse?category=arbusti-fructiferi" class="hover:text-white transition-colors inline-block py-1">Arbuști Fructiferi</NuxtLink></li>
            <li><NuxtLink to="/produse?category=arbusti-ornamentali" class="hover:text-white transition-colors inline-block py-1">Arbuști Ornamentali</NuxtLink></li>
          </ul>
        </div>

        <!-- Links 2 -->
        <div>
          <h4 class="text-xs sm:text-sm font-semibold uppercase tracking-wide text-white mb-5 pb-3 border-b border-white/15 inline-block pr-8">Informații &amp; Legal</h4>
          <ul class="space-y-2 text-sm text-white/80">
            <li><NuxtLink to="/despre-noi" class="hover:text-white transition-colors inline-block py-1">Despre Pepinieră</NuxtLink></li>
            <li><NuxtLink to="/garantie" class="hover:text-white transition-colors inline-block py-1">Garanția Plantelor</NuxtLink></li>
            <li><NuxtLink to="/livrare-si-plata" class="hover:text-white transition-colors inline-block py-1">Livrare &amp; Plată</NuxtLink></li>
            <li><NuxtLink to="/termeni-si-conditii" class="hover:text-white transition-colors inline-block py-1">Termeni și Condiții (Retur)</NuxtLink></li>
            <li><NuxtLink to="/politica-de-confidentialitate" class="hover:text-white transition-colors inline-block py-1">Politica de Confidențialitate (GDPR)</NuxtLink></li>
            <li><NuxtLink to="/contact" class="hover:text-white transition-colors inline-block py-1">Contact &amp; Locație</NuxtLink></li>
            <li>
              <button
                type="button"
                @click="openPreferences"
                class="hover:text-white transition-colors text-left underline decoration-white/40 underline-offset-4 inline-flex items-center min-h-[44px]"
              >
                Preferințe Cookie &amp; Trafic
              </button>
            </li>
          </ul>
        </div>

        <!-- Contact Box -->
        <div class="space-y-3">
          <h4 class="text-xs sm:text-sm font-semibold uppercase tracking-wide text-white mb-5 pb-3 border-b border-white/15 inline-block pr-8">Contact Direct</h4>
          <p class="text-sm text-white/80">
            Aveți întrebări despre plantare sau comenzi en-gros? Echipa noastră vă stă la dispoziție:
          </p>
          <div class="space-y-1.5 text-sm font-medium">
            <div class="text-white font-medium">
              <a :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`" class="hover:underline inline-flex items-center min-h-[36px] sm:min-h-[44px]">
                {{ settings.contact_phone }}
              </a>
            </div>
            <div class="text-white/90">
              <a :href="`mailto:${settings.contact_email}`" class="hover:underline inline-flex items-center min-h-[36px] sm:min-h-[44px]">
                {{ settings.contact_email }}
              </a>
            </div>
            <div
              v-if="settings.schedule_weekdays || settings.schedule_saturday || settings.schedule_sunday"
              class="text-white/65 text-xs pt-1 flex items-start gap-1.5"
            >
              <span class="shrink-0 mt-1.5 w-1.5 h-1.5 rounded-full bg-white/70"></span>
              <div class="space-y-0.5">
                <div v-if="settings.schedule_weekdays" class="text-white/60">{{ settings.schedule_weekdays }}</div>
                <div v-if="settings.schedule_saturday" class="text-white/60">{{ settings.schedule_saturday }}</div>
                <div v-if="settings.schedule_sunday" class="text-white/60">{{ settings.schedule_sunday }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Consumer Protection Links (ANPC / SAL / SOL) -->
      <div class="mt-10 pt-6 border-t border-white/15 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="text-xs text-white/75 space-y-0.5">
          <div class="font-semibold text-white uppercase tracking-wide text-xs">Protecția Consumatorilor</div>
          <p class="text-white/65">
            Conform legislației în vigoare (OUG 34/2014 și OG 38/2015), consumatorii pot apela la mecanismele oficiale de soluționare a litigiilor:
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <a
            href="https://anpc.ro/"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-xs font-semibold text-white transition-colors min-h-[44px]"
          >
            <span>🛡️</span>
            <span>ANPC — Autoritatea Națională pentru Protecția Consumatorilor</span>
            <span class="text-white/60">↗</span>
          </a>

          <a
            href="https://anpc.ro/ce-este-sal/"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-xs font-semibold text-white transition-colors min-h-[44px]"
          >
            <span>⚖️</span>
            <span>ANPC — Soluționarea Alternativă a Litigiilor (SAL)</span>
            <span class="text-white/60">↗</span>
          </a>

          <a
            href="https://ec.europa.eu/consumers/odr"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-xs font-semibold text-white transition-colors min-h-[44px]"
          >
            <span>🇪🇺</span>
            <span>Soluționarea Online a Litigiilor (SOL / ODR)</span>
            <span class="text-white/60">↗</span>
          </a>
        </div>
      </div>

      <!-- Bottom Copyright Bar -->
      <div class="mt-6 pt-6 border-t border-white/15 flex flex-col sm:flex-row items-center justify-between text-xs text-white/60 gap-4">
        <div>
          © {{ new Date().getFullYear() }} {{ settings.footer_copyright || `${settings.site_title}. Toate drepturile rezervate.` }}
        </div>
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
          <span>Prețuri exprimate în RON cu TVA inclus.</span>
          <span>Produs în România</span>
        </div>
      </div>
    </div>
  </footer>
</template>
