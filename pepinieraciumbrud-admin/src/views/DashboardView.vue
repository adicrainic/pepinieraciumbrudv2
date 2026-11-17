<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { dashboardService } from '@/services/dashboardService'
import { ordersService } from '@/services/ordersService'
import { productsService } from '@/services/productsService'
import { messagesService } from '@/services/messagesService'
import { getStatusBadge, getStatusLabel } from '@/utils/order'
import type { Order } from '@/types/order'
import type { Product, InventoryItem } from '@/types/product'

const stats = ref<any>({})
const recentOrders = ref<Order[]>([])
const products = ref<Product[]>([])
const activeProductsCount = ref(0)
const criticalStockCount = ref(0)
const unreadMessagesCount = ref(0)
const pendingOrdersCount = ref(0)
const loading = ref(true)

onMounted(async () => {
  try {
    const [dashData, ordersData, prodData, messagesData, invData] = await Promise.allSettled([
      dashboardService.getDashboard(),
      ordersService.list({ per_page: 5 }),
      productsService.list({ per_page: 6 }),
      messagesService.list({ per_page: 1 }),
      dashboardService.getInventory()
    ])

    let pendingCount: number | null = null
    if (dashData.status === 'fulfilled' && dashData.value) {
      stats.value = dashData.value
      if (typeof dashData.value.products_count === 'number') {
        activeProductsCount.value = dashData.value.products_count
      } else if (Array.isArray(dashData.value.products_total)) {
        activeProductsCount.value = dashData.value.products_total.reduce(
          (sum: number, item: any) => sum + (Number(item.total) || 0),
          0
        )
      }

      const pendingObj = Array.isArray(dashData.value.orders_total)
        ? dashData.value.orders_total.find((s: any) => s.slug === 'pending')
        : null
      if (pendingObj && pendingObj.total !== undefined) {
        pendingCount = Number(pendingObj.total) || 0
      }
    }

    if (pendingCount !== null) {
      pendingOrdersCount.value = pendingCount
    } else {
      try {
        const pendingList = await ordersService.list({ status: 'pending', per_page: 100 })
        pendingOrdersCount.value = pendingList.length
      } catch {
        pendingOrdersCount.value = 0
      }
    }

    if (ordersData.status === 'fulfilled') recentOrders.value = ordersData.value
    if (prodData.status === 'fulfilled') products.value = prodData.value
    if (messagesData.status === 'fulfilled' && messagesData.value) {
      unreadMessagesCount.value = messagesData.value.unread_count ?? 0
    }
    if (invData.status === 'fulfilled' && Array.isArray(invData.value)) {
      let lowCount = 0
      for (const item of (invData.value as InventoryItem[])) {
        const qty = item.stock_quantity
        const status = item.stock_status
        const threshold = item.low_stock_amount !== null && item.low_stock_amount !== undefined
          ? item.low_stock_amount
          : item.effective_threshold

        const isOutOfStock = status === 'outofstock' || (qty !== null && qty <= 0)
        if (!isOutOfStock && qty !== null && qty <= threshold) {
          lowCount++
        }
      }
      criticalStockCount.value = lowCount
    }
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
        <RouterLink
          to="/produse"
          class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between hover:border-emerald-400 hover:shadow-md transition-all group block"
        >
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Produse Active
            </span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold text-gray-900 group-hover:text-emerald-700 transition-colors">
                {{ activeProductsCount }}
              </span>
            </div>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              {{ activeProductsCount === 1 ? '1 produs în catalog' : `${activeProductsCount} produse în catalog` }}
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 group-hover:bg-emerald-100 flex items-center justify-center text-2xl transition-colors">
            🍏
          </div>
        </RouterLink>

        <RouterLink
          to="/comenzi?status=pending"
          class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between hover:border-blue-400 hover:shadow-md transition-all group block"
        >
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Comenzi Noi
            </span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold transition-colors" :class="pendingOrdersCount > 0 ? 'text-blue-700' : 'text-gray-900 group-hover:text-blue-700'">
                {{ pendingOrdersCount }}
              </span>
              <span
                v-if="pendingOrdersCount > 0"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800"
              >
                {{ pendingOrdersCount === 1 ? '1 nouă' : `${pendingOrdersCount} noi` }}
              </span>
            </div>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              {{ pendingOrdersCount === 1 ? '1 comandă nouă de preluat' : (pendingOrdersCount === 0 ? 'Nicio comandă nouă' : `${pendingOrdersCount} comenzi noi de preluat`) }}
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 group-hover:bg-blue-100 flex items-center justify-center text-2xl transition-colors">
            📦
          </div>
        </RouterLink>

        <RouterLink
          to="/inventar?status=low_stock"
          class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between hover:border-amber-400 hover:shadow-md transition-all group block"
        >
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Stoc Critic
            </span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold transition-colors" :class="criticalStockCount > 0 ? 'text-amber-600' : 'text-gray-900 group-hover:text-amber-600'">
                {{ criticalStockCount }}
              </span>
              <span
                v-if="criticalStockCount > 0"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800"
              >
                {{ criticalStockCount === 1 ? '1 articol' : `${criticalStockCount} articole` }}
              </span>
            </div>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              {{ criticalStockCount > 0 ? 'Atinge pragul de alertă' : 'Toate stocurile sunt optime' }}
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 group-hover:bg-amber-100 flex items-center justify-center text-2xl transition-colors">
            ⚠️
          </div>
        </RouterLink>

        <RouterLink
          to="/mesaje"
          class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between hover:border-primary/40 hover:shadow-md transition-all group block"
        >
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Mesaje Noi
            </span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold text-gray-900 group-hover:text-primary transition-colors">
                {{ unreadMessagesCount }}
              </span>
              <span
                v-if="unreadMessagesCount > 0"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700"
              >
                {{ unreadMessagesCount === 1 ? '1 nou' : `${unreadMessagesCount} noi` }}
              </span>
            </div>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              {{ unreadMessagesCount === 1 ? '1 mesaj de contact necitit' : (unreadMessagesCount === 0 ? 'Toate mesajele sunt citite' : `${unreadMessagesCount} mesaje de contact necitite`) }}
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 group-hover:bg-purple-100 flex items-center justify-center text-2xl transition-colors">
            💬
          </div>
        </RouterLink>
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
                  <span v-if="p.categories && p.categories.length" class="text-xs text-emerald-700 block font-medium truncate max-w-xs">
                    {{ p.categories.map(c => c.name).join(', ') }}
                  </span>
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
                <span class="font-bold text-gray-900 block mb-1">{{ o.total }} {{ o.currency }}</span>
                <span
                  class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                  :class="getStatusBadge(o.status)"
                >
                  {{ getStatusLabel(o.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

