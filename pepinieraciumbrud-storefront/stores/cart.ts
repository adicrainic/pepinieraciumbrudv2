import { defineStore } from 'pinia'
import type { Cart, CartItem } from '~/types/cart'
import { storeApi } from '~/services/storeApi'

export const useCartStore = defineStore('cart', {
  state: () => ({
    cart: null as Cart | null,
    isOpen: false,
    loading: false,
    error: null as string | null
  }),

  getters: {
    itemCount: (state) => {
      if (!state.cart || !state.cart.items) return 0
      return state.cart.items.reduce((total, item) => total + item.quantity, 0)
    },

    subtotal: (state) => {
      if (!state.cart || !state.cart.totals) return '0.00'
      const price = parseFloat(state.cart.totals.total_items || '0')
      const minorUnit = state.cart.totals.currency_minor_unit ?? 2
      return (price / Math.pow(10, minorUnit)).toFixed(2)
    },

    total: (state) => {
      if (!state.cart || !state.cart.totals) return '0.00'
      const price = parseFloat(state.cart.totals.total_price || '0')
      const minorUnit = state.cart.totals.currency_minor_unit ?? 2
      return (price / Math.pow(10, minorUnit)).toFixed(2)
    },

    formattedTotal: (state) => {
      if (!state.cart || !state.cart.totals) return '0,00 lei'
      const price = parseFloat(state.cart.totals.total_price || '0')
      const minorUnit = state.cart.totals.currency_minor_unit ?? 2
      const val = (price / Math.pow(10, minorUnit)).toFixed(2).replace('.', ',')
      return `${val} lei`
    },

    formattedSubtotal: (state) => {
      if (!state.cart || !state.cart.totals) return '0,00 lei'
      const price = parseFloat(state.cart.totals.total_items || '0')
      const minorUnit = state.cart.totals.currency_minor_unit ?? 2
      const val = (price / Math.pow(10, minorUnit)).toFixed(2).replace('.', ',')
      return `${val} lei`
    },

    hasShippingCost: (state): boolean => {
      if (!state.cart || !state.cart.totals) return false
      return parseFloat(state.cart.totals.total_shipping || '0') > 0
    },

    formattedShipping: (state): string => {
      if (!state.cart || !state.cart.totals) return '0,00 lei'
      const price = parseFloat(state.cart.totals.total_shipping || '0')
      const minorUnit = state.cart.totals.currency_minor_unit ?? 2
      const val = (price / Math.pow(10, minorUnit)).toFixed(2).replace('.', ',')
      return `${val} lei`
    },

    items: (state): CartItem[] => {
      return state.cart?.items || []
    },

    getItemQuantity: (state) => {
      return (productId: number): number => {
        if (!state.cart || !state.cart.items) return 0
        const found = state.cart.items.find((item) => item.id === productId)
        return found ? found.quantity : 0
      }
    }
  },

  actions: {
    async fetchCart() {
      this.loading = true
      this.error = null
      try {
        this.cart = await storeApi.getCart()
      } catch (err: any) {
        this.error = err.message || 'Eroare la încărcarea coșului.'
      } finally {
        this.loading = false
      }
    },

    clearLocalCart() {
      if (this.cart) {
        this.cart.items = []
        this.cart.items_count = 0
        if (this.cart.totals) {
          this.cart.totals.total_items = '0'
          this.cart.totals.total_price = '0'
        }
      }
      this.isOpen = false
    },

    async addItem(productId: number, quantity: number = 1) {
      this.loading = true
      this.error = null
      try {
        this.cart = await storeApi.addToCart(productId, quantity)
        this.isOpen = true
      } catch (err: any) {
        this.error = err.message || 'Nu s-a putut adăuga produsul în coș.'
      } finally {
        this.loading = false
      }
    },

    async updateItem(key: string, quantity: number) {
      this.loading = true
      this.error = null
      try {
        if (quantity <= 0) {
          await this.removeItem(key)
        } else {
          this.cart = await storeApi.updateCartItem(key, quantity)
        }
      } catch (err: any) {
        this.error = err.data?.message || err.message || 'Eroare la actualizarea cantității.'
      } finally {
        this.loading = false
      }
    },

    async removeItem(key: string) {
      this.loading = true
      this.error = null
      try {
        this.cart = await storeApi.removeCartItem(key)
      } catch (err: any) {
        this.error = err.message || 'Eroare la ștergerea produsului.'
      } finally {
        this.loading = false
      }
    },

    toggleDrawer() {
      this.isOpen = !this.isOpen
    },

    openDrawer() {
      this.isOpen = true
    },

    closeDrawer() {
      this.isOpen = false
    }
  }
})

