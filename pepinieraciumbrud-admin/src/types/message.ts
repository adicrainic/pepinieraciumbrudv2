export type MessageStatus = 'unread' | 'read' | 'replied' | 'archived'

export interface ContactMessage {
  id: number
  name: string
  email: string
  phone: string
  message: string
  status: MessageStatus
  email_sent: boolean | number
  ip_address?: string
  user_agent?: string
  created_at: string
  read_at?: string | null
  reply_message?: string | null
  replied_at?: string | null
  replied_by?: string | null
}

export interface ContactMessagesResponse {
  items: ContactMessage[]
  total: number
  page: number
  per_page: number
  total_pages: number
  unread_count: number
}

export interface MessageFilters {
  page?: number
  per_page?: number
  status?: string
  search?: string
}

export interface ReplyPayload {
  message: string
  subject?: string
}
