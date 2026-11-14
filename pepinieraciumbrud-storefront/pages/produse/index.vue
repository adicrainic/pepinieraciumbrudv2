<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import ProductGrid from '~/components/product/ProductGrid.vue'
import AppButton from '~/components/ui/AppButton.vue'

const route = useRoute()
const router = useRouter()
const { fetchProductsPaged, fetchCategories } = useProducts()
const { setPageSeo } = useSeo()

setPageSeo({
  title: 'Catalog Produse – Pomi Fructiferi și Trandafiri',
  description: 'Descoperiți catalogul complet Pepiniera Ciumbrud: pomi fructiferi altoiți, trandafiri nobili, arbuști fructiferi și arbuști ornamentali.'
})

const selectedCategory = ref(route.query.category?.toString() || '')
const searchInput = ref(route.query.search?.toString() || '')
const searchQuery = ref(route.query.search?.toString() || '')
const sortBy = ref(route.query.orderby?.toString() || 'date')
const onlyInStock = ref(route.query.in_stock === '1')

const syncUrl = () => {
  const query: Record<string, string> = {}
  if (searchQuery.value) query.search = searchQuery.value
  if (selectedCategory.value) query.category = selectedCategory.value
  if (sortBy.value && sortBy.value !== 'date') query.orderby = sortBy.value
  if (onlyInStock.value) query.in_stock = '1'

  // Compare to current query to avoid duplicate navigation
  const currentQuery = route.query
  const isSame =
    (query.search || '') === (currentQuery.search?.toString() || '') &&
    (query.category || '') === (currentQuery.category?.toString() || '') &&
    (query.orderby || '') === (currentQuery.orderby?.toString() || '') &&
    (query.in_stock || '') === (currentQuery.in_stock?.toString() || '')

  if (!isSame) {
    router.replace({ query })
  }
}

const executeSearch = () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchQuery.value = searchInput.value.trim()
  syncUrl()
}

const clearSearch = () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchInput.value = ''
  searchQuery.value = ''
  syncUrl()
}

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchInput, (val) => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    if (searchQuery.value !== val.trim()) {
      searchQuery.value = val.trim()
      syncUrl()
    }
  }, 350)
})

watch([selectedCategory, sortBy, onlyInStock], () => {
  syncUrl()
})

watch(
  () => route.query.category,
  (newCat) => {
    const c = newCat?.toString() || ''
    if (selectedCategory.value !== c) {
      selectedCategory.value = c
    }
  }
)

watch(
  () => route.query.search,
  (newSearch) => {
    const s = newSearch?.toString() || ''
    if (searchInput.value !== s) searchInput.value = s
    if (searchQuery.value !== s) searchQuery.value = s
  }
)

watch(
  () => route.query.in_stock,
  (newStock) => {
    const isStock = newStock === '1'
    if (onlyInStock.value !== isStock) onlyInStock.value = isStock
  }
)

const PER_PAGE = 20
const currentPage = ref(1)
const extraProducts = ref<any[]>([])
const loadingMore = ref(false)

const buildParams = (page: number) => {
  const params: Record<string, any> = { per_page: PER_PAGE, page }
  const sortMap: Record<string, { orderby: string; order?: string }> = {
    date: { orderby: 'date', order: 'desc' },
    price_asc: { orderby: 'price', order: 'asc' },
    price_desc: { orderby: 'price', order: 'desc' },
    title: { orderby: 'title', order: 'asc' }
  }
  Object.assign(params, sortMap[sortBy.value] || sortMap.date)
  if (searchQuery.value) params.search = searchQuery.value
  // "Toate categoriile" => no category filter, all products are returned
  if (selectedCategory.value) params.category = selectedCategory.value
  if (onlyInStock.value) params.stock_status = 'instock'
  return params
}

// Fetch first page based on filters
const { data: productsData, status, refresh, error } = await useAsyncData(
  'catalog-products',
  () => fetchProductsPaged(buildParams(1)),
  {
    watch: [selectedCategory, searchQuery, sortBy, onlyInStock]
  }
)

// Reset "show more" state whenever filters change
watch([selectedCategory, searchQuery, sortBy, onlyInStock], () => {
  currentPage.value = 1
  extraProducts.value = []
})

const loadMore = async () => {
  if (loadingMore.value) return
  loadingMore.value = true
  try {
    const next = currentPage.value + 1
    const res = await fetchProductsPaged(buildParams(next))
    extraProducts.value = [...extraProducts.value, ...res.items]
    currentPage.value = next
  } finally {
    loadingMore.value = false
  }
}

