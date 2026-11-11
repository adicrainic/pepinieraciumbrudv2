<script setup lang="ts">
interface Props {
  variant?: 'success' | 'warning' | 'danger' | 'info' | 'neutral'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'neutral'
})

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'success':
      return 'bg-leaf-50 text-leaf-700 border-leaf-100'
    case 'warning':
      return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'danger':
      return 'bg-rose-50 text-rose-700 border-rose-200'
    case 'info':
      return 'bg-blue-50 text-blue-700 border-blue-200'
    case 'neutral':
    default:
      return 'bg-surface-warm text-text-muted border-border'
  }
})
</script>

<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
    :class="variantClasses"
  >
    <slot />
  </span>
</template>

