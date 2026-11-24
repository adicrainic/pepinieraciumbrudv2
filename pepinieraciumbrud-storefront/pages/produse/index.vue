<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import ProductGrid from '~/components/product/ProductGrid.vue'
import AppButton from '~/components/ui/AppButton.vue'
import type { Category } from '~/types/category'

const route = useRoute()
const router = useRouter()
const { fetchProductsPaged, fetchCategories, sortInStockFirst } = useProducts()
const { setPageSeo } = useSeo()

setPageSeo({
  title: 'Catalog Produse – Pomi Fructiferi și Trandafiri',
  description: 'Descoperiți catalogul complet Pepiniera Ciumbrud: pomi fructiferi altoiți, trandafiri nobili, arbuști fructiferi și arbuști ornamentali.'
})

const { data: categoriesData } = await useAsyncData('catalog-categories', () =>
  fetchCategories()
)

const categories = computed<Category[]>(() =>
  (categoriesData.value || []).filter((c: Category) => c.slug !== 'uncategorized')
)

// Preferred display order for the 4 principal categories
const PRINCIPAL_ORDER = [
  'pomi-fructiferi',
  'trandafiri',
  'arbusti-fructiferi',
  'arbusti-ornamentali'
]

// Top-level principal categories (parent === 0)
const principalCategories = computed<Category[]>(() => {
  const roots = categories.value.filter((c) => !c.parent || c.parent === 0)
  return roots.sort((a, b) => {
    const idxA = PRINCIPAL_ORDER.indexOf(a.slug)
    const idxB = PRINCIPAL_ORDER.indexOf(b.slug)
    if (idxA !== -1 && idxB !== -1) return idxA - idxB
    if (idxA !== -1) return -1
    if (idxB !== -1) return 1
    return a.name.localeCompare(b.name, 'ro')
  })
})

const totalCatalogCount = computed(() =>
  principalCategories.value.reduce((sum, cat) => sum + (cat.count || 0), 0)
)

// Selected Principal Category slug ('' = "Toate categoriile") and Subcategory slug ('' = all in principal)
const selectedPrincipal = ref('')
const selectedSubcategory = ref('')

// Resolve initial principal + subcategory from a URL ?category=... slug
const syncStateFromCategorySlug = (slug: string) => {
  if (!slug) {
    selectedPrincipal.value = ''
    selectedSubcategory.value = ''
    return
  }

  const found = categories.value.find((c) => c.slug === slug)
  if (!found) {
    selectedPrincipal.value = slug
    selectedSubcategory.value = ''
    return
  }

  if (!found.parent || found.parent === 0) {
    selectedPrincipal.value = found.slug
    selectedSubcategory.value = ''
  } else {
    const parentCat = categories.value.find((c) => c.id === found.parent)
    selectedPrincipal.value = parentCat ? parentCat.slug : ''
    selectedSubcategory.value = found.slug
  }
}

// Initialize from current route query
syncStateFromCategorySlug(route.query.category?.toString() || '')

// Re-sync if categoriesData finishes loading after initial setup
watch(
  categories,
  () => {
    const currentUrlCat = route.query.category?.toString() || ''
    if (currentUrlCat) {
      syncStateFromCategorySlug(currentUrlCat)
    }
  },
  { immediate: true }
)

// Subcategories scoped to the active principal category (single unified category navigation)
const availableSubcategories = computed<Category[]>(() => {
  if (!selectedPrincipal.value) return []
  const principalObj = principalCategories.value.find((c) => c.slug === selectedPrincipal.value)
  if (!principalObj) return []

  return categories.value
    .filter((c) => c.parent === principalObj.id)
    .sort((a, b) => a.name.localeCompare(b.name, 'ro'))
})

// The effective category slug sent to the WooCommerce Store API and URL query
const activeCategorySlug = computed(() => selectedSubcategory.value || selectedPrincipal.value || '')

const activePrincipalObj = computed(() =>
  principalCategories.value.find((c) => c.slug === selectedPrincipal.value) || null
)

const activeSubcategoryObj = computed(() =>
  categories.value.find((c) => c.slug === selectedSubcategory.value) || null
)

const isRoseContext = computed(() =>
  selectedPrincipal.value === 'trandafiri' ||
  activeCategorySlug.value.includes('trandafir')
)

const selectPrincipalCategory = (slug: string) => {
  selectedPrincipal.value = slug
  selectedSubcategory.value = ''
  if (slug !== 'trandafiri') {
    selectedColor.value = ''
    selectedFragrance.value = ''
  }
}

