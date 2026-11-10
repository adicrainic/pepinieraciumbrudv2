<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import AppButton from '~/components/ui/AppButton.vue'

definePageMeta({
  layout: 'minimal'
})

const route = useRoute()
const orderId = route.query.order || 'N/A'

const { setPageSeo } = useSeo()
setPageSeo({
  title: 'Comandă Confirmată',
  description: 'Vă mulțumim pentru comanda plasată la Pepiniera Ciumbrud.'
})
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-3xl mx-auto mb-6">
      ✓
    </div>

    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark mb-4">
      Vă mulțumim! Comanda a fost înregistrată.
    </h1>

    <p class="text-sm text-text-muted mb-2">
      Număr referință comandă: <span class="font-bold text-text">#{{ orderId }}</span>
    </p>

    <p class="text-sm text-text-muted max-w-md mx-auto mb-8 leading-relaxed">
      Echipa noastră va pregăti cu atenție plantele pentru ambalare și expediere. Veți fi contactat telefonic înainte de predarea coletului către curier.
    </p>

    <div class="bg-white p-6 rounded-3xl border border-border shadow-sm text-left text-xs space-y-3 mb-8">
      <h3 class="font-bold text-text font-serif text-sm">Ce urmează acum?</h3>
      <div class="flex items-start space-x-3 text-text-muted">
        <span class="text-primary font-bold">1.</span>
        <span>Verificăm calitatea plantelor și pregătim balotul de pământ umed pentru rădăcini.</span>
      </div>
      <div class="flex items-start space-x-3 text-text-muted">
        <span class="text-primary font-bold">2.</span>
        <span>Plantele sunt ambalate în saci speciali respirați și cutii de carton rigidizat.</span>
      </div>
      <div class="flex items-start space-x-3 text-text-muted">
        <span class="text-primary font-bold">3.</span>
        <span>Curierul livrează comanda direct la adresa dumneavoastră.</span>
      </div>
    </div>

    <AppButton to="/" variant="primary" size="lg">
      Înapoi la Pagina Principală
    </AppButton>
  </div>
</template>

