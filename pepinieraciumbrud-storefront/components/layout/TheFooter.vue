<script setup lang="ts">
import { useSiteSettings } from '~/composables/useSiteSettings'

const { settings } = useSiteSettings()
</script>

<template>
  <footer class="bg-primary-dark text-white mt-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
        <!-- Brand Info -->
        <div class="space-y-4 md:col-span-1">
          <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-lg bg-white text-primary-dark flex items-center justify-center font-bold overflow-hidden">
              <img
                v-if="settings.logo_url"
                :src="settings.logo_url"
                :alt="settings.site_title"
                class="max-h-full max-w-full object-contain p-1"
              />
              <span v-else>🌿</span>
            </div>
            <span class="font-serif text-xl font-bold tracking-tight">{{ settings.site_title }}</span>
          </div>
          <p class="text-xs text-white/70 leading-relaxed">
            {{ settings.site_description }}
          </p>
          <div class="pt-2 text-xs text-white/60">
            {{ settings.contact_address }}
          </div>
        </div>

        <!-- Links 1 -->
        <div>
          <h4 class="text-sm font-semibold uppercase tracking-wider text-accent mb-4">Produse</h4>
          <ul class="space-y-2 text-sm text-white/80">
            <li><NuxtLink to="/produse?category=meri" class="hover:text-accent transition-colors">Meri & Peri</NuxtLink></li>
            <li><NuxtLink to="/produse?category=pruni" class="hover:text-accent transition-colors">Pruni & Cireși</NuxtLink></li>
            <li><NuxtLink to="/produse?category=caisi" class="hover:text-accent transition-colors">Caiși & Piersici</NuxtLink></li>
            <li><NuxtLink to="/produse?category=trandafiri" class="hover:text-accent transition-colors">Trandafiri Teahibrizi</NuxtLink></li>
            <li><NuxtLink to="/produse?category=arbusti" class="hover:text-accent transition-colors">Arbuști Fructiferi</NuxtLink></li>
          </ul>
        </div>

        <!-- Links 2 -->
        <div>
          <h4 class="text-sm font-semibold uppercase tracking-wider text-accent mb-4">Informații Utile</h4>
          <ul class="space-y-2 text-sm text-white/80">
            <li><NuxtLink to="/despre-noi" class="hover:text-accent transition-colors">Despre Pepinieră</NuxtLink></li>
            <li><NuxtLink to="/livrare-si-plata" class="hover:text-accent transition-colors">Livrare & Plată</NuxtLink></li>
            <li><NuxtLink to="/termeni-si-conditii" class="hover:text-accent transition-colors">Termeni și Condiții</NuxtLink></li>
            <li><NuxtLink to="/politica-de-confidentialitate" class="hover:text-accent transition-colors">Politica de Confidențialitate</NuxtLink></li>
            <li><NuxtLink to="/contact" class="hover:text-accent transition-colors">Contact & Locație</NuxtLink></li>
          </ul>
        </div>

        <!-- Contact Box -->
        <div class="space-y-3">
          <h4 class="text-sm font-semibold uppercase tracking-wider text-accent mb-4">Contact Direct</h4>
          <p class="text-xs text-white/70">
            Aveți întrebări despre plantare sau comenzi en-gros? Echipa noastră vă stă la dispoziție:
          </p>
          <div class="space-y-1 text-sm font-medium">
            <div class="text-accent-warm font-semibold">
              <a :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`" class="hover:underline">
                📞 {{ settings.contact_phone }}
              </a>
            </div>
            <div class="text-white/90">
              <a :href="`mailto:${settings.contact_email}`" class="hover:underline">
                ✉️ {{ settings.contact_email }}
              </a>
            </div>
            <div class="text-white/60 text-xs">
              {{ settings.schedule_weekdays }}
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

