<script setup lang="ts">
// Pepiniera Ciumbrud emblem: a "P" fused with an "S" curve, topped by a rose.
// Strokes are vector (recolorable via currentColor); the rose is a transparent PNG.
withDefaults(defineProps<{ rose?: boolean }>(), { rose: true })
</script>

<template>
  <svg viewBox="296 172 448 676" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <g fill="none" stroke="currentColor" stroke-width="25" stroke-linecap="round">
      <path d="M565 272 A144 109 0 0 1 565 490 C440 490 358 550 358 650 C358 760 430 828 526 828 C612 828 668 772 688 692" />
      <path d="M468 340 V712" />
    </g>
    <path d="M457 700 H479 L486 745 H450 Z" fill="currentColor" />
    <image v-if="rose" href="/logo-rose.png" x="300" y="175" width="290" height="225" />
  </svg>
</template>
