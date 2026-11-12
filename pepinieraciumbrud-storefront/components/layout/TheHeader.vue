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
  <div class="hidden md:block bg-primary text-white/90 text-[11px] uppercase tracking-[0.2em]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between">
      <span class="flex items-center gap-2">
        <span class="inline-block w-1.5 h-1.5 rounded-full bg-accent"></span>
        Livrare în toată țara · Ambalare specială pentru rădăcini
      </span>
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2.5">
          <a
            href="https://www.facebook.com/profile.php?id=61554189699153"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Facebook"
            class="text-white/80 hover:text-white transition-colors flex items-center"
          >
            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </a>
          <a
            href="https://www.instagram.com/pepinieraciumbrud/"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Instagram"
            class="text-white/80 hover:text-white transition-colors flex items-center"
          >
            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
          </a>
        </div>
        <a
          v-if="settings.contact_phone"
          :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`"
          class="hover:text-white transition-colors"
        >
          {{ settings.contact_phone }}
        </a>
        <NuxtLink to="/livrare-si-plata" class="hover:text-white/60 transition-colors">Livrare &amp; plată</NuxtLink>
      </div>
    </div>
  </div>
  <header
    class="sticky top-0 z-40 transition-all duration-300 backdrop-blur-md"
    :class="[
      isScrolled ? 'bg-white/95 border-b border-primary/20 py-1' : 'bg-white border-b border-primary/10 py-2'
    ]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-[auto_1fr_auto] items-center gap-6">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-3.5 group order-1 justify-self-start">
          <div class="flex items-center justify-center flex-shrink-0">
            <img
              v-if="settings.logo_url"
              :src="settings.logo_url"
              :alt="settings.site_title"
              class="mix-blend-multiply h-14 sm:h-[4.5rem] w-auto object-contain transition-transform duration-200 group-hover:scale-105 select-none"
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
          <div class="hidden sm:flex flex-col justify-center border-l border-primary/20 pl-4">
            <span class="font-serif text-2xl lg:text-3xl font-medium leading-none text-primary-dark tracking-wide">Pepiniera Ciumbrud</span>
            <span class="mt-1.5 text-[10px] lg:text-[11px] uppercase tracking-[0.28em] text-accent font-medium">Pepiniera de familie din 2000</span>
          </div>
          <span class="sr-only sm:hidden">Pepiniera Ciumbrud</span>
        </NuxtLink>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-9 order-2 justify-self-center">
          <NuxtLink
            to="/"
            class="link-underline text-[11px] uppercase tracking-[0.22em] font-medium text-text hover:text-accent transition-colors pb-1"
            active-class="!text-accent"
          >
            Acasă
          </NuxtLink>
          <NuxtLink
            to="/produse"
            class="link-underline text-[11px] uppercase tracking-[0.22em] font-medium text-text hover:text-accent transition-colors pb-1"
            active-class="!text-accent"
          >
            Magazin & Produse
          </NuxtLink>
          <NuxtLink
            to="/despre-noi"
            class="link-underline text-[11px] uppercase tracking-[0.22em] font-medium text-text hover:text-accent transition-colors pb-1"
            active-class="!text-accent"
          >
            Despre noi
          </NuxtLink>
          <NuxtLink
            to="/contact"
            class="link-underline text-[11px] uppercase tracking-[0.22em] font-medium text-text hover:text-accent transition-colors pb-1"
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
                class="absolute top-0 right-0 -translate-y-1/3 translate-x-1/3 bg-accent text-white font-medium rounded-full min-w-[20px] h-5 px-1.5 flex items-center justify-center text-[10px] leading-none shadow-sm whitespace-nowrap tracking-tight tabular-nums select-none"
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

