import { defineStore } from 'pinia'

export interface ToastNotification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  message: string
}

export const useUiStore = defineStore('ui', {
  state: () => ({
    sidebarCollapsed: false,
    notifications: [] as ToastNotification[]
  }),

  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
    },

    addNotification(type: 'success' | 'error' | 'warning' | 'info', message: string) {
      const id = Math.random().toString(36).substring(2, 9)
      this.notifications.push({ id, type, message })
      setTimeout(() => {
        this.removeNotification(id)
      }, 4000)
    },

    removeNotification(id: string) {
      this.notifications = this.notifications.filter(n => n.id !== id)
    }
  }
})

