<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'

const { setPageSeo } = useSeo()
setPageSeo({
  title: 'Termeni și Condiții',
  description: 'Termenii și condițiile de utilizare și achiziție pe platforma Pepiniera Ciumbrud.'
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="font-serif text-3xl font-bold text-primary-dark mb-8">
      Termeni și Condiții
    </h1>

    <div class="bg-white rounded-3xl border border-border p-8 sm:p-12 shadow-sm space-y-6 text-sm text-text-muted leading-relaxed">
      <p>
        Bine ați venit pe site-ul oficial Pepiniera Ciumbrud. Prin utilizarea acestui site și plasarea unei comenzi, sunteți de acord cu termenii și condițiile detaliate mai jos.
      </p>

      <h3 class="font-serif text-lg font-bold text-text">1. Condiții Generale</h3>
      <p>
        Produsele comercializate sunt destinate uzului horticol și agricol. Toate imaginile au caracter informativ și reflectă soiul în perioada de vegetație sau fructificare maximă.
      </p>

      <h3 class="font-serif text-lg font-bold text-text">2. Garanția Prinderii și Calității</h3>
      <p>
        Garantăm autenticitatea soiului și starea fitosanitară impecabilă la livrare. Prinderea ulterioară a plantelor depinde în mod direct de respectarea instrucțiunilor de plantare, udare și condițiile meteo locale.
      </p>

      <h3 class="font-serif text-lg font-bold text-text">3. Reclamații și Retururi</h3>
      <p>
        Plantele vii constituie produse perisabile conform legislației OUG 34/2014. În cazul în care un colet ajunge deteriorat, vă rugăm să întocmiți proces-verbal cu curierul și să ne contactați în termen de 24 de ore.
      </p>
    </div>
  </div>
</template>

