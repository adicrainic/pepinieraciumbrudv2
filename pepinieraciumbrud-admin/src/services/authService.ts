import { api } from './api'
import type { LoginResponse, AdminUser } from '@/types/auth'

export const authService = {
  async login(username: string, password: string): Promise<LoginResponse> {
    const res = await api.post<LoginResponse>('/admin/auth/login', { username, password })
    return res.data
  },

  async logout(): Promise<void> {
    try {
      await api.post('/admin/auth/logout')
    } finally {
      localStorage.removeItem('pcs_admin_token')
      localStorage.removeItem('pcs_admin_user')
    }
  },

  async getMe(): Promise<AdminUser> {
    const res = await api.get<AdminUser>('/admin/auth/me')
    return res.data
  },

  getToken(): string | null {
    return localStorage.getItem('pcs_admin_token')
  },

  isAuthenticated(): boolean {
    return !!localStorage.getItem('pcs_admin_token')
  }
}

