<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { productsService } from '@/services/productsService'
import { categoriesService } from '@/services/categoriesService'
import { useUiStore } from '@/stores/ui'
import { useRouter } from 'vue-router'
import type { Category } from '@/types/category'

const router = useRouter()
const uiStore = useUiStore()

const categories = ref<Category[]>([])
const saving = ref(false)

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
  categories: [] as { id: number }[],
  images: [{ src: '' }]
})

onMounted(async () => {
  try {
    categories.value = await categoriesService.list()
  } catch (err) {
    // ignore
  }
})

const handleSave = async () => {
  if (!form.value.name || !form.value.regular_price) {
    uiStore.addNotification('error', 'Numele și prețul sunt obligatorii.')
    return
  }

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

    const payload: any = {
      name: form.value.name,
      regular_price: form.value.regular_price,
      sale_price: form.value.sale_price || undefined,
      sku: form.value.sku,
      description: form.value.description,
      short_description: form.value.short_description,
      stock_status: form.value.stock_status,
      stock_quantity: form.value.stock_quantity === '' ? null : Number(form.value.stock_quantity),
      low_stock_amount: form.value.low_stock_amount === '' ? null : Number(form.value.low_stock_amount),
      manage_stock: form.value.stock_quantity !== '',
      status: form.value.status,
      categories: form.value.categories,
      attributes
    }

    if (form.value.images[0].src) {
      payload.images = [{ src: form.value.images[0].src }]
    }

    await productsService.create(payload)
    uiStore.addNotification('success', 'Produsul a fost creat cu succes.')
    router.push('/produse')
  } catch (err: any) {
    uiStore.addNotification('error', 'Eroare la crearea produsului.')
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
            Adaugă Produs Nou
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Completează datele pomului sau plantei pentru introducere în magazin
          </p>
        </div>

        <RouterLink
          to="/produse"
          class="px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50"
        >
          Anulează
        </RouterLink>
      </div>

      <form @submit.prevent="handleSave" class="space-y-6">
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-border shadow-sm space-y-5">
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Denumire Produs / Soi *
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Ex: MĂR - FLORINA, PĂR - WILLIAMS, etc."
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Preț Standard (RON) *
              </label>
              <input
                v-model="form.regular_price"
                type="text"
                required
                placeholder="15.00"
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
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Cod SKU
              </label>
              <input
                v-model="form.sku"
                type="text"
                placeholder="Ex: POM-MAR-01"
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
                <option value="publish">Publicat (Vizibil)</option>
                <option value="draft">Ciornă (Draft)</option>
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
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              URL Imagine Produs
            </label>
            <input
              v-model="form.images[0].src"
              type="url"
              placeholder="http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-content/uploads/..."
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Descriere Scurtă (Anotimp / Maturare)
            </label>
            <textarea
              v-model="form.short_description"
              rows="2"
              placeholder="Ex: Soi de toamnă, rezistent la ger, fructe mari și zemoase."
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Descriere Detaliată & Ghid Plantare
            </label>
            <textarea
              v-model="form.description"
              rows="5"
              placeholder="Detalii complete despre vigoarea pomului, polenizare, recoltare..."
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <RouterLink
            to="/produse"
            class="px-5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50"
          >
            Anulează
          </RouterLink>
          <button
            type="submit"
            :disabled="saving"
            class="px-6 py-2.5 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition-colors shadow-sm disabled:opacity-50"
          >
            <span v-if="!saving">Salvează Produsul</span>
            <span v-else>Se salvează...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