const selectSubcategory = (slug: string) => {
  selectedSubcategory.value = slug || ''
}

// Filter & Sort state (URL-synced)
const filterDrawerOpen = ref(false)
const searchInput = ref(route.query.search?.toString() || '')
const searchQuery = ref(route.query.search?.toString() || '')
const sortBy = ref(route.query.orderby?.toString() || 'date')
const onlyInStock = ref(route.query.in_stock === '1')
const selectedColor = ref(route.query.color?.toString() || '')
const selectedFragrance = ref(route.query.fragrance?.toString() || '')
const minPrice = ref(route.query.min_price?.toString() || '')
const maxPrice = ref(route.query.max_price?.toString() || '')

const ROSE_COLORS = [
  { value: 'Roșu', label: 'Roșu', swatch: '#be123c' },
  { value: 'Roz', label: 'Roz', swatch: '#f472b6' },
  { value: 'Galben', label: 'Galben', swatch: '#facc15' },
  { value: 'Alb', label: 'Alb', swatch: '#ffffff', border: true },
  { value: 'Portocaliu', label: 'Portocaliu', swatch: '#fb923c' },
  { value: 'Mov / Lila', label: 'Mov / Lila', swatch: '#c084fc' },
  { value: 'Bicolor', label: 'Bicolor', swatch: 'linear-gradient(135deg, #be123c 50%, #fde047 50%)' }
]

const ROSE_FRAGRANCES = [
  { value: 'Intens', label: 'Intens parfumat' },
  { value: 'Moderat', label: 'Parfum moderat' },
  { value: 'Discret', label: 'Parfum discret' }
]

const PRICE_PRESETS = [
  { id: 'all', label: 'Toate prețurile', min: '', max: '' },
  { id: 'under20', label: 'Sub 20 lei', min: '', max: '20' },
  { id: '20to25', label: '20 – 25 lei', min: '20', max: '25' },
  { id: 'over25', label: 'Peste 25 lei', min: '26', max: '' }
]

const activePricePreset = computed(() => {
  const found = PRICE_PRESETS.find(
    (p) => p.min === minPrice.value.trim() && p.max === maxPrice.value.trim()
  )
  return found ? found.id : 'custom'
})

const applyPricePreset = (preset: { min: string; max: string }) => {
  minPrice.value = preset.min
  maxPrice.value = preset.max
}

const toggleColor = (color: string) => {
  selectedColor.value = selectedColor.value === color ? '' : color
  if (selectedColor.value && !selectedPrincipal.value) {
    selectedPrincipal.value = 'trandafiri'
  }
}

const toggleFragrance = (fragrance: string) => {
  selectedFragrance.value = selectedFragrance.value === fragrance ? '' : fragrance
  if (selectedFragrance.value && !selectedPrincipal.value) {
    selectedPrincipal.value = 'trandafiri'
  }
}

const sortLabelMap: Record<string, string> = {
  date: 'Cele mai noi',
  price_asc: 'Preț: crescător',
  price_desc: 'Preț: descrescător',
  title: 'Denumire (A-Z)'
}

const activeFilterCount = computed(() => {
  let count = 0
  if (selectedColor.value) count++
  if (selectedFragrance.value) count++
  if (minPrice.value || maxPrice.value) count++
  if (onlyInStock.value) count++
  if (sortBy.value && sortBy.value !== 'date') count++
  return count
})

const hasAnyFilter = computed(() =>
  Boolean(
    selectedPrincipal.value ||
    selectedSubcategory.value ||
    searchQuery.value ||
    selectedColor.value ||
    selectedFragrance.value ||
    minPrice.value ||
    maxPrice.value ||
    onlyInStock.value ||
    (sortBy.value && sortBy.value !== 'date')
  )
)

const priceSummaryLabel = computed(() => {
  if (minPrice.value && maxPrice.value) return `${minPrice.value} – ${maxPrice.value} lei`
  if (minPrice.value) return `Peste ${minPrice.value} lei`
  if (maxPrice.value) return `Sub ${maxPrice.value} lei`
  return ''
})

