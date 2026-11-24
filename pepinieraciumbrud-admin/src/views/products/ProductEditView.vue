<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import ProductImageUploader from '@/components/ui/ProductImageUploader.vue'
import { productsService } from '@/services/productsService'
import { categoriesService } from '@/services/categoriesService'
import { useUiStore } from '@/stores/ui'
import { stripHtml } from '@/utils/text'
import type { Category } from '@/types/category'

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()

const id = Number(route.params.id)
const loading = ref(true)
const saving = ref(false)
const categories = ref<Category[]>([])
const selectedCategoryIds = ref<number[]>([])

const fromInventory = computed(() => route.query.from === 'inventar')
const backRoute = computed(() => (fromInventory.value ? '/inventar' : '/produse'))
const backLabel = computed(() => (fromInventory.value ? 'Înapoi la Inventar' : 'Înapoi la Listă'))

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

const form = ref({
  name: '',
  regular_price: '',
  sale_price: '',
  sku: '',
  description: '',
  short_description: '',
  stock_status: 'instock' as 'instock' | 'outofstock',
  stock_quantity: '' as number | string,
  low_stock_amount: '' as number | string,
  status: 'publish' as 'publish' | 'draft',
  portaltoi: '',
  coacere: '',
  polenizatori: '',
  distanta: '',
  culoare: '',
  parfum: '',
  inaltime: '',
  inflorire: '',
  images: [
    { id: undefined as number | undefined, src: '' },
    { id: undefined as number | undefined, src: '' }
  ]
})

onMounted(async () => {
  if (!id || isNaN(id)) {
    uiStore.addNotification('error', 'ID produs invalid.')
    router.push(backRoute.value)
    return
  }

  try {
    try {
      categories.value = await categoriesService.list()
    } catch {
      // ignore
    }

    const prod = await productsService.get(id)
    form.value.name = prod.name
    form.value.regular_price = prod.regular_price || prod.price || ''
    form.value.sale_price = prod.sale_price || ''
    form.value.sku = prod.sku || ''
    form.value.description = stripHtml(prod.description)
    form.value.short_description = stripHtml(prod.short_description)
    form.value.stock_status = prod.stock_status || 'instock'
    form.value.stock_quantity = prod.stock_quantity !== null && prod.stock_quantity !== undefined ? prod.stock_quantity : ''
    form.value.low_stock_amount = prod.low_stock_amount !== null && prod.low_stock_amount !== undefined ? prod.low_stock_amount : ''
    form.value.status = (prod.status as any) || 'publish'
    const loadedImages = Array.isArray(prod.images) ? prod.images : []
    form.value.images = [
      loadedImages[0] && (loadedImages[0].src || loadedImages[0].id)
        ? { id: loadedImages[0].id, src: loadedImages[0].src || '' }
        : { id: undefined, src: '' },
      loadedImages[1] && (loadedImages[1].src || loadedImages[1].id)
        ? { id: loadedImages[1].id, src: loadedImages[1].src || '' }
        : { id: undefined, src: '' }
    ]

    if (prod.categories && Array.isArray(prod.categories)) {
      selectedCategoryIds.value = prod.categories.map((c: any) => c.id)
    }

    if (prod.attributes && Array.isArray(prod.attributes)) {
      const getAttr = (name: string) => {
        const a = prod.attributes.find((x: any) => x.name.toLowerCase() === name.toLowerCase())
        return a && a.options ? a.options.join(', ') : ''
      }
      form.value.portaltoi = getAttr('Portaltoi')
      form.value.coacere = getAttr('Perioadă de coacere')
      form.value.polenizatori = getAttr('Polenizatori')
      form.value.distanta = getAttr('Distanță de plantare')
      form.value.culoare = getAttr('Culoare')
      form.value.parfum = getAttr('Parfum')
      form.value.inaltime = getAttr('Înălțime la maturitate')
      form.value.inflorire = getAttr('Perioadă de înflorire')
    }
  } catch (err: any) {
    uiStore.addNotification('error', 'Nu s-a putut încărca produsul.')
    router.push(backRoute.value)
  } finally {
    loading.value = false
  }
})

