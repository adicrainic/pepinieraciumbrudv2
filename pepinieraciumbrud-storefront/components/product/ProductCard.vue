<script setup lang="ts">
import type { Product } from '~/types/product'
import { useCartStore } from '~/stores/cart'
import { useProducts } from '~/composables/useProducts'
import AppButton from '~/components/ui/AppButton.vue'
import AppBadge from '~/components/ui/AppBadge.vue'

const props = defineProps<{
  product: Product
}>()

const cartStore = useCartStore()
const { formatPrice, formatRegularPrice, getDiscountPercent, getProductImage } = useProducts()

const isAdding = ref(false)

const inCartQty = computed(() => cartStore.getItemQuantity(props.product.id))
const regularPriceFormatted = computed(() => formatRegularPrice(props.product.prices))
const discountPercent = computed(() => getDiscountPercent(props.product.prices))

const keySpecs = computed(() => {
  if (!props.product.attributes || !Array.isArray(props.product.attributes)) return []
  return props.product.attributes
    .filter((attr: any) => attr?.name && attr?.terms?.length > 0)
    .slice(0, 2)
    .map((attr: any) => ({
      name: attr.name,
      value: attr.terms.map((t: any) => t.name).join(', ')
    }))
})

const handleAddToCart = async () => {
  isAdding.value = true
  try {
    await cartStore.addItem(props.product.id, 1)
  } finally {
    isAdding.value = false
  }
}
</script>

<template>
  <div class="group relative bg-white rounded-2xl border border-border/80 hover:border-primary/60 shadow-sm hover:shadow-soft transition-all duration-300 flex flex-col h-full overflow-hidden">
    <!-- Image with Link -->
    <NuxtLink :to="`/produse/${product.slug || product.id}`" class="block aspect-[4/5] w-full overflow-hidden bg-surface-warm relative">
      <img
        :src="getProductImage(product)"
        :alt="product.name"
        class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
        loading="lazy"
        onerror="this.src='/placeholder-plant.svg'"
      />
      <!-- Badge stock & sale -->
      <div class="absolute top-3 left-3 flex flex-col items-start gap-1.5">
        <AppBadge v-if="product.is_in_stock" variant="success">
          În stoc
        </AppBadge>
        <AppBadge v-else variant="danger">
          Stoc epuizat
        </AppBadge>
        <AppBadge v-if="product.on_sale && discountPercent > 0" variant="danger">
          -{{ discountPercent }}% Promoție
        </AppBadge>
        <AppBadge v-else-if="product.on_sale" variant="danger">
          Promoție
        </AppBadge>
      </div>

      <!-- In Cart Indicator Badge -->
      <div
        v-if="inCartQty > 0"
        class="absolute top-3 right-3 bg-primary text-white text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1"
      >
        <span>✓ În coș ({{ inCartQty }})</span>
      </div>
    </NuxtLink>

    <!-- Content -->
    <div class="p-5 flex flex-col flex-1">
      <!-- Category Tag -->
      <div v-if="product.categories && product.categories.length > 0" class="text-xs uppercase tracking-[0.18em] text-accent font-semibold mb-1.5">
        {{ product.categories[0].name }}
      </div>

      <!-- Title -->
      <h3 class="font-serif text-xl sm:text-2xl font-medium text-text line-clamp-2 leading-tight mb-2 group-hover:text-primary transition-colors">
        <NuxtLink :to="`/produse/${product.slug || product.id}`">
          {{ product.name }}
        </NuxtLink>
      </h3>

      <!-- Key Botanical Specs Pills (if available) -->
      <div v-if="keySpecs.length > 0" class="flex flex-wrap gap-1.5 mb-3">
        <span
          v-for="spec in keySpecs"
          :key="spec.name"
          class="inline-flex items-center text-xs bg-surface-warm text-text-muted px-2 py-0.5 rounded-md border border-border/70"
        >
          <strong class="text-text font-medium mr-1">{{ spec.name }}:</strong> {{ spec.value }}
        </span>
      </div>

      <!-- Short Excerpt -->
      <div
        v-if="product.short_description"
        class="text-sm text-text-muted line-clamp-2 mb-4 prose-sm prose-p:my-0 leading-relaxed"
        v-html="product.short_description"
      ></div>

      <!-- Price & Button (Pinned to Bottom) -->
      <div class="mt-auto pt-4 border-t border-primary/15 flex items-center justify-between gap-2">
        <div class="min-w-0">
          <span class="text-xs text-text-muted block leading-none mb-1 whitespace-nowrap">Preț / buc.</span>
          <div class="flex items-baseline gap-1.5 flex-wrap">
            <span class="text-lg font-bold text-primary-dark font-sans tracking-wide whitespace-nowrap">
              {{ formatPrice(product.prices) }}
            </span>
            <span
              v-if="regularPriceFormatted"
              class="text-xs text-text-muted line-through font-sans whitespace-nowrap"
            >
              {{ regularPriceFormatted }}
            </span>
          </div>
        </div>

        <AppButton
          variant="primary"
          size="sm"
          class="shrink-0"
          :disabled="!product.is_in_stock || isAdding"
          :loading="isAdding"
          @click="handleAddToCart"
        >
          <span v-if="!product.is_in_stock">Indisponibil</span>
          <template v-else>
            <span>Adaugă</span>
            <svg class="w-4 h-4 ml-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </template>
        </AppButton>
      </div>
    </div>
  </div>
</template>

