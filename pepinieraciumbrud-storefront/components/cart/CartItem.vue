<script setup lang="ts">
import type { CartItem as CartItemType } from '~/types/cart'
import { useCartStore } from '~/stores/cart'

const props = defineProps<{
  item: CartItemType
}>()

const emit = defineEmits<{
  (e: 'navigate'): void
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

const maxQuantity = computed(() => {
  return props.item.quantity_limits?.maximum || 99999
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
  if (val > maxQuantity.value) {
    val = maxQuantity.value
    localQuantity.value = val
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

const itemPath = computed(() => {
  if (props.item.permalink) {
    try {
      const url = new URL(props.item.permalink)
      const parts = url.pathname.split('/').filter(Boolean)
      const slug = parts[parts.length - 1]
      if (slug) return `/produse/${slug}`
    } catch {
      // fallback to id
    }
  }
  return `/produse/${props.item.id}`
})
</script>

<template>
  <div class="flex items-center space-x-4 py-4 border-b border-border last:border-0">
    <!-- Thumbnail -->
    <NuxtLink
      :to="itemPath"
      @click="emit('navigate')"
      class="w-16 h-16 rounded-xl bg-surface-warm overflow-hidden flex-shrink-0 border border-border hover:border-primary transition-colors block"
    >
      <img
        :src="itemImage"
        :alt="item.name"
        class="w-full h-full object-cover"
        onerror="this.src='/placeholder-plant.svg'"
      />
    </NuxtLink>

    <!-- Details -->
    <div class="flex-1 min-w-0">
      <h4 class="text-sm font-semibold text-text truncate font-serif">
        <NuxtLink :to="itemPath" @click="emit('navigate')" class="hover:text-primary transition-colors">
          {{ item.name }}
        </NuxtLink>
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
          class="w-9 h-9 sm:w-10 sm:h-10 min-w-[36px] min-h-[36px] rounded-lg bg-surface-warm border border-border flex items-center justify-center font-bold hover:bg-primary hover:text-white hover:border-primary text-text disabled:bg-surface-warm disabled:text-text-muted disabled:border-border/80 disabled:cursor-not-allowed transition-colors select-none shadow-sm"
          title="Scade cantitatea"
          aria-label="Scade cantitatea"
        >
          <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
          </svg>
        </button>
        <input
          type="number"
          min="1"
          :max="maxQuantity"
          step="1"
          v-model.number="localQuantity"
          @input="handleInput"
          @change="commitQuantity()"
          @blur="commitQuantity()"
          @keydown.enter.prevent="handleEnter"
          :disabled="isUpdating || cartStore.loading"
          class="w-14 sm:w-16 h-9 sm:h-10 text-center text-xs sm:text-sm font-bold bg-white border border-border rounded-lg text-text focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary disabled:bg-surface-warm disabled:text-text-muted disabled:cursor-not-allowed [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
          title="Cantitate"
          aria-label="Cantitate produs"
        />
        <button
          type="button"
          @click="changeQuantity(1)"
          :disabled="isUpdating || cartStore.loading || Number(localQuantity) >= maxQuantity"
          class="w-9 h-9 sm:w-10 sm:h-10 min-w-[36px] min-h-[36px] rounded-lg bg-surface-warm border border-border flex items-center justify-center font-bold hover:bg-primary hover:text-white hover:border-primary text-text disabled:bg-surface-warm disabled:text-text-muted disabled:border-border/80 disabled:cursor-not-allowed transition-colors select-none shadow-sm"
          title="Crește cantitatea"
          aria-label="Crește cantitatea"
        >
          <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
          </svg>
        </button>
        <span class="text-xs text-text-muted pl-0.5">buc.</span>
      </div>
    </div>

    <!-- Subtotal & Remove -->
    <div class="text-right flex flex-col items-end justify-between self-stretch py-0.5">
      <button
        @click="cartStore.removeItem(item.key)"
        class="min-w-[44px] min-h-[44px] p-2.5 flex items-center justify-center text-text-muted hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors"
        title="Șterge produs"
        aria-label="Șterge produs"
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

