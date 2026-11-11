<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ordersService } from '@/services/ordersService'
import { useUiStore } from '@/stores/ui'
import type { Order } from '@/types/order'

const route = useRoute()
const uiStore = useUiStore()
const id = Number(route.params.id)

const order = ref<Order | null>(null)
const notes = ref<any[]>([])
const newNote = ref('')
const loading = ref(true)
const updating = ref(false)

const fetchOrder = async () => {
  loading.value = true
  try {
    const [ord, nts] = await Promise.all([
      ordersService.get(id),
      ordersService.getNotes(id)
    ])
    order.value = ord
    notes.value = nts
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-a putut încărca comanda.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchOrder()
})

const handleStatusChange = async (event: Event) => {
  const select = event.target as HTMLSelectElement
  updating.value = true
  try {
    order.value = await ordersService.updateStatus(id, select.value)
    uiStore.addNotification('success', 'Statusul comenzii a fost modificat.')
  } catch (err) {
    uiStore.addNotification('error', 'Eroare la modificarea statusului.')
  } finally {
    updating.value = false
  }
}

const handleAddNote = async () => {
  if (!newNote.value.trim()) return
  try {
    await ordersService.addNote(id, newNote.value.trim())
    uiStore.addNotification('success', 'Nota a fost adăugată.')
    newNote.value = ''
    notes.value = await ordersService.getNotes(id)
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-a putut adăuga nota.')
  }
}

