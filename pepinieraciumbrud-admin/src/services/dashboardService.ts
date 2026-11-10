import { api } from './api'

export const dashboardService = {
  async getDashboard(): Promise<any> {
    const res = await api.get('/admin/dashboard')
    return res.data
  },

  async getInventory(params: Record<string, any> = {}): Promise<any[]> {
    const res = await api.get('/admin/inventory', { params })
    return res.data
  },

  async updateStock(
    id: number,
    data: {
      stock_quantity?: number | null
      low_stock_amount?: number | null
      stock_status?: string
      manage_stock?: boolean
    }
  ): Promise<any> {
    const res = await api.put(`/admin/inventory/${id}`, data)
    return res.data
  }
}

