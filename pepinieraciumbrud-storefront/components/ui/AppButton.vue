<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'warm'
  size?: 'sm' | 'md' | 'lg'
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  loading?: boolean
  to?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  type: 'button',
  disabled: false,
  loading: false,
  to: undefined
})

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-primary text-white hover:bg-primary-dark shadow-sm hover:shadow-soft active:scale-[0.98]'
    case 'secondary':
      return 'bg-surface-warm text-primary-dark hover:bg-primary-100/70 active:scale-[0.98]'
    case 'outline':
      return 'border border-primary text-primary hover:bg-primary hover:text-white active:scale-[0.98]'
    case 'ghost':
      return 'text-primary hover:bg-primary-50 active:scale-[0.98]'
    case 'warm':
      return 'bg-rose-500 text-white hover:bg-rose-600 shadow-sm active:scale-[0.98]'
    default:
      return 'bg-primary text-white hover:bg-primary-dark active:scale-[0.98]'
  }
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-3.5 py-1.5 text-xs rounded-full'
    case 'lg':
      return 'px-6 py-3 text-base rounded-full font-medium'
    case 'md':
    default:
      return 'px-5 py-2 text-sm rounded-full font-medium'
  }
})
</script>

<template>
  <NuxtLink
    v-if="to"
    :to="to"
    class="inline-flex items-center justify-center transition-all duration-200 cursor-pointer text-center select-none"
    :class="[variantClasses, sizeClasses, { 'opacity-50 pointer-events-none': disabled || loading }]"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4 text-current"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
    </svg>
    <slot />
  </NuxtLink>

  <button
    v-else
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center transition-all duration-200 cursor-pointer text-center select-none"
    :class="[variantClasses, sizeClasses, { 'opacity-50 cursor-not-allowed': disabled || loading }]"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4 text-current"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
    </svg>
    <slot />
  </button>
</template>

