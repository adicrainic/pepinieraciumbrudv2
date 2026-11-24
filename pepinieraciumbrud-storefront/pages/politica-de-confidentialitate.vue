<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import { useSiteSettings } from '~/composables/useSiteSettings'
import { useCookieConsent } from '~/composables/useCookieConsent'

const { setPageSeo } = useSeo()
const { settings } = useSiteSettings()
const { consent, openPreferences, acceptAll, acceptNecessaryOnly } = useCookieConsent()

setPageSeo({
  title: 'Politica de Confidențialitate (GDPR) și Cookies',
  description: 'Informații complete privind operatorul de date, scopurile prelucrării, durata de stocare, drepturile GDPR, împuterniciții și politica de cookies la Pepiniera Ciumbrud.'
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    <!-- Breadcrumb -->
    <nav class="text-xs text-text-muted mb-6 flex items-center space-x-2">
      <NuxtLink to="/" class="hover:text-primary">Acasă</NuxtLink>
      <span>/</span>
      <span class="text-text font-medium">Politica de Confidențialitate (GDPR)</span>
    </nav>

    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark mb-4">
      Politica de Confidențialitate (GDPR) și Cookies
    </h1>
    <p class="text-xs sm:text-sm text-text-muted mb-8">
      Elaborată în conformitate cu Regulamentul (UE) 2016/679 privind protecția persoanelor fizice în ceea ce privește prelucrarea datelor cu caracter personal (GDPR), Legea nr. 190/2018 și Legea nr. 506/2004.
    </p>

    <div class="bg-white rounded-3xl border border-border p-6 sm:p-12 shadow-sm space-y-8 text-sm text-text-muted leading-relaxed">
      <!-- 1. Identitatea Operatorului de Date -->
      <section class="space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          1. Identitatea Operatorului de Date cu Caracter Personal
        </h2>
        <p>
          Operatorul datelor cu caracter personal colectate prin intermediul magazinului online <strong>{{ settings.site_title || 'Pepiniera Ciumbrud' }}</strong> este:
        </p>

        <div class="p-5 rounded-2xl bg-surface-warm border border-border grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm text-text">
          <div class="space-y-1.5">
            <div>
              <span class="text-text-muted">Operator de date (Denumire juridică):</span>
              <strong class="block text-primary-dark">{{ settings.company_legal_name || 'S.C. Pepiniera Ciumbrud S.R.L.' }}</strong>
            </div>
            <div>
              <span class="text-text-muted">CUI / CIF &amp; Nr. Reg. Com.:</span>
              <strong class="block">{{ settings.company_cui || 'RO12345678' }} · {{ settings.company_reg_com || 'J01/123/2000' }}</strong>
            </div>
            <div v-if="settings.company_authorization">
              <span class="text-text-muted">Autorizație fitosanitară:</span>
              <strong class="block">{{ settings.company_authorization }}</strong>
            </div>
          </div>

          <div class="space-y-1.5">
            <div>
              <span class="text-text-muted">Sediu social / Adresă corespondență:</span>
              <strong class="block">{{ settings.contact_address }}</strong>
            </div>
            <div>
              <span class="text-text-muted">Responsabil protecția datelor (Email &amp; Telefon):</span>
              <strong class="block">
                <a :href="`mailto:${settings.contact_email}`" class="text-primary hover:underline">{{ settings.contact_email }}</a>
                ·
                <a :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`" class="text-primary hover:underline">{{ settings.contact_phone }}</a>
              </strong>
            </div>
          </div>
        </div>
      </section>

      <!-- 2. Categorii de Date Colectate -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          2. Categorii de Date cu Caracter Personal Prelucrate
        </h2>
        <p>
          Colectăm strict datele necesare pentru onorarea comenzilor, acordarea garanției plantelor, comunicarea cu clienții și, cu acordul dumneavoastră, analiza statistică internă a traficului:
        </p>
        <ul class="list-disc pl-6 space-y-1.5">
          <li>
            <strong>Date de identificare și livrare (la plasarea comenzii):</strong> nume, prenume, adresă completă de livrare și facturare (stradă, număr, localitate, județ, cod poștal), număr de telefon, adresă de email și eventuale observații privind livrarea.
          </li>
          <li>
            <strong>Date de comunicare și asistență (formularul de contact și cereri de garanție):</strong> nume, număr de telefon, adresă de email, conținutul mesajului și fotografiile plantelor transmise pentru validarea garanției.
          </li>
          <li>
            <strong>Date privind plățile și rambursările:</strong> modalitatea de plată aleasă (ramburs la curier sau ordin de plată) și, exclusiv în cazul solicitării unei rambursări sau al plăților prin OP, contul IBAN și titularul contului. Magazinul nu colectează și nu stochează date de carduri bancare.
          </li>
          <li>
            <strong>Date tehnice și statistice de trafic (doar cu consimțământ):</strong> calea paginii vizitate, titlul paginii, sursa vizitei (referrer / parametru <code class="font-mono text-xs">utm_source</code>), tipul dispozitivului (mobil, tabletă, desktop), localizarea statistică la nivel de oraș/județ/țară și o amprentă zilnică nereversibilă (<code class="font-mono text-xs">visitor_hash</code> calculat prin <code class="font-mono text-xs">SHA-256</code>). Adresa IP brută <strong>nu</strong> este stocată în tabelul de statistici.
          </li>
        </ul>
      </section>

      <!-- 3. Scopurile și Temeiurile Legale ale Prelucrării -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          3. Scopurile și Temeiurile Legale ale Prelucrării (Art. 6 GDPR)
        </h2>
        <ul class="list-disc pl-6 space-y-2">
          <li>
            <strong>Executarea contractului de vânzare la distanță (Art. 6 alin. 1 lit. b GDPR):</strong> preluarea, confirmarea telefonică și pe email a comenzii, ambalarea, generarea documentului de transport (AWB), livrarea prin curier rapid și soluționarea cererilor aferente <NuxtLink to="/garantie" class="text-primary underline">Garanției Plantelor</NuxtLink>.
          </li>
          <li>
            <strong>Îndeplinirea unor obligații legale (Art. 6 alin. 1 lit. c GDPR):</strong> emiterea și arhivarea facturilor fiscale conform legislației financiar-contabile (Legea contabilității nr. 82/1991 și Codul Fiscal), gestionarea retururilor conform OUG nr. 34/2014 și evidențele fitosanitare.
          </li>
          <li>
            <strong>Consimțământul persoanei vizate (Art. 6 alin. 1 lit. a GDPR):</strong> pentru măsurarea statistică internă a traficului pe site (activată doar dacă selectați <em>„Acceptă toate”</em> în bannerul de consimțământ).
          </li>
          <li>
            <strong>Interesul legitim al Operatorului (Art. 6 alin. 1 lit. f GDPR):</strong> asigurarea securității tehnice a magazinului online, protecția împotriva mesajelor automate de tip spam (prin câmp anti-spam <em>honeypot</em> fără profilarea utilizatorului) și răspunsul la întrebările adresate de clienți.
          </li>
        </ul>
      </section>

      <!-- 4. Durata de Stocare (Retenția Datelor) -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          4. Perioada de Stocare și Retenție a Datelor
        </h2>
        <ul class="list-disc pl-6 space-y-1.5">
          <li>
            <strong>Documente financiar-contabile și datele din comenzi / facturi:</strong> se păstrează pe perioada prevăzută de legislația fiscală din România (<strong>5–10 ani</strong> începând de la data încheierii exercițiului financiar în cursul căruia au fost întocmite).
          </li>
          <li>
            <strong>Evidența garanției comerciale (prindere și autenticitate soi):</strong> pe toată durata perioadei de garanție (până la prima înflorire sau fructificare, respectiv <strong>1–2 ani</strong> de la livrare).
          </li>
          <li>
            <strong>Mesajele transmise prin formularul de contact:</strong> maximum <strong>24 de luni</strong> de la soluționarea solicitării, dacă nu s-au concretizat într-o comandă.
          </li>
          <li>
            <strong>Datele din sistemul intern de analiză a traficului:</strong> sunt stocate în formă agregată și anonimizată (amprentă zilnică <code class="font-mono text-xs">SHA-256</code> care se schimbă automat în fiecare zi și nu permite identificarea persoanei fizice).
          </li>
        </ul>
      </section>

      <!-- 5. Destinatarii Datelor și Persoane Împuternicite -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          5. Destinatarii Datelor și Persoanele Împuternicite (Processors)
        </h2>
        <p>
          Nu vindem, nu închiriem și nu cedăm datele dumneavoastră către terți în scopuri de marketing sau publicitate. Datele sunt transmise strict către următoarele categorii de împuterniciți și parteneri contractuali, în limita necesară furnizării serviciului:
        </p>
        <ul class="list-disc pl-6 space-y-1.5">
          <li>
            <strong>Companii de curierat rapid (Fan Courier, DPD, Sameday):</strong> primesc numele, adresa de livrare, numărul de telefon și suma de încasat ramburs pentru generarea AWB-ului și livrarea fizică a coletelor.
          </li>
          <li>
            <strong>Instituții bancare / procesatori de plăți:</strong> în cazul plăților efectuate prin Ordin de Plată (transfer bancar) sau al rambursărilor în contul IBAN al clientului.
          </li>
          <li>
            <strong>Analiză trafic și geolocație IP la nivel statistic:</strong> analiza traficului rulează pe propriul nostru server (<em>First-Party Analytics</em> — <code class="font-mono text-xs">/pcs/v1/traffic/track</code>), fără Google Analytics sau Facebook Pixel. Pentru estimarea județului/orașului vizitei la nivel agregat, serverul poate interoga antetele Cloudflare sau servicii de geolocație IP (<code class="font-mono text-xs">ipwho.is</code> / <code class="font-mono text-xs">ipinfo.io</code>), fără a transmite numele, emailul sau alte date de identificare ale clientului.
          </li>
          <li>
            <strong>Servicii de hartă externă (Google Maps pe pagina Contact):</strong> pagina <NuxtLink to="/contact" class="text-primary underline">/contact</NuxtLink> încorporează o hartă Google Maps pentru localizarea pepinierei din Ciumbrud.
          </li>
          <li>
            <strong>Autorități publice și fiscale (ANAF, ANPC, instanțe):</strong> exclusiv în cazurile și în limitele impuse de legislația română în vigoare.
          </li>
        </ul>
      </section>

      <!-- 6. Drepturile Persoanelor Vizate -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          6. Drepturile Dumneavoastră conform GDPR
        </h2>
        <p>
          În calitate de persoană vizată, aveți următoarele drepturi garantate de Regulamentul (UE) 2016/679, pe care le puteți exercita oricând printr-o cerere transmisă pe email la <a :href="`mailto:${settings.contact_email}`" class="text-primary underline font-semibold">{{ settings.contact_email }}</a>:
        </p>
        <ul class="list-disc pl-6 space-y-1.5">
          <li><strong>Dreptul de acces (Art. 15 GDPR):</strong> de a obține confirmarea prelucrării și o copie a datelor deținute despre dumneavoastră.</li>
          <li><strong>Dreptul la rectificare (Art. 16 GDPR):</strong> de a solicita corectarea datelor inexacte sau completarea datelor incomplete.</li>
          <li><strong>Dreptul la ștergerea datelor — „Dreptul de a fi uitat” (Art. 17 GDPR):</strong> de a solicita ștergerea datelor atunci când nu mai sunt necesare sau când vă retrageți consimțământul (cu excepția datelor din facturile fiscale a căror păstrare este obligatorie prin lege).</li>
          <li><strong>Dreptul la restricționarea prelucrării (Art. 18 GDPR)</strong> și <strong>Dreptul la portabilitatea datelor (Art. 20 GDPR)</strong>.</li>
          <li><strong>Dreptul la opoziție (Art. 21 GDPR)</strong> și <strong>Dreptul de a vă retrage consimțământul</strong> în orice moment (inclusiv prin panoul de preferințe cookie de mai jos).</li>
          <li>
            <strong>Dreptul de a depune o plângere la autoritatea de supraveghere:</strong>
            Autoritatea Națională de Supraveghere a Prelucrării Datelor cu Caracter Personal (<strong>ANSPDCP</strong>), B-dul G-ral. Gheorghe Magheru nr. 28-30, Sector 1, București, website:
            <a href="https://www.dataprotection.ro/" target="_blank" rel="noopener noreferrer" class="text-primary underline hover:text-primary-dark">www.dataprotection.ro</a>.
          </li>
        </ul>
      </section>

      <!-- 7. Politica de Cookies și Stocare Locală -->
      <section class="border-t border-border pt-6 space-y-4">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          7. Politica de Cookies, Stocare Locală și Analiză a Traficului
        </h2>
        <p>
          Platforma {{ settings.site_title || 'Pepiniera Ciumbrud' }} este proiectată cu respect pentru viața privată: <strong>nu folosim cookie-uri de profilare publicitară terță parte</strong>. Utilizăm exclusiv următoarele mecanisme tehnice de stocare în browser și telemetrie internă:
        </p>

        <div class="overflow-x-auto rounded-2xl border border-border">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-surface-warm border-b border-border text-primary-dark font-bold">
                <th class="p-3.5">Nume / Mecanism</th>
                <th class="p-3.5">Tip &amp; Categorie</th>
                <th class="p-3.5">Durată</th>
                <th class="p-3.5">Scop și Funcționare</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border bg-white">
              <tr>
                <td class="p-3.5 font-mono font-semibold text-text">pcs_wc_cart_token</td>
                <td class="p-3.5">
                  <span class="px-2 py-0.5 rounded-full bg-leaf-50 text-leaf-800 border border-leaf-200 font-semibold">Strict necesar</span>
                  <div class="text-text-muted mt-0.5">localStorage</div>
                </td>
                <td class="p-3.5">Sesiune coș</td>
                <td class="p-3.5 text-text-muted">
                  Stochează jetonul (Cart-Token) al coșului WooCommerce Store API pentru a păstra produsele adăugate în coș pe parcursul navigării și până la finalizarea comenzii.
                </td>
              </tr>
              <tr>
                <td class="p-3.5 font-mono font-semibold text-text">pcs_cookie_consent</td>
                <td class="p-3.5">
                  <span class="px-2 py-0.5 rounded-full bg-leaf-50 text-leaf-800 border border-leaf-200 font-semibold">Strict necesar</span>
                  <div class="text-text-muted mt-0.5">Cookie &amp; localStorage</div>
                </td>
                <td class="p-3.5">180 zile</td>
                <td class="p-3.5 text-text-muted">
                  Memorează opțiunea dumneavoastră privind consimțământul (<code class="font-mono">all</code> sau <code class="font-mono">necessary</code>) pentru a nu reafișa bannerul la fiecare pagină și pentru a controla scriptul de analiză trafic.
                </td>
              </tr>
              <tr>
                <td class="p-3.5 font-mono font-semibold text-text">/pcs/v1/traffic/track</td>
                <td class="p-3.5">
                  <span class="px-2 py-0.5 rounded-full bg-surface-warm text-text border border-border font-semibold">Analiză trafic (Opțional)</span>
                  <div class="text-text-muted mt-0.5">Beacon First-Party (fără cookie)</div>
                </td>
                <td class="p-3.5">Hash zilnic (24h)</td>
                <td class="p-3.5 text-text-muted">
                  Se activează <strong>numai dacă ați selectat „Acceptă toate”</strong>. Trimite către serverul propriu pagina vizitată, referrerul și tipul dispozitivului pentru statistici agregate, folosind un hash zilnic <code class="font-mono">SHA-256</code> nereversibil.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Interactive Consent Control Box -->
        <div class="p-5 rounded-2xl bg-surface-warm border border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="space-y-1">
            <div class="text-xs sm:text-sm font-bold uppercase tracking-wide text-primary-dark">
              Starea actuală a consimțământului dumneavoastră:
            </div>
            <div class="text-sm font-semibold text-text">
              <span v-if="consent === 'all'" class="text-leaf-800">✅ Toate active (Coș strict necesar + Analiză internă anonimizată a traficului)</span>
              <span v-else-if="consent === 'necessary'" class="text-text">🔒 Doar strict necesare (Analiza traficului este dezactivată)</span>
              <span v-else class="text-text-muted">⏳ Nu ați selectat încă o opțiune</span>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button
              type="button"
              @click="acceptAll"
              class="min-h-[44px] px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs sm:text-sm font-semibold transition-colors flex items-center justify-center"
            >
              Acceptă toate
            </button>
            <button
              type="button"
              @click="acceptNecessaryOnly"
              class="min-h-[44px] px-4 py-2.5 rounded-xl bg-white hover:bg-border/40 text-text border border-border text-xs sm:text-sm font-semibold transition-colors flex items-center justify-center"
            >
              Doar strict necesare
            </button>
            <button
              type="button"
              @click="openPreferences"
              class="min-h-[44px] px-3.5 py-2 rounded-xl text-primary hover:text-primary-dark underline text-xs sm:text-sm font-semibold flex items-center justify-center"
            >
              Deschide panoul
            </button>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
