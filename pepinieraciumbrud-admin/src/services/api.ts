import axios from 'axios'

const API_BASE = import.meta.env.VITE_API_BASE || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json/pcs/v1'

export const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Attach JWT token automatically
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('pcs_admin_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Intercept 401 and redirect to login
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('pcs_admin_token')
      localStorage.removeItem('pcs_admin_user')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

