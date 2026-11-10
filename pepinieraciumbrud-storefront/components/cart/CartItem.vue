<script setup lang="ts">
import type { CartItem as CartItemType } from '~/types/cart'
import { useCartStore } from '~/stores/cart'

const props = defineProps<{
  item: CartItemType
}>()

const cartStore = useCartStore()

const formatItemPrice = (priceStr: string, minorUnit: number = 2) => {
  const num = parseFloat(priceStr || '0') / Math.pow(10, minorUnit)
  return `${num.toFixed(2).replace('.', ',')} lei`
}

const itemImage = computed(() => {
  if (props.item.images && props.item.images.length > 0 && props.item.images[0].src) {
    return props.item.images[0].src
  }
  return '/placeholder-plant.svg'
})
</script>

<template>
  <div class="flex items-center space-x-4 py-4 border-b border-border last:border-0">
    <!-- Thumbnail -->
    <div class="w-16 h-16 rounded-xl bg-surface-warm overflow-hidden flex-shrink-0 border border-border">
      <img
        :src="itemImage"
        :alt="item.name"
        class="w-full h-full object-cover"
        onerror="this.src='/placeholder-plant.svg'"
      />
    </div>

    <!-- Details -->
    <div class="flex-1 min-w-0">
      <h4 class="text-sm font-semibold text-text truncate font-serif">
        {{ item.name }}
      </h4>
      <div class="text-xs text-text-muted mt-0.5">
        {{ formatItemPrice(item.prices.price, item.prices.currency_minor_unit) }} / buc.
      </div>

      <!-- Quantity Controls -->
      <div class="flex items-center space-x-2 mt-2">
        <button
          @click="cartStore.updateItem(item.key, item.quantity - 1)"
          class="w-6 h-6 rounded-md bg-surface-warm border border-border flex items-center justify-center text-xs hover:bg-border text-text"
        >
          -
        </button>
        <span class="text-xs font-semibold px-1 min-w-[20px] text-center">{{ item.quantity }}</span>
        <button
          @click="cartStore.updateItem(item.key, item.quantity + 1)"
          class="w-6 h-6 rounded-md bg-surface-warm border border-border flex items-center justify-center text-xs hover:bg-border text-text"
        >
          +
        </button>
      </div>
    </div>

    <!-- Subtotal & Remove -->
    <div class="text-right flex flex-col items-end justify-between self-stretch py-0.5">
      <button
        @click="cartStore.removeItem(item.key)"
        class="text-text-muted hover:text-rose-600 p-1"
        title="Șterge produs"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
      </button>

      <span class="text-sm font-bold text-primary-dark">
        {{ formatItemPrice(item.totals.line_total, item.totals.currency_minor_unit) }}
      </span>
    </div>
  </div>
</template>

