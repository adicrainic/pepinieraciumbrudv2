<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import { useSiteSettings } from '~/composables/useSiteSettings'
import { useCartStore } from '~/stores/cart'
import AppButton from '~/components/ui/AppButton.vue'
import AppBadge from '~/components/ui/AppBadge.vue'
import ProductGrid from '~/components/product/ProductGrid.vue'

const route = useRoute()
const slug = route.params.slug as string

const { fetchProductBySlug, formatPrice, getProductImage } = useProducts()
const { setPageSeo, setProductJsonLd } = useSeo()
const { settings } = useSiteSettings()
const cartStore = useCartStore()

const { data: product, status } = await useAsyncData(`product-${slug}`, () =>
  fetchProductBySlug(slug)
)

const quantity = ref(1)
const isAdding = ref(false)
const selectedImageIndex = ref(0)

const productImages = computed(() => {
  if (product.value?.images && product.value.images.length > 0) {
    return product.value.images.filter((img: any) => img?.src)
  }
  if (product.value) {
    return [{ id: 0, src: getProductImage(product.value), alt: product.value.name }]
  }
  return []
})

const activeImageSrc = computed(() => {
  if (productImages.value.length > 0) {
    const img = productImages.value[selectedImageIndex.value] || productImages.value[0]
    return img.src
  }
  return product.value ? getProductImage(product.value) : '/placeholder-plant.svg'
})

const { formatRegularPrice, getDiscountPercent, fetchProducts } = useProducts()
const regularPriceFormatted = computed(() => product.value ? formatRegularPrice(product.value.prices) : null)
const discountPercent = computed(() => product.value ? getDiscountPercent(product.value.prices) : 0)
const inCartQty = computed(() => product.value ? cartStore.getItemQuantity(product.value.id) : 0)
const maxSelectableQty = computed(() => {
  const limit = product.value?.quantity_limit || (product.value as any)?.add_to_cart?.maximum
  if (!limit || limit >= 9999) return 99999
  return Math.max(1, limit - inCartQty.value)
})
const hasReachedStockLimit = computed(() => {
  const limit = product.value?.quantity_limit || (product.value as any)?.add_to_cart?.maximum
  return Boolean(limit && limit < 9999 && inCartQty.value >= limit)
})

// Fetch related products from the same primary category
const primaryCategorySlug = computed(() => product.value?.categories?.[0]?.slug || '')
const { data: relatedRaw } = await useAsyncData(`related-${slug}`, async () => {
  if (!primaryCategorySlug.value) return []
  return await fetchProducts({ category: primaryCategorySlug.value, per_page: 5 })
})
const relatedProducts = computed(() => {
  if (!relatedRaw.value || !product.value) return []
  return relatedRaw.value.filter((p: any) => p.id !== product.value?.id).slice(0, 4)
})

if (product.value) {
  setPageSeo({
    title: product.value.name,
    description: product.value.short_description || product.value.description,
    image: getProductImage(product.value)
  })
  setProductJsonLd(product.value)
}

const validateQuantity = () => {
  if (!quantity.value || quantity.value < 1) {
    quantity.value = 1
  } else {
    quantity.value = Math.min(maxSelectableQty.value, Math.floor(quantity.value))
  }
}

const handleEnter = (event: Event) => {
  (event.target as HTMLInputElement)?.blur()
}

