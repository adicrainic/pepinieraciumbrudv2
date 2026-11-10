<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { categoriesService } from '@/services/categoriesService'
import { useUiStore } from '@/stores/ui'
import type { Category } from '@/types/category'

const uiStore = useUiStore()
const categories = ref<Category[]>([])
const loading = ref(true)

const newCategory = ref({
  name: '',
  description: ''
})

const fetchCategories = async () => {
  loading.value = true
  try {
    categories.value = await categoriesService.list()
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-au putut încărca categoriile.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCategories()
})

const handleCreate = async () => {
  if (!newCategory.value.name) return
  try {
    await categoriesService.create(newCategory.value)
    uiStore.addNotification('success', 'Categoria a fost adăugată.')
    newCategory.value = { name: '', description: '' }
    fetchCategories()
  } catch (err) {
    uiStore.addNotification('error', 'Eroare la crearea categoriei.')
  }
}

const handleDelete = async (id: number) => {
  if (!confirm('Sigur doriți să ștergeți această categorie?')) return
  try {
    await categoriesService.delete(id)
    uiStore.addNotification('success', 'Categoria a fost ștearsă.')
    categories.value = categories.value.filter(c => c.id !== id)
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-a putut șterge categoria.')
  }
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-serif font-bold text-gray-900">
          Categorii de Plante
        </h1>
        <p class="text-xs text-gray-500 mt-1">
          Organizează catalogul (Pomi fructiferi, Trandafiri, Arbuști, etc.)
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Add Category Form -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm space-y-4">
          <h3 class="font-serif font-bold text-base text-gray-900">
            Adaugă Categorie Nouă
          </h3>

          <form @submit.prevent="handleCreate" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Nume Categorie *
              </label>
              <input
                v-model="newCategory.name"
                type="text"
                required
                placeholder="Ex: Pomi Fructiferi"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Descriere
              </label>
              <textarea
                v-model="newCategory.description"
                rows="3"
                placeholder="Descriere scurtă a categoriei..."
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              ></textarea>
            </div>

            <button
              type="submit"
              class="w-full py-2.5 bg-primary text-white text-xs font-semibold rounded-xl hover:bg-primary-dark transition-colors shadow-sm"
            >
              Adaugă Categoria
            </button>
          </form>
        </div>

        <!-- Categories List Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
          <div v-if="loading" class="py-12 text-center text-xs text-gray-400">
            Se încarcă categoriile...
          </div>

          <table v-else class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-border">
              <tr>
                <th class="px-6 py-3.5">Nume Categorie</th>
                <th class="px-6 py-3.5">Slug</th>
                <th class="px-6 py-3.5 text-center">Produse</th>
                <th class="px-6 py-3.5 text-right">Acțiuni</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 font-semibold text-gray-900">
                  {{ cat.name }}
                </td>
                <td class="px-6 py-4 font-mono text-xs text-gray-500">
                  {{ cat.slug }}
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-block px-2.5 py-0.5 rounded-full bg-gray-100 text-xs font-semibold text-gray-700">
                    {{ cat.count }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button
                    @click="handleDelete(cat.id)"
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

