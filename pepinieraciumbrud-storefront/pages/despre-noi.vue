<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import AppButton from '~/components/ui/AppButton.vue'

const { setPageSeo } = useSeo()
setPageSeo({
  title: 'Despre Pepiniera Ciumbrud – Tradiție și Calitate',
  description: 'Aflați istoria și pasiunea din spatele Pepinierei Ciumbrud din județul Alba.'
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
      <span class="text-xs uppercase tracking-wider text-primary font-bold block mb-2">
        Istorie & Pasiune
      </span>
      <h1 class="font-serif text-4xl sm:text-5xl font-bold text-primary-dark">
        Despre Pepiniera Ciumbrud
      </h1>
    </div>

    <div class="bg-white rounded-3xl border border-border p-8 sm:p-12 shadow-sm space-y-6 text-text leading-relaxed text-base">
      <p>
        Localitatea <strong>Ciumbrud</strong>, situată pe malul stâng al râului Mureș în județul Alba, este recunoscută drept capitala trandafirilor și a pomiculturii transilvănene. Cu un sol deosebit de fertil și un microclimat ideal, tradiția altoirii s-a transmis aici din tată în fiu de peste un secol.
      </p>

      <h3 class="font-serif text-2xl font-bold text-primary-dark pt-4">
        Misiunea Noastră
      </h3>
      <p>
        Misiunea Pepinierei Ciumbrud este de a pune la dispoziția grădinarilor amatori și pomicultorilor profesioniști pomi fructiferi certificați și trandafiri de o calitate ireproșabilă. Toate soiurile noastre sunt atent selecționate și testate pentru a rezista condițiilor climatice din România (geruri aspre de iarnă și secete de vară).
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 py-6 border-y border-border text-center">
        <div>
          <div class="text-3xl font-serif font-bold text-primary mb-1">100%</div>
          <p class="text-xs text-text-muted">Plante Altoite Manual</p>
        </div>
        <div>
          <div class="text-3xl font-serif font-bold text-primary mb-1">150+</div>
          <p class="text-xs text-text-muted">Soiuri Omologate</p>
        </div>
        <div>
          <div class="text-3xl font-serif font-bold text-primary mb-1">România</div>
          <p class="text-xs text-text-muted">Origine Garantată</p>
        </div>
      </div>

      <h3 class="font-serif text-2xl font-bold text-primary-dark pt-4">
        Ce oferim clienților noștri:
      </h3>
      <ul class="list-disc pl-6 space-y-2 text-sm text-text-muted">
        <li>Material săditor cu certificare fitosanitară oficială.</li>
        <li>Rădăcini puternice, protejate la transport cu hidrogel și rumeguș umed.</li>
        <li>Ghid gratuit de plantare inclus în fiecare pachet.</li>
        <li>Asistență telefonică pentru tăieri și tratamente preventive.</li>
      </ul>

      <div class="pt-8 text-center">
        <AppButton to="/produse" variant="warm" size="lg">
          Vezi Oferta de Plante
        </AppButton>
      </div>
    </div>
  </div>
</template>

