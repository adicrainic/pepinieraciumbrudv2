<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  trafficService,
  type TrafficPeriod,
  type TrafficStatsResponse
} from '@/services/trafficService'

const selectedPeriod = ref<TrafficPeriod>('30d')
const loading = ref(true)
const error = ref('')
const data = ref<TrafficStatsResponse | null>(null)

const periodOptions: { value: TrafficPeriod; label: string }[] = [
  { value: 'today', label: 'Astăzi' },
  { value: '7d', label: 'Ultimele 7 zile' },
  { value: '30d', label: 'Ultimele 30 zile' },
  { value: '90d', label: 'Ultimele 90 zile' }
]

const loadStats = async (period: TrafficPeriod = selectedPeriod.value) => {
  selectedPeriod.value = period
  loading.value = true
  error.value = ''
  try {
    data.value = await trafficService.getStats(period)
  } catch (err: any) {
    error.value =
      err?.response?.data?.message ||
      err?.message ||
      'Nu s-au putut încărca statisticile de trafic.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadStats('30d')
})

const summary = computed(() => {
  return (
    data.value?.summary || {
      unique_visitors: 0,
      total_pageviews: 0,
      pages_per_visitor: 0,
      orders_count: 0,
      conversion_rate: 0,
      previous_unique_visitors: 0,
      previous_total_pageviews: 0,
      today_visitors: 0,
      today_pageviews: 0
    }
  )
})

const calcTrend = (current: number, previous: number): { text: string; positive: boolean | null } => {
  if (previous === 0) {
    if (current > 0) return { text: '+100%', positive: true }
    return { text: '0%', positive: null }
  }
  const diff = ((current - previous) / previous) * 100
  const rounded = Math.round(diff)
  if (rounded > 0) return { text: `+${rounded}%`, positive: true }
  if (rounded < 0) return { text: `${rounded}%`, positive: false }
  return { text: '0%', positive: null }
}

const visitorsTrend = computed(() =>
  calcTrend(summary.value.unique_visitors, summary.value.previous_unique_visitors)
)
const pageviewsTrend = computed(() =>
  calcTrend(summary.value.total_pageviews, summary.value.previous_total_pageviews)
)

const maxDailyPageviews = computed(() => {
  const list = data.value?.daily || []
  const max = Math.max(...list.map((d) => Math.max(d.pageviews, d.unique_visitors)), 0)
  return max > 0 ? max : 10
})

const getBarHeight = (value: number): string => {
  if (value <= 0) return '4px'
  const pct = Math.max((value / maxDailyPageviews.value) * 100, 6)
  return `${Math.min(pct, 100)}%`
}

const getDeviceIcon = (type: string): string => {
  if (type === 'mobile') return '📱'
  if (type === 'tablet') return '📟'
  return '💻'
}

const getSourceIcon = (source: string): string => {
  const s = source.toLowerCase()
  if (s === 'direct') return '🔗'
  if (s.includes('google') || s.includes('bing') || s.includes('yahoo') || s.includes('duckduckgo')) return '🔍'
  if (s.includes('facebook') || s.includes('instagram') || s.includes('tiktok') || s.includes('pinterest')) return '📣'
  if (s.includes('youtube')) return '▶️'
  return '🌐'
}

const getCountryFlag = (countryCode?: string): string => {
  if (!countryCode || countryCode.length !== 2) return '📍'
  const codePoints = countryCode
    .toUpperCase()
    .split('')
    .map((char) => 127397 + char.charCodeAt(0))
  return String.fromCodePoint(...codePoints)
}

const formatDateRo = (isoDate?: string): string => {
  if (!isoDate) return ''
  const parts = isoDate.split('-')
  if (parts.length !== 3) return isoDate
  return `${parts[2]}.${parts[1]}.${parts[0]}`
}
</script>

