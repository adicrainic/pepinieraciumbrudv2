<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import AppButton from '~/components/ui/AppButton.vue'

const { setPageSeo } = useSeo()
setPageSeo({
  title: 'Contact Pepiniera Ciumbrud',
  description: 'Luați legătura cu specialiștii Pepinierei Ciumbrud pentru comenzi, sfaturi și comenzi speciale.'
})

const form = ref({
  name: '',
  email: '',
  phone: '',
  message: ''
})

const sent = ref(false)

const handleSubmit = () => {
  sent.value = true
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
                <strong>Pepiniera Ciumbrud</strong><br />
                Sat Ciumbrud, Jud. Alba, România
              </div>
            </div>

            <div class="flex items-center space-x-3">
              <span class="text-xl">📞</span>
              <div>
                <a href="tel:+40700000000" class="hover:text-primary font-semibold">
                  +40 700 000 000
                </a>
              </div>
            </div>

            <div class="flex items-center space-x-3">
              <span class="text-xl">✉️</span>
              <div>
                <a href="mailto:contact@pepinieraciumbrud.ro" class="hover:text-primary">
                  contact@pepinieraciumbrud.ro
                </a>
              </div>
            </div>

            <div class="flex items-start space-x-3">
              <span class="text-xl">🕒</span>
              <div>
                <strong>Program comenzi & asistență:</strong><br />
                Luni – Vineri: 08:00 – 17:00<br />
                Sâmbătă: 08:00 – 13:00
              </div>
            </div>
          </div>
        </div>

        <div class="p-4 rounded-2xl bg-surface-warm text-xs text-text-muted leading-relaxed">
          💡 <strong>Sfat util:</strong> Comenzile se pot plasa direct prin magazinul online la orice oră. Expedierea se face în ordinea primirii comenzilor.
        </div>
      </div>

      <!-- Contact Form -->
      <div class="bg-white rounded-3xl border border-border p-8 shadow-sm">
        <h3 class="font-serif text-xl font-bold text-primary-dark mb-4">
          Trimiteți un Mesaj
        </h3>

        <div v-if="sent" class="p-6 bg-emerald-50 rounded-2xl border border-emerald-200 text-center text-emerald-800">
          <div class="text-3xl mb-2">✉️</div>
          <h4 class="font-bold text-base mb-1">Mesaj trimis cu succes!</h4>
          <p class="text-xs">Vă vom răspunde în cel mai scurt timp posibil.</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-text mb-1">Nume și Prenume *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-text mb-1">Telefon *</label>
              <input
                v-model="form.phone"
                type="tel"
                required
                class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-text mb-1">Email *</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-text mb-1">Mesaj / Solicitare *</label>
            <textarea
              v-model="form.message"
              rows="4"
              required
              class="w-full bg-surface-warm border border-border rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Întrebare despre stocuri, comenzi sau recomandări..."
            ></textarea>
          </div>

          <AppButton type="submit" variant="primary" size="lg" class="w-full">
            Trimite Mesajul
          </AppButton>
        </form>
      </div>
    </div>
  </div>
</template>

