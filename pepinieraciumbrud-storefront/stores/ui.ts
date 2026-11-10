import { defineStore } from 'pinia'

export const useUiStore = defineStore('ui', {
  state: () => ({
    mobileMenuOpen: false,
    searchOpen: false,
    notification: null as { type: 'success' | 'error' | 'info'; message: string } | null
  }),

  actions: {
    toggleMobileMenu() {
      this.mobileMenuOpen = !this.mobileMenuOpen
    },
    closeMobileMenu() {
      this.mobileMenuOpen = false
    },
    toggleSearch() {
      this.searchOpen = !this.searchOpen
    },
    closeSearch() {
      this.searchOpen = false
    },
    showNotification(message: string, type: 'success' | 'error' | 'info' = 'success') {
      this.notification = { message, type }
      setTimeout(() => {
        this.notification = null
      }, 4000)
    }
  }
})

