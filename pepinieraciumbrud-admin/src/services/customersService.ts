import { api } from './api'
import type { Customer } from '@/types/customer'
import type { Order } from '@/types/order'

export const customersService = {
  async list(params: Record<string, any> = {}): Promise<Customer[]> {
    const res = await api.get<Customer[]>('/admin/customers', { params })
    return res.data
  },

  async get(id: number): Promise<Customer> {
    const res = await api.get<Customer>(`/admin/customers/${id}`)
    return res.data
  },

  async getOrders(id: number): Promise<Order[]> {
    const res = await api.get<Order[]>(`/admin/customers/${id}/orders`)
    return res.data
  }
}

