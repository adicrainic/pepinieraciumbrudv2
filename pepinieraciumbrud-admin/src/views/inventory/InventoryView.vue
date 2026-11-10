<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { dashboardService } from '@/services/dashboardService'
import { useUiStore } from '@/stores/ui'
import type { InventoryItem } from '@/types/product'

interface RowEditState {
  stock_quantity: number | null
  low_stock_amount: number | null
  stock_status: 'instock' | 'outofstock' | 'onbackorder'
  manage_stock: boolean
}

const uiStore = useUiStore()
const inventory = ref<InventoryItem[]>([])
const loading = ref(true)

// Filters
const searchQuery = ref('')
const statusFilter = ref<'all' | 'low_stock' | 'outofstock' | 'instock'>('all')

// Edit states
const editedRows = ref<Record<number, RowEditState>>({})
const savingRows = ref<Record<number, boolean>>({})
const savingAll = ref(false)

const fetchInventory = async () => {
  loading.value = true
  try {
    inventory.value = await dashboardService.getInventory()
  } catch (err: any) {
    uiStore.addNotification('error', 'Nu s-a putut încărca stocul.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchInventory()
})

// Helper functions for reading current value (edited or original)
const getRowQuantity = (item: InventoryItem): number | string => {
  const edited = editedRows.value[item.id]
  if (edited && edited.stock_quantity !== undefined) {
    return edited.stock_quantity !== null ? edited.stock_quantity : ''
  }
  return item.stock_quantity !== null ? item.stock_quantity : ''
}

const getRowThreshold = (item: InventoryItem): number | string => {
  const edited = editedRows.value[item.id]
  if (edited && edited.low_stock_amount !== undefined) {
    return edited.low_stock_amount !== null ? edited.low_stock_amount : ''
  }
  return item.low_stock_amount !== null ? item.low_stock_amount : ''
}

const getRowStatus = (item: InventoryItem): string => {
  const edited = editedRows.value[item.id]
  if (edited && edited.stock_status !== undefined) {
    return edited.stock_status
  }
  return item.stock_status || 'instock'
}

const isRowDirty = (id: number): boolean => {
  return Boolean(editedRows.value[id])
}

const dirtyCount = computed(() => Object.keys(editedRows.value).length)

// Ensure row is in editedRows
const ensureRowEditState = (item: InventoryItem): RowEditState => {
  if (!editedRows.value[item.id]) {
    editedRows.value[item.id] = {
      stock_quantity: item.stock_quantity,
      low_stock_amount: item.low_stock_amount,
      stock_status: item.stock_status,
      manage_stock: item.manage_stock ?? true,
    }
  }
  return editedRows.value[item.id]
}

const checkIfRowRevertedToOriginal = (item: InventoryItem) => {
  const state = editedRows.value[item.id]
  if (!state) return

  const originalQty = item.stock_quantity !== null ? item.stock_quantity : null
  const currentQty = state.stock_quantity !== null ? state.stock_quantity : null

  const originalLow = item.low_stock_amount !== null ? item.low_stock_amount : null
  const currentLow = state.low_stock_amount !== null ? state.low_stock_amount : null

  const isSame =
    originalQty === currentQty &&
    originalLow === currentLow &&
    item.stock_status === state.stock_status &&
    (item.manage_stock ?? true) === state.manage_stock

  if (isSame) {
    delete editedRows.value[item.id]
  }
}

const updateRowQuantity = (item: InventoryItem, val: string) => {
  const state = ensureRowEditState(item)
  const trimmed = val.trim()

  if (trimmed === '') {
    state.stock_quantity = null
  } else {
    const num = Math.max(0, parseInt(trimmed, 10) || 0)
    state.stock_quantity = num
    state.manage_stock = true

    // Auto-update stock status
    if (num === 0 && state.stock_status === 'instock') {
      state.stock_status = 'outofstock'
    } else if (num > 0 && state.stock_status === 'outofstock') {
      state.stock_status = 'instock'
    }
  }

  checkIfRowRevertedToOriginal(item)
}

const updateRowThreshold = (item: InventoryItem, val: string) => {
  const state = ensureRowEditState(item)
  const trimmed = val.trim()

  if (trimmed === '') {
    state.low_stock_amount = null
  } else {
    state.low_stock_amount = Math.max(0, parseInt(trimmed, 10) || 0)
  }

  checkIfRowRevertedToOriginal(item)
}

const updateRowStatus = (item: InventoryItem, status: any) => {
  const state = ensureRowEditState(item)
  state.stock_status = status

  checkIfRowRevertedToOriginal(item)
}

// Compute real-time status alert for visual feedback
const getItemStatusAlert = (item: InventoryItem) => {
  const state = editedRows.value[item.id]
  const qty = state && state.stock_quantity !== undefined ? state.stock_quantity : item.stock_quantity
  const status = state && state.stock_status !== undefined ? state.stock_status : item.stock_status
  const customThreshold = state && state.low_stock_amount !== undefined ? state.low_stock_amount : item.low_stock_amount
  const effectiveThreshold = customThreshold !== null ? customThreshold : item.effective_threshold

  if (status === 'outofstock' || (qty !== null && qty <= 0)) {
    return {
      type: 'critical',
      label: 'Stoc Epuizat',
      badgeClass: 'bg-rose-50 text-rose-700 border-rose-200',
    }
  }

  if (qty !== null && qty <= effectiveThreshold) {
    return {
      type: 'warning',
      label: `Stoc Critic (≤ ${effectiveThreshold} buc)`,
      badgeClass: 'bg-amber-50 text-amber-700 border-amber-200 font-bold',
    }
  }

  return {
    type: 'ok',
    label: 'Stoc Optim',
    badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  }
}

// Stats
const stats = computed(() => {
  const total = inventory.value.length
  let lowStockCount = 0
  let outOfStockCount = 0
  let inStockCount = 0

  for (const item of inventory.value) {
    const alert = getItemStatusAlert(item)
    if (alert.type === 'critical') {
      outOfStockCount++
    } else if (alert.type === 'warning') {
      lowStockCount++
    } else {
      inStockCount++
    }
  }

  return {
    total,
    inStock: inStockCount,
    lowStock: lowStockCount,
    outOfStock: outOfStockCount,
  }
})

// Filtered list
const filteredItems = computed(() => {
  let list = inventory.value

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(item =>
      item.name.toLowerCase().includes(q) ||
      (item.sku && item.sku.toLowerCase().includes(q))
    )
  }

  if (statusFilter.value === 'low_stock') {
    list = list.filter(item => {
      const alert = getItemStatusAlert(item)
      return alert.type === 'warning'
    })
  } else if (statusFilter.value === 'outofstock') {
    list = list.filter(item => {
      const alert = getItemStatusAlert(item)
      return alert.type === 'critical'
    })
  } else if (statusFilter.value === 'instock') {
    list = list.filter(item => {
      const alert = getItemStatusAlert(item)
      return alert.type === 'ok'
    })
  }

  return list
})

