import { api } from './api'

export interface MediaItem {
  id: number
  url: string
  src: string
  title: string
  alt?: string
  mime_type?: string
  width?: number | null
  height?: number | null
  date?: string
}

export interface MediaListResponse {
  items: MediaItem[]
  total: number
  total_pages: number
  page: number
  per_page: number
}

export const mediaService = {
  /**
   * Upload an image file locally to the WordPress server.
   */
  async upload(file: File, onProgress?: (percent: number) => void): Promise<MediaItem> {
    const formData = new FormData()
    formData.append('file', file)

    const res = await api.post<MediaItem>('/admin/media/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        if (onProgress && progressEvent.total) {
          const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total)
          onProgress(percent)
        }
      }
    })
    return res.data
  },

  /**
   * List existing images from the WordPress media library.
   */
  async list(params: { page?: number; per_page?: number; search?: string } = {}): Promise<MediaListResponse> {
    const res = await api.get<MediaListResponse>('/admin/media', { params })
    return res.data
  }
}

