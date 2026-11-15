<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import ProductGrid from '~/components/product/ProductGrid.vue'
import AppButton from '~/components/ui/AppButton.vue'
import type { Category } from '~/types/category'

const route = useRoute()
const router = useRouter()
const { fetchProductsPaged, fetchCategories } = useProducts()
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
    // Fallback if categories haven't loaded or custom slug
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

// Subcategories available based on the selected principal category on the left menu
const availableSubcategories = computed<Category[]>(() => {
  if (!selectedPrincipal.value) {
    // When "Toate categoriile" is selected, list all subcategories (children)
    return categories.value
      .filter((c) => c.parent && c.parent !== 0)
      .sort((a, b) => a.name.localeCompare(b.name, 'ro'))
  }

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

const selectPrincipalCategory = (slug: string) => {
  selectedPrincipal.value = slug
  selectedSubcategory.value = ''
}

const selectSubcategory = (slug: string) => {
  if (!slug) {
    selectedSubcategory.value = ''
    return
  }
  // If user is on "Toate categoriile" and picks a subcategory, keep principal synced or filter directly
  selectedSubcategory.value = slug
}

const searchInput = ref(route.query.search?.toString() || '')
const searchQuery = ref(route.query.search?.toString() || '')
const sortBy = ref(route.query.orderby?.toString() || 'date')
const onlyInStock = ref(route.query.in_stock === '1')

const syncUrl = () => {
  const query: Record<string, string> = {}
  if (searchQuery.value) query.search = searchQuery.value
  if (activeCategorySlug.value) query.category = activeCategorySlug.value
  if (sortBy.value && sortBy.value !== 'date') query.orderby = sortBy.value
  if (onlyInStock.value) query.in_stock = '1'

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

watch([activeCategorySlug, sortBy, onlyInStock], () => {
  syncUrl()
})

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
  if (activeCategorySlug.value) params.category = activeCategorySlug.value
  if (onlyInStock.value) params.stock_status = 'instock'
  return params
}

// Fetch first page based on filters
const { data: productsData, status, refresh } = await useAsyncData(
  'catalog-products',
  () => fetchProductsPaged(buildParams(1)),
  {
    watch: [activeCategorySlug, searchQuery, sortBy, onlyInStock]
  }
)

// Reset "show more" state whenever filters change
watch([activeCategorySlug, searchQuery, sortBy, onlyInStock], () => {
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

const allLoadedProducts = computed(() => [...(productsData.value?.items || []), ...extraProducts.value])
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
  selectedPrincipal.value = ''
  selectedSubcategory.value = ''
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
        <button
          v-if="activePrincipalObj"
          type="button"
          @click="selectPrincipalCategory('')"
          class="hover:text-primary transition-colors"
        >
          Catalog Produse
        </button>
        <span v-else class="text-text font-medium">Catalog Produse</span>
        <template v-if="activePrincipalObj">
          <span>/</span>
          <button
            v-if="activeSubcategoryObj"
            type="button"
            @click="selectedSubcategory = ''"
            class="hover:text-primary transition-colors"
          >
            {{ activePrincipalObj.name }}
          </button>
          <span v-else class="text-text font-medium">{{ activePrincipalObj.name }}</span>
        </template>
        <template v-if="activeSubcategoryObj">
          <span>/</span>
          <span class="text-text font-medium">{{ activeSubcategoryObj.name }}</span>
        </template>
      </nav>

      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
          <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark">
            {{ activeSubcategoryObj?.name || activePrincipalObj?.name || 'Catalog Plante & Pomi Fructiferi' }}
          </h1>
          <p class="text-sm text-text-muted mt-2 max-w-2xl">
            {{
              activeSubcategoryObj?.description ||
              activePrincipalObj?.description ||
              'Alegeți dintr-o gamă variată de soiuri productive și rezistente, pregătite pentru plantare în grădina sau livada dumneavoastră.'
            }}
          </p>
        </div>
        <div v-if="status !== 'pending'" class="text-xs font-medium text-text-muted bg-white border border-border px-3.5 py-2 rounded-xl self-start sm:self-auto">
          Total: <strong class="text-text">{{ totalProducts }}</strong> {{ totalProducts === 1 ? 'soi disponibil' : 'soiuri în această selecție' }}
        </div>
      </div>
    </div>

    <!-- Main Layout: Left Principal Category Menu + Right Filters & Product Grid -->
    <div class="flex flex-col lg:flex-row gap-8 items-start">
      <!-- Left Sidebar: Principal Categories Menu -->
      <aside class="w-full lg:w-64 xl:w-72 shrink-0 lg:sticky lg:top-28">
        <div class="bg-white border border-border rounded-2xl overflow-hidden shadow-sm">
          <div class="bg-surface-warm/80 px-5 py-3.5 border-b border-border flex items-center justify-between">
            <h2 class="font-serif text-lg font-bold text-primary-dark tracking-wide">
              Categorii Principale
            </h2>
            <span class="text-[11px] uppercase tracking-wider font-semibold text-primary">
              {{ principalCategories.length }} grupe
            </span>
          </div>

          <nav aria-label="Categorii principale" class="p-2.5 space-y-1">
            <!-- 1. Default First Option: Toate categoriile -->
            <button
              type="button"
              @click="selectPrincipalCategory('')"
              class="w-full flex items-center justify-between px-3.5 py-3 rounded-xl text-left text-sm transition-all duration-150 group"
              :class="
                selectedPrincipal === ''
                  ? 'bg-primary text-white font-semibold shadow-sm'
                  : 'text-text hover:bg-surface-warm hover:text-primary-dark font-medium'
              "
            >
              <span class="flex items-center gap-2.5 min-w-0">
                <span
                  class="w-2 h-2 rounded-full shrink-0 transition-colors"
                  :class="selectedPrincipal === '' ? 'bg-white' : 'bg-primary/40 group-hover:bg-primary'"
                ></span>
                <span class="truncate">Toate categoriile</span>
              </span>
              <span
                v-if="totalCatalogCount > 0"
                class="text-xs px-2 py-0.5 rounded-full shrink-0 ml-2 font-semibold"
                :class="
                  selectedPrincipal === ''
                    ? 'bg-white/20 text-white'
                    : 'bg-surface-warm text-text-muted group-hover:bg-white'
                "
              >
                {{ totalCatalogCount }}
              </span>
            </button>

            <!-- 2. Each of the Principal Categories -->
            <button
              v-for="cat in principalCategories"
              :key="cat.id"
              type="button"
              @click="selectPrincipalCategory(cat.slug)"
              class="w-full flex items-center justify-between px-3.5 py-3 rounded-xl text-left text-sm transition-all duration-150 group"
              :class="
                selectedPrincipal === cat.slug
                  ? 'bg-primary text-white font-semibold shadow-sm'
                  : 'text-text hover:bg-surface-warm hover:text-primary-dark font-medium'
              "
            >
              <span class="flex items-center gap-2.5 min-w-0">
                <span
                  class="w-2 h-2 rounded-full shrink-0 transition-colors"
                  :class="selectedPrincipal === cat.slug ? 'bg-white' : 'bg-primary/40 group-hover:bg-primary'"
                ></span>
                <span class="truncate">{{ cat.name }}</span>
              </span>
              <span
                class="text-xs px-2 py-0.5 rounded-full shrink-0 ml-2 font-semibold"
                :class="
                  selectedPrincipal === cat.slug
                    ? 'bg-white/20 text-white'
                    : 'bg-surface-warm text-text-muted group-hover:bg-white'
                "
              >
                {{ cat.count }}
              </span>
            </button>
          </nav>
        </div>
      </aside>

      <!-- Right Content Area: Contextual Filters + Products Grid -->
      <div class="flex-1 min-w-0 w-full">
        <!-- Contextual Subcategory Pills (dynamically scoped to the selected Principal Category) -->
        <div
          v-if="availableSubcategories.length > 0"
          class="mb-4 flex items-center gap-2 overflow-x-auto pb-1.5 no-scrollbar"
        >
          <button
            type="button"
            @click="selectSubcategory('')"
            class="px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all border"
            :class="
              selectedSubcategory === ''
                ? 'bg-primary text-white border-primary shadow-sm'
                : 'bg-white text-text hover:border-primary/50 border-border'
            "
          >
            {{ activePrincipalObj ? `Toate din ${activePrincipalObj.name}` : 'Toate soiurile' }}
          </button>
          <button
            v-for="sub in availableSubcategories"
            :key="sub.id"
            type="button"
            @click="selectSubcategory(sub.slug)"
            class="px-3.5 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all border flex items-center gap-1.5"
            :class="
              selectedSubcategory === sub.slug
                ? 'bg-primary text-white border-primary shadow-sm font-semibold'
                : 'bg-white text-text hover:border-primary/50 border-border'
            "
          >
            <span>{{ sub.name }}</span>
            <span
              class="text-[11px] px-1.5 py-0.2 rounded-full"
              :class="selectedSubcategory === sub.slug ? 'bg-white/20 text-white' : 'bg-surface-warm text-text-muted'"
            >
              {{ sub.count }}
            </span>
          </button>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-border rounded-2xl p-4 sm:p-5 mb-6 shadow-sm flex flex-col xl:flex-row gap-4 items-stretch xl:items-center justify-between">
          <!-- Search Input Form -->
          <form @submit.prevent="executeSearch" class="w-full xl:w-5/12 relative flex items-center">
            <div class="relative w-full">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>

              <input
                v-model="searchInput"
                type="text"
                placeholder="Căutare soi (ex: Ionatan, Florina, Teahibrid)..."
                class="w-full bg-surface-warm border border-border rounded-xl pl-10 pr-20 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text placeholder:text-text-muted transition-all"
                @keydown.enter="executeSearch"
              />

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
            <!-- Contextual Subcategories Dropdown (options based on selected Principal Category) -->
            <div v-if="availableSubcategories.length > 0" class="w-full sm:w-48">
              <select
                v-model="selectedSubcategory"
                aria-label="Filtrează după subcategorie / specie"
                class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary text-text"
              >
                <option value="">
                  {{ activePrincipalObj ? `Toate (${activePrincipalObj.name})` : 'Toate subcategoriile' }}
                </option>
                <option v-for="sub in availableSubcategories" :key="sub.id" :value="sub.slug">
                  {{ sub.name }} ({{ sub.count }})
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
              v-if="selectedPrincipal || selectedSubcategory || searchInput || onlyInStock"
              @click="resetFilters"
              class="text-xs text-rose-600 hover:underline font-medium whitespace-nowrap px-2 py-1"
            >
              Resetează
            </button>
          </div>
        </div>

        <!-- Active Filter Badges -->
        <div v-if="searchQuery || selectedPrincipal || selectedSubcategory || onlyInStock" class="mb-6 flex flex-wrap items-center gap-2 text-xs text-text-muted">
          <span>Filtre active:</span>
          <span v-if="activePrincipalObj" class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary-dark font-medium rounded-full">
            Categorie: {{ activePrincipalObj.name }}
            <button @click="selectPrincipalCategory('')" class="hover:text-rose-600 transition-colors ml-1" title="Toate categoriile">
              ✕
            </button>
          </span>
          <span v-if="activeSubcategoryObj" class="inline-flex items-center gap-1 px-3 py-1 bg-surface-warm border border-border text-text font-medium rounded-full">
            Specie: {{ activeSubcategoryObj.name }}
            <button @click="selectedSubcategory = ''" class="hover:text-rose-600 transition-colors ml-1" title="Șterge subcategoria">
              ✕
            </button>
          </span>
          <span v-if="searchQuery" class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary-dark font-medium rounded-full">
            Căutare: „{{ searchQuery }}”
            <button @click="clearSearch" class="hover:text-rose-600 transition-colors ml-1" title="Șterge termenul de căutare">
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
    </div>
  </div>
</template>
