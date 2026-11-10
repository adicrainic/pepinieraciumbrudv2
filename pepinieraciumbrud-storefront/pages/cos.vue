<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import { useSeo } from '~/composables/useSeo'
import CartItem from '~/components/cart/CartItem.vue'
import AppButton from '~/components/ui/AppButton.vue'

const cartStore = useCartStore()
const { setPageSeo } = useSeo()

setPageSeo({
  title: 'Coșul Tău de Cumpărături',
  description: 'Vizualizează produsele selectate din Pepiniera Ciumbrud și finalizează comanda online.'
})

onMounted(() => {
  cartStore.fetchCart()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark mb-8">
      Coșul Tău de Cumpărături
    </h1>

    <div v-if="!cartStore.items || cartStore.items.length === 0" class="bg-white rounded-3xl border border-border p-12 text-center max-w-xl mx-auto my-12">
      <div class="text-4xl mb-4">🛒</div>
      <h2 class="font-serif text-2xl font-bold text-text mb-2">Coșul tău este gol</h2>
      <p class="text-sm text-text-muted mb-6">
        Alege pomi fructiferi sau trandafiri din oferta noastră bogată pentru a începe comanda.
      </p>
      <AppButton to="/produse" variant="primary" size="lg">
        Explorează Magazinul
      </AppButton>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
      <!-- Items Table -->
      <div class="lg:col-span-2 bg-white rounded-3xl border border-border p-6 sm:p-8 shadow-sm">
        <div class="border-b border-border pb-4 mb-4 font-serif font-bold text-lg text-primary-dark">
          Produse Adăugate ({{ cartStore.itemCount }})
        </div>

        <div class="divide-y divide-border">
          <CartItem
            v-for="item in cartStore.items"
            :key="item.key"
            :item="item"
          />
        </div>
      </div>

      <!-- Order Summary Card -->
      <div class="bg-white rounded-3xl border border-border p-6 sm:p-8 shadow-sm space-y-6">
        <h3 class="font-serif text-xl font-bold text-primary-dark border-b border-border pb-4">
          Sumar Comandă
        </h3>

        <div class="space-y-3 text-sm">
          <div class="flex justify-between text-text-muted">
            <span>Subtotal</span>
            <span class="font-semibold text-text">{{ cartStore.formattedTotal }}</span>
          </div>
          <div class="flex justify-between text-text-muted">
            <span>Livrare</span>
            <span class="text-xs text-primary font-medium">Calculată la pasul următor</span>
          </div>
          <div class="border-t border-border pt-3 flex justify-between text-base font-bold text-primary-dark">
            <span>Total estimat</span>
            <span>{{ cartStore.formattedTotal }}</span>
          </div>
        </div>

        <AppButton to="/comanda" variant="warm" size="lg" class="w-full">
          Continuă cu Comanda
        </AppButton>

        <div class="text-center">
          <NuxtLink to="/produse" class="text-xs text-text-muted hover:text-primary underline">
            Continuă cumpărăturile
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

