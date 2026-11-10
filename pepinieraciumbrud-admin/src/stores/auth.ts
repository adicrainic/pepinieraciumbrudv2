import { defineStore } from 'pinia'
import { authService } from '@/services/authService'
import type { AdminUser } from '@/types/auth'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as AdminUser | null,
    token: null as string | null,
    loading: false
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    fullName: (state) => {
      if (!state.user) return 'Administrator'
      const name = `${state.user.first_name || ''} ${state.user.last_name || ''}`.trim()
      return name || state.user.username || 'Administrator'
    }
  },

  actions: {
    initFromStorage() {
      const token = localStorage.getItem('pcs_admin_token')
      const userStr = localStorage.getItem('pcs_admin_user')
      if (token) {
        this.token = token
        if (userStr) {
          try {
            this.user = JSON.parse(userStr)
          } catch (e) {
            // ignore
          }
        }
      }
    },

    async login(username: string, password: string) {
      this.loading = true
      try {
        const response = await authService.login(username, password)
        this.token = response.token
        this.user = response.user
        localStorage.setItem('pcs_admin_token', response.token)
        localStorage.setItem('pcs_admin_user', JSON.stringify(response.user))
        return response
      } finally {
        this.loading = false
      }
    },

    async logout() {
      await authService.logout()
      this.user = null
      this.token = null
    }
  }
})

