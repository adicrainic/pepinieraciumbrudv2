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
const {
  cleanProductText,
  formatPrice,
  formatRegularPrice,
  getProductImage,
  getSecondaryProductImage,
  getProductExceptionBadges,
  getProductCardSpecs
} = useProducts()

const isAdding = ref(false)
const secondaryImageFailed = ref(false)

watch(
  () => props.product?.id,
  () => {
    secondaryImageFailed.value = false
  }
)

const inCartQty = computed(() => cartStore.getItemQuantity(props.product.id))
const hasReachedStockLimit = computed(() => {
  const limit = props.product.quantity_limit || (props.product as any)?.add_to_cart?.maximum
  return Boolean(limit && limit < 9999 && inCartQty.value >= limit)
})

const regularPriceFormatted = computed(() => formatRegularPrice(props.product.prices))
const primaryImage = computed(() => getProductImage(props.product))
const secondaryImage = computed(() => getSecondaryProductImage(props.product))
const hasSecondaryImage = computed(() => Boolean(secondaryImage.value && !secondaryImageFailed.value))

const exceptionBadges = computed(() => getProductExceptionBadges(props.product))
const cardSpecs = computed(() => getProductCardSpecs(props.product))

const cleanName = computed(() => cleanProductText(props.product.name) || 'Produs Pepiniera Ciumbrud')

const categoryLabel = computed(() => {
  const cats = props.product.categories
  if (!cats || !Array.isArray(cats) || cats.length === 0) {
    return 'Plante & Pomi'
  }
  const rootSlugs = ['pomi-fructiferi', 'trandafiri', 'arbusti-fructiferi', 'arbusti-ornamentali']
  const specific = cats.find(c => c?.slug && !rootSlugs.includes(c.slug))
  return cleanProductText((specific || cats[0])?.name) || 'Plante & Pomi'
})

