<script setup lang="ts">
import { useUiStore } from '~/stores/ui'

const uiStore = useUiStore()
</script>

<template>
  <div
    v-if="uiStore.mobileMenuOpen"
    class="fixed inset-0 z-50 md:hidden"
  >
    <!-- Backdrop -->
    <div
      class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
      @click="uiStore.closeMobileMenu()"
    ></div>

    <!-- Panel -->
    <div class="fixed inset-y-0 right-0 max-w-xs w-full bg-white shadow-xl flex flex-col p-6 z-10 animate-slide-left">
      <div class="flex items-center justify-between pb-6 border-b border-border">
        <span class="font-serif text-lg font-bold text-primary-dark">Meniu</span>
        <button
          @click="uiStore.closeMobileMenu()"
          class="p-2 text-text-muted hover:text-text rounded-lg"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <nav class="flex-1 py-6 space-y-4">
        <NuxtLink
          to="/"
          class="block py-2 text-base font-medium text-text hover:text-primary"
          @click="uiStore.closeMobileMenu()"
        >
          Acasă
        </NuxtLink>
        <NuxtLink
          to="/produse"
          class="block py-2 text-base font-medium text-text hover:text-primary"
          @click="uiStore.closeMobileMenu()"
        >
          Toate Produsele
        </NuxtLink>
        <NuxtLink
          to="/despre-noi"
          class="block py-2 text-base font-medium text-text hover:text-primary"
          @click="uiStore.closeMobileMenu()"
        >
          Despre Pepinieră
        </NuxtLink>
        <NuxtLink
          to="/contact"
          class="block py-2 text-base font-medium text-text hover:text-primary"
          @click="uiStore.closeMobileMenu()"
        >
          Contact & Comenzi
        </NuxtLink>
        <NuxtLink
          to="/livrare-si-plata"
          class="block py-2 text-base font-medium text-text hover:text-primary"
          @click="uiStore.closeMobileMenu()"
        >
          Livrare și Plată
        </NuxtLink>
      </nav>

      <div class="pt-6 border-t border-border text-xs text-text-muted">
        <p>Pepiniera Ciumbrud, Sat Ciumbrud, Jud. Alba</p>
        <p class="mt-1 font-semibold text-primary">Tel: +40 700 000 000</p>
      </div>
    </div>
  </div>
</template>

