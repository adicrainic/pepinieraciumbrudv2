<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import { useUiStore } from '~/stores/ui'
import { useSiteSettings } from '~/composables/useSiteSettings'
import { useProducts } from '~/composables/useProducts'
import { storeApi } from '~/services/storeApi'
import type { Product } from '~/types/product'

const cartStore = useCartStore()
const uiStore = useUiStore()
const { settings } = useSiteSettings()
const { formatPrice, getProductImage } = useProducts()

const whatsappUrl = computed(() => {
  const raw = settings.value?.contact_phone || '+40 765 152 832'
  let digits = raw.replace(/\D/g, '')
  if (digits.startsWith('0') && digits.length === 10) {
    digits = '40' + digits.slice(1)
  }
  return `https://wa.me/${digits || '40765152832'}`
})

const isScrolled = ref(false)
const searchQuery = ref('')
const searchResults = ref<Product[]>([])
const isSearching = ref(false)
let searchTimeout: any = null

const cartItemCountFormatted = computed(() => {
  const count = cartStore.itemCount
  if (count > 9999) return '9999+'
  return count
})

onMounted(() => {
  window.addEventListener('scroll', () => {
    isScrolled.value = window.scrollY > 20
  })
})

const onSearchInput = () => {
  clearTimeout(searchTimeout)
  const q = searchQuery.value.trim()
  if (q.length < 2) {
    searchResults.value = []
    isSearching.value = false
    return
  }

  isSearching.value = true
  searchTimeout = setTimeout(async () => {
    try {
      const results = await storeApi.getProducts({ search: q, per_page: 5 })
      searchResults.value = results || []
    } catch {
      searchResults.value = []
    } finally {
      isSearching.value = false
    }
  }, 250)
}

const handleSelectResult = (slug: string) => {
  uiStore.closeSearch()
  searchQuery.value = ''
  searchResults.value = []
  navigateTo(`/produse/${slug}`)
}

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    navigateTo(`/produse?search=${encodeURIComponent(searchQuery.value.trim())}`)
    uiStore.closeSearch()
    searchQuery.value = ''
    searchResults.value = []
  }
}
</script>