const handleAddToCart = async () => {
  if (!product.value) return
  isAdding.value = true
  try {
    const qty = Math.min(maxSelectableQty.value, Math.max(1, Math.floor(quantity.value || 1)))
    quantity.value = 1
    await cartStore.addItem(product.value.id, qty)
  } finally {
    isAdding.value = false
  }
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb -->
    <nav class="text-xs text-text-muted mb-8 flex items-center space-x-2">
      <NuxtLink to="/" class="hover:text-primary">Acasă</NuxtLink>
      <span>/</span>
      <NuxtLink to="/produse" class="hover:text-primary">Produse</NuxtLink>
      <span>/</span>
      <span class="text-text font-medium truncate">{{ product?.name || 'Detalii Produs' }}</span>
    </nav>

    <!-- Loading State -->
    <div v-if="status === 'pending'" class="py-20 text-center">
      <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto mb-4"></div>
      <p class="text-sm text-text-muted">Se încarcă detaliile produsului...</p>
    </div>

    <!-- Not Found -->
    <div v-else-if="!product" class="text-center py-24 bg-white rounded-2xl border border-border">
      <h2 class="font-serif text-2xl font-bold text-text mb-2">Produsul nu a fost găsit</h2>
      <p class="text-sm text-text-muted mb-6">Ne pare rău, dar produsul căutat nu există sau a fost retras.</p>
      <AppButton to="/produse" variant="primary">Înapoi la Catalog</AppButton>
    </div>

    <!-- Product Layout -->
    <div v-else class="pb-20 sm:pb-0">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-start">
        <!-- Image Gallery -->
        <div class="bg-white border border-border rounded-3xl p-4 sm:p-6 overflow-hidden lg:sticky lg:top-24">
          <div class="aspect-square rounded-2xl overflow-hidden bg-surface-warm relative">
            <img
              :src="activeImageSrc"
              :alt="product.name"
              class="w-full h-full object-cover object-center transition-all duration-300"
              onerror="this.src='/placeholder-plant.svg'"
            />
            <div v-if="discountPercent > 0 || product.on_sale" class="absolute top-3 left-3">
              <AppBadge variant="danger">{{ discountPercent > 0 ? `-${discountPercent}% Promoție` : 'Promoție' }}</AppBadge>
            </div>
          </div>

          <!-- Thumbnails if multiple images exist -->
          <div v-if="productImages.length > 1" class="mt-4 flex items-center gap-3 overflow-x-auto pb-1">
            <button
              v-for="(img, idx) in productImages"
              :key="img.id || idx"
              type="button"
              @click="selectedImageIndex = idx"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden border-2 flex-shrink-0 transition-all"
              :class="selectedImageIndex === idx ? 'border-primary ring-2 ring-primary/20' : 'border-border opacity-70 hover:opacity-100'"
            >
              <img
                :src="img.src"
                :alt="img.alt || product.name"
                class="w-full h-full object-cover"
                onerror="this.src='/placeholder-plant.svg'"
              />
            </button>
          </div>
        </div>

        <!-- Product Details -->
        <div class="space-y-6">
          <!-- Category & Badges -->
          <div class="flex flex-wrap items-center gap-2">
            <NuxtLink
              v-if="product.categories && product.categories.length > 0"
              :to="`/produse?category=${product.categories[0].slug}`"
              class="text-xs sm:text-sm uppercase tracking-wide text-primary font-semibold hover:underline mr-1 min-h-[44px] inline-flex items-center"
            >
              {{ product.categories[0].name }}
            </NuxtLink>
            <AppBadge v-if="product.is_in_stock" variant="success">În Stoc</AppBadge>
            <AppBadge v-else variant="neutral" class="!bg-neutral-800 !text-white !border-neutral-700 font-semibold">Stoc Epuizat</AppBadge>
            <AppBadge v-if="discountPercent > 0 || product.on_sale" variant="danger">
              {{ discountPercent > 0 ? `Reducere -${discountPercent}%` : 'Promoție' }}
            </AppBadge>
            <span v-if="product.sku" class="text-xs text-text-muted font-mono">
              SKU: {{ product.sku }}
            </span>
          </div>

          <!-- Name -->
          <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark leading-tight">
            {{ product.name }}
          </h1>

          <!-- Price -->
          <div class="flex items-baseline flex-wrap gap-3">
            <span class="text-3xl font-bold text-primary-dark font-sans">
              {{ formatPrice(product.prices) }}
            </span>
            <span
              v-if="regularPriceFormatted"
              class="text-lg text-text-muted line-through font-sans"
            >
              {{ regularPriceFormatted }}
            </span>
            <span class="text-xs text-text-muted">TVA inclus / buc.</span>
          </div>

          <!-- Short Description -->
          <div
            v-if="product.short_description"
            class="text-sm text-text-muted leading-relaxed prose prose-sm"
            v-html="product.short_description"
          ></div>

          <!-- Quantity & Add to Cart -->
          <div class="pt-4 border-t border-border space-y-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
              <div class="flex items-center justify-between sm:justify-start border border-border rounded-xl bg-surface-warm p-1 shadow-inner">
                <button
                  type="button"
                  @click="quantity = Math.max(1, (quantity || 1) - 1)"
                  class="min-w-[44px] min-h-[44px] w-11 h-11 flex items-center justify-center rounded-lg bg-white border border-border text-text font-bold hover:bg-primary hover:text-white hover:border-primary disabled:bg-surface-warm disabled:text-text-muted disabled:border-border/80 disabled:cursor-not-allowed select-none transition-colors shadow-sm"
                  :disabled="!product.is_in_stock || quantity <= 1"
                  title="Scade cantitatea"
                  aria-label="Scade cantitatea"
                >
                  <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                  </svg>
                </button>
                <input
                  type="number"
                  min="1"
                  :max="maxSelectableQty"
                  step="1"
                  v-model.number="quantity"
                  @blur="validateQuantity"
                  @keydown.enter.prevent="handleEnter"
                  :disabled="!product.is_in_stock"
                  class="w-16 h-11 text-center font-bold text-base text-text bg-transparent border-0 focus:outline-none focus:ring-1 focus:ring-primary rounded-lg disabled:text-text-muted disabled:cursor-not-allowed [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                  title="Cantitate"
                  aria-label="Cantitate produs"
                />
                <button
                  type="button"
                  @click="quantity = Math.min(maxSelectableQty, (quantity || 0) + 1)"
                  :disabled="!product.is_in_stock || quantity >= maxSelectableQty || hasReachedStockLimit"
                  class="min-w-[44px] min-h-[44px] w-11 h-11 flex items-center justify-center rounded-lg bg-white border border-border text-text font-bold hover:bg-primary hover:text-white hover:border-primary disabled:bg-surface-warm disabled:text-text-muted disabled:border-border/80 disabled:cursor-not-allowed select-none transition-colors shadow-sm"
                  title="Crește cantitatea"
                  aria-label="Crește cantitatea"
                >
                  <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                  </svg>
                </button>
              </div>

              <AppButton
                variant="warm"
                size="lg"
                class="flex-1"
                :class="!product.is_in_stock ? '!bg-surface-warm !text-text-muted !border-border !opacity-100 !shadow-none' : ''"
                :disabled="!product.is_in_stock || isAdding || hasReachedStockLimit"
                :loading="isAdding"
                @click="handleAddToCart"
              >
                {{ !product.is_in_stock ? 'Stoc Epuizat' : (hasReachedStockLimit ? 'Stoc Maxim în Coș' : 'Adaugă în Coș') }}
              </AppButton>
            </div>

            <!-- In-Cart Feedback Banner -->
            <div
              v-if="inCartQty > 0"
              class="flex items-center justify-between bg-leaf-50 border border-leaf-100 text-leaf-900 px-4 py-2.5 rounded-xl text-xs"
            >
              <span class="font-medium">✓ Aveți deja <strong>{{ inCartQty }} buc.</strong> din acest soi în coș.</span>
              <button
                type="button"
                @click="cartStore.openDrawer()"
                class="font-bold text-leaf-700 underline hover:text-leaf-900 ml-2 min-h-[44px] inline-flex items-center px-1"
              >
                Vezi coșul →
              </button>
            </div>
          </div>

          <!-- Plant Information & Guarantee -->
          <div class="bg-surface-warm/60 border border-border rounded-2xl p-5 space-y-3 text-xs text-text-muted mt-6">
            <div class="flex items-start space-x-2.5 text-text">
              <span class="text-base leading-none">🛡️</span>
              <div>
                <strong class="font-semibold block">Garanție autenticitate soi &amp; prindere</strong>
                <span class="text-text-muted">
                  Material săditor certificat, aclimatizat în Ciumbrud (48h transport · prindere până la 30 iunie).
                  <NuxtLink to="/garantie" class="text-primary underline hover:text-primary-dark ml-1">Condiții garanție</NuxtLink>
                </span>
              </div>
            </div>
            <div class="flex items-start space-x-2.5 text-text">
              <span class="text-base leading-none">📦</span>
              <div>
                <strong class="font-semibold block">Ambalare specială cu rădăcini hidratate</strong>
                <span class="text-text-muted">Rădăcini protejate în hidrogel și rumeguș umed pentru transport sigur.</span>
              </div>
            </div>
            <div class="flex items-start space-x-2.5 text-text">
              <span class="text-base leading-none">🚚</span>
              <div>
                <strong class="font-semibold block">Livrare prin curier rapid în 24-48h de la expediere</strong>
                <span class="text-text-muted">
                  Cost transport: {{ settings.shipping_cost || '30' }} lei (tarif fix în toată România).
                  <NuxtLink to="/livrare-si-plata" class="text-primary underline hover:text-primary-dark ml-1">Detalii livrare și plată</NuxtLink>
                </span>
              </div>
            </div>
          </div>

          <!-- Botanical Attributes Table -->
          <div v-if="product.attributes && product.attributes.length > 0" class="pt-8 border-t border-border">
            <h3 class="font-serif text-xl font-bold text-primary-dark mb-4 flex items-center gap-2">
              <span>🌿</span>
              <span>Fișă Tehnică & Caracteristici Pomicole</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div
                v-for="attr in product.attributes"
                :key="attr.name"
                class="bg-surface-warm/70 border border-border/80 rounded-xl p-3.5 flex flex-col"
              >
                <span class="text-xs sm:text-sm uppercase tracking-wide font-semibold text-text-muted">
                  {{ attr.name }}
                </span>
                <span class="text-sm font-medium text-text mt-0.5">
                  {{ attr.terms ? attr.terms.map((t: any) => t.name).join(', ') : '' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Full Description -->
          <div class="pt-8 border-t border-border">
            <h3 class="font-serif text-xl font-bold text-primary-dark mb-4">
              Descriere & Recomandări de Plantare
            </h3>
            <div
              v-if="product.description"
              class="text-sm text-text leading-relaxed prose max-w-none"
              v-html="product.description"
            ></div>
            <div v-else class="text-sm text-text-muted italic">
              Pentru detalii suplimentare despre plantarea și îngrijirea acestui soi, echipa noastră vă oferă consultanță gratuită la telefon.
            </div>
          </div>
        </div>
      </div>

      <!-- Related / Cross-Sell Products Section -->
      <div v-if="relatedProducts.length > 0" class="mt-20 pt-12 border-t border-border">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-3">
          <div>
            <span class="text-xs sm:text-sm uppercase tracking-wide text-primary font-semibold block mb-1">Recomandări din aceeași categorie</span>
            <h2 class="font-serif text-2xl sm:text-3xl font-bold text-primary-dark">
              Soiuri similare care v-ar putea interesa
            </h2>
          </div>
          <NuxtLink
            v-if="primaryCategorySlug"
            :to="`/produse?category=${primaryCategorySlug}`"
            class="text-xs sm:text-sm font-semibold text-primary hover:underline min-h-[44px] inline-flex items-center"
          >
            Vezi toată categoria →
          </NuxtLink>
        </div>
        <ProductGrid :products="relatedProducts" />
      </div>

      <!-- Sticky Mobile Add-to-Cart Bar -->
      <div class="sm:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-border p-3 shadow-2xl flex items-center justify-between gap-3">
        <div class="min-w-0">
          <div class="text-xs font-semibold text-text truncate">{{ product.name }}</div>
          <div class="text-base font-bold text-primary-dark font-sans">{{ formatPrice(product.prices) }}</div>
        </div>
        <AppButton
          variant="warm"
          size="md"
          class="flex-shrink-0"
          :class="!product.is_in_stock ? '!bg-surface-warm !text-text-muted !border-border !opacity-100 !shadow-none' : ''"
          :disabled="!product.is_in_stock || isAdding || hasReachedStockLimit"
          :loading="isAdding"
          @click="handleAddToCart"
        >
          {{ !product.is_in_stock ? 'Indisponibil' : (hasReachedStockLimit ? 'Stoc Maxim în Coș' : 'Adaugă în Coș') }}
        </AppButton>
      </div>
    </div>
  </div>
</template>

