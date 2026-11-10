<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import { useCartStore } from '~/stores/cart'
import AppButton from '~/components/ui/AppButton.vue'
import AppBadge from '~/components/ui/AppBadge.vue'

const route = useRoute()
const slug = route.params.slug as string

const { fetchProductBySlug, formatPrice, getProductImage } = useProducts()
const { setPageSeo, setProductJsonLd } = useSeo()
const cartStore = useCartStore()

const { data: product, status } = await useAsyncData(`product-${slug}`, () =>
  fetchProductBySlug(slug)
)

const quantity = ref(1)
const isAdding = ref(false)

if (product.value) {
  setPageSeo({
    title: product.value.name,
    description: product.value.short_description || product.value.description,
    image: getProductImage(product.value)
  })
  setProductJsonLd(product.value)
}

const handleAddToCart = async () => {
  if (!product.value) return
  isAdding.value = true
  try {
    await cartStore.addItem(product.value.id, quantity.value)
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
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
      <!-- Image Gallery -->
      <div class="bg-white border border-border rounded-3xl p-6 overflow-hidden">
        <div class="aspect-square rounded-2xl overflow-hidden bg-surface-warm mb-4">
          <img
            :src="getProductImage(product)"
            :alt="product.name"
            class="w-full h-full object-cover object-center"
            onerror="this.src='/placeholder-plant.svg'"
          />
        </div>
      </div>

      <!-- Product Details -->
      <div class="space-y-6">
        <!-- Badges -->
        <div class="flex items-center gap-2">
          <AppBadge v-if="product.is_in_stock" variant="success">În Stoc</AppBadge>
          <AppBadge v-else variant="danger">Stoc Epuizat</AppBadge>
          <span v-if="product.sku" class="text-xs text-text-muted font-mono">
            SKU: {{ product.sku }}
          </span>
        </div>

        <!-- Name -->
        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark leading-tight">
          {{ product.name }}
        </h1>

        <!-- Price -->
        <div class="flex items-baseline space-x-3">
          <span class="text-3xl font-bold text-primary-dark font-sans">
            {{ formatPrice(product.prices) }}
          </span>
          <span class="text-xs text-text-muted">TVA inclus</span>
        </div>

        <!-- Short Description -->
        <div
          v-if="product.short_description"
          class="text-sm text-text-muted leading-relaxed prose prose-sm"
          v-html="product.short_description"
        ></div>

        <!-- Quantity & Add to Cart -->
        <div class="pt-4 border-t border-border flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
          <div class="flex items-center border border-border rounded-xl bg-surface-warm p-1">
            <button
              @click="quantity = Math.max(1, quantity - 1)"
              class="w-10 h-10 flex items-center justify-center text-lg hover:bg-border rounded-lg"
              :disabled="quantity <= 1"
            >
              -
            </button>
            <span class="w-12 text-center font-bold text-sm">{{ quantity }}</span>
            <button
              @click="quantity++"
              class="w-10 h-10 flex items-center justify-center text-lg hover:bg-border rounded-lg"
            >
              +
            </button>
          </div>

          <AppButton
            variant="warm"
            size="lg"
            class="flex-1"
            :disabled="!product.is_in_stock || isAdding"
            :loading="isAdding"
            @click="handleAddToCart"
          >
            Adaugă în Coș
          </AppButton>
        </div>

        <!-- Plant Information & Guarantee -->
        <div class="bg-surface-warm/50 border border-border rounded-2xl p-5 space-y-3 text-xs text-text-muted mt-6">
          <div class="flex items-center space-x-2 text-text font-medium">
            <span>🛡️</span>
            <span>Garanție autenticitate soi & prindere</span>
          </div>
          <div class="flex items-center space-x-2 text-text font-medium">
            <span>📦</span>
            <span>Ambalare specială pentru rădăcini libere</span>
          </div>
          <div class="flex items-center space-x-2 text-text font-medium">
            <span>🚚</span>
            <span>Expediere în 24-48h prin curier rapid</span>
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
              <span class="text-[11px] uppercase tracking-wider font-semibold text-text-muted">
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
            Nu există o descriere detaliată pentru acest produs.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

