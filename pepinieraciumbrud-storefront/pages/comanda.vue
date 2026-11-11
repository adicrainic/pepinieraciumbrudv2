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

const handlePlaceOrder = async () => {
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
    navigateTo(`/confirmare?order=${orderId}`)
  } catch (err: any) {
    const apiError =
      err?.data?.message ||
      err?.response?._data?.message ||
      err?.message ||
      'A apărut o eroare la procesarea comenzii. Vă rugăm să încercați din nou.'
    errorMessage.value = apiError
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <div class="mb-8 text-center">
      <h1 class="font-serif text-3xl font-bold text-primary-dark">Finalizare Comandă</h1>
      <p class="text-xs text-text-muted mt-1">Completați adresa pentru expedierea plantelor în siguranță.</p>
    </div>

    <!-- Error Alert -->
    <div v-if="errorMessage" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="handlePlaceOrder" class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Address Details -->
      <div class="md:col-span-2 space-y-6 bg-white p-6 sm:p-8 rounded-3xl border border-border shadow-sm">
        <h3 class="font-serif text-lg font-bold text-primary-dark border-b border-border pb-3">
          1. Date de Contact și Livrare
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Prenume *</label>
            <input
              v-model="form.first_name"
              type="text"
              required
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Nume de Familie *</label>
            <input
              v-model="form.last_name"
              type="text"
              required
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Telefon *</label>
            <input
              v-model="form.phone"
              type="tel"
              required
              placeholder="07xxxxxxxx"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Email *</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="adresa@exemplu.ro"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-text mb-1">Adresă completă (Strada, Număr, Bloc/Apt) *</label>
          <input
            v-model="form.address_1"
            type="text"
            required
            class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Județ *</label>
            <select
              v-model="form.state"
              required
              class="w-full bg-surface-warm border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
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
              placeholder="Ex: Ciumbrud"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Cod Postal *</label>
            <input
              v-model="form.postcode"
              type="text"
              required
              placeholder="Ex: 515200"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-text mb-1">Observații comandă / preferințe de livrare</label>
          <textarea
            v-model="form.customer_note"
            rows="2"
            placeholder="Ex: prefer livrare după data de 15 noiembrie"
            class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
          ></textarea>
        </div>

        <h3 class="font-serif text-lg font-bold text-primary-dark border-t border-border pt-6 pb-2">
          2. Metodă de Plată
        </h3>

        <div class="space-y-3">
          <label class="flex items-center p-3.5 border border-primary/40 bg-leaf-50/50 rounded-xl cursor-pointer">
            <input
              type="radio"
              v-model="form.payment_method"
              value="cod"
              class="text-primary focus:ring-primary"
            />
            <div class="ml-3">
              <span class="text-sm font-bold text-text block">Plată Ramburs la Curier</span>
              <span class="text-xs text-text-muted">Achitați numerar sau cu cardul curierului la livrarea coletului.</span>
            </div>
          </label>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-border shadow-sm space-y-4">
          <h3 class="font-serif text-base font-bold text-primary-dark border-b border-border pb-3">
            Comanda Dumneavoastră
          </h3>

          <div class="divide-y divide-border text-xs max-h-60 overflow-y-auto">
            <div v-for="item in cartStore.items" :key="item.key" class="py-2.5 flex justify-between">
              <div>
                <span class="font-medium text-text">{{ item.name }}</span>
                <span class="text-text-muted block">x {{ item.quantity }} buc.</span>
              </div>
              <span class="font-bold text-primary-dark">
                {{ cartStore.formattedTotal }}
              </span>
            </div>
          </div>

          <div class="border-t border-border pt-4 space-y-2 text-xs">
            <div class="flex justify-between">
              <span class="text-text-muted">Total produse</span>
              <span class="font-semibold">{{ cartStore.formattedTotal }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-text-muted">Transport</span>
              <span class="font-semibold text-leaf-700">Calculat la expediere</span>
            </div>
            <div class="border-t border-border pt-2 flex justify-between text-sm font-bold text-primary-dark">
              <span>Total plată</span>
              <span>{{ cartStore.formattedTotal }}</span>
            </div>
          </div>

          <AppButton
            type="submit"
            variant="warm"
            size="lg"
            class="w-full mt-4"
            :loading="isSubmitting"
          >
            Trimite Comanda
          </AppButton>
        </div>

        <div class="text-[11px] text-text-muted text-center">
          Prin plasarea comenzii confirmați că sunteți de acord cu termenii și condițiile Pepinierei Ciumbrud.
        </div>
      </div>
    </form>
  </div>
</template>