<template>
  <!-- Sticky Header Container (Pins to top on ALL screens; red bar and navigation stay visible when scrolling) -->
  <header class="sticky top-0 z-40">
    <!-- Top Announcement & Phone Bar (Always visible on desktop & mobile) -->
    <div class="bg-primary text-white border-b border-primary-dark/20 shadow-sm transition-all duration-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 sm:py-2.5 flex items-center justify-between gap-3 sm:gap-6">
        <!-- Delivery info badge / text -->
        <div class="flex items-center gap-2 min-w-0 text-xs sm:text-sm font-medium tracking-wider uppercase text-white/95">
          <span class="inline-block w-2 h-2 rounded-full bg-white flex-shrink-0 shadow-sm"></span>
          <span class="truncate">
            <span class="sm:hidden">Livrare în toată țara</span>
            <span class="hidden sm:inline">Livrare în toată țara · Ambalare specială pentru rădăcini</span>
          </span>
        </div>

        <!-- Phone, Socials & Secondary Link -->
        <div class="flex items-center gap-3.5 sm:gap-6 flex-shrink-0">
          <!-- Social icons (Desktop) -->
          <div class="hidden md:flex items-center gap-3 border-r border-white/20 pr-4">
            <a
              href="https://www.facebook.com/profile.php?id=61554189699153"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Facebook"
              class="text-white/80 hover:text-white hover:scale-110 transition-all flex items-center"
            >
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a
              href="https://www.instagram.com/pepinieraciumbrud/"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Instagram"
              class="text-white/80 hover:text-white hover:scale-110 transition-all flex items-center"
            >
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </a>
            <a
              :href="whatsappUrl"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="WhatsApp"
              class="text-white/80 hover:text-white hover:scale-110 transition-all flex items-center"
            >
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
              </svg>
            </a>
          </div>

          <!-- Highlighted, Prominent Phone Number -->
          <a
            v-if="settings.contact_phone"
            :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`"
            class="flex items-center gap-1.5 sm:gap-2 text-white font-bold text-xs sm:text-sm md:text-[15px] lg:text-base tracking-normal hover:text-white/90 transition-colors"
          >
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-current text-white flex-shrink-0" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/>
            </svg>
            <span class="whitespace-nowrap">{{ settings.contact_phone }}</span>
          </a>

          <!-- Livrare & Plată link (Desktop) -->
          <NuxtLink
            to="/livrare-si-plata"
            class="hidden sm:inline text-white/85 hover:text-white text-xs sm:text-sm uppercase tracking-wider font-medium transition-colors"
          >
            Livrare &amp; plată
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Main Navigation Bar -->
    <div
      class="transition-all duration-300 backdrop-blur-md"
      :class="[
        isScrolled ? 'bg-white/95 border-b border-primary/20 py-1 shadow-sm' : 'bg-white border-b border-primary/10 py-1.5 sm:py-2'
      ]"
    >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-[auto_1fr_auto] items-center gap-3 sm:gap-6">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-2.5 sm:gap-3.5 group order-1 justify-self-start">
          <div class="flex items-center justify-center flex-shrink-0">
            <img
              v-if="settings.logo_url"
              :src="settings.logo_url"
              :alt="settings.site_title"
              class="mix-blend-multiply h-11 sm:h-[4.5rem] w-auto object-contain transition-transform duration-200 group-hover:scale-105 select-none"
            />
            <svg
              v-else
              class="h-10 w-10 sm:h-12 sm:w-12 text-primary transition-transform duration-200 group-hover:scale-105"
              fill="currentColor"
              viewBox="0 0 24 24"
            >
              <path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.3c.48.17.98.3 1.34.3C19 20 22 3 22 3c-1 2-8 2.25-13 3.25S2 11.5 2 13.5s1.75 3.75 1.75 3.75C7 8 17 8 17 8z"/>
            </svg>
          </div>
          <div class="flex flex-col justify-center border-l border-primary/20 pl-2.5 sm:pl-4">
            <span class="font-serif text-lg sm:text-2xl lg:text-3xl font-medium leading-none text-primary-dark tracking-wide">{{ settings.site_title || 'Pepiniera Ciumbrud' }}</span>
            <span class="mt-1 sm:mt-1.5 text-[11px] sm:text-xs uppercase tracking-[0.18em] sm:tracking-[0.24em] text-accent font-medium">{{ settings.site_tagline || 'Din 2000 · Satul Rozelor' }}</span>
          </div>
        </NuxtLink>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-9 order-2 justify-self-center">
          <NuxtLink
            to="/"
            class="link-underline inline-block text-xs lg:text-sm uppercase tracking-[0.18em] font-medium text-text hover:text-accent pb-1"
            exact-active-class="!text-accent"
          >
            Acasă
          </NuxtLink>
          <NuxtLink
            to="/produse"
            class="link-underline inline-block text-xs lg:text-sm uppercase tracking-[0.18em] font-medium text-text hover:text-accent pb-1"
            active-class="!text-accent"
          >
            Magazin & Produse
          </NuxtLink>
          <NuxtLink
            to="/despre-noi"
            class="link-underline inline-block text-xs lg:text-sm uppercase tracking-[0.18em] font-medium text-text hover:text-accent pb-1"
            active-class="!text-accent"
          >
            Despre noi
          </NuxtLink>
          <NuxtLink
            to="/contact"
            class="link-underline inline-block text-xs lg:text-sm uppercase tracking-[0.18em] font-medium text-text hover:text-accent pb-1"
            active-class="!text-accent"
          >
            Contact
          </NuxtLink>
        </nav>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 order-3 col-start-3">
          <!-- Search trigger -->
          <button
            @click="uiStore.toggleSearch()"
            class="p-2 text-text-muted hover:text-primary rounded-lg hover:bg-surface-warm transition-colors"
            aria-label="Căutare"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </button>

          <!-- Cart button -->
          <button
            @click="cartStore.toggleDrawer()"
            class="relative p-2 text-primary hover:text-primary-dark rounded-lg hover:bg-primary-50 transition-colors flex items-center gap-2"
            aria-label="Coș de cumpărături"
          >
            <div class="relative">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
              <span
                v-if="cartStore.itemCount > 0"
                class="absolute top-0 right-0 -translate-y-1/3 translate-x-1/3 bg-accent text-white font-medium rounded-full min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[11px] leading-none shadow-sm whitespace-nowrap tracking-tight tabular-nums select-none"
              >
                {{ cartItemCountFormatted }}
              </span>
            </div>
            <span class="hidden sm:inline text-sm font-semibold text-text">
              {{ cartStore.formattedTotal }}
            </span>
          </button>

          <!-- Mobile menu toggle -->
          <button
            @click="uiStore.toggleMobileMenu()"
            class="md:hidden p-2 text-text hover:text-primary rounded-lg hover:bg-surface-warm"
            aria-label="Meniu"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Quick Search Overlay -->
      <div v-if="uiStore.searchOpen" class="pt-3 pb-2 border-t border-border mt-3 animate-fade-in relative">
        <form @submit.prevent="handleSearch" class="relative flex items-center">
          <input
            v-model="searchQuery"
            @input="onSearchInput"
            type="text"
            placeholder="Căutați soiuri de măr, păr, cireș, trandafiri, arbuști..."
            class="w-full bg-surface-warm border border-border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white text-text pr-20"
            autoFocus
          />
          <button
            type="submit"
            class="absolute right-2 px-3 py-1 bg-primary text-white border border-primary text-xs font-medium rounded-lg hover:bg-white hover:text-primary transition-all duration-200 active:scale-[0.98]"
          >
            Caută
          </button>
        </form>

        <!-- Live Instant Search Dropdown -->
        <div
          v-if="searchQuery.trim().length >= 2"
          class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl border border-border shadow-xl z-50 overflow-hidden divide-y divide-border/60 max-h-80 overflow-y-auto"
        >
          <div v-if="isSearching" class="p-4 text-center text-xs text-text-muted">
            Căutare soiuri pomicole...
          </div>
          <div v-else-if="searchResults.length === 0" class="p-4 text-center text-xs text-text-muted">
            Nu am găsit niciun soi pentru „{{ searchQuery }}”. Apăsați Enter pentru căutare avansată.
          </div>
          <div v-else>
            <div
              v-for="item in searchResults"
              :key="item.id"
              @click="handleSelectResult(item.slug)"
              class="p-3.5 hover:bg-surface-warm/70 cursor-pointer flex items-center justify-between gap-3 transition-colors"
            >
              <div class="flex items-center gap-3 min-w-0">
                <img
                  :src="getProductImage(item)"
                  :alt="item.name"
                  class="w-11 h-11 rounded-lg object-cover border border-border bg-surface-warm flex-shrink-0"
                  onerror="this.src='/placeholder-plant.svg'"
                />
                <div class="min-w-0">
                  <div class="font-serif font-bold text-base text-primary-dark hover:underline truncate">
                    {{ item.name }}
                  </div>
                  <div class="text-xs text-text-muted truncate">
                    {{ item.categories && item.categories[0] ? item.categories[0].name : 'Pepinieră' }}
                    <span v-if="item.sku" class="font-mono text-gray-400"> • {{ item.sku }}</span>
                  </div>
                </div>
              </div>
              <div class="text-right flex-shrink-0">
                <div class="font-bold text-sm text-text">
                  {{ formatPrice(item.prices) }}
                </div>
                <span
                  class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full"
                  :class="item.is_in_stock ? 'bg-leaf-50 text-leaf-700' : 'bg-gray-100 text-gray-500'"
                >
                  {{ item.is_in_stock ? 'În stoc' : 'Epuizat' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>
</template>

