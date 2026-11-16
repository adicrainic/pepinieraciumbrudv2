<script setup lang="ts">
import { useSiteSettings } from '~/composables/useSiteSettings'

const { fetchSettings } = useSiteSettings()
await useAsyncData('site-settings', () => fetchSettings())
</script>

<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>