// Save individual row
const saveRow = async (item: InventoryItem) => {
  const state = editedRows.value[item.id]
  if (!state) return

  savingRows.value[item.id] = true
  try {
    const updated = await dashboardService.updateStock(item.id, {
      stock_quantity: state.stock_quantity,
      low_stock_amount: state.low_stock_amount,
      stock_status: state.stock_status,
      manage_stock: state.manage_stock,
    })

    const idx = inventory.value.findIndex(p => p.id === item.id)
    if (idx !== -1) {
      inventory.value[idx] = { ...inventory.value[idx], ...updated }
    }

    delete editedRows.value[item.id]
    uiStore.addNotification('success', `Stoc actualizat: "${item.name}".`)
  } catch (err: any) {
    const msg = err.response?.data?.message || err.message || 'Nu s-a putut salva stocul.'
    uiStore.addNotification('error', msg)
  } finally {
    delete savingRows.value[item.id]
  }
}

// Revert individual row
const revertRow = (item: InventoryItem) => {
  delete editedRows.value[item.id]
}

// Save all modified
const saveAllDirty = async () => {
  const ids = Object.keys(editedRows.value).map(Number)
  if (ids.length === 0) return

  savingAll.value = true
  let successCount = 0

  for (const id of ids) {
    const item = inventory.value.find(p => p.id === id)
    if (item && editedRows.value[id]) {
      try {
        await saveRow(item)
        successCount++
      } catch {
        // notification already dispatched
      }
    }
  }

  savingAll.value = false
  if (successCount > 0) {
    uiStore.addNotification('success', `Au fost actualizate ${successCount} produse cu succes.`)
  }
}

