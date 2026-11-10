<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { dashboardService } from '@/services/dashboardService'
import { ordersService } from '@/services/ordersService'
import { productsService } from '@/services/productsService'
import type { Order } from '@/types/order'
import type { Product } from '@/types/product'

const stats = ref<any>({})
const recentOrders = ref<Order[]>([])
const products = ref<Product[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [dashData, ordersData, prodData] = await Promise.allSettled([
      dashboardService.getDashboard(),
      ordersService.list({ per_page: 5 }),
      productsService.list({ per_page: 6 })
    ])

    if (dashData.status === 'fulfilled') stats.value = dashData.value
    if (ordersData.status === 'fulfilled') recentOrders.value = ordersData.value
    if (prodData.status === 'fulfilled') products.value = prodData.value
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Title -->
      <div>
        <h1 class="text-2xl font-serif font-bold text-gray-900">
          Panou Principal
        </h1>
        <p class="text-xs text-gray-500 mt-1">
          Prezentare generală comenzi, stocuri și activitate Pepiniera Ciumbrud
        </p>
      </div>

      <!-- Stat Widgets -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Produse Active
            </span>
            <span class="text-2xl font-bold text-gray-900">
              {{ products.length }}
            </span>
            <span class="text-[11px] text-emerald-600 font-medium block mt-1">
              În catalogul WooCommerce
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-2xl">
            🍏
          </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Comenzi Totale
            </span>
            <span class="text-2xl font-bold text-gray-900">
              {{ recentOrders.length }}
            </span>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              Înregistrate în magazin
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-2xl">
            📦
          </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Monedă Magazin
            </span>
            <span class="text-2xl font-bold text-gray-900">
              RON (Lei)
            </span>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              Regiunea România (AB)
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-2xl">
            💰
          </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Stare Servicii
            </span>
            <span class="text-2xl font-bold text-emerald-700">
              Activ
            </span>
            <span class="text-[11px] text-emerald-600 font-medium block mt-1">
              Store API & REST Proxy Conectate
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-2xl">
            ⚡
          </div>
        </div>
      </div>

      <!-- Quick Action Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Products Quick Table -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
            <h3 class="font-serif font-bold text-base text-gray-900">
              Produse Recente
            </h3>
            <RouterLink to="/produse" class="text-xs font-semibold text-primary hover:underline">
              Vezi toate →
            </RouterLink>
          </div>

          <div v-if="loading" class="py-8 text-center text-xs text-gray-400">
            Se încarcă produsele...
          </div>

          <div v-else-if="products.length === 0" class="py-8 text-center text-xs text-gray-500">
            Nu există produse încă.
          </div>

          <div v-else class="divide-y divide-gray-100">
            <div
              v-for="p in products"
              :key="p.id"
              class="py-3 flex items-center justify-between text-sm"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                  <img
                    v-if="p.images && p.images[0]"
                    :src="p.images[0].src"
                    :alt="p.name"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-xs">🌱</div>
                </div>
                <div>
                  <span class="font-semibold text-gray-800 block truncate max-w-xs">{{ p.name }}</span>
                  <span class="text-xs text-gray-400">SKU: {{ p.sku || 'N/A' }}</span>
                </div>
              </div>

              <div class="text-right">
                <span class="font-bold text-primary-dark block">{{ p.price || p.regular_price || '0' }} lei</span>
                <span class="text-[10px] uppercase font-semibold" :class="p.stock_status === 'instock' ? 'text-emerald-600' : 'text-rose-600'">
                  {{ p.stock_status === 'instock' ? 'În stoc' : 'Stoc epuizat' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Orders Quick Table -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
            <h3 class="font-serif font-bold text-base text-gray-900">
              Comenzi Recente
            </h3>
            <RouterLink to="/comenzi" class="text-xs font-semibold text-primary hover:underline">
              Vezi toate →
            </RouterLink>
          </div>

          <div v-if="loading" class="py-8 text-center text-xs text-gray-400">
            Se încarcă comenzile...
          </div>

          <div v-else-if="recentOrders.length === 0" class="py-12 text-center text-xs text-gray-500">
            <div class="text-3xl mb-2">📦</div>
            Nu există comenzi plasate încă.<br />
            Comenzile noi primite prin Storefront vor apărea automat aici.
          </div>

          <div v-else class="divide-y divide-gray-100">
            <div
              v-for="o in recentOrders"
              :key="o.id"
              class="py-3 flex items-center justify-between text-sm"
            >
              <div>
                <span class="font-semibold text-gray-800">#{{ o.id }} – {{ o.billing?.first_name }} {{ o.billing?.last_name }}</span>
                <span class="text-xs text-gray-400 block">{{ new Date(o.date_created).toLocaleDateString('ro-RO') }}</span>
              </div>
              <div class="text-right">
                <span class="font-bold text-gray-900 block">{{ o.total }} {{ o.currency }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-medium">
                  {{ o.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

