<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { productsService } from '@/services/productsService'
import { categoriesService } from '@/services/categoriesService'
import { useUiStore } from '@/stores/ui'
import type { Product } from '@/types/product'
import type { Category } from '@/types/category'

const uiStore = useUiStore()
const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const selectedCategory = ref<number | ''>('')
const loading = ref(true)
const searchQuery = ref('')

const formattedCategories = computed(() => {
  const list = [...categories.value].filter(c => c.slug !== 'uncategorized')
  const parents = list.filter(c => !c.parent || c.parent === 0)
  const result: (Category & { level: number })[] = []

  parents.forEach(parent => {
    result.push({ ...parent, level: 0 })
    const children = list.filter(c => c.parent === parent.id)
    children.forEach(child => {
      result.push({ ...child, level: 1 })
    })
  })

  list.forEach(c => {
    if (!result.some(r => r.id === c.id)) {
      result.push({ ...c, level: 0 })
    }
  })

  return result
})

const fetchProducts = async () => {
  loading.value = true
  try {
    const params: Record<string, any> = { per_page: 50 }
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedCategory.value) params.category = selectedCategory.value
    products.value = await productsService.list(params)
  } catch (err: any) {
    uiStore.addNotification('error', 'Eroare la încărcarea produselor.')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    categories.value = await categoriesService.list()
  } catch {
    // ignore
  }
  fetchProducts()
})

const handleDelete = async (id: number) => {
  if (!confirm('Sunteți sigur că doriți să ștergeți acest produs?')) return
  try {
    await productsService.delete(id)
    uiStore.addNotification('success', 'Produsul a fost șters cu succes.')
    products.value = products.value.filter(p => p.id !== id)
  } catch (err: any) {
    uiStore.addNotification('error', 'Nu s-a putut șterge produsul.')
  }
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-serif font-bold text-gray-900">
            Gestionare Produse
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Total {{ products.length }} produse în magazinul Pepiniera Ciumbrud
          </p>
        </div>

        <RouterLink
          to="/produse/nou"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-xs font-semibold rounded-xl hover:bg-primary-dark transition-colors shadow-sm self-start sm:self-auto"
        >
          <span>＋</span>
          <span>Adaugă Produs Nou</span>
        </RouterLink>
      </div>

      <!-- Filter bar -->
      <div class="bg-white p-4 rounded-2xl border border-border flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <input
          v-model="searchQuery"
          @keyup.enter="fetchProducts"
          type="text"
          placeholder="Căutați după nume produs..."
          class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
        />

        <select
          v-model="selectedCategory"
          @change="fetchProducts"
          class="bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary text-gray-700 font-medium"
        >
          <option value="">Toate Categoriile</option>
          <option
            v-for="cat in formattedCategories"
            :key="cat.id"
            :value="cat.id"
          >
            {{ cat.level === 1 ? '— ' + cat.name : cat.name }}
          </option>
        </select>

        <button
          @click="fetchProducts"
          class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-xs font-semibold text-gray-700"
        >
          Filtrează
        </button>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
        <div v-if="loading" class="py-16 text-center text-xs text-gray-400">
          Se încarcă produsele...
        </div>

        <div v-else-if="products.length === 0" class="py-16 text-center text-xs text-gray-500">
          Nu s-au găsit produse.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-border">
              <tr>
                <th class="px-6 py-3.5">Produs</th>
                <th class="px-6 py-3.5">Categorie</th>
                <th class="px-6 py-3.5">Preț</th>
                <th class="px-6 py-3.5">Stoc</th>
                <th class="px-6 py-3.5">Status</th>
                <th class="px-6 py-3.5 text-right">Acțiuni</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="p in products" :key="p.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                    <img
                      v-if="p.images && p.images[0]"
                      :src="p.images[0].src"
                      :alt="p.name"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-sm">🌱</div>
                  </div>
                  <div>
                    <span class="font-semibold text-gray-900 block">{{ p.name }}</span>
                    <span class="text-xs text-gray-400">ID: #{{ p.id }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="p.categories && p.categories.length > 0" class="flex flex-wrap gap-1 max-w-xs">
                    <span
                      v-for="cat in p.categories"
                      :key="cat.id"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200/60"
                    >
                      {{ cat.name }}
                    </span>
                  </div>
                  <span v-else class="text-xs text-gray-400 italic">Fără categorie</span>
                </td>
                <td class="px-6 py-4 font-bold text-gray-900">
                  {{ p.price || p.regular_price || '0' }} lei
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-block px-2 py-0.5 rounded-full text-xs font-medium"
                    :class="p.stock_status === 'instock' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                  >
                    {{ p.stock_status === 'instock' ? 'În stoc' : 'Epuizat' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-xs capitalize text-gray-500">
                  {{ p.status }}
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <RouterLink
                    :to="`/produse/${p.id}`"
                    class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-medium"
                  >
                    Editează
                  </RouterLink>
                  <button
                    @click="handleDelete(p.id)"
                    class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-medium"
                  >
                    Șterge
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

