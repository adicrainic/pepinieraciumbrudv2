<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { settingsService } from '@/services/settingsService'
import { mediaService } from '@/services/mediaService'
import { useUiStore } from '@/stores/ui'
import type { SiteSettings } from '@/types/settings'

const uiStore = useUiStore()

const loading = ref(true)
const saving = ref(false)
const uploadingLogo = ref(false)
const logoUploadError = ref('')
const fileInput = ref<HTMLInputElement | null>(null)

const defaultSettings: SiteSettings = {
  site_title: 'Pepiniera Ciumbrud',
  site_tagline: 'Tradiție & Calitate din 1990',
  site_description: 'Producător autorizat de material săditor pomicol, trandafiri și plante ornamentale de cea mai bună calitate, aclimatizate pentru România.',
  logo_url: '',
  contact_phone: '+40 700 000 000',
  contact_email: 'contact@pepinieraciumbrud.ro',
  contact_address: 'Sat Ciumbrud, Jud. Alba, România',
  schedule_weekdays: 'Luni – Vineri: 08:00 – 17:00',
  schedule_saturday: 'Sâmbătă: 08:00 – 13:00',
  schedule_sunday: 'Duminică: Închis',
  schedule_notes: 'Comenzile se pot plasa direct prin magazinul online la orice oră. Expedierea se face în ordinea primirii comenzilor.',
  footer_copyright: 'Pepiniera Ciumbrud. Toate drepturile rezervate.'
}

const form = ref<SiteSettings>({ ...defaultSettings })
const originalForm = ref<SiteSettings>({ ...defaultSettings })

const isDirty = computed(() => {
  return JSON.stringify(form.value) !== JSON.stringify(originalForm.value)
})

