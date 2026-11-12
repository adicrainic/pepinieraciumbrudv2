<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const uiStore = useUiStore()
const router = useRouter()

const navItems = [
  { name: 'Dashboard', path: '/dashboard', icon: '📊' },
  { name: 'Produse', path: '/produse', icon: '🍏' },
  { name: 'Categorii', path: '/categorii', icon: '🏷️' },
  { name: 'Comenzi', path: '/comenzi', icon: '📦' },
  { name: 'Mesaje', path: '/mesaje', icon: '💬' },
  { name: 'Inventar', path: '/inventar', icon: '📋' },
  { name: 'Setări', path: '/setari', icon: '⚙️' },
]

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <aside
    class="bg-sidebar text-white transition-all duration-300 flex flex-col z-30"
    :class="uiStore.sidebarCollapsed ? 'w-20' : 'w-64'"
  >
    <!-- Brand -->
    <div class="h-16 flex items-center px-6 border-b border-white/10 gap-3">
      <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center flex-shrink-0 overflow-hidden">
        <img src="/logo.jpg" alt="Pepiniera Ciumbrud" class="w-full h-full object-contain p-0.5" />
      </div>
      <div v-if="!uiStore.sidebarCollapsed" class="overflow-hidden whitespace-nowrap">
        <h1 class="font-serif font-bold text-sm tracking-wide">Pepiniera Ciumbrud</h1>
        <p class="text-[10px] text-white/60 uppercase">Admin Panel</p>
      </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto">
      <RouterLink
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors"
        :class="[
          $route.path.startsWith(item.path)
            ? 'bg-white/15 text-white font-semibold'
            : 'text-white/70 hover:bg-white/10 hover:text-white'
        ]"
      >
        <span class="text-lg flex-shrink-0">{{ item.icon }}</span>
        <span v-if="!uiStore.sidebarCollapsed" class="truncate">{{ item.name }}</span>
      </RouterLink>
    </nav>

    <!-- User / Logout Footer -->
    <div class="p-4 border-t border-white/10">
      <div v-if="!uiStore.sidebarCollapsed" class="mb-3 px-2">
        <div class="text-xs font-semibold text-white truncate">{{ authStore.fullName }}</div>
        <div class="text-[10px] text-white/50 truncate">{{ authStore.user?.email || 'admin' }}</div>
      </div>
      <button
        @click="handleLogout"
        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-300 hover:bg-rose-500/20 transition-colors"
      >
        <span>🚪</span>
        <span v-if="!uiStore.sidebarCollapsed">Deconectare</span>
      </button>
    </div>
  </aside>
</template>

