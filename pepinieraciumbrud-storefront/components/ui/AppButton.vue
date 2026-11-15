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
      return 'bg-primary text-white border border-primary hover:bg-primary-dark hover:border-primary-dark shadow-sm hover:shadow-md active:scale-[0.98]'
    case 'secondary':
      return 'bg-surface-warm text-primary-dark border border-border hover:border-primary hover:bg-white active:scale-[0.98]'
    case 'outline':
      return 'border border-primary text-primary hover:bg-primary hover:text-white active:scale-[0.98]'
    case 'ghost':
      return 'text-primary hover:bg-primary-50 active:scale-[0.98]'
    case 'warm':
      return 'bg-accent text-white border border-accent hover:bg-primary-dark hover:border-primary-dark shadow-sm hover:shadow-md active:scale-[0.98]'
    default:
      return 'bg-primary text-white border border-primary hover:bg-primary-dark hover:border-primary-dark shadow-sm hover:shadow-md active:scale-[0.98]'
  }
})

const sizeClasses = computed(() => {
  // Square-ish with one "petal" corner, echoing the logo's curves
  switch (props.size) {
    case 'sm':
      return 'px-4 py-2 text-xs uppercase tracking-[0.16em] rounded-tr-xl rounded-bl-xl font-medium'
    case 'lg':
      return 'px-8 py-4 text-sm uppercase tracking-[0.2em] rounded-tr-[1.25rem] rounded-bl-[1.25rem] font-medium'
    case 'md':
    default:
      return 'px-6 py-3 text-xs sm:text-sm uppercase tracking-[0.18em] rounded-tr-2xl rounded-bl-2xl font-medium'
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