const handleUpdate = async () => {
  saving.value = true
  try {
    const attributes: any[] = []
    if (form.value.portaltoi.trim()) {
      attributes.push({ name: 'Portaltoi', options: [form.value.portaltoi.trim()], visible: true })
    }
    if (form.value.coacere.trim()) {
      attributes.push({ name: 'Perioadă de coacere', options: [form.value.coacere.trim()], visible: true })
    }
    if (form.value.polenizatori.trim()) {
      attributes.push({
        name: 'Polenizatori',
        options: form.value.polenizatori.split(',').map((s: string) => s.trim()).filter(Boolean),
        visible: true
      })
    }
    if (form.value.distanta.trim()) {
      attributes.push({ name: 'Distanță de plantare', options: [form.value.distanta.trim()], visible: true })
    }
    if (form.value.culoare.trim()) {
      attributes.push({ name: 'Culoare', options: [form.value.culoare.trim()], visible: true })
    }
    if (form.value.parfum.trim()) {
      attributes.push({ name: 'Parfum', options: [form.value.parfum.trim()], visible: true })
    }
    if (form.value.inaltime.trim()) {
      attributes.push({ name: 'Înălțime la maturitate', options: [form.value.inaltime.trim()], visible: true })
    }
    if (form.value.inflorire.trim()) {
      attributes.push({ name: 'Perioadă de înflorire', options: [form.value.inflorire.trim()], visible: true })
    }

    const imagesPayload: any[] = []
    for (const img of form.value.images || []) {
      if (img && (img.src || img.id)) {
        imagesPayload.push(img.id ? { id: img.id, src: img.src } : { src: img.src })
      }
    }

    const payload: any = {
      name: form.value.name,
      regular_price: form.value.regular_price,
      sale_price: form.value.sale_price,
      sku: form.value.sku,
      description: form.value.description,
      short_description: form.value.short_description,
      stock_status: form.value.stock_status,
      stock_quantity: form.value.stock_quantity === '' ? null : Number(form.value.stock_quantity),
      low_stock_amount: form.value.low_stock_amount === '' ? null : Number(form.value.low_stock_amount),
      manage_stock: form.value.stock_quantity !== '',
      status: form.value.status,
      attributes,
      images: imagesPayload,
      categories: selectedCategoryIds.value.map(id => ({ id }))
    }
    await productsService.update(id, payload)
    uiStore.addNotification('success', 'Produsul a fost actualizat.')
    router.push(backRoute.value)
  } catch (err: any) {
    uiStore.addNotification('error', 'Eroare la actualizare.')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-serif font-bold text-gray-900">
            Editare Produs <span v-if="form.name">: {{ form.name }}</span><span v-else>#{{ id }}</span>
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Modifică prețul, stocul sau descrierea acestui soi (ID: #{{ id }})
          </p>
        </div>

        <RouterLink
          :to="backRoute"
          class="px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 flex items-center gap-1.5 transition-colors"
        >
          <span>←</span>
          <span>{{ backLabel }}</span>
        </RouterLink>
      </div>

      <div v-if="loading" class="py-16 text-center text-xs text-gray-400">
        Se încarcă datele produsului...
      </div>

      <form v-else @submit.prevent="handleUpdate" class="space-y-6">
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-border shadow-sm space-y-5">
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Denumire Produs *
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            />
          </div>

          <!-- Categorii Produs -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-semibold text-gray-700">
                Categorii Produs (din <RouterLink to="/categorii" class="text-primary hover:underline">/categorii</RouterLink>)
              </label>
              <RouterLink
                to="/categorii"
                target="_blank"
                class="text-[11px] text-primary hover:underline font-medium inline-flex items-center gap-1"
              >
                <span>Gestionează Categoriile ↗</span>
              </RouterLink>
            </div>

            <!-- Categories Selector Box -->
            <div class="border border-gray-300 rounded-xl p-3 bg-gray-50/70 max-h-52 overflow-y-auto space-y-1">
              <div v-if="categories.length === 0" class="text-xs text-gray-400 py-2 text-center">
                Se încarcă categoriile...
              </div>
              <div
                v-for="cat in formattedCategories"
                :key="cat.id"
                class="flex items-center gap-2.5 py-1 px-2 rounded-lg hover:bg-white transition-colors cursor-pointer"
                :class="{ 'bg-emerald-50 font-medium': selectedCategoryIds.includes(cat.id) }"
                :style="{ paddingLeft: cat.level === 1 ? '1.5rem' : '0.5rem' }"
              >
                <input
                  type="checkbox"
                  :id="'edit-cat-' + cat.id"
                  :value="cat.id"
                  v-model="selectedCategoryIds"
                  class="rounded text-primary focus:ring-primary h-4 w-4 border-gray-300 cursor-pointer"
                />
                <label :for="'edit-cat-' + cat.id" class="text-xs text-gray-700 cursor-pointer flex-1 select-none flex items-center justify-between">
                  <span :class="{ 'font-semibold text-gray-900': cat.level === 0 }">
                    {{ cat.level === 1 ? '↳ ' + cat.name : cat.name }}
                  </span>
                  <span v-if="cat.count !== undefined" class="text-[10px] text-gray-400 font-normal">
                    ({{ cat.count }} produse)
                  </span>
                </label>
              </div>
            </div>

            <!-- Selected categories preview badges -->
            <div v-if="selectedCategoryIds.length > 0" class="flex items-center gap-1.5 flex-wrap mt-2">
              <span class="text-[11px] text-gray-500 font-medium">Selectate:</span>
              <span
                v-for="id in selectedCategoryIds"
                :key="id"
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800"
              >
                {{ categories.find(c => c.id === id)?.name || id }}
                <button
                  type="button"
                  @click="selectedCategoryIds = selectedCategoryIds.filter(cid => cid !== id)"
                  class="hover:text-emerald-950 font-bold ml-0.5 text-xs"
                >×</button>
              </span>
            </div>
            <p v-else class="text-[11px] text-gray-400 mt-1">
              Bifați una sau mai multe categorii din cele definite în secțiunea Categorii.
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Cod Produs / SKU
              </label>
              <input
                v-model="form.sku"
                type="text"
                placeholder="Opțional (ex: MAR-FLOR-01)"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Preț Standard (RON) *
              </label>
              <input
                v-model="form.regular_price"
                type="text"
                required
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Preț Redus (Promoție)
              </label>
              <input
                v-model="form.sale_price"
                type="text"
                placeholder="Opțional"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Cantitate În Stoc
              </label>
              <input
                v-model="form.stock_quantity"
                type="number"
                min="0"
                placeholder="ex: 50"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Prag Avertizare Stoc
              </label>
              <input
                v-model="form.low_stock_amount"
                type="number"
                min="0"
                placeholder="Implicit: 2"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Stare Stoc
              </label>
              <select
                v-model="form.stock_status"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              >
                <option value="instock">În stoc</option>
                <option value="outofstock">Stoc epuizat</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Status Publicare
              </label>
              <select
                v-model="form.status"
                class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
              >
                <option value="publish">Publicat</option>
                <option value="draft">Ciornă</option>
              </select>
            </div>
          </div>

          <!-- Specificații Botanice & Pomicole -->
          <div class="pt-5 border-t border-gray-200">
            <h3 class="font-serif font-bold text-sm text-gray-900 mb-3 flex items-center gap-1.5">
              <span>🌿</span>
              <span>Specificații Botanice & Pomicole</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Portaltoi (Rootstock)
                </label>
                <input
                  v-model="form.portaltoi"
                  type="text"
                  placeholder="ex: MM106, M9, Mirobolan, Kober 5BB"
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Perioadă de Coacere / Recoltare
                </label>
                <input
                  v-model="form.coacere"
                  type="text"
                  placeholder="ex: Iulie, Octombrie, etc."
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Polenizatori Recomandați (separați prin virgulă)
                </label>
                <input
                  v-model="form.polenizatori"
                  type="text"
                  placeholder="ex: Golden Delicious, Jonathan, Autofertil"
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Distanță de Plantare Recomandată
                </label>
                <input
                  v-model="form.distanta"
                  type="text"
                  placeholder="ex: 4 x 3 metri, 3 x 2 metri"
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Culoare Floare (Trandafiri / Ornamentale)
                </label>
                <input
                  v-model="form.culoare"
                  type="text"
                  placeholder="ex: Roșu, Roz, Galben, Alb, Portocaliu, Mov / Lila, Bicolor"
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Intensitate Parfum (Trandafiri)
                </label>
                <input
                  v-model="form.parfum"
                  type="text"
                  placeholder="ex: Intens, Moderat, Discret"
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Înălțime la Maturitate
                </label>
                <input
                  v-model="form.inaltime"
                  type="text"
                  placeholder="ex: 80 - 110 cm, 250 - 350 cm"
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  Perioadă de Înflorire
                </label>
                <input
                  v-model="form.inflorire"
                  type="text"
                  placeholder="ex: Mai - Octombrie (repetată)"
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <ProductImageUploader
              v-model="form.images[0]"
              label="Imagine Principală Produs"
              helper-text="Imaginile sunt stocate local pe serverul Pepiniera Ciumbrud. Formate acceptate: JPG, PNG, GIF, WEBP."
              compact
            />
            <ProductImageUploader
              v-model="form.images[1]"
              label="Imagine Secundară (Opțional – afișată la hover)"
              empty-title="Adaugă a doua fotografie (opțional)"
              empty-subtitle="Se afișează la trecerea cursorului peste cardul produsului și în galeria produsului"
              helper-text="Opțional: fotografie de detaliu (floare, fruct sau rădăcină)."
              compact
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Descriere Scurtă
            </label>
            <textarea
              v-model="form.short_description"
              rows="2"
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Descriere Detaliată
            </label>
            <textarea
              v-model="form.description"
              rows="5"
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <RouterLink
            :to="backRoute"
            class="px-5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Anulează
          </RouterLink>
          <button
            type="submit"
            :disabled="saving"
            class="px-6 py-2.5 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition-colors shadow-sm disabled:opacity-50"
          >
            <span v-if="!saving">Salvează Modificările</span>
            <span v-else>Se actualizează...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

