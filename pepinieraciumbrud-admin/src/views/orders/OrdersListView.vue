<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ordersService } from '@/services/ordersService'
import { useUiStore } from '@/stores/ui'
import type { Order } from '@/types/order'

const uiStore = useUiStore()
const orders = ref<Order[]>([])
const loading = ref(true)

const fetchOrders = async () => {
  loading.value = true
  try {
    orders.value = await ordersService.list({ per_page: 50 })
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-au putut încărca comenzile.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchOrders()
})

const getStatusBadge = (status: string) => {
  switch (status) {
    case 'completed': return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'processing': return 'bg-blue-50 text-blue-700 border-blue-200'
    case 'pending': return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'cancelled': return 'bg-rose-50 text-rose-700 border-rose-200'
    default: return 'bg-gray-50 text-gray-700 border-gray-200'
  }
}
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

      <div class="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
        <div v-if="loading" class="py-16 text-center text-xs text-gray-400">
          Se încarcă comenzile...
        </div>

        <div v-else-if="orders.length === 0" class="py-16 text-center text-xs text-gray-500">
          <div class="text-3xl mb-2">📦</div>
          Nu există comenzi plasate încă.
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
                    class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold border capitalize"
                    :class="getStatusBadge(o.status)"
                  >
                    {{ o.status }}
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

