<template>
  <div class="min-h-screen flex flex-col bg-background">
    <header class="py-6 border-b border-border bg-white text-center">
      <NuxtLink to="/" class="inline-flex items-center space-x-2">
        <span class="text-2xl">🌿</span>
        <span class="font-serif text-2xl font-bold text-primary-dark">Pepiniera Ciumbrud</span>
      </NuxtLink>
    </header>

    <main class="flex-1 py-12">
      <slot />
    </main>

    <footer class="py-6 border-t border-border bg-white text-center text-xs text-text-muted">
      © {{ new Date().getFullYear() }} Pepiniera Ciumbrud. Plăți securizate & Date protejate.
    </footer>
  </div>
</template>

