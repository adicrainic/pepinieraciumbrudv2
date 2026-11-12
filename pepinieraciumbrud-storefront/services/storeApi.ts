import type { Product } from '~/types/product'
import type { Category } from '~/types/category'
import type { Cart } from '~/types/cart'

export class StoreApiService {
  private getApiBase(): string {
    const config = useRuntimeConfig()
    return config.public.apiBase || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json/wc/store/v1'
  }

  private getNonce(): string {
    if (import.meta.client) {
      return localStorage.getItem('wc_store_nonce') || ''
    }
    return ''
  }

  private setNonce(nonce: string) {
    if (import.meta.client && nonce) {
      localStorage.setItem('wc_store_nonce', nonce)
    }
  }

  private getCartToken(): string {
    if (import.meta.client) {
      return localStorage.getItem('wc_cart_token') || ''
    }
    return ''
  }

  private setCartToken(token: string) {
    if (import.meta.client && token) {
      localStorage.setItem('wc_cart_token', token)
    }
  }

  private async fetchWithHeaders<T>(url: string, options: any = {}, requireSession: boolean = false): Promise<T> {
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      ...(options.headers || {})
    }

    if (requireSession) {
      const nonce = this.getNonce()
      const cartToken = this.getCartToken()
      if (nonce) {
        headers['Nonce'] = nonce
      }
      if (cartToken) {
        headers['Cart-Token'] = cartToken
      }
    }

    try {
      const response = await $fetch.raw<T>(url, {
        ...options,
        headers,
        credentials: 'include'
      })

      const newNonce = response.headers.get('Nonce') || response.headers.get('X-WC-Store-API-Nonce')
      if (newNonce) {
        this.setNonce(newNonce)
      }

      const newCartToken = response.headers.get('Cart-Token')
      if (newCartToken) {
        this.setCartToken(newCartToken)
      }

      return response._data as T
    } catch (err: any) {
      console.warn(`[StoreApi] Request failed for ${url}:`, err.message || err)
      throw err
    }
  }

  async getProducts(params: Record<string, any> = {}): Promise<Product[]> {
    const base = this.getApiBase()
    const query = new URLSearchParams()
    Object.entries(params).forEach(([k, v]) => {
      if (v !== undefined && v !== null && v !== '') {
        query.append(k, String(v))
      }
    })
    const url = `${base}/products${query.toString() ? `?${query.toString()}` : ''}`
    return this.fetchWithHeaders<Product[]>(url, {}, false)
  }

  async getProduct(id: number): Promise<Product> {
    const base = this.getApiBase()
    return this.fetchWithHeaders<Product>(`${base}/products/${id}`, {}, false)
  }

  async getProductBySlug(slug: string): Promise<Product | null> {
    const base = this.getApiBase()
    const products = await this.fetchWithHeaders<Product[]>(`${base}/products?slug=${encodeURIComponent(slug)}`, {}, false)
    return products && products.length > 0 ? products[0] : null
  }

  async getCategories(params: Record<string, any> = {}): Promise<Category[]> {
    const base = this.getApiBase()
    const query = new URLSearchParams()
    const finalParams = { per_page: 100, hide_empty: false, ...params }
    Object.entries(finalParams).forEach(([k, v]) => {
      if (v !== undefined && v !== null && v !== '') {
        query.append(k, String(v))
      }
    })
    const url = `${base}/products/categories${query.toString() ? `?${query.toString()}` : ''}`
    return this.fetchWithHeaders<Category[]>(url, {}, false)
  }

  async getCart(): Promise<Cart> {
    const base = this.getApiBase()
    return this.fetchWithHeaders<Cart>(`${base}/cart`, {}, true)
  }

  async addToCart(productId: number, quantity: number = 1): Promise<Cart> {
    const base = this.getApiBase()
    return this.fetchWithHeaders<Cart>(`${base}/cart/add-item`, {
      method: 'POST',
      body: {
        id: productId,
        quantity
      }
    }, true)
  }

  async updateCartItem(itemKey: string, quantity: number): Promise<Cart> {
    const base = this.getApiBase()
    return this.fetchWithHeaders<Cart>(`${base}/cart/update-item`, {
      method: 'POST',
      body: {
        key: itemKey,
        quantity
      }
    }, true)
  }

  async removeCartItem(itemKey: string): Promise<Cart> {
    const base = this.getApiBase()
    return this.fetchWithHeaders<Cart>(`${base}/cart/remove-item`, {
      method: 'POST',
      body: {
        key: itemKey
      }
    }, true)
  }

  async checkout(payload: any): Promise<any> {
    const base = this.getApiBase()
    return this.fetchWithHeaders<any>(`${base}/checkout`, {
      method: 'POST',
      body: payload
    }, true)
  }
}

export const storeApi = new StoreApiService()

