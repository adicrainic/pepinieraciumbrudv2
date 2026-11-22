<script setup lang="ts">
import { useCartStore } from '~/stores/cart'
import { useSeo } from '~/composables/useSeo'
import { storeApi } from '~/services/storeApi'
import AppButton from '~/components/ui/AppButton.vue'

definePageMeta({
  layout: 'minimal'
})

const cartStore = useCartStore()
const { setPageSeo } = useSeo()

setPageSeo({
  title: 'Finalizare Comandă',
  description: 'Completați datele de livrare și plată pentru comanda dumneavoastră.'
})

onMounted(() => {
  cartStore.fetchCart()
})

const ROMANIAN_COUNTIES = [
  { code: 'AB', name: 'Alba' },
  { code: 'AR', name: 'Arad' },
  { code: 'AG', name: 'Argeș' },
  { code: 'BC', name: 'Bacău' },
  { code: 'BH', name: 'Bihor' },
  { code: 'BN', name: 'Bistrița-Năsăud' },
  { code: 'BT', name: 'Botoșani' },
  { code: 'BV', name: 'Brașov' },
  { code: 'BR', name: 'Brăila' },
  { code: 'B',  name: 'București' },
  { code: 'BZ', name: 'Buzău' },
  { code: 'CL', name: 'Călărași' },
  { code: 'CS', name: 'Caraș-Severin' },
  { code: 'CJ', name: 'Cluj' },
  { code: 'CT', name: 'Constanța' },
  { code: 'CV', name: 'Covasna' },
  { code: 'DB', name: 'Dâmbovița' },
  { code: 'DJ', name: 'Dolj' },
  { code: 'GL', name: 'Galați' },
  { code: 'GR', name: 'Giurgiu' },
  { code: 'GJ', name: 'Gorj' },
  { code: 'HR', name: 'Harghita' },
  { code: 'HD', name: 'Hunedoara' },
  { code: 'IL', name: 'Ialomița' },
  { code: 'IS', name: 'Iași' },
  { code: 'IF', name: 'Ilfov' },
  { code: 'MM', name: 'Maramureș' },
  { code: 'MH', name: 'Mehedinți' },
  { code: 'MS', name: 'Mureș' },
  { code: 'NT', name: 'Neamț' },
  { code: 'OT', name: 'Olt' },
  { code: 'PH', name: 'Prahova' },
  { code: 'SJ', name: 'Sălaj' },
  { code: 'SM', name: 'Satu Mare' },
  { code: 'SB', name: 'Sibiu' },
  { code: 'SV', name: 'Suceava' },
  { code: 'TR', name: 'Teleorman' },
  { code: 'TM', name: 'Timiș' },
  { code: 'TL', name: 'Tulcea' },
  { code: 'VL', name: 'Vâlcea' },
  { code: 'VS', name: 'Vaslui' },
  { code: 'VN', name: 'Vrancea' },
]

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address_1: '',
  city: '',
  state: 'AB',
  postcode: '',
  country: 'RO',
  customer_note: '',
  payment_method: 'cod' // Ramburs la livrare
})

const isSubmitting = ref(false)
const errorMessage = ref('')

const formatLinePrice = (priceStr: string, minorUnit: number = 2) => {
  const num = parseFloat(priceStr || '0') / Math.pow(10, minorUnit)
  return `${num.toFixed(2).replace('.', ',')} lei`
}

const getItemLineTotal = (item: any) => {
  if (item.totals?.line_total) {
    return formatLinePrice(item.totals.line_total, item.totals.currency_minor_unit ?? 2)
  }
  const unitPrice = parseFloat(item.prices?.price || '0')
  const minorUnit = item.prices?.currency_minor_unit ?? 2
  const total = (unitPrice * (item.quantity || 1)) / Math.pow(10, minorUnit)
  return `${total.toFixed(2).replace('.', ',')} lei`
}

const getItemUnitPrice = (item: any) => {
  return formatLinePrice(item.prices?.price || '0', item.prices?.currency_minor_unit ?? 2)
}

const getItemPath = (item: any) => {
  if (item.permalink) {
    try {
      const url = new URL(item.permalink)
      const parts = url.pathname.split('/').filter(Boolean)
      const slug = parts[parts.length - 1]
      if (slug) return `/produse/${slug}`
    } catch {
      // fallback
    }
  }
  return `/produse/${item.id}`
}

