<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import { useSiteSettings } from '~/composables/useSiteSettings'
import AppButton from '~/components/ui/AppButton.vue'

const { settings } = useSiteSettings()
const config = useRuntimeConfig()
const wpBase = config.public.wpBase || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json'

const { setPageSeo } = useSeo()
setPageSeo({
  title: `Contact ${settings.value.site_title}`,
  description: `Luați legătura cu specialiștii ${settings.value.site_title} pentru comenzi, sfaturi și comenzi speciale.`
})

const form = ref({
  name: '',
  email: '',
  phone: '',
  message: '',
  honeypot: ''
})

const sent = ref(false)
const submitting = ref(false)
const errorMessage = ref('')

const handleSubmit = async () => {
  submitting.value = true
  errorMessage.value = ''

  try {
    const res = await $fetch<{ success: boolean; message?: string }>(`${wpBase}/pcs/v1/contact`, {
      method: 'POST',
      body: {
        name: form.value.name,
        email: form.value.email,
        phone: form.value.phone,
        message: form.value.message,
        honeypot: form.value.honeypot
      }
    })

    if (res && res.success) {
      sent.value = true
      form.value = {
        name: '',
        email: '',
        phone: '',
        message: '',
        honeypot: ''
      }
    } else {
      errorMessage.value = res?.message || 'A apărut o eroare la trimiterea mesajului.'
    }
  } catch (err: any) {
    errorMessage.value = err?.data?.message || err?.message || 'Nu s-a putut trimite mesajul. Vă rugăm să încercați din nou.'
  } finally {
    submitting.value = false
  }
}

const resetForm = () => {
  sent.value = false
  errorMessage.value = ''
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
      <span class="text-xs uppercase tracking-wider text-primary font-bold block mb-2">
        Suntem Aici Să Vă Ajutăm
      </span>
      <h1 class="font-serif text-4xl sm:text-5xl font-bold text-primary-dark">
        Contactați-ne
      </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
      <!-- Contact Info -->
      <div class="bg-white rounded-3xl border border-border p-8 space-y-8 shadow-sm">
        <div>
          <h3 class="font-serif text-xl font-bold text-primary-dark mb-4">
            Date de Contact
          </h3>
          <div class="space-y-4 text-sm text-text">
            <div class="flex items-start space-x-3">
              <span class="text-xl">📍</span>
              <div>
                <strong>{{ settings.site_title }}</strong><br />
                {{ settings.contact_address }}
              </div>
            </div>

            <div class="flex items-center space-x-3">
              <span class="text-xl">📞</span>
              <div>
                <a :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`" class="hover:text-primary font-semibold">
                  {{ settings.contact_phone }}
                </a>
              </div>
            </div>

            <div class="flex items-center space-x-3">
              <span class="text-xl">✉️</span>
              <div>
                <a :href="`mailto:${settings.contact_email}`" class="hover:text-primary">
                  {{ settings.contact_email }}
                </a>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <span class="text-xl">🕒</span>
              <div>
                <strong>Program comenzi & asistență:</strong><br />
                <div>{{ settings.schedule_weekdays }}</div>
                <div v-if="settings.schedule_saturday">{{ settings.schedule_saturday }}</div>
                <div v-if="settings.schedule_sunday && settings.schedule_sunday !== 'Duminică: Închis'" class="text-text-muted">{{ settings.schedule_sunday }}</div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="settings.schedule_notes" class="p-4 rounded-2xl bg-surface-warm text-xs text-text-muted leading-relaxed">
          💡 <strong>Sfat util:</strong> {{ settings.schedule_notes }}
        </div>
      </div>

      <!-- Contact Form -->
      <div class="bg-white rounded-3xl border border-border p-8 shadow-sm">
        <h3 class="font-serif text-xl font-bold text-primary-dark mb-4">
          Trimiteți un Mesaj
        </h3>

        <div v-if="sent" class="p-6 bg-leaf-50 rounded-2xl border border-leaf-100 text-center text-leaf-900 space-y-3">
          <div class="text-3xl">✉️</div>
          <h4 class="font-bold text-base">Mesaj trimis cu succes!</h4>
          <p class="text-xs text-text-muted">
            Vă mulțumim pentru mesaj. Echipa Pepiniera Ciumbrud vă va răspunde în cel mai scurt timp posibil.
          </p>
          <div class="pt-2">
            <button
              type="button"
              @click="resetForm"
              class="inline-block text-xs font-semibold text-primary underline hover:text-primary-dark"
            >
              Trimiteți un alt mesaj
            </button>
          </div>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Error banner -->
          <div v-if="errorMessage" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ errorMessage }}</span>
          </div>

          <!-- Honeypot anti-spam (hidden) -->
          <div class="hidden" aria-hidden="true">
            <label for="honeypot_field">Leave this empty</label>
            <input
              id="honeypot_field"
              v-model="form.honeypot"
              type="text"
              name="website"
              tabindex="-1"
              autocomplete="off"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-text mb-1">Nume și Prenume *</label>
            <input
              v-model="form.name"
              type="text"
              required
              :disabled="submitting"
              placeholder="ex: Ion Popescu"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-text mb-1">Telefon *</label>
              <input
                v-model="form.phone"
                type="tel"
                required
                :disabled="submitting"
                placeholder="07xx xxx xxx"
                class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-text mb-1">Email *</label>
              <input
                v-model="form.email"
                type="email"
                required
                :disabled="submitting"
                placeholder="nume@exemplu.ro"
                class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-text mb-1">Mesaj / Solicitare *</label>
            <textarea
              v-model="form.message"
              rows="4"
              required
              :disabled="submitting"
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50"
              placeholder="Întrebare despre stocuri, comenzi sau recomandări..."
            ></textarea>
          </div>

          <AppButton type="submit" variant="primary" size="lg" class="w-full" :disabled="submitting">
            <span v-if="submitting" class="flex items-center justify-center gap-2">
              <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
              Se trimite...
            </span>
            <span v-else>Trimite Mesajul</span>
          </AppButton>
        </form>
      </div>
    </div>
  </div>
</template>

