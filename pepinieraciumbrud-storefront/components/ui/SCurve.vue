<script setup lang="ts">
// The "reversed S" from the Pepiniera Ciumbrud logo, drawn as a single monoline stroke.
withDefaults(defineProps<{ stem?: boolean; rose?: boolean; width?: number }>(), {
  stem: true,
  rose: false,
  width: 2
})
</script>

<template>
  <svg viewBox="0 0 200 300" fill="none" stroke="currentColor" stroke-linecap="round" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
    <path d="M100 20 A65 65 0 0 1 100 150 A65 65 0 0 0 100 280 H150" :stroke-width="width" vector-effect="non-scaling-stroke" />
    <path v-if="stem" d="M100 20 V280" :stroke-width="width" vector-effect="non-scaling-stroke" />
    <circle v-if="rose" cx="100" cy="14" r="9" fill="#C8102E" stroke="none" />
  </svg>
</template>
