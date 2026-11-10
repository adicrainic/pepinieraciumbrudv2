import { api } from './api'
import type { Order } from '@/types/order'

export const ordersService = {
  async list(params: Record<string, any> = {}): Promise<Order[]> {
    const res = await api.get<Order[]>('/admin/orders', { params })
    return res.data
  },

  async get(id: number): Promise<Order> {
    const res = await api.get<Order>(`/admin/orders/${id}`)
    return res.data
  },

  async updateStatus(id: number, status: string): Promise<Order> {
    const res = await api.put<Order>(`/admin/orders/${id}`, { status })
    return res.data
  },

  async getNotes(id: number): Promise<any[]> {
    const res = await api.get<any[]>(`/admin/orders/${id}/notes`)
    return res.data
  },

  async addNote(id: number, note: string, customerNote: boolean = false): Promise<any> {
    const res = await api.post<any>(`/admin/orders/${id}/notes`, { note, customer_note: customerNote })
    return res.data
  }
}

