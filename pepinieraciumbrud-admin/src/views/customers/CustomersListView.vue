<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { customersService } from '@/services/customersService'
import { useUiStore } from '@/stores/ui'
import type { Customer } from '@/types/customer'

const uiStore = useUiStore()
const customers = ref<Customer[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    customers.value = await customersService.list()
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-au putut încărca clienții.')
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-serif font-bold text-gray-900">
          Clienți Înregistrați
        </h1>
        <p class="text-xs text-gray-500 mt-1">
          Bază de date cumpărători și istoricul comenzilor lor
        </p>
      </div>

      <div class="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
        <div v-if="loading" class="py-16 text-center text-xs text-gray-400">
          Se încarcă clienții...
        </div>

        <div v-else-if="customers.length === 0" class="py-16 text-center text-xs text-gray-500">
          Nu există clienți înregistrați încă.
        </div>

        <table v-else class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-border">
            <tr>
              <th class="px-6 py-3.5">Client</th>
              <th class="px-6 py-3.5">Email</th>
              <th class="px-6 py-3.5 text-center">Comenzi</th>
              <th class="px-6 py-3.5 text-right">Total Cheltuit</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="c in customers" :key="c.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-semibold text-gray-900">
                {{ c.first_name || '' }} {{ c.last_name || c.username }}
              </td>
              <td class="px-6 py-4 text-xs text-gray-600">
                {{ c.email }}
              </td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-xs font-semibold">
                  {{ c.orders_count || 0 }}
                </span>
              </td>
              <td class="px-6 py-4 text-right font-bold text-primary-dark">
                {{ c.total_spent || '0' }} lei
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

