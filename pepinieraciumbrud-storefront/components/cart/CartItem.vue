<script setup lang="ts">
import type { CartItem as CartItemType } from '~/types/cart'
import { useCartStore } from '~/stores/cart'

const props = defineProps<{
  item: CartItemType
}>()

const cartStore = useCartStore()

const isUpdating = ref(false)
const localQuantity = ref<number | string>(props.item.quantity)
let debounceTimer: ReturnType<typeof setTimeout> | null = null

watch(() => props.item.quantity, (newVal) => {
  localQuantity.value = newVal
})

const formatItemPrice = (priceStr: string, minorUnit: number = 2) => {
  const num = parseFloat(priceStr || '0') / Math.pow(10, minorUnit)
  return `${num.toFixed(2).replace('.', ',')} lei`
}

const displayLineTotal = computed(() => {
  const unitPrice = parseFloat(props.item.prices.price || '0')
  const minorUnit = props.item.prices.currency_minor_unit ?? 2
  const qty = Number(localQuantity.value)
  if (!isNaN(qty) && qty > 0) {
    const total = (unitPrice * qty) / Math.pow(10, minorUnit)
    return `${total.toFixed(2).replace('.', ',')} lei`
  }
  return formatItemPrice(props.item.totals.line_total, props.item.totals.currency_minor_unit)
})

const commitQuantity = async (valToCommit?: number) => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
    debounceTimer = null
  }
  let val = Math.floor(Number(valToCommit !== undefined ? valToCommit : localQuantity.value))
  if (isNaN(val) || val < 1) {
    localQuantity.value = props.item.quantity
    return
  }
  if (val !== props.item.quantity) {
    isUpdating.value = true
    try {
      await cartStore.updateItem(props.item.key, val)
    } catch {
      localQuantity.value = props.item.quantity
    } finally {
      isUpdating.value = false
    }
  }
}

const handleInput = () => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  const val = Math.floor(Number(localQuantity.value))
  if (!isNaN(val) && val >= 1 && val !== props.item.quantity) {
    debounceTimer = setTimeout(() => {
      commitQuantity(val)
    }, 400)
  }
}

const handleEnter = (event: Event) => {
  (event.target as HTMLInputElement)?.blur()
}

const changeQuantity = async (delta: number) => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
    debounceTimer = null
  }
  let current = Math.floor(Number(localQuantity.value))
  if (isNaN(current) || current < 1) {
    current = props.item.quantity
  }
  const target = current + delta
  if (target <= 0) {
    isUpdating.value = true
    try {
      await cartStore.removeItem(props.item.key)
    } finally {
      isUpdating.value = false
    }
    return
  }
  localQuantity.value = target
  if (target !== props.item.quantity) {
    isUpdating.value = true
    try {
      await cartStore.updateItem(props.item.key, target)
    } catch {
      localQuantity.value = props.item.quantity
    } finally {
      isUpdating.value = false
    }
  }
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
      <div class="flex items-center space-x-1.5 mt-2">
        <button
          type="button"
          @click="changeQuantity(-1)"
          :disabled="isUpdating || cartStore.loading"
          class="w-7 h-7 rounded-lg bg-surface-warm border border-border flex items-center justify-center text-xs font-bold hover:bg-border text-text disabled:opacity-40 disabled:cursor-not-allowed transition-colors select-none"
          title="Scade cantitatea"
          aria-label="Scade cantitatea"
        >
          -
        </button>
        <input
          type="number"
          min="1"
          step="1"
          v-model.number="localQuantity"
          @input="handleInput"
          @change="commitQuantity()"
          @blur="commitQuantity()"
          @keydown.enter.prevent="handleEnter"
          :disabled="isUpdating || cartStore.loading"
          class="w-16 h-7 text-center text-xs font-semibold bg-white border border-border rounded-lg text-text focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary disabled:bg-surface-warm disabled:cursor-not-allowed [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
          title="Cantitate"
          aria-label="Cantitate produs"
        />
        <button
          type="button"
          @click="changeQuantity(1)"
          :disabled="isUpdating || cartStore.loading"
          class="w-7 h-7 rounded-lg bg-surface-warm border border-border flex items-center justify-center text-xs font-bold hover:bg-border text-text disabled:opacity-40 disabled:cursor-not-allowed transition-colors select-none"
          title="Crește cantitatea"
          aria-label="Crește cantitatea"
        >
          +
        </button>
        <span class="text-[11px] text-text-muted pl-0.5">buc.</span>
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
        {{ displayLineTotal }}
      </span>
    </div>
  </div>
</template>

