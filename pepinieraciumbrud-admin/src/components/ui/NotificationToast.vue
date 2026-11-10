<script setup lang="ts">
import { useUiStore } from '@/stores/ui'

const uiStore = useUiStore()
</script>

<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col space-y-2 max-w-sm w-full pointer-events-none">
    <div
      v-for="item in uiStore.notifications"
      :key="item.id"
      class="pointer-events-auto p-4 rounded-xl shadow-lg border text-sm flex items-center justify-between transition-all duration-300"
      :class="{
        'bg-emerald-50 text-emerald-800 border-emerald-200': item.type === 'success',
        'bg-rose-50 text-rose-800 border-rose-200': item.type === 'error',
        'bg-amber-50 text-amber-800 border-amber-200': item.type === 'warning',
        'bg-blue-50 text-blue-800 border-blue-200': item.type === 'info',
      }"
    >
      <span>{{ item.message }}</span>
      <button
        @click="uiStore.removeNotification(item.id)"
        class="ml-3 text-current opacity-60 hover:opacity-100"
      >
        ✕
      </button>
    </div>
  </div>
</template>

