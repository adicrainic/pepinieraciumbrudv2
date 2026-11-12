<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import { useUiStore } from '~/stores/ui'
import { useSiteSettings } from '~/composables/useSiteSettings'
import { storeApi } from '~/services/storeApi'
import type { Product } from '~/types/product'

const cartStore = useCartStore()
const uiStore = useUiStore()
const { settings } = useSiteSettings()

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
  <div class="hidden md:block bg-gradient-to-r from-primary-dark via-[#247545] to-primary-dark text-white/95 text-xs border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between">
      <span class="flex items-center gap-2">
        <span class="inline-block w-1.5 h-1.5 rounded-full bg-accent-light"></span>
        🚚 Livrare în toată țara · Ambalare specială pentru rădăcini
      </span>
      <span class="flex items-center gap-4">
        <span v-if="settings.contact_phone">📞 {{ settings.contact_phone }}</span>
        <NuxtLink to="/livrare-si-plata" class="hover:text-accent-light transition-colors">Livrare &amp; plată</NuxtLink>
      </span>
    </div>
  </div>
  <header
    class="sticky top-0 z-40 transition-all duration-300 backdrop-blur-md"
    :class="[
      isScrolled ? 'bg-background/95 shadow-soft border-b border-border/80 py-3' : 'bg-background border-b border-border/60 py-4'
    ]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-3.5 group">
          <div class="flex items-center justify-center flex-shrink-0">
            <img
              v-if="settings.logo_url"
              :src="settings.logo_url"
              :alt="settings.site_title"
              class="h-11 sm:h-12 w-auto object-contain transition-transform duration-200 group-hover:scale-105 select-none"
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
          <div class="flex flex-col justify-center">
            <span class="font-serif text-xl sm:text-2xl font-bold tracking-tight text-primary-dark block leading-tight">
              {{ settings.site_title }}
            </span>
            <span class="text-[11px] sm:text-xs tracking-wider uppercase text-text-muted font-sans font-medium block leading-snug">
              {{ settings.site_tagline }}
            </span>
          </div>
        </NuxtLink>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
          <NuxtLink
            to="/"
            class="text-sm font-medium text-text hover:text-primary transition-colors"
            active-class="text-primary font-semibold"
          >
            Acasă
          </NuxtLink>
          <NuxtLink
            to="/produse"
            class="text-sm font-medium text-text hover:text-primary transition-colors"
            active-class="text-primary font-semibold"
          >
            Magazin & Produse
          </NuxtLink>
          <NuxtLink
            to="/despre-noi"
            class="text-sm font-medium text-text hover:text-primary transition-colors"
            active-class="text-primary font-semibold"
          >
            Despre noi
          </NuxtLink>
          <NuxtLink
            to="/contact"
            class="text-sm font-medium text-text hover:text-primary transition-colors"
            active-class="text-primary font-semibold"
          >
            Contact
          </NuxtLink>
        </nav>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-3">
          <!-- Search trigger -->
          <button
            @click="uiStore.toggleSearch()"
            class="p-2 text-text-muted hover:text-primary rounded-lg hover:bg-surface-warm transition-colors"
            aria-label="Căutare"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
              <span
                v-if="cartStore.itemCount > 0"
                class="absolute top-0 right-0 -translate-y-1/3 translate-x-1/3 bg-rose-500 text-white font-bold rounded-full min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[10px] leading-none shadow-sm whitespace-nowrap tracking-tight tabular-nums select-none"
              >
                {{ cartItemCountFormatted }}
              </span>
            </div>
            <span class="hidden sm:inline text-xs font-semibold text-text">
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
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
            class="absolute right-2 px-3 py-1 bg-primary text-white text-xs font-medium rounded-lg hover:bg-primary-dark"
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
              class="p-3.5 hover:bg-surface-warm/70 cursor-pointer flex items-center justify-between transition-colors"
            >
              <div class="flex items-center gap-3">
                <span class="text-xl">🌿</span>
                <div>
                  <div class="font-serif font-bold text-sm text-primary-dark hover:underline">
                    {{ item.name }}
                  </div>
                  <div class="text-[11px] text-text-muted">
                    {{ item.categories && item.categories[0] ? item.categories[0].name : 'Pepinieră' }}
                    <span v-if="item.sku" class="font-mono text-gray-400"> • {{ item.sku }}</span>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold text-sm text-text">
                  {{ (parseFloat(item.prices.price) / 100).toFixed(2).replace('.', ',') }} lei
                </div>
                <span
                  class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full"
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
  </header>
</template>

