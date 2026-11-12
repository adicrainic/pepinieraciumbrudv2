<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ProductImageUploader from '@/components/ui/ProductImageUploader.vue'
import { categoriesService } from '@/services/categoriesService'
import { useUiStore } from '@/stores/ui'
import { stripHtml } from '@/utils/text'
import type { Category } from '@/types/category'

const uiStore = useUiStore()
const categories = ref<Category[]>([])
const loading = ref(true)
const createLoading = ref(false)
const editLoading = ref(false)

// Create state
const newCategory = ref({
  name: '',
  parent: 0,
  description: '',
  image: { id: undefined as number | undefined, src: '' }
})

// Edit state & modal
const isEditModalOpen = ref(false)
const editingCategory = ref<Category | null>(null)
const editForm = ref({
  id: 0,
  name: '',
  parent: 0,
  description: '',
  image: { id: undefined as number | undefined, src: '' }
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

// Hierarchical listing of categories
const formattedCategories = computed(() => {
  const list = [...categories.value].filter(c => c.slug !== 'uncategorized')
  const parents = list.filter(c => !c.parent || c.parent === 0)
  const result: (Category & { level: number; parentName?: string })[] = []

  parents.forEach(parent => {
    result.push({ ...parent, level: 0 })
    const children = list.filter(c => c.parent === parent.id)
    children.forEach(child => {
      result.push({ ...child, level: 1, parentName: parent.name })
    })
  })

  // Append any categories with unknown or deeper parents
  list.forEach(c => {
    if (!result.some(r => r.id === c.id)) {
      const p = categories.value.find(item => item.id === c.parent)
      result.push({ ...c, level: c.parent ? 1 : 0, parentName: p?.name })
    }
  })

  return result
})

// Prevent circular parenting when editing
const getEligibleParents = (currentId?: number) => {
  if (!currentId) {
    return formattedCategories.value
  }
  const excludeIds = new Set<number>([currentId])
  let added = true
  while (added) {
    added = false
    for (const c of categories.value) {
      if (c.parent && excludeIds.has(c.parent) && !excludeIds.has(c.id)) {
        excludeIds.add(c.id)
        added = true
      }
    }
  }
  return formattedCategories.value.filter(c => !excludeIds.has(c.id))
}

const handleCreate = async () => {
  if (!newCategory.value.name.trim()) {
    uiStore.addNotification('error', 'Numele categoriei este obligatoriu.')
    return
  }

  createLoading.value = true
  try {
    const payload: any = {
      name: newCategory.value.name.trim(),
      parent: Number(newCategory.value.parent),
      description: newCategory.value.description
    }
    if (newCategory.value.image?.id || newCategory.value.image?.src) {
      payload.image = {
        id: newCategory.value.image.id,
        src: newCategory.value.image.src
      }
    }
    await categoriesService.create(payload)
    uiStore.addNotification('success', 'Categoria a fost adăugată.')
    newCategory.value = { name: '', parent: 0, description: '', image: { id: undefined, src: '' } }
    await fetchCategories()
  } catch (err) {
    uiStore.addNotification('error', 'Eroare la crearea categoriei.')
  } finally {
    createLoading.value = false
  }
}

const openEditModal = (cat: Category) => {
  editingCategory.value = cat
  editForm.value = {
    id: cat.id,
    name: cat.name,
    parent: cat.parent || 0,
    description: stripHtml(cat.description),
    image: cat.image ? { id: cat.image.id, src: cat.image.src, title: cat.image.name || cat.name } : { id: undefined, src: '' }
  }
  isEditModalOpen.value = true
}

const closeEditModal = () => {
  isEditModalOpen.value = false
  editingCategory.value = null
}

const handleUpdate = async () => {
  if (!editForm.value.name.trim()) {
    uiStore.addNotification('error', 'Numele categoriei este obligatoriu.')
    return
  }

  editLoading.value = true
  try {
    const payload: any = {
      name: editForm.value.name.trim(),
      parent: Number(editForm.value.parent),
      description: editForm.value.description,
      image: (editForm.value.image?.id || editForm.value.image?.src)
        ? { id: editForm.value.image.id, src: editForm.value.image.src }
        : null
    }
    await categoriesService.update(editForm.value.id, payload)
    uiStore.addNotification('success', 'Categoria a fost actualizată.')
    closeEditModal()
    await fetchCategories()
  } catch (err) {
    uiStore.addNotification('error', 'Eroare la actualizarea categoriei.')
  } finally {
    editLoading.value = false
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
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-serif font-bold text-gray-900">
            Categorii de Plante
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Organizează catalogul în categorii principale și subcategorii (ex: Pomi Fructiferi ➔ Meri, Peri)
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Add Category Form -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm space-y-4">
          <div class="flex items-center gap-2">
            <span class="text-lg">🌿</span>
            <h3 class="font-serif font-bold text-base text-gray-900">
              Adaugă Categorie Nouă
            </h3>
          </div>

          <form @submit.prevent="handleCreate" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Nume Categorie *
              </label>
              <input
                v-model="newCategory.name"
                type="text"
                required
                placeholder="Ex: Pomi Fructiferi sau Meri"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Categorie Părinte (Parent)
              </label>
              <select
                v-model="newCategory.parent"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              >
                <option :value="0">— Niciuna (Categorie Principală) —</option>
                <option
                  v-for="parent in formattedCategories"
                  :key="parent.id"
                  :value="parent.id"
                >
                  {{ parent.level === 1 ? '↳ ' + parent.name : parent.name }}
                </option>
              </select>
              <p class="text-[11px] text-gray-400 mt-1">
                Alegeți o categorie părinte pentru a crea o subcategorie, sau lăsați „Niciuna” pentru categorie principală.
              </p>
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

            <div>
              <ProductImageUploader
                v-model="newCategory.image"
                label="Imagine Categorie"
                helper-text="Format: JPG, PNG, WEBP. Salvare pe server."
                empty-title="Încarcă imagine categorie"
                empty-subtitle="Apasă sau trage imaginea aici"
                :compact="true"
              />
            </div>

            <button
              type="submit"
              :disabled="createLoading"
              class="w-full py-2.5 bg-primary text-white text-xs font-semibold rounded-xl hover:bg-primary-dark transition-colors shadow-sm disabled:opacity-50"
            >
              <span v-if="!createLoading">＋ Adaugă Categoria</span>
              <span v-else>Se adaugă...</span>
            </button>
          </form>
        </div>

        <!-- Categories List Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
          <div v-if="loading" class="py-16 text-center text-xs text-gray-400">
            Se încarcă categoriile...
          </div>

          <div v-else-if="formattedCategories.length === 0" class="py-16 text-center text-xs text-gray-500">
            Nu s-au găsit categorii.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-border">
                <tr>
                  <th class="px-6 py-3.5">Nume Categorie</th>
                  <th class="px-6 py-3.5">Părinte</th>
                  <th class="px-6 py-3.5 text-center">Produse</th>
                  <th class="px-6 py-3.5 text-right">Acțiuni</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="cat in formattedCategories"
                  :key="cat.id"
                  class="hover:bg-gray-50 transition-colors"
                  :class="{ 'bg-gray-50/40': cat.level === 1 }"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3" :style="{ paddingLeft: cat.level === 1 ? '1.5rem' : '0' }">
                      <span v-if="cat.level === 1" class="text-gray-400 select-none font-bold">↳</span>
                      <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-200 flex items-center justify-center">
                        <img
                          v-if="cat.image?.src"
                          :src="cat.image.src"
                          :alt="cat.name"
                          class="w-full h-full object-cover"
                        />
                        <span v-else class="text-base select-none">{{ cat.level === 1 ? '🌿' : '📁' }}</span>
                      </div>
                      <div class="min-w-0">
                        <div class="flex items-center gap-2">
                          <span
                            class="text-gray-900"
                            :class="cat.level === 0 ? 'font-bold' : 'font-medium text-gray-700'"
                          >
                            {{ cat.name }}
                          </span>
                          <span
                            v-if="cat.level === 1"
                            class="text-[10px] uppercase font-semibold tracking-wider px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 border border-amber-200/50"
                          >
                            Subcategorie
                          </span>
                        </div>
                        <p v-if="cat.description" class="text-xs text-gray-400 truncate max-w-xs mt-0.5">
                          {{ stripHtml(cat.description) }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-xs text-gray-600">
                    <span v-if="cat.parentName" class="inline-flex items-center gap-1 font-medium text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md">
                      📁 {{ cat.parentName }}
                    </span>
                    <span v-else class="text-gray-400 italic">
                      — Principală
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-gray-100 text-xs font-semibold text-gray-700">
                      {{ cat.count }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                    <button
                      @click="openEditModal(cat)"
                      class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-medium transition-colors"
                    >
                      Editează
                    </button>
                    <button
                      @click="handleDelete(cat.id)"
                      class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-medium transition-colors"
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
    </div>

    <!-- Edit Category Modal Dialog -->
    <div
      v-if="isEditModalOpen"
      class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4"
      @click.self="closeEditModal"
      @keydown.esc="closeEditModal"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-border space-y-5 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div>
            <h3 class="font-serif font-bold text-lg text-gray-900">
              Editează Categoria
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">
              Modifică denumirea, categoria părinte sau descrierea
            </p>
          </div>
          <button
            @click="closeEditModal"
            class="text-gray-400 hover:text-gray-700 rounded-lg p-1 text-lg leading-none"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="handleUpdate" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Nume Categorie *
            </label>
            <input
              v-model="editForm.name"
              type="text"
              required
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Categorie Părinte (Parent)
            </label>
            <select
              v-model="editForm.parent"
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            >
              <option :value="0">— Niciuna (Categorie Principală) —</option>
              <option
                v-for="parent in getEligibleParents(editingCategory?.id)"
                :key="parent.id"
                :value="parent.id"
              >
                {{ parent.level === 1 ? '↳ ' + parent.name : parent.name }}
              </option>
            </select>
            <p class="text-[11px] text-gray-400 mt-1">
              Schimbarea categoriei părinte reorganizează această categorie în arborele magazinului.
            </p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Descriere
            </label>
            <textarea
              v-model="editForm.description"
              rows="3"
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            ></textarea>
          </div>

          <div>
            <ProductImageUploader
              v-model="editForm.image"
              label="Imagine Categorie"
              helper-text="Format: JPG, PNG, WEBP. Salvare pe server."
              empty-title="Încarcă imagine categorie"
              empty-subtitle="Apasă sau trage imaginea aici"
              :compact="true"
            />
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
            <button
              type="button"
              @click="closeEditModal"
              class="px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Anulează
            </button>
            <button
              type="submit"
              :disabled="editLoading"
              class="px-5 py-2 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition-colors shadow-sm disabled:opacity-50"
            >
              <span v-if="!editLoading">Salvează Modificările</span>
              <span v-else>Se salvează...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
