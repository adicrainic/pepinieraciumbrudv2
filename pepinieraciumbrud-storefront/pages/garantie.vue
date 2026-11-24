<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import { useSiteSettings } from '~/composables/useSiteSettings'
import AppButton from '~/components/ui/AppButton.vue'

const { setPageSeo } = useSeo()
const { settings } = useSiteSettings()

setPageSeo({
  title: 'Garanția de Prindere și Autenticitate a Soiului',
  description: 'Termenii completi ai garanției oferite de Pepiniera Ciumbrud: garanție la transport (48h), garanție de prindere în primul sezon vegetativ și garanția autenticității soiului.'
})

const whatsappUrl = computed(() => {
  const raw = settings.value?.contact_phone || '+40 765 152 832'
  let digits = raw.replace(/\D/g, '')
  if (digits.startsWith('0') && digits.length === 10) {
    digits = '40' + digits.slice(1)
  }
  return `https://wa.me/${digits || '40765152832'}`
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    <!-- Breadcrumb -->
    <nav class="text-xs text-text-muted mb-6 flex items-center space-x-2">
      <NuxtLink to="/" class="hover:text-primary">Acasă</NuxtLink>
      <span>/</span>
      <span class="text-text font-medium">Garanția Plantelor</span>
    </nav>

    <!-- Page Header -->
    <div class="text-center mb-10 sm:mb-12">
      <span class="inline-flex items-center gap-1.5 text-xs sm:text-sm uppercase tracking-wide text-primary font-bold bg-primary/10 px-3.5 py-1.5 rounded-full mb-3">
        <span>🛡️</span>
        <span>Angajamentul Producătorului · Pepiniera Ciumbrud</span>
      </span>
      <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-primary-dark leading-tight">
        Garanția de Prindere &amp; Soi Autentic
      </h1>
      <p class="mt-3 text-sm sm:text-base text-text-muted max-w-2xl mx-auto leading-relaxed">
        Cultivăm, altoim și ambalăm fiecare puiet și trandafir în propria pepinieră din Ciumbrud. Oferim o garanție simplă, cinstită și clară, cu înlocuire gratuită sau rambursarea contravalorii.
      </p>
    </div>

    <!-- 3 Pillars of Guarantee -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10 items-stretch">
      <div class="bg-white rounded-3xl border border-border p-6 shadow-sm flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-2xl bg-surface-warm border border-border flex items-center justify-center text-xl mb-4">
            📦
          </div>
          <span class="text-xs sm:text-sm font-bold uppercase tracking-wide text-primary block mb-1">
            Termen: 48 de ore de la livrare
          </span>
          <h2 class="font-serif text-lg font-bold text-primary-dark mb-2">
            1. Garanția la Transport
          </h2>
          <p class="text-xs sm:text-sm text-text-muted leading-relaxed">
            Acoperă orice colet ajuns deteriorat în urma transportului: lăstari sau altoi rupți, ambalaj compromis ori rădăcini deshidratate la deschiderea coletului.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-border/60 text-xs font-semibold text-text">
          Soluție: Înlocuire gratuită sau rambursare
        </div>
      </div>

      <div class="bg-white rounded-3xl border-2 border-primary/30 p-6 shadow-sm flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-2xl bg-leaf-50 border border-leaf-100 flex items-center justify-center text-xl mb-4">
            🌱
          </div>
          <span class="text-xs sm:text-sm font-bold uppercase tracking-wide text-leaf-700 block mb-1">
            Termen: Primul sezon (până la 30 iunie)
          </span>
          <h2 class="font-serif text-lg font-bold text-primary-dark mb-2">
            2. Garanția de Prindere
          </h2>
          <p class="text-xs sm:text-sm text-text-muted leading-relaxed">
            Acoperă pomii fructiferi, trandafirii și arbuștii plantați corespunzător în sezonul de toamnă sau primăvară care nu intră în vegetație până la data de <strong>30 iunie</strong> a primului sezon.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-border/60 text-xs font-semibold text-text">
          Soluție: Înlocuire gratuită sau rambursare
        </div>
      </div>

      <div class="bg-white rounded-3xl border border-border p-6 shadow-sm flex flex-col justify-between">
        <div>
          <div class="w-10 h-10 rounded-2xl bg-surface-warm border border-border flex items-center justify-center text-xl mb-4">
            🏷️
          </div>
          <span class="text-xs sm:text-sm font-bold uppercase tracking-wide text-primary block mb-1">
            Termen: Prima înflorire / rodire (1–2 ani)
          </span>
          <h2 class="font-serif text-lg font-bold text-primary-dark mb-2">
            3. Autenticitatea Soiului
          </h2>
          <p class="text-xs sm:text-sm text-text-muted leading-relaxed">
            Garantăm că fiecare plantă aparține soiului înscris pe etichetă. Dacă la prima înflorire (trandafiri) sau fructificare (pomi/arbuști) constatați o neconcordanță, vă despăgubim integral.
          </p>
        </div>
        <div class="mt-4 pt-3 border-t border-border/60 text-xs font-semibold text-text">
          Soluție: Soiul corect gratuit sau rambursare
        </div>
      </div>
    </div>

    <!-- Main Detailed Content Card -->
    <div class="bg-white rounded-3xl border border-border p-6 sm:p-10 shadow-sm space-y-10 text-sm text-text leading-relaxed">
      <!-- Step-by-Step Claim Procedure -->
      <section>
        <h2 class="font-serif text-2xl font-bold text-primary-dark mb-2">
          Cum solicitați garanția — Procedura pas cu pas
        </h2>
        <p class="text-text-muted text-xs sm:text-sm mb-6">
          Fără formulare birocratice complicate. Tot ce avem nevoie sunt câteva fotografii clare și numărul comenzii.
        </p>

        <div class="space-y-4">
          <div class="p-5 rounded-2xl bg-surface-warm/70 border border-border flex flex-col sm:flex-row gap-4">
            <div class="w-9 h-9 rounded-xl bg-primary text-white font-bold text-sm flex items-center justify-center shrink-0">
              1
            </div>
            <div class="space-y-2">
              <h3 class="font-serif font-bold text-base text-primary-dark">
                Pasul 1: Verificarea și fotografierea plantei (cu eticheta originală)
              </h3>
              <ul class="list-disc pl-5 space-y-1.5 text-xs sm:text-sm text-text-muted">
                <li>
                  <strong>Pentru deteriorări la transport (în max. 48 de ore de la primire):</strong> fotografiați coletul la exterior (inclusiv eticheta AWB a curierului) și plantele afectate imediat după deschidere, înainte de plantare.
                </li>
                <li>
                  <strong>Pentru neprindere (până la 30 iunie):</strong> efectuați mai întâi <em>testul scoarței</em> — zgâriați ușor cu unghia scoarța pe tulpina principală, la 10–15 cm deasupra punctului de altoire. Dacă țesutul de sub scoarță este verde și umed, planta este vie și doar pornește mai târziu în vegetație. Dacă țesutul este brun și uscat, fotografiați: <strong>(a)</strong> planta în ansamblu în locul în care este plantată, <strong>(b)</strong> detaliul cu zgârietura de pe tulpină și <strong>(c)</strong> eticheta originală atașată.
                </li>
                <li>
                  <strong>Pentru autenticitatea soiului (la prima înflorire / fructificare):</strong> fotografiați floarea sau fructul pe plantă, alături de eticheta originală.
                </li>
              </ul>
            </div>
          </div>

          <div class="p-5 rounded-2xl bg-surface-warm/70 border border-border flex flex-col sm:flex-row gap-4">
            <div class="w-9 h-9 rounded-xl bg-primary text-white font-bold text-sm flex items-center justify-center shrink-0">
              2
            </div>
            <div class="space-y-2">
              <h3 class="font-serif font-bold text-base text-primary-dark">
                Pasul 2: Trimiteți fotografiile pe WhatsApp sau Email
              </h3>
              <p class="text-xs sm:text-sm text-text-muted">
                Trimiteți fotografiile împreună cu <strong>numărul comenzii</strong> (sau numele complet și numărul de telefon folosit la plasarea comenzii) pe unul dintre canalele noastre directe:
              </p>
              <div class="flex flex-wrap items-center gap-3 pt-1 text-xs font-semibold">
                <a
                  :href="whatsappUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-leaf-50 border border-leaf-200 text-leaf-900 hover:bg-leaf-100 transition-colors"
                >
                  <span>💬</span>
                  <span>WhatsApp: {{ settings.contact_phone }}</span>
                </a>
                <a
                  :href="`mailto:${settings.contact_email}`"
                  class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-border text-primary-dark hover:border-primary transition-colors"
                >
                  <span>✉️</span>
                  <span>Email: {{ settings.contact_email }}</span>
                </a>
              </div>
            </div>
          </div>

          <div class="p-5 rounded-2xl bg-surface-warm/70 border border-border flex flex-col sm:flex-row gap-4">
            <div class="w-9 h-9 rounded-xl bg-primary text-white font-bold text-sm flex items-center justify-center shrink-0">
              3
            </div>
            <div class="space-y-2">
              <h3 class="font-serif font-bold text-base text-primary-dark">
                Pasul 3: Confirmarea și soluționarea în 24–48 de ore lucrătoare
              </h3>
              <p class="text-xs sm:text-sm text-text-muted">
                Inginerii noștri horticoli analizează solicitarea în maximum <strong>24–48 de ore lucrătoare</strong>. Dacă planta mai are muguri viabili, vă ghidăm pas cu pas pentru stimularea pornirii în vegetație. Dacă neprinderea sau deteriorarea se confirmă, alegeți modalitatea de despăgubire dorită.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- What Remedy is Offered -->
      <section class="border-t border-border pt-8">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-primary-dark mb-4">
          Ce oferim în cadrul garanției (Modalități de despăgubire)
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="p-5 rounded-2xl bg-leaf-50/60 border border-leaf-100">
            <h3 class="font-bold text-text mb-1.5 flex items-center gap-2">
              <span>🔄</span>
              <span>Opțiunea A: Înlocuire gratuită</span>
            </h3>
            <p class="text-xs text-text-muted leading-relaxed">
              Vă expediem gratuit un exemplar sănătos din același soi (sau un soi echivalent ales de dumneavoastră) în prima fereastră optimă de plantare ori atașat unei comenzi viitoare. Pentru daunele la transport raportate în 48h, transportul înlocuirii este suportat integral de Pepiniera Ciumbrud.
            </p>
          </div>
          <div class="p-5 rounded-2xl bg-surface-warm border border-border">
            <h3 class="font-bold text-text mb-1.5 flex items-center gap-2">
              <span>💳</span>
              <span>Opțiunea B: Rambursarea contravalorii</span>
            </h3>
            <p class="text-xs text-text-muted leading-relaxed">
              Dacă nu doriți să așteptați următorul sezon de plantare sau soiul nu mai este în stoc, vă restituim integral contravaloarea plantei (sau plantelor afectate) prin transfer bancar în contul IBAN indicat de dumneavoastră.
            </p>
          </div>
        </div>
      </section>

      <!-- Planting Conditions & Exclusions -->
      <section class="border-t border-border pt-8">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-primary-dark mb-4">
          Condiții de plantare pentru validarea garanției
        </h2>
        <p class="text-xs sm:text-sm text-text-muted mb-4">
          Plantele sunt organisme vii a căror prindere depinde de îngrijirea din primele zile după recepție. Pentru ca garanția de prindere să fie valabilă, vă rugăm să respectați următoarele reguli horticole de bază:
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="p-5 rounded-2xl bg-white border border-border space-y-2.5">
            <h3 class="font-bold text-leaf-800 text-sm flex items-center gap-2">
              <span>✅</span>
              <span>Ce trebuie respectat</span>
            </h3>
            <ul class="list-disc pl-5 space-y-1.5 text-xs text-text-muted">
              <li><strong>Păstrarea etichetei originale</strong> pe tulpină pe toată perioada de garanție.</li>
              <li><strong>Plantarea în 48–72 de ore de la primire</strong> (sau depozitarea temporară la rece, 2–8°C, ferit de soare, vânt și îngheț, cu rădăcinile menținute umede în ambalajul original).</li>
              <li><strong>Hidratarea și mocirlirea rădăcinilor</strong> înainte de plantare și tasarea fermă a pământului în jurul rădăcinilor pentru a elimina golurile de aer.</li>
              <li><strong>Udarea abundentă la plantare</strong> (10–15 L apă pentru un pom, 5–10 L pentru un trandafir/arbust) și udarea regulată în perioadele secetoase.</li>
              <li><strong>Nu scoateți planta din pământ</strong> înainte de a ne trimite fotografiile pentru evaluare.</li>
            </ul>
          </div>

          <div class="p-5 rounded-2xl bg-rose-50/50 border border-rose-200/70 space-y-2.5">
            <h3 class="font-bold text-rose-800 text-sm flex items-center gap-2">
              <span>⚠️</span>
              <span>Situații neacoperite de garanție</span>
            </h3>
            <ul class="list-disc pl-5 space-y-1.5 text-xs text-text-muted">
              <li><strong>Arderea rădăcinilor</strong> prin punerea de îngrășăminte chimice sau gunoi de grajd proaspăt în contact direct cu rădăcinile la plantare.</li>
              <li><strong>Seceta extremă</strong> și lipsa udării după plantare sau băltirea prelungită a apei în soluri fără drenaj.</li>
              <li><strong>Fenomene meteo extreme după plantare</strong> (înghețuri severe fără mușuroire de protecție la trandafiri, grindină, vijelii).</li>
              <li><strong>Deteriorări mecanice sau biologice externe:</strong> rosături de iepuri/rozătoare/câini, lovituri de motocoasă sau aplicarea accidentală de erbicide.</li>
              <li>Plantele cărora le-a fost îndepărtată eticheta de identificare sau care au fost smulse din sol înainte de constatare.</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Legal Reference & Alignment -->
      <section class="border-t border-border pt-6 text-xs text-text-muted flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          Această politică de garanție face parte integrantă din
          <NuxtLink to="/termeni-si-conditii" class="text-primary underline hover:text-primary-dark font-medium">Termenii și Condițiile</NuxtLink>
          Pepinierei Ciumbrud și se completează cu informațiile din pagina
          <NuxtLink to="/livrare-si-plata" class="text-primary underline hover:text-primary-dark font-medium">Livrare și Plată</NuxtLink>.
        </div>
        <div class="shrink-0">
          <AppButton to="/contact" variant="primary" size="md">
            Contactează Suportul →
          </AppButton>
        </div>
      </section>
    </div>
  </div>
</template>
