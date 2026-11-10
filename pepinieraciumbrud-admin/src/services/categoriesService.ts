import { api } from './api'
import type { Category } from '@/types/category'

export const categoriesService = {
  async list(): Promise<Category[]> {
    const res = await api.get<Category[]>('/admin/categories')
    return res.data
  },

  async get(id: number): Promise<Category> {
    const res = await api.get<Category>(`/admin/categories/${id}`)
    return res.data
  },

  async create(data: Partial<Category>): Promise<Category> {
    const res = await api.post<Category>('/admin/categories', data)
    return res.data
  },

  async update(id: number, data: Partial<Category>): Promise<Category> {
    const res = await api.put<Category>(`/admin/categories/${id}`, data)
    return res.data
  },

  async delete(id: number): Promise<{ deleted: boolean; id: number }> {
    const res = await api.delete<{ deleted: boolean; id: number }>(`/admin/categories/${id}`)
    return res.data
  }
}

