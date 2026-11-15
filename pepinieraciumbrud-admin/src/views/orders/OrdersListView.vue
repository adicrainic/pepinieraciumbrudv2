<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ordersService } from '@/services/ordersService'
import { useUiStore } from '@/stores/ui'
import { getStatusBadge, getStatusLabel } from '@/utils/order'
import type { Order } from '@/types/order'

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()
const orders = ref<Order[]>([])
const loading = ref(true)

const currentStatus = ref<string>((route.query.status as string) || 'all')

const statusFilters = [
  { id: 'all', label: 'Toate Comenzile' },
  { id: 'pending', label: 'În așteptare (Nouă)' },
  { id: 'processing', label: 'În procesare' },
  { id: 'prepared', label: 'Procesată (Pregătită)' },
  { id: 'shipped', label: 'Predată Curier' },
  { id: 'completed', label: 'Finalizată (Livrată)' },
  { id: 'cancelled', label: 'Anulată' }
]

const setStatus = (status: string) => {
  currentStatus.value = status
  router.push({ query: status === 'all' ? {} : { status } })
  fetchOrders()
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const params: Record<string, any> = { per_page: 50 }
    if (currentStatus.value && currentStatus.value !== 'all') {
      params.status = currentStatus.value
    }
    orders.value = await ordersService.list(params)
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-au putut încărca comenzile.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (route.query.status) {
    currentStatus.value = route.query.status as string
  }
  fetchOrders()
})

watch(
  () => route.query.status,
  (newStatus) => {
    currentStatus.value = (newStatus as string) || 'all'
    fetchOrders()
  }
)
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-serif font-bold text-gray-900">
            Comenzi Clienți
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Urmărește și actualizează comenzile plasate pe Pepiniera Ciumbrud
          </p>
        </div>

        <button
          @click="fetchOrders"
          class="px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50"
        >
          Reîmprospătează
        </button>
      </div>

      <!-- Status Tabs -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <button
          v-for="filter in statusFilters"
          :key="filter.id"
          @click="setStatus(filter.id)"
          class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors"
          :class="currentStatus === filter.id ? 'bg-primary text-white shadow-sm' : 'bg-white border border-border text-gray-600 hover:bg-gray-50'"
        >
          {{ filter.label }}
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
        <div v-if="loading" class="py-16 text-center text-xs text-gray-400">
          Se încarcă comenzile...
        </div>

        <div v-else-if="orders.length === 0" class="py-16 text-center text-xs text-gray-500">
          <div class="text-3xl mb-2">📦</div>
          {{ currentStatus !== 'all' ? 'Nu există comenzi cu acest status.' : 'Nu există comenzi plasate încă.' }}
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-border">
              <tr>
                <th class="px-6 py-3.5">Comandă</th>
                <th class="px-6 py-3.5">Client & Adresă</th>
                <th class="px-6 py-3.5">Dată</th>
                <th class="px-6 py-3.5">Status</th>
                <th class="px-6 py-3.5">Total</th>
                <th class="px-6 py-3.5 text-right">Detalii</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="o in orders" :key="o.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 font-bold text-gray-900">
                  #{{ o.id }}
                </td>
                <td class="px-6 py-4">
                  <div class="font-semibold text-gray-900">
                    {{ o.billing?.first_name }} {{ o.billing?.last_name }}
                  </div>
                  <div class="text-xs text-gray-400">
                    {{ o.billing?.city || o.shipping?.city }}, {{ o.billing?.state || 'AB' }}
                  </div>
                </td>
                <td class="px-6 py-4 text-xs text-gray-500">
                  {{ new Date(o.date_created).toLocaleDateString('ro-RO') }}
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                    :class="getStatusBadge(o.status)"
                  >
                    {{ getStatusLabel(o.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 font-bold text-primary-dark">
                  {{ o.total }} {{ o.currency }}
                </td>
                <td class="px-6 py-4 text-right">
                  <RouterLink
                    :to="`/comenzi/${o.id}`"
                    class="px-3 py-1.5 rounded-lg bg-primary-50 text-primary-dark hover:bg-primary-100 text-xs font-semibold"
                  >
                    Vezi Comanda →
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