<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Header & Period Controls -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-serif font-bold text-gray-900">
            Trafic & Vizitatori
          </h1>
          <p class="text-xs text-gray-500 mt-1">
            Statistici magazin public (fără cookie-uri, 100% compatibil GDPR)
            <span v-if="data?.date_range" class="text-gray-400">
              • {{ formatDateRo(data.date_range.start_date) }} – {{ formatDateRo(data.date_range.end_date) }}
            </span>
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <div class="inline-flex bg-white border border-border rounded-xl p-1 shadow-sm">
            <button
              v-for="opt in periodOptions"
              :key="opt.value"
              type="button"
              @click="loadStats(opt.value)"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
              :class="
                selectedPeriod === opt.value
                  ? 'bg-primary text-white shadow-sm'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
              "
            >
              {{ opt.label }}
            </button>
          </div>

          <button
            type="button"
            @click="loadStats(selectedPeriod)"
            :disabled="loading"
            class="px-3 py-2 rounded-xl bg-white border border-border text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm disabled:opacity-50"
            title="Reîmprospătează datele"
          >
            🔄 Reîncarcă
          </button>
        </div>
      </div>

      <!-- Error Banner -->
      <div
        v-if="error"
        class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center justify-between"
      >
        <span>{{ error }}</span>
        <button
          type="button"
          @click="loadStats(selectedPeriod)"
          class="text-xs font-semibold underline hover:no-underline"
        >
          Încearcă din nou
        </button>
      </div>

      <!-- 4 Main KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Unique Visitors -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Vizitatori Unici
            </span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold text-gray-900">
                {{ summary.unique_visitors.toLocaleString('ro-RO') }}
              </span>
              <span
                v-if="visitorsTrend.positive !== null"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                :class="
                  visitorsTrend.positive
                    ? 'bg-emerald-100 text-emerald-800'
                    : 'bg-rose-100 text-rose-800'
                "
                title="Față de perioada anterioară"
              >
                {{ visitorsTrend.text }}
              </span>
            </div>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              Astăzi: {{ summary.today_visitors }} {{ summary.today_visitors === 1 ? 'vizitator' : 'vizitatori' }}
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-2xl">
            👥
          </div>
        </div>

        <!-- 2. Total Pageviews -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Afișări de Pagină
            </span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold text-gray-900">
                {{ summary.total_pageviews.toLocaleString('ro-RO') }}
              </span>
              <span
                v-if="pageviewsTrend.positive !== null"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                :class="
                  pageviewsTrend.positive
                    ? 'bg-emerald-100 text-emerald-800'
                    : 'bg-rose-100 text-rose-800'
                "
                title="Față de perioada anterioară"
              >
                {{ pageviewsTrend.text }}
              </span>
            </div>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              Astăzi: {{ summary.today_pageviews }} {{ summary.today_pageviews === 1 ? 'afișare' : 'afișări' }}
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-2xl">
            👁️
          </div>
        </div>

        <!-- 3. Pages per Visitor -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Pagini / Vizitator
            </span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold text-gray-900">
                {{ summary.pages_per_visitor }}
              </span>
            </div>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              Medie pagini parcurse pe vizită
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-2xl">
            📖
          </div>
        </div>

        <!-- 4. Conversion Rate -->
        <div class="bg-white p-6 rounded-2xl border border-border shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">
              Rată de Conversie
            </span>
            <div class="flex items-baseline gap-2">
              <span class="text-2xl font-bold text-gray-900">
                {{ summary.conversion_rate }}%
              </span>
            </div>
            <span class="text-[11px] text-gray-500 font-medium block mt-1">
              {{ summary.orders_count === 1 ? '1 comandă plasată' : `${summary.orders_count} comenzi plasate` }} în perioadă
            </span>
          </div>
          <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-2xl">
            🛒
          </div>
        </div>
      </div>

      <!-- Daily Traffic Chart -->
      <div class="bg-white rounded-2xl border border-border shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
          <div>
            <h2 class="font-serif font-bold text-lg text-gray-900">
              Evoluție Zilnică Trafic
            </h2>
            <p class="text-xs text-gray-500">
              Comparație zilnică între vizitatori unici și totalul afișărilor de pagină
            </p>
          </div>

          <div class="flex items-center gap-4 text-xs font-medium text-gray-600">
            <span class="inline-flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-emerald-600 inline-block"></span>
              Vizitatori unici
            </span>
            <span class="inline-flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-sm bg-emerald-200 inline-block"></span>
              Afișări de pagină
            </span>
          </div>
        </div>

        <div v-if="loading && !data" class="h-56 flex items-center justify-center text-sm text-gray-400">
          Se încarcă graficul...
        </div>

        <div v-else-if="data?.daily?.length" class="space-y-2">
          <div class="h-56 flex items-end gap-1 sm:gap-2 pt-6 pb-2 border-b border-gray-100 overflow-x-auto">
            <div
              v-for="item in data.daily"
              :key="item.date"
              class="flex-1 min-w-[18px] h-full flex flex-col justify-end items-center group relative"
            >
              <!-- Hover Tooltip -->
              <div
                class="opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity absolute -top-2 z-20 bg-gray-900 text-white text-[10px] rounded-lg px-2.5 py-1.5 whitespace-nowrap shadow-lg"
              >
                <div class="font-bold text-emerald-300">{{ formatDateRo(item.date) }}</div>
                <div>Vizitatori: {{ item.unique_visitors }}</div>
                <div>Afișări: {{ item.pageviews }}</div>
              </div>

              <!-- Paired Bars (Pageviews beside Unique Visitors) -->
              <div class="w-full h-full flex items-end justify-center gap-0.5 px-0.5">
                <div
                  class="w-1/2 max-w-[14px] bg-emerald-600 rounded-t transition-all duration-300 group-hover:bg-emerald-700"
                  :style="{ height: getBarHeight(item.unique_visitors) }"
                ></div>
                <div
                  class="w-1/2 max-w-[14px] bg-emerald-200 rounded-t transition-all duration-300 group-hover:bg-emerald-300"
                  :style="{ height: getBarHeight(item.pageviews) }"
                ></div>
              </div>
            </div>
          </div>

          <!-- X-axis date labels -->
          <div class="flex items-center justify-between text-[10px] text-gray-400 font-medium px-1">
            <span>{{ formatDateRo(data.daily[0]?.date) }}</span>
            <span v-if="data.daily.length > 2">
              {{ formatDateRo(data.daily[Math.floor(data.daily.length / 2)]?.date) }}
            </span>
            <span v-if="data.daily.length > 1">
              {{ formatDateRo(data.daily[data.daily.length - 1]?.date) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Bottom Breakdown Grid: Top Pages (2 cols) + Sources, Locations & Devices (1 col) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Top Visited Pages & Products -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-border shadow-sm overflow-hidden flex flex-col">
          <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <div>
              <h2 class="font-serif font-bold text-gray-900">
                Cele Mai Vizitate Pagini & Produse
              </h2>
              <p class="text-xs text-gray-500">
                Paginile din magazin cu cel mai mare interes în perioada selectată
              </p>
            </div>
          </div>

          <div v-if="!data?.top_pages?.length" class="p-8 text-center text-sm text-gray-400">
            Nu există încă afișări de pagină înregistrate în această perioadă.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-border bg-gray-50/60 text-[11px] font-semibold text-gray-500 uppercase">
                  <th class="py-3 px-6">Pagină / Produs</th>
                  <th class="py-3 px-4 text-right">Vizitatori Unici</th>
                  <th class="py-3 px-6 text-right">Afișări</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border text-sm">
                <tr
                  v-for="page in data.top_pages"
                  :key="page.page_path"
                  class="hover:bg-gray-50/80 transition-colors"
                >
                  <td class="py-3.5 px-6">
                    <div class="font-semibold text-gray-900 truncate max-w-md">
                      {{ page.page_title }}
                    </div>
                    <div class="text-xs text-gray-400 font-mono truncate max-w-md">
                      {{ page.page_path }}
                    </div>
                  </td>
                  <td class="py-3.5 px-4 text-right font-medium text-gray-700">
                    {{ page.unique_visitors.toLocaleString('ro-RO') }}
                  </td>
                  <td class="py-3.5 px-6 text-right">
                    <div class="inline-flex items-center gap-2">
                      <span class="font-bold text-gray-900">
                        {{ page.pageviews.toLocaleString('ro-RO') }}
                      </span>
                      <span class="text-[11px] text-gray-400 w-10 text-right">
                        ({{ page.share_percent }}%)
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Right Column: Locations, Traffic Sources & Devices -->
        <div class="space-y-8">
          <!-- Visitor Locations (Orașe / Județe & Țări) -->
          <div class="bg-white rounded-2xl border border-border shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
              <h2 class="font-serif font-bold text-gray-900">
                Locații Vizitatori
              </h2>
              <p class="text-xs text-gray-500">
                Orașe, județe și țări de proveniență
              </p>
            </div>

            <div v-if="!data?.locations?.length" class="p-6 text-center text-sm text-gray-400">
              Nicio locație înregistrată în această perioadă.
            </div>

            <div v-else class="p-6 space-y-4">
              <div
                v-for="(loc, idx) in data.locations"
                :key="`${loc.country_code}-${loc.region_name}-${loc.city_name}-${idx}`"
                class="space-y-1.5"
              >
                <div class="flex items-center justify-between text-xs">
                  <span class="font-semibold text-gray-800 flex items-center gap-2 truncate pr-2">
                    <span class="text-sm flex-shrink-0" :title="loc.country_name || loc.country_code">
                      {{ getCountryFlag(loc.country_code) }}
                    </span>
                    <span class="truncate">{{ loc.label }}</span>
                    <span
                      v-if="loc.country_code && loc.country_code !== 'RO'"
                      class="text-[10px] text-gray-400 font-normal"
                    >
                      ({{ loc.country_name || loc.country_code }})
                    </span>
                  </span>
                  <span class="text-gray-600 font-medium whitespace-nowrap">
                    <strong class="text-gray-900">{{ loc.unique_visitors }}</strong>
                    <span class="text-gray-400 ml-1">({{ loc.share_percent }}%)</span>
                  </span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-amber-500 rounded-full transition-all duration-300"
                    :style="{ width: `${Math.max(loc.share_percent, 4)}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Traffic Sources (Referrers) -->
          <div class="bg-white rounded-2xl border border-border shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
              <h2 class="font-serif font-bold text-gray-900">
                Surse de Trafic
              </h2>
              <p class="text-xs text-gray-500">
                De unde ajung vizitatorii în magazin
              </p>
            </div>

            <div v-if="!data?.referrers?.length" class="p-6 text-center text-sm text-gray-400">
              Nicio sursă de trafic înregistrată în această perioadă.
            </div>

            <div v-else class="p-6 space-y-4">
              <div
                v-for="refItem in data.referrers"
                :key="refItem.source"
                class="space-y-1.5"
              >
                <div class="flex items-center justify-between text-xs">
                  <span class="font-semibold text-gray-800 flex items-center gap-2">
                    <span>{{ getSourceIcon(refItem.source) }}</span>
                    <span>{{ refItem.source }}</span>
                  </span>
                  <span class="text-gray-600 font-medium">
                    <strong class="text-gray-900">{{ refItem.unique_visitors }}</strong> vizitatori
                    <span class="text-gray-400">({{ refItem.share_percent }}%)</span>
                  </span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-emerald-600 rounded-full transition-all duration-300"
                    :style="{ width: `${Math.max(refItem.share_percent, 3)}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Devices Breakdown -->
          <div class="bg-white rounded-2xl border border-border shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
              <h2 class="font-serif font-bold text-gray-900">
                Dispozitive Utilizate
              </h2>
              <p class="text-xs text-gray-500">
                Împărțirea vizitatorilor după tipul dispozitivului
              </p>
            </div>

            <div class="p-6 space-y-4">
              <div
                v-for="dev in data?.devices || []"
                :key="dev.device_type"
                class="space-y-1.5"
              >
                <div class="flex items-center justify-between text-xs">
                  <span class="font-semibold text-gray-800 flex items-center gap-2">
                    <span>{{ getDeviceIcon(dev.device_type) }}</span>
                    <span>{{ dev.label }}</span>
                  </span>
                  <span class="text-gray-600 font-medium">
                    <strong class="text-gray-900">{{ dev.unique_visitors }}</strong>
                    <span class="text-gray-400 ml-1">({{ dev.share_percent }}%)</span>
                  </span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-blue-600 rounded-full transition-all duration-300"
                    :style="{ width: `${dev.unique_visitors > 0 ? Math.max(dev.share_percent, 4) : 0}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
