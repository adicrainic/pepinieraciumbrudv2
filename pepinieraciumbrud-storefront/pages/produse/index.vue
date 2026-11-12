<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import ProductGrid from '~/components/product/ProductGrid.vue'

const route = useRoute()
const { fetchProducts, fetchCategories } = useProducts()
const { setPageSeo } = useSeo()

setPageSeo({
  title: 'Catalog Produse – Pomi Fructiferi și Trandafiri',
  description: 'Descoperiți catalogul complet Pepiniera Ciumbrud: pomi fructiferi altoiți, trandafiri nobili, arbuști fructiferi și arbuști ornamentali.'
})

const selectedCategory = ref(route.query.category?.toString() || '')
const searchInput = ref(route.query.search?.toString() || '')
const searchQuery = ref(route.query.search?.toString() || '')
const sortBy = ref(route.query.orderby?.toString() || 'date')

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchInput, (val) => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    searchQuery.value = val.trim()
  }, 300)
})

watch(
  () => route.query.category,
  (newCat) => {
    selectedCategory.value = newCat?.toString() || ''
  }
)

watch(
  () => route.query.search,
  (newSearch) => {
    const s = newSearch?.toString() || ''
    searchInput.value = s
    searchQuery.value = s
  }
)

// Fetch products based on filters
const { data: productsData, status, refresh, error } = await useAsyncData(
  'catalog-products',
  () => {
    const params: Record<string, any> = {
      per_page: 24,
      orderby: sortBy.value
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedCategory.value) params.category = selectedCategory.value
    return fetchProducts(params)
  },
  {
    watch: [selectedCategory, searchQuery, sortBy]
  }
)

const { data: categoriesData } = await useAsyncData('catalog-categories', () =>
  fetchCategories()
)

const products = computed(() => productsData.value || [])
const categories = computed(() => categoriesData.value || [])

const resetFilters = () => {
  selectedCategory.value = ''
  searchInput.value = ''
  searchQuery.value = ''
  sortBy.value = 'date'
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb & Header -->
    <div class="mb-10">
      <nav class="text-xs text-text-muted mb-3 flex items-center space-x-2">
        <NuxtLink to="/" class="hover:text-primary">Acasă</NuxtLink>
        <span>/</span>
        <span class="text-text font-medium">Catalog Produse</span>
      </nav>
      <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark">
        Catalog Plante & Pomi Fructiferi
      </h1>
      <p class="text-sm text-text-muted mt-2 max-w-2xl">
        Alegeți dintr-o gamă variată de soiuri productive și rezistente, pregătite pentru plantare în grădina sau livada dumneavoastră.
      </p>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-border rounded-2xl p-4 sm:p-6 mb-10 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
      <!-- Search Input -->
      <div class="w-full md:w-1/3 relative">
        <input
          v-model="searchInput"
          type="text"
          placeholder="Căutare după denumire soi..."
          class="w-full bg-surface-warm border border-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
        />
      </div>

      <!-- Categories Dropdown -->
      <div class="w-full md:w-1/3">
        <select
          v-model="selectedCategory"
          class="w-full bg-surface-warm border border-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
        >
          <option value="">Toate categoriile</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.slug">
            {{ cat.name }} ({{ cat.count }})
          </option>
        </select>
      </div>

      <!-- Sort Dropdown -->
      <div class="w-full md:w-1/4">
        <select
          v-model="sortBy"
          class="w-full bg-surface-warm border border-border rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
        >
          <option value="date">Cele mai noi</option>
          <option value="price_asc">Preț: crescător</option>
          <option value="price_desc">Preț: descrescător</option>
          <option value="title">Denumire (A-Z)</option>
        </select>
      </div>

      <button
        v-if="selectedCategory || searchInput"
        @click="resetFilters"
        class="text-xs text-rose-600 hover:underline font-medium whitespace-nowrap"
      >
        Resetează filtrele
      </button>
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
      <button
        @click="() => refresh()"
        class="inline-flex items-center px-4 py-2 bg-primary text-white text-xs font-semibold rounded-xl hover:bg-primary-dark transition-colors shadow-sm"
      >
        Reîncearcă
      </button>
    </div>

    <!-- Grid -->
    <ProductGrid
      v-else
      :products="products"
      :loading="status === 'pending'"
    />
  </div>
</template>