const handlePrintPackingSlip = () => {
  window.print()
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <div class="flex items-center justify-between no-print">
        <div>
          <h1 class="text-2xl font-serif font-bold text-gray-900">
            Comandă #{{ id }}
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Plasată pe {{ order ? new Date(order.date_created).toLocaleString('ro-RO') : '...' }}
          </p>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="handlePrintPackingSlip"
            type="button"
            class="px-3.5 py-2 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition-colors flex items-center gap-1.5 shadow-sm"
          >
            <span>🖨️</span>
            <span>Printează Fișă Livrare / Aviz</span>
          </button>
          <RouterLink
            to="/comenzi"
            class="px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50"
          >
            Înapoi la Comenzi
          </RouterLink>
        </div>
      </div>

      <div v-if="loading" class="py-16 text-center text-xs text-gray-400 no-print">
        Se încarcă detaliile comenzii...
      </div>

      <div v-else-if="order" class="grid grid-cols-1 lg:grid-cols-3 gap-6 no-print">
        <!-- Order Items & Status -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white p-6 rounded-2xl border border-border shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
              <span class="font-serif font-bold text-base text-gray-900">Produse comandate</span>
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">Status:</span>
                <select
                  :value="order.status"
                  @change="handleStatusChange"
                  :disabled="updating"
                  class="bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-primary"
                >
                  <option value="pending">În așteptare (Pending)</option>
                  <option value="processing">În procesare</option>
                  <option value="on-hold">În așteptare plată</option>
                  <option value="completed">Finalizată (Livrată)</option>
                  <option value="cancelled">Anulată</option>
                </select>
              </div>
            </div>

            <!-- Items list -->
            <div class="divide-y divide-gray-100">
              <div
                v-for="item in order.line_items"
                :key="item.id"
                class="py-3 flex items-center justify-between text-sm"
              >
                <div>
                  <span class="font-semibold text-gray-900 block">{{ item.name }}</span>
                  <span class="text-xs text-gray-400">{{ item.price }} lei x {{ item.quantity }} buc.</span>
                </div>
                <span class="font-bold text-primary-dark">{{ item.total }} lei</span>
              </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-between text-base font-bold text-gray-900">
              <span>Total Comandă:</span>
              <span class="text-primary-dark">{{ order.total }} {{ order.currency }}</span>
            </div>
          </div>

          <!-- Notes -->
          <div class="bg-white p-6 rounded-2xl border border-border shadow-sm space-y-4">
            <h3 class="font-serif font-bold text-base text-gray-900">Note Comandă</h3>

            <div v-if="notes.length === 0" class="text-xs text-gray-400 italic">
              Nu există note adăugate încă.
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="n in notes"
                :key="n.id"
                class="p-3 bg-gray-50 rounded-xl text-xs space-y-1"
              >
                <div class="text-gray-800">{{ n.note }}</div>
                <div class="text-[10px] text-gray-400">
                  {{ new Date(n.date_created).toLocaleString('ro-RO') }}
                </div>
              </div>
            </div>

            <form @submit.prevent="handleAddNote" class="pt-3 border-t border-gray-100 flex gap-2">
              <input
                v-model="newNote"
                type="text"
                placeholder="Adaugă o notă internă..."
                class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-primary"
              />
              <button
                type="submit"
                class="px-4 py-2 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark"
              >
                Adaugă
              </button>
            </form>
          </div>
        </div>

        <!-- Customer & Delivery sidebar -->
        <div class="space-y-6">
          <div class="bg-white p-6 rounded-2xl border border-border shadow-sm space-y-4">
            <h3 class="font-serif font-bold text-base text-gray-900 pb-3 border-b border-gray-100">
              Adresă de Livrare
            </h3>

            <div class="text-xs space-y-2 text-gray-700 leading-relaxed">
              <div class="font-bold text-sm text-gray-900">
                {{ order.shipping?.first_name || order.billing?.first_name }}
                {{ order.shipping?.last_name || order.billing?.last_name }}
              </div>
              <div>
                📞 {{ order.billing?.phone || 'Nespecificat' }}
              </div>
              <div>
                ✉️ {{ order.billing?.email || 'Nespecificat' }}
              </div>
              <div class="pt-2 border-t border-gray-100">
                {{ order.shipping?.address_1 || order.billing?.address_1 }}<br />
                {{ order.shipping?.city || order.billing?.city }}, {{ order.shipping?.state || order.billing?.state }}
              </div>
              <div v-if="order.customer_note" class="pt-2 text-amber-700 font-medium">
                Observație: "{{ order.customer_note }}"
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl border border-border shadow-sm space-y-2 text-xs">
            <h4 class="font-bold text-gray-900 mb-1">Metodă de Plată:</h4>
            <div class="text-gray-600">{{ order.payment_method_title || 'Plată Ramburs la Curier' }}</div>
          </div>
        </div>
      </div>

      <!-- Printable Courier Packing Slip (Visible only when printing) -->
      <div v-if="order" class="print-only hidden font-sans text-gray-900 p-8 bg-white">
        <!-- Header -->
        <div class="flex justify-between items-start pb-6 border-b-2 border-gray-900 mb-6">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="text-2xl">🌿</span>
              <h2 class="text-xl font-bold tracking-tight uppercase">Pepiniera Ciumbrud S.R.L.</h2>
            </div>
            <p class="text-xs text-gray-600">Producător Autorizat Material Săditor Pomicol & Viticol</p>
            <p class="text-xs text-gray-600">Sat Ciumbrud, Jud. Alba | Tel: +40 700 000 000 | contact@pepinieraciumbrud.ro</p>
          </div>
          <div class="text-right">
            <div class="text-lg font-bold">AVIZ DE EXPEDIERE</div>
            <div class="text-xs font-semibold text-gray-700">Comandă #{{ order.id }}</div>
            <div class="text-xs text-gray-500">Data: {{ new Date(order.date_created).toLocaleDateString('ro-RO') }}</div>
          </div>
        </div>

        <!-- Sender and Recipient -->
        <div class="grid grid-cols-2 gap-8 mb-6 pb-6 border-b border-gray-200 text-xs">
          <div class="p-4 bg-gray-50 rounded-lg">
            <span class="font-bold uppercase tracking-wider text-[10px] text-gray-500 block mb-1">Expeditor:</span>
            <div class="font-bold text-sm">PEPINIERA CIUMBRUD</div>
            <div>Sat Ciumbrud, Comuna Aiud</div>
            <div>Județul Alba, România</div>
            <div>Tel: +40 700 000 000</div>
          </div>

          <div class="p-4 bg-gray-50 rounded-lg border border-gray-300">
            <span class="font-bold uppercase tracking-wider text-[10px] text-gray-500 block mb-1">Destinatar (Client):</span>
            <div class="font-bold text-sm">
              {{ order.shipping?.first_name || order.billing?.first_name }}
              {{ order.shipping?.last_name || order.billing?.last_name }}
            </div>
            <div>{{ order.shipping?.address_1 || order.billing?.address_1 }}</div>
            <div>{{ order.shipping?.city || order.billing?.city }}, {{ order.shipping?.state || order.billing?.state }}</div>
            <div>Telefon: <strong>{{ order.billing?.phone || 'Nespecificat' }}</strong></div>
            <div v-if="order.billing?.email">Email: {{ order.billing.email }}</div>
          </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-xs text-left mb-6 border-collapse">
          <thead>
            <tr class="border-b-2 border-gray-800 bg-gray-100">
              <th class="py-2.5 px-3 font-bold w-12 text-center">Nr.</th>
              <th class="py-2.5 px-3 font-bold">Denumire Soi & Produs</th>
              <th class="py-2.5 px-3 font-bold w-20 text-center">Cant.</th>
              <th class="py-2.5 px-3 font-bold w-24 text-right">Preț Unitar</th>
              <th class="py-2.5 px-3 font-bold w-24 text-right">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="(item, index) in order.line_items" :key="item.id">
              <td class="py-2.5 px-3 text-center">{{ index + 1 }}</td>
              <td class="py-2.5 px-3 font-medium">{{ item.name }}</td>
              <td class="py-2.5 px-3 text-center font-bold">{{ item.quantity }} buc.</td>
              <td class="py-2.5 px-3 text-right">{{ item.price }} lei</td>
              <td class="py-2.5 px-3 text-right font-bold">{{ item.total }} lei</td>
            </tr>
          </tbody>
        </table>

        <!-- Totals & Payment -->
        <div class="flex justify-between items-start pt-4 border-t-2 border-gray-900 mb-8">
          <div class="text-xs max-w-sm">
            <div class="font-bold mb-1">Instrucțiuni de Livrare & Mențiuni:</div>
            <div class="p-2.5 bg-gray-50 border border-gray-200 rounded text-gray-700 italic">
              {{ order.customer_note || 'Colete cu plante vii. A se manevra cu grijă și a nu se stivui.' }}
            </div>
          </div>

          <div class="text-right space-y-1.5 min-w-[220px]">
            <div class="flex justify-between text-xs text-gray-600">
              <span>Metodă Plată:</span>
              <span class="font-semibold text-gray-900">{{ order.payment_method_title || 'Ramburs' }}</span>
            </div>
            <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-300">
              <span>TOTAL DE ÎNCASAT:</span>
              <span class="text-lg font-black text-gray-900">{{ order.total }} {{ order.currency }}</span>
            </div>
          </div>
        </div>

        <!-- Signature -->
        <div class="grid grid-cols-2 gap-12 pt-8 border-t border-gray-200 text-xs text-center text-gray-500">
          <div>
            <div class="mb-8">Predat curier (Expeditor):</div>
            <div class="border-b border-gray-400 w-48 mx-auto"></div>
          </div>
          <div>
            <div class="mb-8">Primit colet (Destinatar):</div>
            <div class="border-b border-gray-400 w-48 mx-auto"></div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style>
@media print {
  /* Hide all dashboard layout elements */
  nav, header, aside, .no-print, button, .sidebar, #teleports {
    display: none !important;
  }
  body, html, main, #app {
    background: white !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 12pt;
  }
  .print-only {
    display: block !important;
    visibility: visible !important;
    position: static !important;
    width: 100% !important;
    padding: 10mm !important;
  }
}
</style>

