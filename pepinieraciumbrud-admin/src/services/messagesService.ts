import { api } from './api'
import type { ContactMessage, ContactMessagesResponse, MessageFilters, MessageStatus, ReplyPayload } from '@/types/message'

export const messagesService = {
  /**
   * List contact messages with pagination and filtering.
   */
  async list(filters: MessageFilters = {}): Promise<ContactMessagesResponse> {
    const res = await api.get<ContactMessagesResponse>('/admin/contact-messages', {
      params: filters
    })
    return res.data
  },

  /**
   * Get single message by ID.
   */
  async get(id: number): Promise<ContactMessage> {
    const res = await api.get<ContactMessage>(`/admin/contact-messages/${id}`)
    return res.data
  },

  /**
   * Reply to a contact message and send email to sender.
   */
  async reply(id: number, payload: ReplyPayload): Promise<{ success: boolean; message: string; email_sent: boolean; item: ContactMessage }> {
    const res = await api.post<{ success: boolean; message: string; email_sent: boolean; item: ContactMessage }>(`/admin/contact-messages/${id}/reply`, payload)
    return res.data
  },

  /**
   * Update message status (unread, read, replied, archived).
   */
  async updateStatus(id: number, status: MessageStatus): Promise<{ message: string; item: ContactMessage }> {
    const res = await api.patch<{ message: string; item: ContactMessage }>(`/admin/contact-messages/${id}`, { status })
    return res.data
  },

  /**
   * Delete contact message.
   */
  async delete(id: number): Promise<{ message: string; id: number }> {
    const res = await api.delete<{ message: string; id: number }>(`/admin/contact-messages/${id}`)
    return res.data
  }
}

