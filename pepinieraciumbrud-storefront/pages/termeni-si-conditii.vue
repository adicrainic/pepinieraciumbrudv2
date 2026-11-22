<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import { useSiteSettings } from '~/composables/useSiteSettings'

const { setPageSeo } = useSeo()
const { settings } = useSiteSettings()

setPageSeo({
  title: 'Termeni și Condiții',
  description: 'Termenii și condițiile de utilizare, livrare și garanție pentru achiziția de plante pe platforma Pepiniera Ciumbrud.'
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark mb-8">
      Termeni și Condiții
    </h1>

    <div class="bg-white/85 backdrop-blur-sm rounded-3xl border border-border p-8 sm:p-12 shadow-sm space-y-6 text-sm text-text-muted leading-relaxed">
      <p>
        Bine ați venit pe site-ul oficial Pepiniera Ciumbrud. Prin utilizarea acestui site și plasarea unei comenzi, sunteți de acord cu termenii și condițiile detaliate mai jos.
      </p>

      <h3 class="font-serif text-lg font-bold text-text">1. Condiții Generale</h3>
      <p>
        Produsele comercializate (pomi fructiferi, trandafiri, arbuști fructiferi și ornamentali) sunt plante vii destinate uzului horticol și agricol. Toate imaginile de prezentare au caracter informativ și ilustrează soiul la maturitate, în perioada de înflorire sau fructificare maximă.
      </p>

      <h3 class="font-serif text-lg font-bold text-text">2. Garanția la Transport, Garanția de Prindere și Autenticitatea Soiului</h3>
      <p>
        Pepiniera Ciumbrud garantează starea fitosanitară la livrare, prinderea plantelor în primul sezon vegetativ și autenticitatea soiului comandat, în condițiile detaliate integral în pagina dedicată
        <NuxtLink to="/garantie" class="text-primary underline hover:text-primary-dark font-semibold">Garanția Plantelor (/garantie)</NuxtLink>:
      </p>
      <ul class="list-disc pl-6 space-y-1.5">
        <li>
          <strong>Garanția la transport (48 de ore de la primire):</strong> acoperă plantele ajunse rupte, deshidratate sau cu ambalajul deteriorat în timpul transportului.
        </li>
        <li>
          <strong>Garanția de prindere (primul sezon vegetativ — până la 30 iunie):</strong> acoperă plantele care, deși au fost plantate și udate conform instrucțiunilor și au păstrat eticheta originală, nu pornesc în vegetație până la data de 30 iunie a primului sezon după plantare.
        </li>
        <li>
          <strong>Garanția autenticității soiului (până la prima înflorire sau fructificare, 1–2 ani):</strong> acoperă orice neconcordanță între soiul înscris pe etichetă și soiul efectiv înflorit sau intrat pe rod.
        </li>
      </ul>

      <h3 class="font-serif text-lg font-bold text-text">3. Procedura de Reclamație, Despăgubire și Retur</h3>
      <p>
        Plantele vii cu rădăcină protejată reprezintă produse susceptibile de a se deteriora rapid și necesită condiții adecvate de păstrare.
      </p>
      <ul class="list-disc pl-6 space-y-1.5">
        <li>
          <strong>Raportarea daunelor la transport (în maximum 48 de ore de la recepționarea coletului):</strong> în cazul în care un colet ajunge vizibil deteriorat sau plantele prezintă rupturi ori deshidratare la deschidere, vă rugăm să fotografiați coletul (inclusiv eticheta curierului) și plantele cu eticheta originală și să ne transmiteți fotografiile în termen de <strong>48 de ore de la livrare</strong> pe WhatsApp ({{ settings.contact_phone }}) sau pe email ({{ settings.contact_email }}).
        </li>
        <li>
          <strong>Solicitarea garanției de prindere sau soi:</strong> se realizează prin transmiterea fotografiilor plantei la locul plantării (cu eticheta originală vizibilă și testul scoarței pentru neprindere) împreună cu numărul comenzii, conform pașilor descriși în pagina
          <NuxtLink to="/garantie" class="text-primary underline hover:text-primary-dark font-semibold">Garanția de Prindere &amp; Soi Autentic</NuxtLink>.
        </li>
        <li>
          <strong>Modalități de soluționare:</strong> reclamațiile validate se soluționează în 24–48 de ore lucrătoare, la alegerea clientului, prin <strong>înlocuirea gratuită</strong> a plantei sau prin <strong>rambursarea contravalorii</strong> produsului prin transfer bancar.
        </li>
      </ul>
    </div>
  </div>
</template>
