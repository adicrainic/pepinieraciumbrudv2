import { api } from './api'
import type { Product } from '@/types/product'

export const productsService = {
  async list(params: Record<string, any> = {}): Promise<Product[]> {
    const res = await api.get<Product[]>('/admin/products', { params })
    return res.data
  },

  async get(id: number): Promise<Product> {
    const res = await api.get<Product>(`/admin/products/${id}`)
    return res.data
  },

  async create(data: Partial<Product>): Promise<Product> {
    const res = await api.post<Product>('/admin/products', data)
    return res.data
  },

  async update(id: number, data: Partial<Product>): Promise<Product> {
    const res = await api.put<Product>(`/admin/products/${id}`, data)
    return res.data
  },

  async delete(id: number): Promise<{ deleted: boolean; id: number }> {
    const res = await api.delete<{ deleted: boolean; id: number }>(`/admin/products/${id}`)
    return res.data
  }
}