const syncUrl = () => {
  const query: Record<string, string> = {}
  if (searchQuery.value) query.search = searchQuery.value
  if (activeCategorySlug.value) query.category = activeCategorySlug.value
  if (sortBy.value && sortBy.value !== 'date') query.orderby = sortBy.value
  if (onlyInStock.value) query.in_stock = '1'
  if (selectedColor.value) query.color = selectedColor.value
  if (selectedFragrance.value) query.fragrance = selectedFragrance.value
  if (minPrice.value) query.min_price = minPrice.value
  if (maxPrice.value) query.max_price = maxPrice.value

  const currentQuery = route.query
  const keys = ['search', 'category', 'orderby', 'in_stock', 'color', 'fragrance', 'min_price', 'max_price']
  const isSame = keys.every(
    (k) => (query[k] || '') === (currentQuery[k]?.toString() || '')
  )

  if (!isSame) {
    router.replace({ query })
  }
}

const executeSearch = () => {
  if (searchTimer) clearTimeout(searchTimer)
  isTypingSearch.value = false
  searchQuery.value = searchInput.value.trim()
  syncUrl()
}

const clearSearch = () => {
  if (searchTimer) clearTimeout(searchTimer)
  isTypingSearch.value = false
  searchInput.value = ''
  searchQuery.value = ''
  syncUrl()
}

const isTypingSearch = ref(false)
let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchInput, (val) => {
  if (searchTimer) clearTimeout(searchTimer)
  const trimmed = val.trim()
  if (searchQuery.value !== trimmed) {
    isTypingSearch.value = true
    searchTimer = setTimeout(() => {
      isTypingSearch.value = false
      if (searchQuery.value !== trimmed) {
        searchQuery.value = trimmed
        syncUrl()
      }
    }, 350)
  } else {
    isTypingSearch.value = false
  }
})

watch(
  [activeCategorySlug, sortBy, onlyInStock, selectedColor, selectedFragrance, minPrice, maxPrice],
  () => {
    syncUrl()
  }
)

