<script setup lang="ts">
import type { Product } from '~/types/product'
import ProductCard from './ProductCard.vue'

defineProps<{
  products: Product[]
  loading?: boolean
}>()
</script>

<template>
  <div>
    <!-- Skeleton loader -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div
        v-for="i in 4"
        :key="i"
        class="bg-white border border-border rounded-2xl p-5 animate-pulse"
      >
        <div class="aspect-square bg-surface-warm rounded-xl mb-4"></div>
        <div class="h-4 bg-surface-warm rounded w-1/3 mb-2"></div>
        <div class="h-5 bg-surface-warm rounded w-3/4 mb-4"></div>
        <div class="h-8 bg-surface-warm rounded"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!products || products.length === 0"
      class="text-center py-16 px-4 bg-white border border-dashed border-border rounded-2xl"
    >
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-surface-warm flex items-center justify-center text-2xl">
        🌱
      </div>
      <h3 class="font-serif text-lg font-bold text-text mb-1">Nu am găsit produse</h3>
      <p class="text-sm text-text-muted">Încercați să resetați filtrele sau căutați un alt termen.</p>
    </div>

    <!-- Products Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <ProductCard
        v-for="product in products"
        :key="product.id"
        :product="product"
      />
    </div>
  </div>
</template>

