<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import CartItem from './CartItem.vue'
import AppButton from '~/components/ui/AppButton.vue'

const cartStore = useCartStore()

onMounted(() => {
  cartStore.fetchCart()
})
</script>

<template>
  <div
    v-if="cartStore.isOpen"
    class="fixed inset-0 z-50 overflow-hidden"
  >
    <!-- Backdrop -->
    <div
      class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
      @click="cartStore.closeDrawer()"
    ></div>

    <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl flex flex-col z-10">
      <!-- Drawer Header -->
      <div class="p-6 border-b border-border flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span class="font-serif text-xl font-bold text-primary-dark">Coșul tău</span>
          <span class="text-xs bg-surface-warm px-2 py-0.5 rounded-full font-semibold text-text-muted">
            {{ cartStore.itemCount }} {{ cartStore.itemCount === 1 ? 'produs' : 'produse' }}
          </span>
        </div>
        <button
          @click="cartStore.closeDrawer()"
          class="p-2 text-text-muted hover:text-text rounded-lg hover:bg-surface-warm"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Drawer Content -->
      <div class="flex-1 overflow-y-auto p-6">
        <!-- Empty cart message -->
        <div
          v-if="!cartStore.items || cartStore.items.length === 0"
          class="h-full flex flex-col items-center justify-center text-center py-12"
        >
          <div class="w-20 h-20 rounded-full bg-surface-warm flex items-center justify-center text-3xl mb-4">
            🛒
          </div>
          <h3 class="font-serif text-lg font-bold text-text mb-1">Coșul este gol</h3>
          <p class="text-xs text-text-muted max-w-xs mb-6">
            Nu ați adăugat încă niciun produs. Explorați colecția noastră de pomi și trandafiri!
          </p>
          <AppButton
            variant="outline"
            to="/produse"
            @click="cartStore.closeDrawer()"
          >
            Vezi Produsele
          </AppButton>
        </div>

        <!-- Items list -->
        <div v-else>
          <CartItem
            v-for="item in cartStore.items"
            :key="item.key"
            :item="item"
          />
        </div>
      </div>

      <!-- Drawer Footer with Subtotal -->
      <div
        v-if="cartStore.items && cartStore.items.length > 0"
        class="p-6 border-t border-border bg-surface-warm/50 space-y-4"
      >
        <div class="flex items-center justify-between text-sm">
          <span class="text-text-muted">Subtotal produse</span>
          <span class="font-bold text-primary-dark font-sans text-base">
            {{ cartStore.formattedTotal }}
          </span>
        </div>
        <p class="text-[11px] text-text-muted leading-relaxed">
          Taxele de transport se calculează la pasul următor. Livrare prin curier rapid în toată România.
        </p>

        <div class="grid grid-cols-2 gap-3 pt-2">
          <AppButton
            variant="secondary"
            to="/cos"
            @click="cartStore.closeDrawer()"
          >
            Vezi Coșul
          </AppButton>
          <AppButton
            variant="warm"
            to="/comanda"
            @click="cartStore.closeDrawer()"
          >
            Finalizează
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>

