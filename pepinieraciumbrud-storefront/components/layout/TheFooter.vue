<script setup lang="ts">
import { useSiteSettings } from '~/composables/useSiteSettings'

const { settings } = useSiteSettings()
</script>

<template>
  <footer class="bg-gradient-to-b from-primary-dark via-primary-dark to-[#134026] text-white mt-0 border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
        <!-- Brand Info -->
        <div class="space-y-4 md:col-span-1">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 text-white flex items-center justify-center font-bold overflow-hidden shadow-inner">
              <img
                v-if="settings.logo_url"
                :src="settings.logo_url"
                :alt="settings.site_title"
                class="max-h-full max-w-full object-contain p-1"
              />
              <span v-else class="text-xl">🌿</span>
            </div>
            <span class="font-serif text-xl font-bold tracking-tight">{{ settings.site_title }}</span>
          </div>
          <p class="text-xs text-white/75 leading-relaxed">
            {{ settings.site_description }}
          </p>
          <div class="pt-2 text-xs text-white/65">
            {{ settings.contact_address }}
          </div>
        </div>

        <!-- Links 1 -->
        <div>
          <h4 class="text-xs font-bold uppercase tracking-widest text-primary-light mb-4">Produse</h4>
          <ul class="space-y-2.5 text-sm text-white/80">
            <li><NuxtLink to="/produse?category=meri" class="hover:text-white transition-colors">Meri &amp; Peri</NuxtLink></li>
            <li><NuxtLink to="/produse?category=pruni" class="hover:text-white transition-colors">Pruni &amp; Cireși</NuxtLink></li>
            <li><NuxtLink to="/produse?category=caisi" class="hover:text-white transition-colors">Caiși &amp; Piersici</NuxtLink></li>
            <li><NuxtLink to="/produse?category=trandafiri" class="hover:text-white transition-colors">Trandafiri Teahibrizi</NuxtLink></li>
            <li><NuxtLink to="/produse?category=arbusti-fructiferi" class="hover:text-white transition-colors">Arbuști Fructiferi</NuxtLink></li>
            <li><NuxtLink to="/produse?category=arbusti-ornamentali" class="hover:text-white transition-colors">Arbuști Ornamentali</NuxtLink></li>
          </ul>
        </div>

        <!-- Links 2 -->
        <div>
          <h4 class="text-xs font-bold uppercase tracking-widest text-primary-light mb-4">Informații Utile</h4>
          <ul class="space-y-2.5 text-sm text-white/80">
            <li><NuxtLink to="/despre-noi" class="hover:text-white transition-colors">Despre Pepinieră</NuxtLink></li>
            <li><NuxtLink to="/livrare-si-plata" class="hover:text-white transition-colors">Livrare &amp; Plată</NuxtLink></li>
            <li><NuxtLink to="/termeni-si-conditii" class="hover:text-white transition-colors">Termeni și Condiții</NuxtLink></li>
            <li><NuxtLink to="/politica-de-confidentialitate" class="hover:text-white transition-colors">Politica de Confidențialitate</NuxtLink></li>
            <li><NuxtLink to="/contact" class="hover:text-white transition-colors">Contact &amp; Locație</NuxtLink></li>
          </ul>
        </div>

        <!-- Contact Box -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-widest text-primary-light mb-4">Contact Direct</h4>
          <p class="text-xs text-white/75">
            Aveți întrebări despre plantare sau comenzi en-gros? Echipa noastră vă stă la dispoziție:
          </p>
          <div class="space-y-1.5 text-sm font-medium">
            <div class="text-accent-light font-semibold">
              <a :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`" class="hover:underline">
                📞 {{ settings.contact_phone }}
              </a>
            </div>
            <div class="text-white/90">
              <a :href="`mailto:${settings.contact_email}`" class="hover:underline">
                ✉️ {{ settings.contact_email }}
              </a>
            </div>
            <div
              v-if="settings.schedule_weekdays || settings.schedule_saturday || settings.schedule_sunday"
              class="text-white/65 text-xs pt-1 flex items-start gap-1.5"
            >
              <span class="shrink-0 mt-0.5">🕒</span>
              <div class="space-y-0.5">
                <div v-if="settings.schedule_weekdays" class="text-white/60">{{ settings.schedule_weekdays }}</div>
                <div v-if="settings.schedule_saturday" class="text-white/60">{{ settings.schedule_saturday }}</div>
                <div v-if="settings.schedule_sunday" class="text-white/60">{{ settings.schedule_sunday }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-white/60 gap-4">
        <div>
          © {{ new Date().getFullYear() }} {{ settings.footer_copyright || `${settings.site_title}. Toate drepturile rezervate.` }}
        </div>
        <div class="flex items-center space-x-6">
          <span>Prețuri exprimate în RON cu TVA inclus.</span>
          <span>Produs în România 🇷🇴</span>
        </div>
      </div>
    </div>
  </footer>
</template>