const fetchSettings = async () => {
  loading.value = true
  try {
    const data = await settingsService.getSettings()
    form.value = { ...defaultSettings, ...data }
    originalForm.value = { ...form.value }
  } catch (err: any) {
    uiStore.addNotification('error', 'Nu s-au putut încărca setările.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchSettings()
})

const handleSave = async () => {
  saving.value = true
  try {
    const res = await settingsService.updateSettings(form.value)
    form.value = { ...defaultSettings, ...res.settings }
    originalForm.value = { ...form.value }
    uiStore.addNotification('success', res.message || 'Setările au fost salvate cu succes.')
  } catch (err: any) {
    uiStore.addNotification('error', err.response?.data?.message || 'Eroare la salvarea setărilor.')
  } finally {
    saving.value = false
  }
}

const handleReset = () => {
  form.value = { ...originalForm.value }
  uiStore.addNotification('info', 'Modificările nesalvate au fost anulate.')
}

const triggerLogoUpload = () => {
  fileInput.value?.click()
}

const handleLogoFileChange = async (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files || !target.files[0]) return

  const file = target.files[0]
  if (!file.type.startsWith('image/')) {
    logoUploadError.value = 'Vă rugăm să selectați un fișier imagine valid (JPG, PNG, SVG, WEBP).'
    return
  }

  logoUploadError.value = ''
  uploadingLogo.value = true

  try {
    const uploaded = await mediaService.upload(file)
    form.value.logo_url = uploaded.url
    uiStore.addNotification('success', 'Sigla a fost încărcată cu succes!')
  } catch (err: any) {
    logoUploadError.value = err.response?.data?.message || 'Eroare la încărcarea siglei.'
    uiStore.addNotification('error', 'Nu s-a putut încărca imaginea de siglă.')
  } finally {
    uploadingLogo.value = false
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
}

const removeLogo = () => {
  form.value.logo_url = ''
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-6xl mx-auto space-y-8">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-border pb-5">
        <div>
          <div class="flex items-center gap-2">
            <span class="text-2xl">⚙️</span>
            <h1 class="text-2xl font-bold text-gray-900 font-serif">Setări Aplicație Publică</h1>
          </div>
          <p class="text-sm text-gray-500 mt-1">
            Personalizați informațiile afișate pe magazinul online public (siglă, date de contact, program și texte).
          </p>
        </div>

        <div class="flex items-center gap-3">
          <a
            href="http://pepinieraciumbrud.local"
            target="_blank"
            class="px-3.5 py-2 rounded-xl text-xs font-medium text-primary hover:bg-primary-50 transition-colors flex items-center gap-1.5 border border-primary/30"
          >
            <span>Deschide Magazinul</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
          </a>

          <button
            v-if="isDirty"
            @click="handleReset"
            type="button"
            :disabled="saving"
            class="px-4 py-2 rounded-xl text-xs font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors"
          >
            Anulează
          </button>

          <button
            @click="handleSave"
            type="button"
            :disabled="saving || loading || !isDirty"
            class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-primary hover:bg-primary-dark transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="saving" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span>{{ saving ? 'Se salvează...' : 'Salvează Modificările' }}</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center bg-white rounded-2xl border border-border">
        <div class="inline-block w-8 h-8 border-3 border-primary/20 border-t-primary rounded-full animate-spin mb-3"></div>
        <p class="text-sm text-gray-500">Se încarcă setările...</p>
      </div>

      <!-- Settings Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form Column -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Section 1: Identitate Brand & Antet -->
          <div class="bg-white rounded-2xl border border-border p-6 shadow-sm space-y-6">
            <div class="border-b border-border pb-3">
              <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span>🌿</span> Identitate Brand & Antet
              </h2>
              <p class="text-xs text-gray-500 mt-0.5">
                Aceste date sunt afișate în antetul paginii, titlul site-ului și descrieri.
              </p>
            </div>

            <!-- Logo Field -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                Siglă Magazin (Logo)
              </label>

              <div class="flex items-start gap-4">
                <!-- Logo Preview Box -->
                <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center p-2 overflow-hidden flex-shrink-0 relative group">
                  <img
                    v-if="form.logo_url"
                    :src="form.logo_url"
                    alt="Logo Magazin"
                    class="max-h-full max-w-full object-contain"
                  />
                  <div v-else class="text-center text-gray-400">
                    <div class="w-10 h-10 mx-auto rounded-xl bg-primary flex items-center justify-center text-white">
                      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.3c.48.17.98.3 1.34.3C19 20 22 3 22 3c-1 2-8 2.25-13 3.25S2 11.5 2 13.5s1.75 3.75 1.75 3.75C7 8 17 8 17 8z"/>
                      </svg>
                    </div>
                    <span class="text-[10px] block mt-1 font-medium text-gray-500">Implicit</span>
                  </div>
                </div>

                <!-- Logo Actions -->
                <div class="flex-1 space-y-2">
                  <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleLogoFileChange"
                  />

                  <div class="flex flex-wrap items-center gap-2">
                    <button
                      type="button"
                      @click="triggerLogoUpload"
                      :disabled="uploadingLogo"
                      class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium rounded-lg transition-colors flex items-center gap-1.5"
                    >
                      <span v-if="uploadingLogo" class="w-3 h-3 border-2 border-gray-400 border-t-gray-800 rounded-full animate-spin"></span>
                      <span>{{ uploadingLogo ? 'Se încarcă...' : 'Încarcă Siglă Nouă' }}</span>
                    </button>

                    <button
                      v-if="form.logo_url"
                      type="button"
                      @click="removeLogo"
                      class="px-3 py-1.5 text-rose-600 hover:bg-rose-50 text-xs font-medium rounded-lg transition-colors"
                    >
                      Șterge Sigla (Revino la implicit)
                    </button>
                  </div>

                  <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Sau introduceți direct URL-ul imaginii:</label>
                    <input
                      v-model="form.logo_url"
                      type="text"
                      placeholder="https://... sau /cale/catre/logo.svg"
                      class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-primary focus:border-primary"
                    />
                  </div>

                  <p v-if="logoUploadError" class="text-xs text-rose-600 font-medium">
                    {{ logoUploadError }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Site Title & Tagline -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                  Titlu Magazin / Nume Brand *
                </label>
                <input
                  v-model="form.site_title"
                  type="text"
                  required
                  placeholder="ex: Pepiniera Ciumbrud"
                  class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                  Slogan / Subtitlu Antet
                </label>
                <input
                  v-model="form.site_tagline"
                  type="text"
                  placeholder="ex: Tradiție & Calitate din 1990"
                  class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>
            </div>

            <!-- Site Description -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                Descriere Scurtă (Afișată în Subsol și Meta)
              </label>
              <textarea
                v-model="form.site_description"
                rows="3"
                placeholder="Descrierea activității pepinierei..."
                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
              ></textarea>
            </div>
          </div>

          <!-- Section 2: Date de Contact -->
          <div class="bg-white rounded-2xl border border-border p-6 shadow-sm space-y-6">
            <div class="border-b border-border pb-3">
              <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span>📞</span> Date de Contact
              </h2>
              <p class="text-xs text-gray-500 mt-0.5">
                Informații afișate în pagina Contact, subsolul paginii și meniul mobil.
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                  Număr de Telefon *
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">📞</span>
                  <input
                    v-model="form.contact_phone"
                    type="text"
                    required
                    placeholder="+40 700 000 000"
                    class="w-full text-sm pl-9 pr-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                  Adresă de Email *
                </label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">✉️</span>
                  <input
                    v-model="form.contact_email"
                    type="email"
                    required
                    placeholder="contact@pepinieraciumbrud.ro"
                    class="w-full text-sm pl-9 pr-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                  />
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                Adresă Fizică / Sediu *
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">📍</span>
                <input
                  v-model="form.contact_address"
                  type="text"
                  required
                  placeholder="Sat Ciumbrud, Jud. Alba, România"
                  class="w-full text-sm pl-9 pr-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>
            </div>
          </div>

          <!-- Section 3: Program de Lucru -->
          <div class="bg-white rounded-2xl border border-border p-6 shadow-sm space-y-6">
            <div class="border-b border-border pb-3">
              <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span>🕒</span> Program de Lucru & Asistență
              </h2>
              <p class="text-xs text-gray-500 mt-0.5">
                Programul afișat în pagina Contact și în subsol pentru clienți.
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                  Luni – Vineri
                </label>
                <input
                  v-model="form.schedule_weekdays"
                  type="text"
                  placeholder="Luni – Vineri: 08:00 – 17:00"
                  class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                  Sâmbătă
                </label>
                <input
                  v-model="form.schedule_saturday"
                  type="text"
                  placeholder="Sâmbătă: 08:00 – 13:00"
                  class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                  Duminică
                </label>
                <input
                  v-model="form.schedule_sunday"
                  type="text"
                  placeholder="Duminică: Închis"
                  class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                Notă / Informație suplimentară comenzi
              </label>
              <textarea
                v-model="form.schedule_notes"
                rows="2"
                placeholder="Comenzile se pot plasa direct prin magazinul online la orice oră..."
                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
              ></textarea>
            </div>
          </div>

          <!-- Section 4: Subsol & Drepturi de Autor -->
          <div class="bg-white rounded-2xl border border-border p-6 shadow-sm space-y-6">
            <div class="border-b border-border pb-3">
              <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span>📄</span> Subsol & Drepturi de Autor
              </h2>
              <p class="text-xs text-gray-500 mt-0.5">
                Textul afișat la baza paginilor publice.
              </p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                Text Copyright Subsol
              </label>
              <input
                v-model="form.footer_copyright"
                type="text"
                placeholder="Pepiniera Ciumbrud. Toate drepturile rezervate."
                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary"
              />
            </div>
          </div>
        </div>

        <!-- Right Side Preview Column -->
        <div class="space-y-6">
          <div class="sticky top-6 space-y-6">
            <!-- Header Live Preview Card -->
            <div class="bg-white rounded-2xl border border-border p-5 shadow-sm space-y-4">
              <div class="flex items-center justify-between border-b border-border pb-2.5">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Previzualizare Antet</span>
                <span class="text-[10px] px-2 py-0.5 bg-emerald-50 text-emerald-700 font-medium rounded-full">Live</span>
              </div>

              <!-- Header Simulation -->
              <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                <div class="flex items-center space-x-3">
                  <div class="flex items-center justify-center flex-shrink-0">
                    <img
                      v-if="form.logo_url"
                      :src="form.logo_url"
                      alt="Logo"
                      class="h-10 w-auto object-contain"
                    />
                    <svg v-else class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.3c.48.17.98.3 1.34.3C19 20 22 3 22 3c-1 2-8 2.25-13 3.25S2 11.5 2 13.5s1.75 3.75 1.75 3.75C7 8 17 8 17 8z"/>
                    </svg>
                  </div>
                  <div class="min-w-0">
                    <div class="font-serif font-bold text-gray-900 truncate text-base leading-tight">
                      {{ form.site_title || 'Titlu Magazin' }}
                    </div>
                    <div class="text-[10px] tracking-wider uppercase text-gray-500 font-medium truncate">
                      {{ form.site_tagline || 'Slogan Antet' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact Box Live Preview Card -->
            <div class="bg-white rounded-2xl border border-border p-5 shadow-sm space-y-4">
              <div class="flex items-center justify-between border-b border-border pb-2.5">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Previzualizare Date Contact</span>
                <span class="text-[10px] px-2 py-0.5 bg-emerald-50 text-emerald-700 font-medium rounded-full">Live</span>
              </div>

              <div class="p-4 bg-primary-dark text-white rounded-xl space-y-3 text-xs">
                <div class="font-serif font-bold text-sm text-accent">Contact Direct</div>
                <div class="space-y-1.5 text-white/90">
                  <div class="flex items-center gap-2">
                    <span>📞</span>
                    <span class="font-semibold text-accent-warm">{{ form.contact_phone || 'Fără telefon' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span>✉️</span>
                    <span>{{ form.contact_email || 'Fără email' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span>📍</span>
                    <span class="text-white/80">{{ form.contact_address || 'Fără adresă' }}</span>
                  </div>
                  <div
                    v-if="form.schedule_weekdays || form.schedule_saturday || form.schedule_sunday"
                    class="flex items-start gap-2 text-white/70 text-[11px] pt-1 border-t border-white/10"
                  >
                    <span class="shrink-0 mt-0.5">🕒</span>
                    <div class="space-y-0.5">
                      <div v-if="form.schedule_weekdays" class="text-white/60 text-[10px]">{{ form.schedule_weekdays }}</div>
                      <div v-if="form.schedule_saturday" class="text-white/60 text-[10px]">{{ form.schedule_saturday }}</div>
                      <div v-if="form.schedule_sunday" class="text-white/60 text-[10px]">{{ form.schedule_sunday }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Save floating trigger if dirty -->
            <div v-if="isDirty" class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-amber-800 text-xs flex items-center justify-between">
              <span>⚠️ Aveți modificări nesalvate.</span>
              <button
                @click="handleSave"
                :disabled="saving"
                class="px-3 py-1.5 bg-primary text-white font-medium rounded-lg hover:bg-primary-dark transition-colors"
              >
                Salvează acum
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