// Revert all
const revertAllDirty = () => {
  editedRows.value = {}
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-serif font-bold text-gray-900">
            Monitorizare & Gestiune Inventar
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Modifică direct cantitățile în stoc, pragurile de avertizare și starea produselor
          </p>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="fetchInventory"
            :disabled="loading"
            class="px-3.5 py-2 bg-white border border-border hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-1.5"
            title="Reîncarcă datele din magazin"
          >
            <span :class="{ 'animate-spin': loading }">🔄</span>
            <span>Reîmprospătează</span>
          </button>
        </div>
      </div>

      <!-- Quick Summary Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          @click="statusFilter = 'all'"
          class="bg-white p-4 rounded-2xl border transition-all cursor-pointer hover:shadow-md"
          :class="statusFilter === 'all' ? 'border-primary ring-2 ring-primary/10' : 'border-border'"
        >
          <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
            Total Produse
          </div>
          <div class="text-2xl font-bold text-gray-900 mt-1">
            {{ stats.total }}
          </div>
          <div class="text-[11px] text-gray-400 mt-0.5">
            Articole în catalog
          </div>
        </div>

        <div
          @click="statusFilter = 'instock'"
          class="bg-white p-4 rounded-2xl border transition-all cursor-pointer hover:shadow-md"
          :class="statusFilter === 'instock' ? 'border-emerald-500 ring-2 ring-emerald-500/10' : 'border-border'"
        >
          <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider flex items-center justify-between">
            <span>Stoc Optim</span>
            <span>✓</span>
          </div>
          <div class="text-2xl font-bold text-emerald-700 mt-1">
            {{ stats.inStock }}
          </div>
          <div class="text-[11px] text-emerald-600/70 mt-0.5">
            Peste pragul critic
          </div>
        </div>

        <div
          @click="statusFilter = 'low_stock'"
          class="bg-white p-4 rounded-2xl border transition-all cursor-pointer hover:shadow-md"
          :class="statusFilter === 'low_stock' ? 'border-amber-500 ring-2 ring-amber-500/10' : 'border-border'"
        >
          <div class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider flex items-center justify-between">
            <span>Stoc Critic</span>
            <span>⚠️</span>
          </div>
          <div class="text-2xl font-bold text-amber-600 mt-1">
            {{ stats.lowStock }}
          </div>
          <div class="text-[11px] text-amber-600/70 mt-0.5">
            Atinge pragul de alertă
          </div>
        </div>

        <div
          @click="statusFilter = 'outofstock'"
          class="bg-white p-4 rounded-2xl border transition-all cursor-pointer hover:shadow-md"
          :class="statusFilter === 'outofstock' ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-border'"
        >
          <div class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider flex items-center justify-between">
            <span>Stoc Epuizat</span>
            <span>⛔</span>
          </div>
          <div class="text-2xl font-bold text-rose-600 mt-1">
            {{ stats.outOfStock }}
          </div>
          <div class="text-[11px] text-rose-600/70 mt-0.5">
            Indisponibile la vânzare
          </div>
        </div>
      </div>

      <!-- Unsaved Changes Floating Banner -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
      >
        <div
          v-if="dirtyCount > 0"
          class="bg-amber-500 text-white p-3.5 rounded-2xl shadow-lg border border-amber-600 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
        >
          <div class="flex items-center gap-2.5">
            <span class="text-lg">⚠️</span>
            <div>
              <p class="text-xs sm:text-sm font-bold">
                Ai {{ dirtyCount }} {{ dirtyCount === 1 ? 'produs modificat' : 'produse modificate' }} nesalvate
              </p>
              <p class="text-[11px] text-white/80">
                Poți salva individual fiecare rând sau poți aplica toate modificările dintr-un clic.
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 self-end sm:self-center">
            <button
              @click="revertAllDirty"
              :disabled="savingAll"
              class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold transition-colors"
            >
              Anulează toate
            </button>
            <button
              @click="saveAllDirty"
              :disabled="savingAll"
              class="px-4 py-1.5 bg-white text-gray-900 hover:bg-gray-100 rounded-xl text-xs font-bold shadow-md transition-colors flex items-center gap-1.5"
            >
              <span v-if="savingAll">⏳ Se salvează...</span>
              <span v-else>💾 Salvează tot ({{ dirtyCount }})</span>
            </button>
          </div>
        </div>
      </transition>

      <!-- Search & Status Filter Bar -->
      <div class="bg-white p-4 rounded-2xl border border-border shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="relative flex-1 max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Caută după nume produs sau SKU..."
            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
          />
          <span class="absolute left-3 top-2.5 text-xs text-gray-400 select-none">🔍</span>
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-3 top-2.5 text-xs text-gray-400 hover:text-gray-600"
          >
            ✕
          </button>
        </div>

        <!-- Filter pills -->
        <div class="flex items-center gap-1.5 flex-wrap">
          <button
            @click="statusFilter = 'all'"
            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors"
            :class="statusFilter === 'all' ? 'bg-primary text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
          >
            Toate ({{ inventory.length }})
          </button>
          <button
            @click="statusFilter = 'low_stock'"
            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1"
            :class="statusFilter === 'low_stock' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
          >
            <span>⚠️ Stoc Critic</span>
            <span class="bg-white/20 px-1.5 py-0.2 rounded-full text-[10px]">
              {{ stats.lowStock }}
            </span>
          </button>
          <button
            @click="statusFilter = 'outofstock'"
            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1"
            :class="statusFilter === 'outofstock' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100'"
          >
            <span>⛔ Epuizate</span>
            <span class="bg-white/20 px-1.5 py-0.2 rounded-full text-[10px]">
              {{ stats.outOfStock }}
            </span>
          </button>
          <button
            @click="statusFilter = 'instock'"
            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1"
            :class="statusFilter === 'instock' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
          >
            <span>✓ Optim</span>
            <span class="bg-white/20 px-1.5 py-0.2 rounded-full text-[10px]">
              {{ stats.inStock }}
            </span>
          </button>
        </div>
      </div>

      <!-- Main Table Card -->
      <div class="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
        <div v-if="loading" class="py-20 text-center text-xs text-gray-400">
          <div class="inline-block animate-spin text-2xl mb-2">⏳</div>
          <div>Se încarcă datele de inventar din WooCommerce...</div>
        </div>

        <div v-else-if="filteredItems.length === 0" class="py-20 text-center text-xs text-gray-500">
          <div class="text-3xl mb-2">📦</div>
          <div class="font-semibold text-gray-700">Nu s-au găsit produse</div>
          <div class="text-gray-400 mt-1">Încearcă să modifici filtrul sau termenul de căutare.</div>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 border-b border-border">
              <tr>
                <th class="px-5 py-3.5">Produs & SKU</th>
                <th class="px-4 py-3.5">Stare Stoc</th>
                <th class="px-4 py-3.5">
                  <div class="flex items-center gap-1">
                    <span>Cantitate În Stoc</span>
                    <span class="text-[10px] text-gray-400 lowercase font-normal">(buc.)</span>
                  </div>
                </th>
                <th class="px-4 py-3.5">
                  <div class="flex items-center gap-1">
                    <span>Prag Avertizare</span>
                    <span
                      class="text-[10px] text-gray-400 lowercase font-normal cursor-help"
                      title="Numărul de bucăți la care se declanșează alerta de stoc redus. Lăsați gol pentru valoarea implicită din magazin."
                    >
                      (alertă stoc) ⓘ
                    </span>
                  </div>
                </th>
                <th class="px-4 py-3.5">Alertă Activă</th>
                <th class="px-5 py-3.5 text-right">Acțiuni</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="item in filteredItems"
                :key="item.id"
                class="hover:bg-gray-50/80 transition-colors"
                :class="{
                  'bg-amber-50/30': isRowDirty(item.id),
                }"
              >
                <!-- Produs -->
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                      <img
                        v-if="item.image"
                        :src="item.image"
                        :alt="item.name"
                        class="w-full h-full object-cover"
                      />
                      <span v-else class="text-xs text-gray-400">🌱</span>
                    </div>
                    <div class="min-w-0">
                      <div class="font-semibold text-gray-900 truncate max-w-xs" :title="item.name">
                        {{ item.name }}
                      </div>
                      <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500">
                        <span class="font-mono text-[11px] bg-gray-100 px-1.5 py-0.5 rounded">
                          SKU: {{ item.sku || 'fără SKU' }}
                        </span>
                        <span v-if="item.price" class="text-gray-400 text-[11px]">
                          {{ item.price }} lei
                        </span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Stare Dropdown -->
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <select
                    :value="getRowStatus(item)"
                    @change="e => updateRowStatus(item, (e.target as HTMLSelectElement).value)"
                    class="text-xs font-semibold rounded-xl border px-3 py-1.5 focus:ring-2 focus:ring-primary focus:outline-none transition-colors"
                    :class="{
                      'bg-emerald-50 text-emerald-800 border-emerald-300': getRowStatus(item) === 'instock',
                      'bg-rose-50 text-rose-800 border-rose-300': getRowStatus(item) === 'outofstock',
                      'bg-amber-50 text-amber-800 border-amber-300': getRowStatus(item) === 'onbackorder',
                    }"
                  >
                    <option value="instock">● În stoc</option>
                    <option value="outofstock">● Stoc epuizat</option>
                    <option value="onbackorder">● Precomandă</option>
                  </select>
                </td>

                <!-- Cantitate În Stoc Field -->
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <input
                      type="number"
                      min="0"
                      :value="getRowQuantity(item)"
                      @input="e => updateRowQuantity(item, (e.target as HTMLInputElement).value)"
                      @keydown.enter="saveRow(item)"
                      placeholder="0"
                      class="w-24 px-3 py-1.5 text-xs font-bold rounded-xl border focus:ring-2 focus:ring-primary focus:outline-none transition-all"
                      :class="[
                        isRowDirty(item.id) && editedRows[item.id]?.stock_quantity !== undefined
                          ? 'border-amber-400 bg-amber-50/70 text-gray-900 ring-1 ring-amber-300'
                          : 'border-gray-300 bg-white text-gray-900',
                      ]"
                    />
                    <span class="text-xs text-gray-500 font-medium select-none">buc.</span>
                  </div>
                </td>

                <!-- Prag Avertizare Field -->
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <input
                      type="number"
                      min="0"
                      :value="getRowThreshold(item)"
                      @input="e => updateRowThreshold(item, (e.target as HTMLInputElement).value)"
                      @keydown.enter="saveRow(item)"
                      :placeholder="`Imp: ${item.effective_threshold ?? 2}`"
                      title="Prag alertă stoc redus (lăsați gol pentru valoarea implicită)"
                      class="w-24 px-3 py-1.5 text-xs font-bold rounded-xl border focus:ring-2 focus:ring-primary focus:outline-none transition-all"
                      :class="[
                        isRowDirty(item.id) && editedRows[item.id]?.low_stock_amount !== undefined
                          ? 'border-amber-400 bg-amber-50/70 text-gray-900 ring-1 ring-amber-300'
                          : 'border-gray-300 bg-white text-gray-900',
                      ]"
                    />
                    <span class="text-xs text-gray-400 font-medium select-none">buc.</span>
                  </div>
                </td>

                <!-- Alertă Activă (Live Badge) -->
                <td class="px-4 py-3.5 whitespace-nowrap">
                  <span
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border"
                    :class="getItemStatusAlert(item).badgeClass"
                  >
                    {{ getItemStatusAlert(item).label }}
                  </span>
                </td>

                <!-- Acțiuni -->
                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <!-- Save button (visible and highlighted when modified) -->
                    <button
                      v-if="isRowDirty(item.id)"
                      @click="saveRow(item)"
                      :disabled="savingRows[item.id]"
                      class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1"
                      title="Salvează modificările pentru acest produs"
                    >
                      <span v-if="savingRows[item.id]" class="animate-spin text-xs">⏳</span>
                      <span v-else>💾</span>
                      <span>Salvează</span>
                    </button>

                    <!-- Revert button -->
                    <button
                      v-if="isRowDirty(item.id)"
                      @click="revertRow(item)"
                      :disabled="savingRows[item.id]"
                      class="p-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs transition-colors"
                      title="Anulează modificările nesalvate"
                    >
                      ✕
                    </button>

                    <!-- Link to full product edit -->
                    <RouterLink
                      :to="`/produse/${item.id}/edit`"
                      class="p-1.5 text-gray-400 hover:text-primary hover:bg-gray-100 rounded-xl transition-colors"
                      title="Editează detaliile complete ale produsului"
                    >
                      <span class="text-xs">✏️</span>
                    </RouterLink>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer Info -->
        <div class="bg-gray-50/80 px-5 py-3 border-t border-border flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2">
          <div>
            Afișate: <strong>{{ filteredItems.length }}</strong> din <strong>{{ inventory.length }}</strong> produse
          </div>
          <div class="text-[11px] text-gray-400">
            💡 Sfat: Poți apăsa tasta <kbd class="px-1.5 py-0.5 bg-white border border-gray-300 rounded text-gray-700 font-mono">Enter</kbd> în câmpurile de cantitate sau avertizare pentru a salva instant.
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
