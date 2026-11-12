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
const { formatPrice, getProductImage } = useProducts()

const isAdding = ref(false)

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
  <div class="group relative bg-white flex flex-col h-full">
    <!-- Image with Link -->
    <NuxtLink :to="`/produse/${product.slug || product.id}`" class="block aspect-[4/5] w-full overflow-hidden bg-surface-warm relative rounded-tr-[4rem] rounded-bl-[4rem] border border-border group-hover:border-primary transition-colors duration-300">
      <img
        :src="getProductImage(product)"
        :alt="product.name"
        class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
        loading="lazy"
        onerror="this.src='/placeholder-plant.svg'"
      />
      <!-- Badge stock -->
      <div class="absolute top-3 left-3 flex flex-col gap-1.5">
        <AppBadge v-if="product.is_in_stock" variant="success">
          În stoc
        </AppBadge>
        <AppBadge v-else variant="danger">
          Stoc epuizat
        </AppBadge>
        <AppBadge v-if="product.on_sale" variant="danger">
          Promoție
        </AppBadge>
      </div>
    </NuxtLink>

    <!-- Content -->
    <div class="px-6 pt-5 pb-6 flex flex-col flex-1">
      <!-- Category Tag -->
      <div v-if="product.categories && product.categories.length > 0" class="text-[10px] uppercase tracking-[0.25em] text-accent font-medium mb-2">
        {{ product.categories[0].name }}
      </div>

      <!-- Title -->
      <h3 class="font-serif text-2xl font-medium text-text line-clamp-2 leading-tight mb-2">
        <NuxtLink :to="`/produse/${product.slug || product.id}`">
          {{ product.name }}
        </NuxtLink>
      </h3>

      <!-- Short Excerpt / Anotimp -->
      <div
        v-if="product.short_description"
        class="text-xs text-text-muted line-clamp-2 mb-4 prose-sm prose-p:my-0 leading-relaxed"
        v-html="product.short_description"
      ></div>

      <!-- Price & Button (Pinned to Bottom) -->
      <div class="mt-auto pt-4 border-t border-primary/15 flex items-center justify-between">
        <div>
          <span class="text-xs text-text-muted block leading-none mb-1">Preț</span>
          <span class="text-lg font-medium text-primary-dark font-sans tracking-wide">
            {{ formatPrice(product.prices) }}
          </span>
        </div>

        <AppButton
          variant="primary"
          size="sm"
          :disabled="!product.is_in_stock || isAdding"
          :loading="isAdding"
          @click="handleAddToCart"
        >
          <span class="hidden sm:inline">Adaugă</span>
          <svg class="w-4 h-4 sm:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </AppButton>
      </div>
    </div>
  </div>
</template>

