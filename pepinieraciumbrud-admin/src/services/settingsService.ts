import { api } from './api'
import type { SiteSettings, EmailConfigStatus, EmailTestResult } from '@/types/settings'

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
  },

  /**
   * Fetch current SMTP email configuration status.
   */
  async getEmailStatus(): Promise<EmailConfigStatus> {
    const res = await api.get<EmailConfigStatus>('/admin/email/status')
    return res.data
  },

  /**
   * Send test email to verify SMTP configuration.
   */
  async sendTestEmail(to?: string): Promise<EmailTestResult> {
    const res = await api.post<EmailTestResult>('/admin/email/test', { to })
    return res.data
  }
}