const cleanExcerpt = computed(() => {
  const text = cleanProductText(props.product.short_description)
  return text || 'Soi certificat și aclimatizat în câmpurile din Ciumbrud.'
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
  <div
    class="group relative bg-white/95 backdrop-blur-sm rounded-xl sm:rounded-2xl border border-border/80 shadow-sm transition-all duration-300 flex flex-col h-full overflow-hidden"
    :class="product.is_in_stock ? 'hover:border-primary/60 hover:shadow-soft' : 'bg-surface-warm/60 border-border'"
  >
    <!-- Image with Link & Optional 2nd Photo on Hover -->
    <NuxtLink
      :to="`/produse/${product.slug || product.id}`"
      class="block aspect-square sm:aspect-[4/5] w-full overflow-hidden bg-surface-warm relative shrink-0"
    >
      <img
        :src="primaryImage"
        :alt="cleanName"
        class="w-full h-full object-cover object-center transition-all duration-500"
        :class="[
          !product.is_in_stock ? 'grayscale-[30%] opacity-85' : '',
          hasSecondaryImage ? 'group-hover:opacity-0 group-focus-within:opacity-0' : (product.is_in_stock ? 'group-hover:scale-105' : '')
        ]"
        loading="lazy"
        onerror="this.src='/placeholder-plant.svg'"
      />

      <img
        v-if="hasSecondaryImage"
        :src="secondaryImage!"
        :alt="`${cleanName} – imagine secundară`"
        class="absolute inset-0 w-full h-full object-cover object-center opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-all duration-500"
        :class="product.is_in_stock ? 'group-hover:scale-105' : 'grayscale-[30%] opacity-85'"
        loading="lazy"
        @error="secondaryImageFailed = true"
      />

      <!-- Exception Badges ONLY (out of stock, sale, last pieces, new) -->
      <div
        v-if="exceptionBadges.length > 0"
        class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 flex flex-col items-start gap-1 pointer-events-none"
      >
        <AppBadge
          v-for="badge in exceptionBadges"
          :key="badge.key"
          :variant="badge.variant"
          class="!text-[11px] sm:!text-xs !px-2 sm:!px-2.5 !py-0.5 sm:!py-1 font-semibold shadow-sm"
          :class="badge.key === 'out_of_stock' ? '!bg-neutral-800/90 !text-white !border-neutral-700' : ''"
        >
          {{ badge.label }}
        </AppBadge>
      </div>

      <!-- In Cart Indicator Badge -->
      <div
        v-if="inCartQty > 0"
        class="absolute top-2 right-2 sm:top-3 sm:right-3 z-10 bg-primary text-white text-[11px] sm:text-xs font-semibold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full shadow-sm flex items-center gap-1 pointer-events-none"
      >
        <span>✓ {{ inCartQty }}</span>
        <span class="hidden sm:inline">în coș</span>
      </div>

      <!-- Subtle indicator when a second photo is available on hover -->
      <div
        v-if="hasSecondaryImage"
        class="absolute bottom-2 right-2 z-10 flex items-center gap-1 px-1.5 py-1 rounded-full bg-black/35 backdrop-blur-xs pointer-events-none"
        aria-hidden="true"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-white transition-opacity duration-300 group-hover:opacity-45"></span>
        <span class="w-1.5 h-1.5 rounded-full bg-white/45 transition-opacity duration-300 group-hover:bg-white group-hover:opacity-100"></span>
      </div>
    </NuxtLink>

    <!-- Uniform Card Body: Exact same number of info lines on every card -->
    <div class="p-2.5 sm:p-5 flex flex-col flex-1 min-w-0">
      <!-- Line 1: Category Eyebrow (1 line fixed) -->
      <div class="text-[11px] sm:text-xs uppercase tracking-wider text-accent font-semibold truncate h-4 sm:h-5 leading-4 sm:leading-5 mb-1">
        {{ categoryLabel }}
      </div>

      <!-- Lines 2-3: Product Title (2 lines fixed height) -->
      <h3 class="font-serif text-sm sm:text-lg lg:text-xl font-medium text-text line-clamp-2 leading-snug h-[2.4rem] sm:h-[3.1rem] mb-1.5 sm:mb-2 group-hover:text-primary transition-colors">
        <NuxtLink :to="`/produse/${product.slug || product.id}`">
          {{ cleanName }}
        </NuxtLink>
      </h3>

      <!-- Line 4: Uniform 2-Chip Botanical Specs Row (1 line fixed height) -->
      <div class="grid grid-cols-2 gap-1 sm:gap-1.5 h-6 sm:h-7 mb-1.5 sm:mb-2.5 shrink-0">
        <span
          v-for="spec in cardSpecs"
          :key="spec.label"
          class="inline-flex items-center min-w-0 text-[11px] sm:text-xs bg-surface-warm text-text-muted px-1.5 sm:px-2 py-0.5 rounded-md border border-border/70 truncate"
          :title="`${spec.label}: ${spec.value}`"
        >
          <strong class="text-text font-medium mr-1 shrink-0 hidden sm:inline">{{ spec.label }}:</strong>
          <span class="truncate text-text sm:text-text-muted font-medium sm:font-normal">{{ spec.value }}</span>
        </span>
      </div>

      <!-- Lines 5-6: Short Excerpt (2 lines fixed height) -->
      <p class="text-xs sm:text-sm text-text-muted line-clamp-2 leading-snug sm:leading-normal h-[2.1rem] sm:h-[2.6rem] mb-2.5 sm:mb-4">
        {{ cleanExcerpt }}
      </p>

      <!-- Price & Button (Pinned to Bottom) -->
      <div class="mt-auto pt-2 sm:pt-3.5 border-t border-primary/15 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div class="min-w-0 flex sm:block items-baseline justify-between gap-1">
          <span class="text-[11px] sm:text-xs text-text-muted block leading-none sm:mb-1 whitespace-nowrap">Preț / buc.</span>
          <div class="flex items-baseline gap-1 sm:gap-1.5 flex-wrap justify-end sm:justify-start">
            <span class="text-sm sm:text-lg font-bold text-primary-dark font-sans tracking-wide whitespace-nowrap">
              {{ formatPrice(product.prices) }}
            </span>
            <span
              v-if="regularPriceFormatted"
              class="text-[11px] sm:text-xs text-text-muted line-through font-sans whitespace-nowrap"
            >
              {{ regularPriceFormatted }}
            </span>
          </div>
        </div>

        <AppButton
          :variant="product.is_in_stock ? 'primary' : 'secondary'"
          size="sm"
          class="w-full sm:w-auto shrink-0 min-h-[38px] sm:min-h-[44px] !px-2.5 sm:!px-4 !py-2 sm:!py-2.5 text-xs sm:text-sm font-semibold tracking-wide"
          :class="!product.is_in_stock ? '!bg-surface-warm !text-text-muted !border-border !opacity-100 !shadow-none' : ''"
          :disabled="!product.is_in_stock || isAdding || hasReachedStockLimit"
          :loading="isAdding"
          @click="handleAddToCart"
        >
          <span v-if="!product.is_in_stock">Indisponibil</span>
          <span v-else-if="hasReachedStockLimit">Stoc maxim</span>
          <template v-else>
            <span>Adaugă</span>
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 ml-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </template>
        </AppButton>
      </div>
    </div>
  </div>
</template>
