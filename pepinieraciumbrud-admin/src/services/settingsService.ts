import { api } from './api'
import type { SiteSettings } from '@/types/settings'

export const settingsService = {
  /**
   * Fetch current site settings from backend.
   */
  async getSettings(): Promise<SiteSettings> {
    const res = await api.get<SiteSettings>('/admin/settings')
    return res.data
  },

  /**
   * Update site settings.
   */
  async updateSettings(data: Partial<SiteSettings>): Promise<{ message: string; settings: SiteSettings }> {
    const res = await api.post<{ message: string; settings: SiteSettings }>('/admin/settings', data)
    return res.data
  }
}