const handlePlaceOrder = async () => {
  if (!cartStore.items || cartStore.items.length === 0) {
    errorMessage.value = 'Coșul dumneavoastră este gol. Adăugați produse înainte de a plasa comanda.'
    return
  }

  if (
    !form.value.first_name?.trim() ||
    !form.value.last_name?.trim() ||
    !form.value.phone?.trim() ||
    !form.value.email?.trim() ||
    !form.value.address_1?.trim() ||
    !form.value.city?.trim() ||
    !form.value.state?.trim() ||
    !form.value.postcode?.trim()
  ) {
    errorMessage.value = 'Vă rugăm să completați toate câmpurile obligatorii (*).'
    return
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.value.email.trim())) {
    errorMessage.value = 'Vă rugăm să introduceți o adresă de email validă.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const payload = {
      billing_address: {
        first_name: form.value.first_name.trim(),
        last_name: form.value.last_name.trim(),
        email: form.value.email.trim(),
        phone: form.value.phone.trim(),
        address_1: form.value.address_1.trim(),
        city: form.value.city.trim(),
        state: form.value.state.trim(),
        postcode: form.value.postcode.trim(),
        country: form.value.country
      },
      shipping_address: {
        first_name: form.value.first_name.trim(),
        last_name: form.value.last_name.trim(),
        phone: form.value.phone.trim(),
        address_1: form.value.address_1.trim(),
        city: form.value.city.trim(),
        state: form.value.state.trim(),
        postcode: form.value.postcode.trim(),
        country: form.value.country
      },
      customer_note: form.value.customer_note.trim(),
      payment_method: form.value.payment_method
    }

    const orderResponse = await storeApi.checkout(payload)
    const orderId = orderResponse?.order_id || orderResponse?.id || 'succes'
    cartStore.clearLocalCart()
    await cartStore.fetchCart()
    navigateTo(`/confirmare?order=${orderId}`)
  } catch (err: any) {
    const rawError =
      err?.data?.message ||
      err?.response?._data?.message ||
      err?.message ||
      'A apărut o eroare la procesarea comenzii. Vă rugăm să încercați din nou.'
    errorMessage.value = String(rawError)
      .replace(/&bdquo;/g, '„')
      .replace(/&rdquo;/g, '”')
      .replace(/&ldquo;/g, '“')
      .replace(/&quot;/g, '"')
      .replace(/&#039;/g, "'")
      .replace(/&amp;/g, '&')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <div class="mb-8 text-center">
      <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark">Finalizare Comandă</h1>
      <p class="text-xs sm:text-sm text-text-muted mt-1.5">Completați adresa pentru expedierea plantelor în siguranță, direct de la pepinieră.</p>
    </div>

    <!-- Error Alert -->
    <div v-if="errorMessage" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center justify-between">
      <span>{{ errorMessage }}</span>
      <button type="button" @click="errorMessage = ''" class="font-bold text-rose-600 hover:text-rose-800 ml-3">&times;</button>
    </div>

    <!-- Loading Cart State -->
    <div v-if="cartStore.loading && (!cartStore.items || cartStore.items.length === 0)" class="py-16 text-center bg-white/85 backdrop-blur-sm rounded-3xl border border-border">
      <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto mb-3"></div>
      <p class="text-sm text-text-muted">Se încarcă produsele din coș...</p>
    </div>

    <!-- Empty Cart Guard -->
    <div v-else-if="!cartStore.items || cartStore.items.length === 0" class="bg-white/85 backdrop-blur-sm rounded-3xl border border-border p-10 sm:p-12 text-center max-w-xl mx-auto my-8 shadow-sm">
      <div class="text-4xl mb-4">🛒</div>
      <h2 class="font-serif text-2xl font-bold text-text mb-2">Coșul dumneavoastră este gol</h2>
      <p class="text-sm text-text-muted mb-6">
        Pentru a plasa o comandă, vă rugăm să adăugați mai întâi puieții sau trandafirii doriți în coș.
      </p>
      <AppButton to="/produse" variant="primary" size="lg">
        Explorează Catalogul de Plante
      </AppButton>
    </div>

    <form v-else @submit.prevent="handlePlaceOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
      <!-- Address Details -->
      <div class="lg:col-span-2 space-y-6 bg-white/85 backdrop-blur-sm p-6 sm:p-8 rounded-3xl border border-border shadow-sm">
        <div class="flex items-center justify-between border-b border-border pb-3">
          <h3 class="font-serif text-xl font-bold text-primary-dark">
            1. Date de Contact și Livrare
          </h3>
          <span class="text-xs text-text-muted">* Câmpuri obligatorii</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Prenume *</label>
            <input
              v-model="form.first_name"
              type="text"
              required
              autocomplete="given-name"
              placeholder="Ex: Ion"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Nume de Familie *</label>
            <input
              v-model="form.last_name"
              type="text"
              required
              autocomplete="family-name"
              placeholder="Ex: Popescu"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Telefon (pentru curier și confirmare) *</label>
            <input
              v-model="form.phone"
              type="tel"
              required
              autocomplete="tel"
              placeholder="07xxxxxxxx"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Email (pentru confirmarea comenzii) *</label>
            <input
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              placeholder="adresa@exemplu.ro"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-text mb-1">Adresă completă (Strada, Număr, Bloc/Apt) *</label>
          <input
            v-model="form.address_1"
            type="text"
            required
            autocomplete="street-address"
            placeholder="Strada, număr, bloc, scară, apartament"
            class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Județ *</label>
            <select
              v-model="form.state"
              required
              class="w-full bg-surface-warm border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
            >
              <option value="" disabled>Alegeți județul</option>
              <option v-for="county in ROMANIAN_COUNTIES" :key="county.code" :value="county.code">
                {{ county.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Localitate / Sat *</label>
            <input
              v-model="form.city"
              type="text"
              required
              autocomplete="address-level2"
              placeholder="Ex: Ciumbrud"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Cod Poștal *</label>
            <input
              v-model="form.postcode"
              type="text"
              required
              autocomplete="postal-code"
              placeholder="Ex: 515200"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-text mb-1">Observații comandă / preferințe de livrare (opțional)</label>
          <textarea
            v-model="form.customer_note"
            rows="2"
            placeholder="Ex: prefer livrare după data de 15 noiembrie / rog apel înainte de livrare"
            class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-colors"
          ></textarea>
        </div>

        <h3 class="font-serif text-xl font-bold text-primary-dark border-t border-border pt-6 pb-2">
          2. Metodă de Plată și Livrare
        </h3>

        <div class="space-y-3">
          <label class="flex items-start p-4 border-2 border-primary/40 bg-leaf-50/50 rounded-xl cursor-pointer">
            <input
              type="radio"
              v-model="form.payment_method"
              value="cod"
              class="mt-0.5 text-primary focus:ring-primary"
            />
            <div class="ml-3">
              <span class="text-sm font-bold text-text block">Plată Ramburs la Curier (Numerar sau Card)</span>
              <span class="text-xs text-text-muted block mt-0.5">
                Achitați contravaloarea comenzii direct curierului la primirea coletului. Pentru comenzi cu Ordin de Plată (OP / factură proformă), menționați acest lucru în câmpul de observații.
              </span>
            </div>
          </label>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="space-y-6 lg:sticky lg:top-8">
        <div class="bg-white/85 backdrop-blur-sm p-6 rounded-3xl border border-border shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-border pb-3">
            <h3 class="font-serif text-lg font-bold text-primary-dark">
              Comanda Dumneavoastră ({{ cartStore.itemCount }} buc.)
            </h3>
            <NuxtLink to="/cos" class="text-xs font-medium text-primary hover:underline">
              Modifică coșul
            </NuxtLink>
          </div>

          <div class="divide-y divide-border text-xs max-h-72 overflow-y-auto pr-1">
            <div v-for="item in cartStore.items" :key="item.key" class="py-3 flex items-center justify-between gap-3">
              <div class="flex items-center gap-2.5 min-w-0">
                <img
                  :src="item.images && item.images[0]?.src ? item.images[0].src : '/placeholder-plant.svg'"
                  :alt="item.name"
                  class="w-10 h-10 rounded-lg object-cover border border-border flex-shrink-0 bg-surface-warm"
                  onerror="this.src='/placeholder-plant.svg'"
                />
                <div class="min-w-0">
                  <NuxtLink :to="getItemPath(item)" class="font-semibold text-text hover:text-primary truncate block">
                    {{ item.name }}
                  </NuxtLink>
                  <span class="text-text-muted block mt-0.5">
                    {{ item.quantity }} buc. &times; {{ getItemUnitPrice(item) }}
                  </span>
                </div>
              </div>
              <span class="font-bold text-primary-dark whitespace-nowrap">
                {{ getItemLineTotal(item) }}
              </span>
            </div>
          </div>

          <div class="border-t border-border pt-4 space-y-2.5 text-xs">
            <div class="flex justify-between">
              <span class="text-text-muted">Subtotal produse</span>
              <span class="font-semibold text-text">{{ cartStore.formattedSubtotal }}</span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="text-text-muted">Livrare curier rapid</span>
              <span v-if="cartStore.hasShippingCost" class="font-semibold text-text">
                {{ cartStore.formattedShipping }}
              </span>
              <span v-else class="font-medium text-leaf-700 text-right">
                Confirmată telefonic (~25–35 lei)
              </span>
            </div>
            <div class="border-t border-border pt-3 flex justify-between text-base font-bold text-primary-dark">
              <span>Total produse</span>
              <span>{{ cartStore.formattedTotal }}</span>
            </div>
          </div>

          <div class="bg-surface-warm/70 border border-border rounded-xl p-3 text-xs text-text-muted space-y-1.5 leading-relaxed">
            <div class="flex items-center gap-1.5 text-text font-medium">
              <span>📞</span>
              <span>Confirmare telefonică înainte de expediere</span>
            </div>
            <p>
              Vă vom contacta telefonic pentru confirmarea comenzii, stabilirea datei optime de livrare și comunicarea costului exact de transport în funcție de greutatea coletului.
            </p>
          </div>

          <AppButton
            type="submit"
            variant="warm"
            size="lg"
            class="w-full mt-2"
            :loading="isSubmitting"
          >
            Trimite Comanda
          </AppButton>
        </div>

        <div class="text-xs text-text-muted text-center leading-relaxed px-2">
          Prin plasarea comenzii confirmați că sunteți de acord cu
          <NuxtLink to="/termeni-si-conditii" class="text-primary underline hover:text-primary-dark">Termenii și Condițiile</NuxtLink>
          și
          <NuxtLink to="/politica-de-confidentialitate" class="text-primary underline hover:text-primary-dark">Politica de Confidențialitate</NuxtLink>
          ale Pepinierei Ciumbrud.
        </div>
      </div>
    </form>
  </div>
</template>

