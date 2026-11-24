import axios from 'axios'

const API_BASE = import.meta.env.VITE_API_BASE || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json/pcs/v1'

type RequestCountListener = (count: number) => void
let activeAdminRequests = 0
const adminListeners = new Set<RequestCountListener>()

const notifyAdminListeners = () => {
  adminListeners.forEach((fn) => {
    try {
      fn(activeAdminRequests)
    } catch {
      // ignore
    }
  })
}

export const onAdminRequestCountChange = (fn: RequestCountListener) => {
  adminListeners.add(fn)
  fn(activeAdminRequests)
  return () => {
    adminListeners.delete(fn)
  }
}

export const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Attach JWT token automatically & track request start
api.interceptors.request.use(
  (config) => {
    activeAdminRequests++
    notifyAdminListeners()
    const token = localStorage.getItem('pcs_admin_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    activeAdminRequests = Math.max(0, activeAdminRequests - 1)
    notifyAdminListeners()
    return Promise.reject(error)
  }
)

// Intercept 401 and redirect to login & track request completion
api.interceptors.response.use(
  (response) => {
    activeAdminRequests = Math.max(0, activeAdminRequests - 1)
    notifyAdminListeners()
    return response
  },
  (error) => {
    activeAdminRequests = Math.max(0, activeAdminRequests - 1)
    notifyAdminListeners()
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

