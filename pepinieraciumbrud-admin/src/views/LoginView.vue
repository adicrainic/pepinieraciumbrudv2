<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const username = ref('pepinieraciumbrud')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const handleLogin = async () => {
  if (!username.value || !password.value) {
    errorMessage.value = 'Completați utilizatorul și parola.'
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    await authStore.login(username.value, password.value)
    router.push('/dashboard')
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Date de autentificare incorecte.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[#F3F4F6] p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-gray-200 p-8 sm:p-10">
      <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl bg-primary text-white flex items-center justify-center text-3xl mx-auto mb-4 shadow-md">
          🌿
        </div>
        <h1 class="font-serif text-2xl font-bold text-gray-900">
          Pepiniera Ciumbrud
        </h1>
        <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">
          Panou de Administrare Headless
        </p>
      </div>

      <div v-if="errorMessage" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1.5">
            Utilizator WordPress
          </label>
          <input
            v-model="username"
            type="text"
            required
            class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white text-gray-900"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1.5">
            Parolă
          </label>
          <input
            v-model="password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white text-gray-900"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary-dark transition-colors shadow-sm disabled:opacity-50"
        >
          <span v-if="!loading">Autentificare în Dashboard</span>
          <span v-else>Se verifică...</span>
        </button>
      </form>

      <div class="mt-8 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
        Conexiune securizată prin API Proxy JWT • WordPress 7.1.4
      </div>
    </div>
  </div>
</template>

