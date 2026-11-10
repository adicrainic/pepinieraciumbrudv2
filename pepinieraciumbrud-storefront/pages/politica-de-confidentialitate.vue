<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'

const { setPageSeo } = useSeo()
setPageSeo({
  title: 'Politica de Confidențialitate (GDPR)',
  description: 'Informații privind prelucrarea și protecția datelor cu caracter personal la Pepiniera Ciumbrud.'
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="font-serif text-3xl font-bold text-primary-dark mb-8">
      Politica de Confidențialitate
    </h1>

    <div class="bg-white rounded-3xl border border-border p-8 sm:p-12 shadow-sm space-y-6 text-sm text-text-muted leading-relaxed">
      <p>
        Pepiniera Ciumbrud respectă confidențialitatea fiecărui client și se conformează Regulamentului General privind Protecția Datelor (GDPR - Regulament UE 2016/679).
      </p>

      <h3 class="font-serif text-lg font-bold text-text">Datele colectate</h3>
      <p>
        Colectăm datele furnizate exclusiv pentru procesarea și livrarea comenzilor: nume, prenume, număr de telefon, adresă de livrare și adresă de email.
      </p>

      <h3 class="font-serif text-lg font-bold text-text">Destinatarii datelor</h3>
      <p>
        Datele dumneavoastră sunt transmise strict către companiile de curierat partenere pentru livrarea fizică a coletelor. Nu vindem și nu înstrăinăm datele către terți în scopuri publicitare.
      </p>
    </div>
  </div>
</template>