watch(
  () => route.query.category,
  (newCat) => {
    const c = newCat?.toString() || ''
    if (activeCategorySlug.value !== c) {
      syncStateFromCategorySlug(c)
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

watch(
  () => route.query.color,
  (newColor) => {
    const c = newColor?.toString() || ''
    if (selectedColor.value !== c) selectedColor.value = c
  }
)

watch(
  () => route.query.fragrance,
  (newFragrance) => {
    const f = newFragrance?.toString() || ''
    if (selectedFragrance.value !== f) selectedFragrance.value = f
  }
)

watch(
  () => route.query.min_price,
  (newMin) => {
    const m = newMin?.toString() || ''
    if (minPrice.value !== m) minPrice.value = m
  }
)

watch(
  () => route.query.max_price,
  (newMax) => {
    const m = newMax?.toString() || ''
    if (maxPrice.value !== m) maxPrice.value = m
  }
)

const PER_PAGE = 21
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
  if (activeCategorySlug.value) params.category = activeCategorySlug.value
  if (onlyInStock.value) params.stock_status = 'instock'
  if (selectedColor.value) params.color = selectedColor.value
  if (selectedFragrance.value) params.fragrance = selectedFragrance.value

  const minVal = parseFloat(minPrice.value)
  if (!isNaN(minVal) && minVal > 0) {
    params.min_price = Math.round(minVal * 100)
  }
  const maxVal = parseFloat(maxPrice.value)
  if (!isNaN(maxVal) && maxVal > 0) {
    params.max_price = Math.round(maxVal * 100)
  }

  return params
}

// Fetch first page based on filters
const { data: productsData, status, refresh } = await useAsyncData(
  'catalog-products',
  () => fetchProductsPaged(buildParams(1)),
  {
    watch: [activeCategorySlug, searchQuery, sortBy, onlyInStock, selectedColor, selectedFragrance, minPrice, maxPrice]
  }
)

const isFiltering = computed(() => status.value === 'pending' || isTypingSearch.value)

// Reset "show more" state whenever filters change
watch(
  [activeCategorySlug, searchQuery, sortBy, onlyInStock, selectedColor, selectedFragrance, minPrice, maxPrice],
  () => {
    currentPage.value = 1
    extraProducts.value = []
  }
)

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

const allLoadedProducts = computed(() => sortInStockFirst([...(productsData.value?.items || []), ...extraProducts.value]))
const products = computed(() => {
  if (onlyInStock.value) {
    return allLoadedProducts.value.filter((p: any) => p.is_in_stock)
  }
  return allLoadedProducts.value
})
const totalProducts = computed(() => productsData.value?.total || 0)
const hasMore = computed(() => allLoadedProducts.value.length < totalProducts.value)

const resetFilters = () => {
  if (searchTimer) clearTimeout(searchTimer)
  isTypingSearch.value = false
  selectedPrincipal.value = ''
  selectedSubcategory.value = ''
  searchInput.value = ''
  searchQuery.value = ''
  sortBy.value = 'date'
  onlyInStock.value = false
  selectedColor.value = ''
  selectedFragrance.value = ''
  minPrice.value = ''
  maxPrice.value = ''
  syncUrl()
}

const resetDrawerFiltersOnly = () => {
  sortBy.value = 'date'
  onlyInStock.value = false
  selectedColor.value = ''
  selectedFragrance.value = ''
  minPrice.value = ''
  maxPrice.value = ''
  syncUrl()
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-3 sm:py-8">
    <!-- 1. Compact Catalog Header -->
    <div class="mb-2.5 sm:mb-5">
      <div class="flex items-center justify-between gap-2 text-xs text-text-muted mb-1">
        <nav aria-label="Breadcrumb" class="flex items-center space-x-1.5 truncate">
          <NuxtLink to="/" class="hover:text-primary">Acasă</NuxtLink>
          <span>/</span>
          <button
            v-if="activePrincipalObj"
            type="button"
            @click="selectPrincipalCategory('')"
            class="hover:text-primary transition-colors"
          >
            Catalog
          </button>
          <span v-else class="text-text font-medium">Catalog</span>
          <template v-if="activePrincipalObj">
            <span>/</span>
            <button
              v-if="activeSubcategoryObj"
              type="button"
              @click="selectedSubcategory = ''"
              class="hover:text-primary transition-colors truncate"
            >
              {{ activePrincipalObj.name }}
            </button>
            <span v-else class="text-text font-medium truncate">{{ activePrincipalObj.name }}</span>
          </template>
          <template v-if="activeSubcategoryObj">
            <span>/</span>
            <span class="text-text font-medium truncate">{{ activeSubcategoryObj.name }}</span>
          </template>
        </nav>

        <span
          class="shrink-0 text-xs font-semibold text-primary-dark bg-surface-warm border border-border px-3 py-1 rounded-full inline-flex items-center gap-1.5"
          aria-live="polite"
        >
          <span
            v-if="isFiltering"
            class="animate-spin inline-block w-3 h-3 border-2 border-primary border-t-transparent rounded-full"
          ></span>
          <span v-if="isFiltering">Se filtrează...</span>
          <span v-else>{{ totalProducts }} {{ totalProducts === 1 ? 'soi' : 'soiuri' }}</span>
        </span>
      </div>

      <div class="flex items-baseline justify-between gap-4">
        <h1 class="font-serif text-xl sm:text-3xl font-bold text-primary-dark leading-tight">
          {{ activeSubcategoryObj?.name || activePrincipalObj?.name || 'Catalog Plante & Pomi Fructiferi' }}
        </h1>
      </div>
      <p class="hidden sm:block text-sm text-text-muted mt-1 max-w-2xl">
        {{
          activeSubcategoryObj?.description ||
          activePrincipalObj?.description ||
          'Soiuri aclimatizate la Ciumbrud, pregătite pentru plantare în grădina sau livada dumneavoastră.'
        }}
      </p>
    </div>

    <!-- 2. Single Unified Category Navigation -->
    <nav aria-label="Categorii produse" class="mb-2.5 sm:mb-4 space-y-2">
      <!-- Principal Categories Row -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1.5 no-scrollbar">
        <button
          type="button"
          @click="selectPrincipalCategory('')"
          class="min-h-[44px] px-4 py-2 rounded-full text-xs sm:text-sm font-semibold whitespace-nowrap transition-all border flex items-center gap-2 shrink-0"
          :class="
            selectedPrincipal === ''
              ? 'bg-primary text-white border-primary shadow-sm'
              : 'bg-white text-text hover:border-primary/50 border-border'
          "
        >
          <span>Toate</span>
          <span
            v-if="totalCatalogCount > 0"
            class="text-xs px-2 py-0.5 rounded-full leading-none"
            :class="selectedPrincipal === '' ? 'bg-white/20 text-white' : 'bg-surface-warm text-text-muted'"
          >
            {{ totalCatalogCount }}
          </span>
        </button>

        <button
          v-for="cat in principalCategories"
          :key="cat.id"
          type="button"
          @click="selectPrincipalCategory(cat.slug)"
          class="min-h-[44px] px-4 py-2 rounded-full text-xs sm:text-sm font-semibold whitespace-nowrap transition-all border flex items-center gap-2 shrink-0"
          :class="
            selectedPrincipal === cat.slug
              ? 'bg-primary text-white border-primary shadow-sm'
              : 'bg-white text-text hover:border-primary/50 border-border'
          "
        >
          <span>{{ cat.name }}</span>
          <span
            class="text-xs px-2 py-0.5 rounded-full leading-none"
            :class="selectedPrincipal === cat.slug ? 'bg-white/20 text-white' : 'bg-surface-warm text-text-muted'"
          >
            {{ cat.count }}
          </span>
        </button>
      </div>

      <!-- Contextual Subcategories Row (only when a Principal Category with subcategories is selected) -->
      <div
        v-if="availableSubcategories.length > 0"
        class="flex items-center gap-2 overflow-x-auto pb-1.5 no-scrollbar pl-1"
      >
        <button
          type="button"
          @click="selectSubcategory('')"
          class="min-h-[44px] px-4 py-2 rounded-full text-xs sm:text-sm font-medium whitespace-nowrap transition-all border shrink-0 flex items-center"
          :class="
            selectedSubcategory === ''
              ? 'bg-primary-dark text-white border-primary-dark shadow-sm font-semibold'
              : 'bg-surface-warm text-text hover:border-primary/40 border-border'
          "
        >
          Toate din {{ activePrincipalObj?.name }}
        </button>
        <button
          v-for="sub in availableSubcategories"
          :key="sub.id"
          type="button"
          @click="selectSubcategory(sub.slug)"
          class="min-h-[44px] px-4 py-2 rounded-full text-xs sm:text-sm font-medium whitespace-nowrap transition-all border flex items-center gap-2 shrink-0"
          :class="
            selectedSubcategory === sub.slug
              ? 'bg-primary-dark text-white border-primary-dark shadow-sm font-semibold'
              : 'bg-surface-warm text-text hover:border-primary/40 border-border'
          "
        >
          <span>{{ sub.name }}</span>
          <span
            class="text-xs px-2 py-0.5 rounded-full leading-none"
            :class="selectedSubcategory === sub.slug ? 'bg-white/20 text-white' : 'bg-white text-text-muted'"
          >
            {{ sub.count }}
          </span>
        </button>
      </div>
    </nav>

    <!-- 3. Compact Search + "Filtrează" Drawer Trigger Bar -->
    <div class="mb-3 sm:mb-5">
      <div class="flex items-center gap-2 sm:gap-3">
        <!-- Compact Search Form -->
        <form @submit.prevent="executeSearch" class="flex-1 min-w-0 relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
            <span
              v-if="isFiltering"
              class="animate-spin inline-block w-4 h-4 border-2 border-primary border-t-transparent rounded-full"
            ></span>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <input
            v-model="searchInput"
            type="text"
            placeholder="Caută soi (ex: Papa Meilland, Florina)..."
            aria-label="Caută soi în catalog"
            class="w-full min-h-[44px] bg-white border border-border rounded-xl pl-9 pr-11 py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text placeholder:text-text-muted shadow-sm"
            @keydown.enter="executeSearch"
          />

          <button
            v-if="searchInput"
            type="button"
            @click="clearSearch"
            class="absolute inset-y-0 right-0 min-w-[44px] min-h-[44px] px-3 flex items-center justify-center text-text-muted hover:text-text transition-colors"
            title="Șterge căutarea"
            aria-label="Șterge căutarea"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </form>

        <!-- Collapsible "Filtrează" Drawer Toggle Button -->
        <button
          type="button"
          @click="filterDrawerOpen = !filterDrawerOpen"
          :aria-expanded="filterDrawerOpen"
          aria-controls="catalog-filter-drawer"
          class="min-h-[44px] px-4 py-2.5 rounded-xl border text-xs sm:text-sm font-semibold flex items-center gap-2 shrink-0 transition-all shadow-sm"
          :class="
            filterDrawerOpen || activeFilterCount > 0
              ? 'bg-primary text-white border-primary'
              : 'bg-white text-text border-border hover:border-primary/60'
          "
        >
          <span
            v-if="isFiltering"
            class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full shrink-0"
          ></span>
          <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          <span>Filtrează</span>
          <span
            v-if="activeFilterCount > 0"
            class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 rounded-full text-xs font-bold leading-none"
            :class="filterDrawerOpen || activeFilterCount > 0 ? 'bg-white text-primary' : 'bg-primary text-white'"
          >
            {{ activeFilterCount }}
          </span>
          <svg
            class="w-3.5 h-3.5 shrink-0 transition-transform duration-200"
            :class="filterDrawerOpen ? 'rotate-180' : ''"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>

      <!-- Quick Rose Filters Strip (shown when browsing Trandafiri while drawer is closed, for 1-tap rose narrowing) -->
      <div
        v-if="isRoseContext && !filterDrawerOpen"
        class="mt-2.5 flex items-center gap-2 overflow-x-auto pb-1.5 no-scrollbar"
      >
        <span class="text-xs font-semibold text-text-muted shrink-0 mr-0.5">Culoare:</span>
        <button
          v-for="c in ROSE_COLORS"
          :key="c.value"
          type="button"
          @click="toggleColor(c.value)"
          class="min-h-[44px] px-3.5 py-2 rounded-full text-xs sm:text-sm font-medium border flex items-center gap-2 shrink-0 transition-all"
          :class="
            selectedColor === c.value
              ? 'bg-primary text-white border-primary shadow-sm font-semibold'
              : 'bg-white text-text border-border hover:border-primary/50'
          "
        >
          <span
            class="w-3.5 h-3.5 rounded-full shrink-0"
            :class="c.border ? 'border border-gray-300' : ''"
            :style="{ background: c.swatch }"
          ></span>
          <span>{{ c.label }}</span>
        </button>

        <span class="text-border mx-1">|</span>
        <span class="text-xs font-semibold text-text-muted shrink-0 mr-0.5">Parfum:</span>

        <button
          v-for="f in ROSE_FRAGRANCES"
          :key="f.value"
          type="button"
          @click="toggleFragrance(f.value)"
          class="min-h-[44px] px-3.5 py-2 rounded-full text-xs sm:text-sm font-medium border flex items-center gap-1.5 shrink-0 transition-all"
          :class="
            selectedFragrance === f.value
              ? 'bg-primary text-white border-primary shadow-sm font-semibold'
              : 'bg-white text-text border-border hover:border-primary/50'
          "
        >
          <span>🌸 {{ f.label }}</span>
        </button>
      </div>

      <!-- 4. Collapsible "Filtrează" Drawer -->
      <div
        v-if="filterDrawerOpen"
        id="catalog-filter-drawer"
        class="mt-2.5 bg-white border border-border rounded-2xl p-4 sm:p-6 shadow-lg space-y-5 animate-fade-in"
      >
        <!-- Drawer Header: Title + Result Count + Close -->
        <div class="flex items-center justify-between pb-3 border-b border-border">
          <div class="flex items-center gap-2.5">
            <h2 class="font-serif text-base sm:text-lg font-bold text-primary-dark">
              Filtrează și sortează
            </h2>
            <span class="text-xs font-semibold text-primary bg-primary/10 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5" aria-live="polite">
              <span
                v-if="isFiltering"
                class="animate-spin inline-block w-3 h-3 border-2 border-primary border-t-transparent rounded-full"
              ></span>
              <span v-if="isFiltering">Se actualizează...</span>
              <span v-else>{{ totalProducts }} {{ totalProducts === 1 ? 'rezultat' : 'rezultate' }}</span>
            </span>
          </div>
          <button
            type="button"
            @click="filterDrawerOpen = false"
            class="min-h-[44px] px-3.5 py-2 text-xs sm:text-sm font-semibold text-text-muted hover:text-text rounded-lg hover:bg-surface-warm transition-colors flex items-center justify-center"
            aria-label="Închide panoul de filtrare"
          >
            Închide ✕
          </button>
        </div>

        <div class="grid grid-cols-1 gap-5" :class="isRoseContext ? 'md:grid-cols-2' : ''">
          <!-- Sort Order & Availability -->
          <div class="space-y-4">
            <div>
              <label for="catalog-sort-select" class="block text-xs sm:text-sm font-bold uppercase tracking-wide text-text-muted mb-2">
                Sortare produse
              </label>
              <select
                id="catalog-sort-select"
                v-model="sortBy"
                aria-label="Ordine sortare"
                class="w-full min-h-[44px] bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
              >
                <option value="date">Cele mai noi</option>
                <option value="price_asc">Preț: crescător</option>
                <option value="price_desc">Preț: descrescător</option>
                <option value="title">Denumire (A-Z)</option>
              </select>
            </div>

            <!-- Price Filter (Presets + Min/Max inputs) -->
            <div>
              <span class="block text-xs sm:text-sm font-bold uppercase tracking-wide text-text-muted mb-2">
                Preț (lei)
              </span>
              <div class="flex flex-wrap gap-2 mb-2.5">
                <button
                  v-for="preset in PRICE_PRESETS"
                  :key="preset.id"
                  type="button"
                  @click="applyPricePreset(preset)"
                  class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium border transition-all flex items-center justify-center"
                  :class="
                    activePricePreset === preset.id
                      ? 'bg-primary text-white border-primary font-semibold shadow-sm'
                      : 'bg-surface-warm text-text border-border hover:border-primary/50'
                  "
                >
                  {{ preset.label }}
                </button>
              </div>
              <div class="flex items-center gap-2">
                <input
                  v-model="minPrice"
                  type="number"
                  min="0"
                  placeholder="Min (lei)"
                  aria-label="Preț minim în lei"
                  class="w-full min-h-[44px] bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
                />
                <span class="text-text-muted text-xs">–</span>
                <input
                  v-model="maxPrice"
                  type="number"
                  min="0"
                  placeholder="Max (lei)"
                  aria-label="Preț maxim în lei"
                  class="w-full min-h-[44px] bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
                />
              </div>
            </div>

            <!-- In-Stock Toggle -->
            <div>
              <label class="inline-flex items-center gap-2.5 min-h-[44px] px-4 py-2.5 rounded-xl bg-surface-warm border border-border cursor-pointer select-none text-xs sm:text-sm font-medium text-text hover:border-primary/50 transition-colors">
                <input
                  v-model="onlyInStock"
                  type="checkbox"
                  class="rounded text-primary focus:ring-primary w-4 h-4"
                />
                <span>Afișează doar soiurile în stoc</span>
              </label>
            </div>
          </div>

          <!-- Rose-Specific Filters: Colour & Fragrance (shown only when Trandafiri category is selected) -->
          <div v-if="isRoseContext" class="space-y-4">
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wide text-text-muted">
                  Culoare
                </span>
                <button
                  v-if="selectedColor"
                  type="button"
                  @click="selectedColor = ''"
                  class="min-h-[44px] px-2 text-xs sm:text-sm text-primary hover:underline flex items-center"
                >
                  Toate culorile
                </button>
              </div>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="c in ROSE_COLORS"
                  :key="c.value"
                  type="button"
                  @click="toggleColor(c.value)"
                  class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium border flex items-center gap-2 transition-all"
                  :class="
                    selectedColor === c.value
                      ? 'bg-primary text-white border-primary font-semibold shadow-sm'
                      : 'bg-surface-warm text-text border-border hover:border-primary/50'
                  "
                >
                  <span
                    class="w-3.5 h-3.5 rounded-full shrink-0"
                    :class="c.border ? 'border border-gray-300' : ''"
                    :style="{ background: c.swatch }"
                  ></span>
                  <span>{{ c.label }}</span>
                </button>
              </div>
            </div>

            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wide text-text-muted">
                  Parfum
                </span>
                <button
                  v-if="selectedFragrance"
                  type="button"
                  @click="selectedFragrance = ''"
                  class="min-h-[44px] px-2 text-xs sm:text-sm text-primary hover:underline flex items-center"
                >
                  Orice parfum
                </button>
              </div>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="f in ROSE_FRAGRANCES"
                  :key="f.value"
                  type="button"
                  @click="toggleFragrance(f.value)"
                  class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium border transition-all flex items-center justify-center"
                  :class="
                    selectedFragrance === f.value
                      ? 'bg-primary text-white border-primary font-semibold shadow-sm'
                      : 'bg-surface-warm text-text border-border hover:border-primary/50'
                  "
                >
                  🌸 {{ f.label }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Drawer Footer: Reset + Apply / Show N Results -->
        <div class="pt-3 border-t border-border flex flex-wrap items-center justify-between gap-3">
          <button
            v-if="activeFilterCount > 0"
            type="button"
            @click="resetDrawerFiltersOnly"
            class="min-h-[44px] px-3.5 py-2 text-xs sm:text-sm font-semibold text-rose-600 hover:underline flex items-center"
          >
            Resetează filtrele ({{ activeFilterCount }})
          </button>
          <span v-else class="text-xs text-text-muted">
            Selectați criteriile dorite pentru filtrare rapidă.
          </span>

          <AppButton
            type="button"
            variant="primary"
            size="sm"
            class="min-h-[44px] !px-5"
            :loading="isFiltering"
            @click="filterDrawerOpen = false"
          >
            {{ isFiltering ? 'Se filtrează...' : `Vezi ${totalProducts} ${totalProducts === 1 ? 'produs' : 'produse'}` }}
          </AppButton>
        </div>
      </div>

      <!-- Active Filter Chips Summary -->
      <div
        v-if="hasAnyFilter"
        class="mt-2.5 flex flex-wrap items-center gap-2 text-xs text-text-muted"
      >
        <span v-if="activePrincipalObj" class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary-dark font-medium rounded-full">
          {{ activePrincipalObj.name }}
          <button type="button" @click="selectPrincipalCategory('')" class="hover:text-rose-600 ml-0.5 p-1 min-w-[28px] min-h-[28px] flex items-center justify-center" title="Toate categoriile">✕</button>
        </span>
        <span v-if="activeSubcategoryObj" class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-warm border border-border text-text font-medium rounded-full">
          {{ activeSubcategoryObj.name }}
          <button type="button" @click="selectedSubcategory = ''" class="hover:text-rose-600 ml-0.5 p-1 min-w-[28px] min-h-[28px] flex items-center justify-center" title="Șterge subcategoria">✕</button>
        </span>
        <span v-if="selectedColor" class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary-dark font-medium rounded-full">
          Culoare: {{ selectedColor }}
          <button type="button" @click="selectedColor = ''" class="hover:text-rose-600 ml-0.5 p-1 min-w-[28px] min-h-[28px] flex items-center justify-center" title="Șterge filtrul de culoare">✕</button>
        </span>
        <span v-if="selectedFragrance" class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary-dark font-medium rounded-full">
          Parfum: {{ selectedFragrance }}
          <button type="button" @click="selectedFragrance = ''" class="hover:text-rose-600 ml-0.5 p-1 min-w-[28px] min-h-[28px] flex items-center justify-center" title="Șterge filtrul de parfum">✕</button>
        </span>
        <span v-if="priceSummaryLabel" class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-warm border border-border text-text font-medium rounded-full">
          Preț: {{ priceSummaryLabel }}
          <button type="button" @click="applyPricePreset({ min: '', max: '' })" class="hover:text-rose-600 ml-0.5 p-1 min-w-[28px] min-h-[28px] flex items-center justify-center" title="Șterge filtrul de preț">✕</button>
        </span>
        <span v-if="searchQuery" class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary-dark font-medium rounded-full">
          „{{ searchQuery }}”
          <button type="button" @click="clearSearch" class="hover:text-rose-600 ml-0.5 p-1 min-w-[28px] min-h-[28px] flex items-center justify-center" title="Șterge căutarea">✕</button>
        </span>
        <span v-if="onlyInStock" class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-leaf-50 border border-leaf-100 text-leaf-700 font-medium rounded-full">
          Doar în stoc
          <button type="button" @click="onlyInStock = false" class="hover:text-rose-600 ml-0.5 p-1 min-w-[28px] min-h-[28px] flex items-center justify-center" title="Toate produsele">✕</button>
        </span>
        <span v-if="sortBy !== 'date'" class="min-h-[36px] inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-warm border border-border text-text font-medium rounded-full">
          {{ sortLabelMap[sortBy] || sortBy }}
          <button type="button" @click="sortBy = 'date'" class="hover:text-rose-600 ml-0.5 p-1 min-w-[28px] min-h-[28px] flex items-center justify-center" title="Sortare implicită">✕</button>
        </span>
        <button
          type="button"
          @click="resetFilters"
          class="min-h-[44px] px-2 text-xs sm:text-sm text-rose-600 hover:underline font-semibold ml-1 flex items-center"
        >
          Resetează tot
        </button>
      </div>
    </div>

    <!-- Active Filtering Status Banner -->
    <div
      v-if="isFiltering"
      class="mb-3 flex items-center justify-center gap-2 py-2 px-3.5 rounded-xl bg-surface-warm border border-primary/20 text-xs font-semibold text-primary-dark shadow-sm animate-fade-in"
      role="status"
      aria-live="polite"
    >
      <span class="animate-spin inline-block w-3.5 h-3.5 border-2 border-primary border-t-transparent rounded-full shrink-0"></span>
      <span>Se actualizează lista de produse...</span>
    </div>

    <!-- 5. Error State -->
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

    <!-- 6. Products Grid -->
    <ProductGrid
      v-else
      :products="products"
      :loading="isFiltering"
    />

    <!-- 7. Show More Pagination -->
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