const { data: categoriesData } = await useAsyncData('catalog-categories', () =>
  fetchCategories()
)

const allLoadedProducts = computed(() => [...(productsData.value?.items || []), ...extraProducts.value])
const products = computed(() => {
  if (onlyInStock.value) {
    return allLoadedProducts.value.filter((p: any) => p.is_in_stock)
  }
  return allLoadedProducts.value
})
const totalProducts = computed(() => productsData.value?.total || 0)
const hasMore = computed(() => allLoadedProducts.value.length < totalProducts.value)
const categories = computed(() => (categoriesData.value || []).filter((c: any) => c.slug !== 'uncategorized'))

const resetFilters = () => {
  if (searchTimer) clearTimeout(searchTimer)
  selectedCategory.value = ''
  searchInput.value = ''
  searchQuery.value = ''
  sortBy.value = 'date'
  onlyInStock.value = false
  syncUrl()
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
      <nav class="text-xs text-text-muted mb-3 flex items-center space-x-2">
        <NuxtLink to="/" class="hover:text-primary">Acasă</NuxtLink>
        <span>/</span>
        <span class="text-text font-medium">Catalog Produse</span>
      </nav>
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
          <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark">
            Catalog Plante & Pomi Fructiferi
          </h1>
          <p class="text-sm text-text-muted mt-2 max-w-2xl">
            Alegeți dintr-o gamă variată de soiuri productive și rezistente, pregătite pentru plantare în grădina sau livada dumneavoastră.
          </p>
        </div>
        <div v-if="status !== 'pending'" class="text-xs font-medium text-text-muted bg-white border border-border px-3.5 py-2 rounded-xl self-start sm:self-auto">
          Total: <strong class="text-text">{{ totalProducts }}</strong> {{ totalProducts === 1 ? 'soi disponibil' : 'soiuri în catalog' }}
        </div>
      </div>
    </div>

    <!-- Visual Category Pills for 1-Tap Browsing -->
    <div v-if="categories.length > 0" class="mb-6 flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
      <button
        type="button"
        @click="selectedCategory = ''"
        class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all border"
        :class="
          selectedCategory === ''
            ? 'bg-primary text-white border-primary shadow-sm'
            : 'bg-white text-text hover:border-primary/50 border-border'
        "
      >
        Toate plantele
      </button>
      <button
        v-for="cat in categories"
        :key="cat.id"
        type="button"
        @click="selectedCategory = cat.slug"
        class="px-4 py-2 rounded-full text-xs font-medium whitespace-nowrap transition-all border flex items-center gap-1.5"
        :class="
          selectedCategory === cat.slug
            ? 'bg-primary text-white border-primary shadow-sm font-semibold'
            : 'bg-white text-text hover:border-primary/50 border-border'
        "
      >
        <span>{{ cat.name }}</span>
        <span
          class="text-[10px] px-1.5 py-0.2 rounded-full"
          :class="selectedCategory === cat.slug ? 'bg-white/20 text-white' : 'bg-surface-warm text-text-muted'"
        >
          {{ cat.count }}
        </span>
      </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-border rounded-2xl p-4 sm:p-5 mb-6 shadow-sm flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
      <!-- Search Input Form -->
      <form @submit.prevent="executeSearch" class="w-full lg:w-2/5 relative flex items-center">
        <div class="relative w-full">
          <!-- Magnifying glass icon on left -->
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <input
            v-model="searchInput"
            type="text"
            placeholder="Căutare după denumire soi (ex: Ionatan, Florina, Teahibrid)..."
            class="w-full bg-surface-warm border border-border rounded-xl pl-10 pr-20 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text placeholder:text-text-muted transition-all"
            @keydown.enter="executeSearch"
          />

          <!-- Clear (X) button -->
          <button
            v-if="searchInput"
            type="button"
            @click="clearSearch"
            class="absolute inset-y-0 right-16 pr-2 flex items-center text-text-muted hover:text-text transition-colors"
            title="Șterge căutarea"
            aria-label="Șterge căutarea"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Submit Button -->
          <button
            type="submit"
            class="absolute right-1.5 top-1.5 bottom-1.5 px-3 bg-primary text-white text-xs font-medium rounded-lg hover:bg-primary-dark transition-all flex items-center gap-1 active:scale-95"
          >
            <span v-if="status === 'pending'" class="animate-spin inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full mr-1"></span>
            <span>Caută</span>
          </button>
        </div>
      </form>

      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 justify-end">
        <!-- Categories Dropdown -->
        <div class="w-full sm:w-48">
          <select
            v-model="selectedCategory"
            aria-label="Filtrează după categorie"
            class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
          >
            <option value="">Toate categoriile</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.slug">
              {{ cat.name }} ({{ cat.count }})
            </option>
          </select>
        </div>

        <!-- Sort Dropdown -->
        <div class="w-full sm:w-44">
          <select
            v-model="sortBy"
            aria-label="Ordine sortare"
            class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
          >
            <option value="date">Cele mai noi</option>
            <option value="price_asc">Preț: crescător</option>
            <option value="price_desc">Preț: descrescător</option>
            <option value="title">Denumire (A-Z)</option>
          </select>
        </div>

        <!-- In-Stock Only Toggle -->
        <label class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-surface-warm border border-border cursor-pointer select-none text-xs font-medium text-text hover:border-primary/50 transition-colors whitespace-nowrap">
          <input
            v-model="onlyInStock"
            type="checkbox"
            class="rounded text-primary focus:ring-primary w-4 h-4"
          />
          <span>Doar în stoc</span>
        </label>

        <button
          v-if="selectedCategory || searchInput || onlyInStock"
          @click="resetFilters"
          class="text-xs text-rose-600 hover:underline font-medium whitespace-nowrap px-2 py-1"
        >
          Resetează
        </button>
      </div>
    </div>

    <!-- Active Filter Badge -->
    <div v-if="searchQuery || selectedCategory || onlyInStock" class="mb-8 flex flex-wrap items-center gap-2 text-xs text-text-muted">
      <span>Filtre active:</span>
      <span v-if="searchQuery" class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary-dark font-medium rounded-full">
        Căutare: „{{ searchQuery }}”
        <button @click="clearSearch" class="hover:text-rose-600 transition-colors ml-1" title="Șterge termenul de căutare">
          ✕
        </button>
      </span>
      <span v-if="selectedCategory" class="inline-flex items-center gap-1 px-3 py-1 bg-surface-warm border border-border text-text font-medium rounded-full">
        Categorie: {{ categories.find(c => c.slug === selectedCategory)?.name || selectedCategory }}
        <button @click="selectedCategory = ''" class="hover:text-rose-600 transition-colors ml-1" title="Șterge categoria">
          ✕
        </button>
      </span>
      <span v-if="onlyInStock" class="inline-flex items-center gap-1 px-3 py-1 bg-leaf-50 border border-leaf-100 text-leaf-700 font-medium rounded-full">
        Doar produse în stoc
        <button @click="onlyInStock = false" class="hover:text-rose-600 transition-colors ml-1" title="Afișează toate produsele">
          ✕
        </button>
      </span>
      <span class="text-text-muted ml-1">({{ products.length }} afișate din {{ totalProducts }})</span>
    </div>

    <!-- Error State -->
    <div
      v-if="status === 'error'"
      class="text-center py-16 px-4 bg-white border border-rose-200 rounded-2xl shadow-sm mb-8"
    >
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-2xl font-bold">
        !
      </div>
      <h3 class="font-serif text-lg font-bold text-text mb-1">
        A apărut o eroare la încărcarea produselor
      </h3>
      <p class="text-sm text-text-muted mb-4 max-w-md mx-auto">
        Nu am putut prelua catalogul în acest moment. Vă rugăm să verificați conexiunea și să reîncercați.
      </p>
      <AppButton
        @click="() => refresh()"
        variant="primary"
        size="sm"
      >
        Reîncearcă
      </AppButton>
    </div>

    <!-- Grid -->
    <ProductGrid
      v-else
      :products="products"
      :loading="status === 'pending'"
    />

    <!-- Show more -->
    <div v-if="status !== 'error' && hasMore && status !== 'pending'" class="text-center mt-10">
      <p class="text-xs text-text-muted mb-3">Afișate {{ products.length }} din {{ totalProducts }} produse</p>
      <AppButton
        @click="loadMore"
        :disabled="loadingMore"
        :loading="loadingMore"
        variant="primary"
        size="md"
      >
        {{ loadingMore ? 'Se încarcă...' : 'Arată mai multe' }}
      </AppButton>
    </div>
  </div>
</template>

