<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { customersService } from '@/services/customersService'
import { useUiStore } from '@/stores/ui'
import { getStatusBadge, getStatusLabel } from '@/utils/order'
import type { Customer } from '@/types/customer'
import type { Order } from '@/types/order'

const route = useRoute()
const uiStore = useUiStore()
const id = Number(route.params.id)

const customer = ref<Customer | null>(null)
const orders = ref<Order[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [cust, ords] = await Promise.all([
      customersService.get(id),
      customersService.getOrders(id)
    ])
    customer.value = cust
    orders.value = ords
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-au putut încărca datele clientului.')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-serif font-bold text-gray-900">
            Profil Client #{{ id }}
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Informații de contact și istoric achiziții
          </p>
        </div>

        <RouterLink
          to="/clienti"
          class="px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50"
        >
          Înapoi la Clienți
        </RouterLink>
      </div>

      <div v-if="loading" class="py-16 text-center text-xs text-gray-400">
        Se încarcă datele clientului...
      </div>

      <div v-else-if="customer" class="space-y-6">
        <!-- Customer Info Card -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex flex-col sm:flex-row justify-between gap-4">
          <div>
            <h3 class="font-serif font-bold text-xl text-gray-900">
              {{ customer.first_name }} {{ customer.last_name || customer.username }}
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Email: {{ customer.email }}</p>
            <p class="text-xs text-gray-400 mt-1">Înregistrat pe: {{ new Date(customer.date_created).toLocaleDateString('ro-RO') }}</p>
          </div>

          <div class="text-left sm:text-right">
            <span class="text-xs text-gray-500 block">Total Comenzi:</span>
            <span class="text-xl font-bold text-primary-dark">{{ customer.orders_count || orders.length }}</span>
            <span class="text-xs text-gray-400 block mt-1">Total Cheltuit: {{ customer.total_spent || '0' }} lei</span>
          </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
          <div class="p-5 border-b border-gray-100 font-serif font-bold text-base text-gray-900">
            Istoric Comenzi
          </div>

          <div v-if="orders.length === 0" class="py-10 text-center text-xs text-gray-400">
            Clientul nu are comenzi finalizate încă.
          </div>

          <table v-else class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-border">
              <tr>
                <th class="px-6 py-3">Comandă</th>
                <th class="px-6 py-3">Dată</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-right">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="o in orders" :key="o.id" class="hover:bg-gray-50">
                <td class="px-6 py-3.5 font-bold text-gray-900">#{{ o.id }}</td>
                <td class="px-6 py-3.5 text-xs text-gray-500">{{ new Date(o.date_created).toLocaleDateString('ro-RO') }}</td>
                <td class="px-6 py-3.5">
                  <span
                    class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                    :class="getStatusBadge(o.status)"
                  >
                    {{ getStatusLabel(o.status) }}
                  </span>
                </td>
                <td class="px-6 py-3.5 text-right font-bold text-primary-dark">{{ o.total }} {{ o.currency }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

