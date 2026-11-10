<script setup lang="ts">
import AdminSidebar from '@/components/layout/AdminSidebar.vue'
import AdminTopbar from '@/components/layout/AdminTopbar.vue'
import NotificationToast from '@/components/ui/NotificationToast.vue'
</script>

<template>
  <div class="min-h-screen flex bg-background">
    <AdminSidebar />

    <div class="flex-1 flex flex-col min-w-0">
      <AdminTopbar />

      <main class="flex-1 p-6 sm:p-8 overflow-y-auto">
        <slot />
      </main>
    </div>

    <NotificationToast />
  </div>
</template>

