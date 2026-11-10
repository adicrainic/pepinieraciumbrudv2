<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'

const { setPageSeo } = useSeo()
setPageSeo({
  title: 'Informații Livrare și Plată',
  description: 'Aflați detalii despre metodele de livrare prin curier rapid și opțiunile de plată pentru pomi fructiferi și trandafiri.'
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark mb-8 text-center">
      Livrare și Plată
    </h1>

    <div class="bg-white rounded-3xl border border-border p-8 sm:p-12 shadow-sm space-y-8 text-sm text-text leading-relaxed">
      <div>
        <h3 class="font-serif text-xl font-bold text-primary-dark mb-3 flex items-center gap-2">
          <span>🚚</span> 1. Cum și când livrăm?
        </h3>
        <p class="text-text-muted mb-3">
          Livrăm în toată România prin companii de curierat rapid (Fan Courier / DPD / Sameday).
        </p>
        <ul class="list-disc pl-6 space-y-2 text-text-muted">
          <li><strong>Sezonul de toamnă:</strong> Livrările încep de regulă după primele brume (sfârșitul lunii octombrie – noiembrie), când pomii au intrat în repaus vegetativ.</li>
          <li><strong>Sezonul de primăvară:</strong> Livrările se reiau imediat ce solul permite plantarea (februarie – aprilie).</li>
          <li><strong>Timp de tranzit:</strong> 24-48 ore de la predarea coletului către curier.</li>
        </ul>
      </div>

      <div class="border-t border-border pt-6">
        <h3 class="font-serif text-xl font-bold text-primary-dark mb-3 flex items-center gap-2">
          <span>📦</span> 2. Cum ambalăm plantele?
        </h3>
        <p class="text-text-muted">
          Rădăcinile pomilor și trandafirilor sunt înmuiate într-o pastă specială de mocirlă cu hidrogel și ambalate în folie stretch cu rumeguș umed. Acest procedeu garantează menținerea umidității timp de până la 7-10 zile în timpul transportului.
        </p>
      </div>

      <div class="border-t border-border pt-6">
        <h3 class="font-serif text-xl font-bold text-primary-dark mb-3 flex items-center gap-2">
          <span>💳</span> 3. Modalități de plată
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
          <div class="p-4 bg-surface-warm rounded-2xl border border-border">
            <h4 class="font-bold text-text mb-1">Ramburs la curier</h4>
            <p class="text-xs text-text-muted">Achitați numerar sau card curierului în momentul în care primiți coletul.</p>
          </div>
          <div class="p-4 bg-surface-warm rounded-2xl border border-border">
            <h4 class="font-bold text-text mb-1">Ordin de Plată (OP)</h4>
            <p class="text-xs text-text-muted">Pentru comenzi mari sau persoane juridice se poate emite factură proformă.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

