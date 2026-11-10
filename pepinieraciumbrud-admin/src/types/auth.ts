export interface AdminUser {
  id: number
  username: string
  email: string
  first_name: string
  last_name: string
  role: string
}

export interface LoginResponse {
  token: string
  user: AdminUser
  expires_in: number
}

