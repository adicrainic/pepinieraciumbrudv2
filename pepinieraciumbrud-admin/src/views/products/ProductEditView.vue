<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { productsService } from '@/services/productsService'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()

const id = Number(route.params.id)
const loading = ref(true)
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
  images: [{ src: '' }]
})

onMounted(async () => {
  try {
    const prod = await productsService.get(id)
    form.value.name = prod.name
    form.value.regular_price = prod.regular_price || prod.price || ''
    form.value.sale_price = prod.sale_price || ''
    form.value.sku = prod.sku || ''
    form.value.description = prod.description || ''
    form.value.short_description = prod.short_description || ''
    form.value.stock_status = prod.stock_status || 'instock'
    form.value.stock_quantity = prod.stock_quantity !== null && prod.stock_quantity !== undefined ? prod.stock_quantity : ''
    form.value.low_stock_amount = prod.low_stock_amount !== null && prod.low_stock_amount !== undefined ? prod.low_stock_amount : ''
    form.value.status = (prod.status as any) || 'publish'
    if (prod.images && prod.images[0]) {
      form.value.images = [{ src: prod.images[0].src }]
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
    }
  } catch (err: any) {
    uiStore.addNotification('error', 'Nu s-a putut încărca produsul.')
    router.push('/produse')
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

    const payload: any = {
      ...form.value,
      attributes,
      stock_quantity: form.value.stock_quantity === '' ? null : Number(form.value.stock_quantity),
      low_stock_amount: form.value.low_stock_amount === '' ? null : Number(form.value.low_stock_amount),
      manage_stock: form.value.stock_quantity !== ''
    }
    await productsService.update(id, payload)
    uiStore.addNotification('success', 'Produsul a fost actualizat.')
    router.push('/produse')
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
            Editare Produs #{{ id }}
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Modifică prețul, stocul sau descrierea acestui soi
          </p>
        </div>

        <RouterLink
          to="/produse"
          class="px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50"
        >
          Înapoi la Listă
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

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                Preț Redus
              </label>
              <input
                v-model="form.sale_price"
                type="text"
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
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              URL Imagine Produs
            </label>
            <input
              v-model="form.images[0].src"
              type="text"
              class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
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
            <span v-if="!saving">Salvează Modificările</span>
            <span v-else>Se actualizează...</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

