<script setup lang="ts">
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'

const uiStore = useUiStore()
const authStore = useAuthStore()
</script>

<template>
  <header class="h-16 bg-white border-b border-border flex items-center justify-between px-6 z-20">
    <div class="flex items-center gap-4">
      <button
        @click="uiStore.toggleSidebar()"
        class="p-2 text-gray-500 hover:text-primary rounded-lg hover:bg-gray-100"
        title="Comută meniu"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <div class="hidden sm:block text-xs font-medium text-gray-500">
        Panou de Control Headless • Magazin Pepiniera Ciumbrud
      </div>
    </div>

    <div class="flex items-center gap-4">
      <a
        href="http://localhost:3000"
        target="_blank"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border text-xs font-semibold text-gray-700 hover:bg-gray-50"
      >
        <span>Magazin Public</span>
        <span>↗</span>
      </a>

      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-dark font-bold flex items-center justify-center text-xs">
          {{ authStore.user?.username?.[0]?.toUpperCase() || 'A' }}
        </div>
        <span class="text-xs font-semibold text-gray-800 hidden md:inline">
          {{ authStore.fullName }}
        </span>
      </div>
    </div>
  </header>
</template>

