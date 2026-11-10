import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guestOnly: true }
    },
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/produse',
      name: 'products',
      component: () => import('@/views/products/ProductsListView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/produse/nou',
      name: 'product-create',
      component: () => import('@/views/products/ProductCreateView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/produse/:id',
      name: 'product-edit',
      component: () => import('@/views/products/ProductEditView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/categorii',
      name: 'categories',
      component: () => import('@/views/categories/CategoriesListView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/comenzi',
      name: 'orders',
      component: () => import('@/views/orders/OrdersListView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/comenzi/:id',
      name: 'order-detail',
      component: () => import('@/views/orders/OrderDetailView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/clienti',
      name: 'customers',
      component: () => import('@/views/customers/CustomersListView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/clienti/:id',
      name: 'customer-detail',
      component: () => import('@/views/customers/CustomerDetailView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/inventar',
      name: 'inventory',
      component: () => import('@/views/inventory/InventoryView.vue'),
      meta: { requiresAuth: true }
    }
  ]
})

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()
  authStore.initFromStorage()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
  } else if (to.meta.guestOnly && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router

