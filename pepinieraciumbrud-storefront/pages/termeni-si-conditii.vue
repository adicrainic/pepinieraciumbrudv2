<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import { useSiteSettings } from '~/composables/useSiteSettings'

const { setPageSeo } = useSeo()
const { settings } = useSiteSettings()

setPageSeo({
  title: 'Termeni și Condiții',
  description: 'Termenii și condițiile de utilizare, comandă, prețuri, livrare, drept de retragere (retur) și garanție pentru achiziția de plante pe platforma Pepiniera Ciumbrud.'
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    <!-- Breadcrumb -->
    <nav class="text-xs text-text-muted mb-6 flex items-center space-x-2">
      <NuxtLink to="/" class="hover:text-primary">Acasă</NuxtLink>
      <span>/</span>
      <span class="text-text font-medium">Termeni și Condiții</span>
    </nav>

    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark mb-4">
      Termeni și Condiții
    </h1>
    <p class="text-xs sm:text-sm text-text-muted mb-8">
      Condiții contractuale aplicabile comenzilor plasate pe magazinul online {{ settings.site_title || 'Pepiniera Ciumbrud' }}, în conformitate cu OUG nr. 34/2014, Legea nr. 365/2002 privind comerțul electronic și OG nr. 21/1992 privind protecția consumatorilor.
    </p>

    <div class="bg-white rounded-3xl border border-border p-6 sm:p-12 shadow-sm space-y-8 text-sm text-text-muted leading-relaxed">
      <!-- 1. Identificarea Vânzătorului -->
      <section class="space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          1. Identificarea Vânzătorului și Datele Societății
        </h2>
        <p>
          Magazinul online <strong>{{ settings.site_title || 'Pepiniera Ciumbrud' }}</strong> este operat de producătorul autorizat de material săditor pomicol, viticol, dendrologic și plante ornamentale cu următoarele date de identificare:
        </p>

        <div class="p-5 rounded-2xl bg-surface-warm border border-border grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm text-text">
          <div class="space-y-1.5">
            <div>
              <span class="text-text-muted">Denumire juridică:</span>
              <strong class="block text-primary-dark">{{ settings.company_legal_name || 'S.C. Pepiniera Ciumbrud S.R.L.' }}</strong>
            </div>
            <div>
              <span class="text-text-muted">Cod Unic de Înregistrare (CUI / CIF):</span>
              <strong class="block">{{ settings.company_cui || 'RO12345678' }}</strong>
            </div>
            <div>
              <span class="text-text-muted">Nr. Registrul Comerțului:</span>
              <strong class="block">{{ settings.company_reg_com || 'J01/123/2000' }}</strong>
            </div>
          </div>

          <div class="space-y-1.5">
            <div>
              <span class="text-text-muted">Autorizație producător / fitosanitară:</span>
              <strong class="block">{{ settings.company_authorization || 'AB 1234 / 0000' }}</strong>
            </div>
            <div>
              <span class="text-text-muted">Sediu social &amp; Punct de lucru:</span>
              <strong class="block">{{ settings.contact_address }}</strong>
            </div>
            <div>
              <span class="text-text-muted">Contact oficial:</span>
              <strong class="block">
                <a :href="`tel:${settings.contact_phone.replace(/\s+/g, '')}`" class="text-primary hover:underline">{{ settings.contact_phone }}</a>
                ·
                <a :href="`mailto:${settings.contact_email}`" class="text-primary hover:underline">{{ settings.contact_email }}</a>
              </strong>
            </div>
          </div>
        </div>
      </section>

      <!-- 2. Obiectul Contractului și Specificațiile Produselor -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          2. Condiții Generale și Caracteristicile Plantelor Vii
        </h2>
        <p>
          Prin utilizarea acestui site și plasarea unei comenzi, Cumpărătorul (Orice persoană fizică în vârstă de minimum 18 ani sau persoană juridică) declară că a luat cunoștință și este de acord cu prezenții Termeni și Condiții.
        </p>
        <ul class="list-disc pl-6 space-y-1.5">
          <li>
            <strong>Natura produselor:</strong> Produsele comercializate (pomi fructiferi, trandafiri, arbuști fructiferi, viță-de-vie și plante ornamentale) sunt <strong>organisme biologice vii</strong>, cultivate și aclimatizate în România, destinate uzului horticol și agricol.
          </li>
          <li>
            <strong>Imagini și stadiu vegetativ:</strong> Fotografiile de pe site au caracter informativ și ilustrează soiul la maturitate, în perioada de înflorire sau fructificare maximă. La momentul livrării în ferestrele de repaus vegetativ (toamna și primăvara), plantele cu rădăcină protejată sau la ghiveci sunt fasonate pentru plantare, fără frunze, flori sau fructe.
          </li>
          <li>
            <strong>Starea fitosanitară:</strong> Fiecare plantă expediată este însoțită de eticheta de identificare a soiului și respectă normele fitosanitare în vigoare în România și Uniunea Europeană.
          </li>
        </ul>
      </section>

      <!-- 3. Plasarea și Confirmarea Comenzii -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          3. Plasarea Comenzii și Încheierea Contractului la Distanță
        </h2>
        <p>
          Comenzile pot fi plasate online 24/7 direct prin magazinul electronic, fără a fi obligatorie crearea unui cont de utilizator (<em>Guest Checkout</em>), sau telefonic la numărul {{ settings.contact_phone }}.
        </p>
        <ul class="list-disc pl-6 space-y-1.5">
          <li>
            <strong>Pașii plasării comenzii:</strong> (1) Selectarea produselor și a cantităților dorite în coșul de cumpărături; (2) Verificarea coșului și a costului total (inclusiv transportul); (3) Completarea datelor de contact și a adresei de livrare pe pagina de finalizare comandă (<NuxtLink to="/comanda" class="text-primary underline hover:text-primary-dark">/comanda</NuxtLink>); (4) Apăsarea butonului <em>„Trimite Comanda”</em>.
          </li>
          <li>
            <strong>Confirmarea comenzii:</strong> După trimiterea comenzii, Cumpărătorul primește automat pe adresa de email numărul de referință și rezumatul comenzii. Înainte de scoaterea plantelor din pepinieră și predarea către curier, echipa noastră poate contacta telefonic Cumpărătorul pentru confirmarea adresei și stabilirea ferestrei optime de livrare în funcție de starea vremii și sezonul de plantare.
          </li>
          <li>
            <strong>Momentul încheierii contractului:</strong> Contractul la distanță se consideră încheiat în momentul confirmării comenzii (electronic sau telefonic) și alocării stocului pentru expediere.
          </li>
        </ul>
      </section>

      <!-- 4. Prețuri, TVA, Facturare și Plată -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          4. Prețuri, TVA, Costuri de Transport și Modalități de Plată
        </h2>
        <ul class="list-disc pl-6 space-y-1.5">
          <li>
            <strong>Prețuri și TVA:</strong> Toate prețurile afișate pe site sunt exprimate în <strong>Lei (RON)</strong> și includ TVA conform legislației fiscale în vigoare. Prețul aplicabil este cel afișat pe site în momentul plasării comenzii.
          </li>
          <li>
            <strong>Costul de livrare:</strong> Taxa standard de livrare prin curier rapid este de <strong>{{ settings.shipping_cost || '30' }} lei</strong> (TVA inclus), tarif fix valabil pentru orice localitate din România, fără taxe de kilometri suplimentari. Costul transportului este calculat automat și afișat distinct în coșul de cumpărături și la finalizarea comenzii, fiind inclus în <em>„Total de plată”</em>.
          </li>
          <li>
            <strong>Modalități de plată acceptate:</strong>
            <ul class="list-circle pl-6 mt-1 space-y-1">
              <li><strong>Ramburs la livrare (COD):</strong> plata în numerar sau cu cardul bancar direct la agentul curierului în momentul recepționării coletului.</li>
              <li><strong>Ordin de Plată (Transfer bancar / Factură proformă):</strong> pentru persoane juridice sau comenzi speciale, pe baza facturii proforme emise la cerere (menționată în câmpul de observații al comenzii).</li>
            </ul>
          </li>
          <li>
            <strong>Facturare:</strong> Pentru fiecare comandă livrată se emite factură fiscală în RON, transmisă în colet și/sau în format electronic pe adresa de email indicată în comandă.
          </li>
        </ul>
      </section>

      <!-- 5. Livrarea, Ambalarea și Ferestrele Sezoniere -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          5. Condiții de Livrare, Ambalare și Sezonalitate
        </h2>
        <p>
          Detaliile complete privind expedierea sunt disponibile și în pagina dedicată
          <NuxtLink to="/livrare-si-plata" class="text-primary underline hover:text-primary-dark font-semibold">Livrare și Plată (/livrare-si-plata)</NuxtLink>:
        </p>
        <ul class="list-disc pl-6 space-y-1.5">
          <li>
            <strong>Curierat rapid național:</strong> Expedierea se realizează prin curier rapid (Fan Courier, DPD sau Sameday), cu timp de tranzit de <strong>24–48 de ore lucrătoare</strong> de la predarea coletului către curier.
          </li>
          <li>
            <strong>Ferestre sezoniere de expediere:</strong> Pentru a garanta prinderea de 100%, materialul săditor cu rădăcină protejată se expediază în perioadele de repaus vegetativ: <strong>Sezonul de toamnă</strong> (sfârșitul lunii octombrie – decembrie, până la înghețul solului) și <strong>Sezonul de primăvară</strong> (februarie – aprilie). Comenzile plasate în afara ferestrei de scoatere se rezervă în ordinea înregistrării și se expediază la deschiderea sezonului.
          </li>
          <li>
            <strong>Ambalare cu hidrogel:</strong> Rădăcinile sunt tratate cu mocirlă și hidrogel profesional, învelite în rumeguș umed și folie stretch protectoare, menținând hidratarea optimă timp de 7–10 zile.
          </li>
          <li>
            <strong>Obligațiile Cumpărătorului la recepție:</strong> Cumpărătorul are obligația de a prelua coletul în termen util de la curier și de a planta puieții în 48–72 de ore de la primire (sau de a-i păstra temporar la rece, 2–8°C, feriți de îngheț și soare direct, în ambalajul umed original).
          </li>
        </ul>
      </section>

      <!-- 6. Dreptul Legal de Retragere și Excepții pentru Plante Vii -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          6. Dreptul de Retragere în 14 Zile (OUG nr. 34/2014) și Excepțiile pentru Plante Vii
        </h2>
        <p>
          Conform <strong>Ordonanței de Urgență nr. 34/2014</strong> privind drepturile consumatorilor în cadrul contractelor încheiate cu profesioniștii, consumatorul (persoană fizică) beneficiază de un termen de <strong>14 zile calendaristice</strong> de la recepționarea fizică a produselor pentru a se retrage din contractul la distanță, fără a fi nevoit să justifice decizia.
        </p>

        <div class="p-5 rounded-2xl bg-surface-warm border border-border space-y-2.5 text-xs sm:text-sm text-text">
          <h3 class="font-bold text-primary-dark text-sm">
            A. Condiții pentru exercitarea dreptului de retragere (Retur standard în 14 zile)
          </h3>
          <ul class="list-disc pl-5 space-y-1.5 text-text-muted">
            <li>
              <strong>Notificare:</strong> Înainte de expirarea perioadei de 14 zile, Cumpărătorul informează Vânzătorul printr-o declarație neechivocă trimisă pe email la <a :href="`mailto:${settings.contact_email}`" class="text-primary underline">{{ settings.contact_email }}</a> sau telefonic/WhatsApp la {{ settings.contact_phone }}, menționând numărul comenzii și produsele returnate.
            </li>
            <li>
              <strong>Starea plantelor la retur:</strong> Pentru a putea fi returnate în baza dreptului de retragere, plantele trebuie să fie în <strong>ambalajul protector original cu hidrogel și rumeguș umed nedesfăcut</strong> (sau rehidratat corespunzător), cu eticheta originală intactă, fără a fi fost plantate, fasonate suplimentar ori lăsate să se usuce.
            </li>
            <li>
              <strong>Costul transportului de retur:</strong> Conform art. 14 alin. (2) din OUG 34/2014, costul direct al returnării produselor prin curier rapid (fără ramburs) este suportat de către Cumpărător.
            </li>
            <li>
              <strong>Rambursarea sumelor:</strong> Vânzătorul va rambursa contravaloarea produselor returnate în maximum <strong>14 zile calendaristice</strong> de la data la care este informat de decizia de retragere (cu dreptul legal de a amâna rambursarea până la recepționarea și verificarea integrității biologice a plantelor returnate), prin transfer bancar în contul IBAN indicat de Cumpărător.
            </li>
          </ul>
        </div>

        <div class="p-5 rounded-2xl bg-rose-50/50 border border-rose-200/70 space-y-2 text-xs sm:text-sm text-text">
          <h3 class="font-bold text-rose-900 text-sm">
            B. Excepții legale aplicabile plantelor vii perisabile (Art. 16 lit. d din OUG nr. 34/2014)
          </h3>
          <p class="text-text-muted">
            Conform <strong>art. 16 lit. d) din OUG nr. 34/2014</strong>, sunt exceptate de la dreptul de retragere <em>„furnizarea de produse care sunt susceptibile a se deteriora sau a expira rapid”</em>. În această categorie intră plantele vii cu rădăcină liberă/protejată în următoarele situații:
          </p>
          <ul class="list-disc pl-5 space-y-1 text-text-muted">
            <li>Plantele cărora le-a fost îndepărtat ambalajul protector cu hidrogel și rumeguș umed, lăsând rădăcinile expuse la aer și deshidratare;</li>
            <li>Plantele care au fost deja plantate în sol, scurtate (rădăcini sau coroană), depozitate în medii încălzite ori la îngheț sau cărora le-a fost înlăturată eticheta de identificare;</li>
            <li>Coletele neridicate de la curier timp de mai mult de 5 zile lucrătoare din culpa exclusivă a destinatarului, fapt ce conduce la degradarea biologică a materialului săditor.</li>
          </ul>
          <p class="text-xs font-semibold text-primary-dark pt-1">
            Important: Aceste excepții de la returul fără motiv NU afectează în niciun fel dreptul dumneavoastră la Garanția la Transport (48h), Garanția de Prindere (până la 30 iunie) și Garanția Autenticității Soiului!
          </p>
        </div>
      </section>

      <!-- 7. Garanția Comercială a Plantelor -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          7. Garanția la Transport, Garanția de Prindere și Autenticitatea Soiului
        </h2>
        <p>
          {{ settings.site_title || 'Pepiniera Ciumbrud' }} garantează starea fitosanitară la livrare, prinderea plantelor în primul sezon vegetativ și autenticitatea soiului comandat, în condițiile detaliate integral în pagina dedicată
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
      </section>

      <!-- 8. Procedura de Reclamații și Soluționarea Litigiilor (ANPC / SAL / SOL) -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          8. Procedura de Soluționare a Reclamațiilor, ANPC, SAL și SOL
        </h2>
        <p>
          Orice sesizare sau reclamație legată de comandă, livrare sau calitatea materialului săditor poate fi transmisă direct către echipa noastră:
        </p>
        <ul class="list-disc pl-6 space-y-1.5">
          <li>
            <strong>Raportarea daunelor la transport (în maximum 48 de ore de la recepție):</strong> transmiteți fotografiile coletului (inclusiv eticheta AWB) și ale plantelor cu eticheta originală pe WhatsApp la <strong>{{ settings.contact_phone }}</strong> sau pe email la <strong>{{ settings.contact_email }}</strong>.
          </li>
          <li>
            <strong>Solicitarea garanției de prindere sau soi:</strong> transmiteți fotografiile plantei la locul plantării (cu eticheta originală vizibilă și testul scoarței pentru neprindere) împreună cu numărul comenzii, conform pașilor din pagina
            <NuxtLink to="/garantie" class="text-primary underline hover:text-primary-dark font-semibold">Garanția de Prindere &amp; Soi Autentic</NuxtLink>.
          </li>
          <li>
            <strong>Termen de răspuns și soluționare:</strong> Reclamațiile sunt analizate de inginerii noștri horticoli în <strong>24–48 de ore lucrătoare</strong> (și soluționate în cel mult 15 zile calendaristice conform legii), la alegerea clientului, prin <strong>înlocuirea gratuită</strong> a plantei sau prin <strong>rambursarea contravalorii</strong> produsului în cont bancar.
          </li>
        </ul>

        <div class="p-5 rounded-2xl bg-surface-warm border border-border space-y-2 text-xs sm:text-sm text-text mt-4">
          <h3 class="font-bold text-primary-dark text-sm">
            Soluționarea Alternativă a Litigiilor (ANPC / SAL / SOL)
          </h3>
          <p class="text-text-muted">
            În eventualitatea în care un diferend nu poate fi soluționat pe cale amiabilă direct cu reprezentanții {{ settings.site_title || 'Pepinierei Ciumbrud' }}, consumatorul are dreptul de a apela la instituțiile oficiale de protecție a consumatorilor:
          </p>
          <ul class="list-disc pl-5 space-y-1 text-text-muted">
            <li>
              <strong>ANPC (Autoritatea Națională pentru Protecția Consumatorilor):</strong>
              <a href="https://anpc.ro/" target="_blank" rel="noopener noreferrer" class="text-primary underline hover:text-primary-dark ml-1">https://anpc.ro/</a>
            </li>
            <li>
              <strong>SAL (Direcția de Soluționare Alternativă a Litigiilor din cadrul ANPC, conform OG nr. 38/2015):</strong>
              <a href="https://anpc.ro/ce-este-sal/" target="_blank" rel="noopener noreferrer" class="text-primary underline hover:text-primary-dark ml-1">https://anpc.ro/ce-este-sal/</a>
            </li>
            <li>
              <strong>Platforma Europeană SOL / ODR (Soluționarea Online a Litigiilor):</strong>
              <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener noreferrer" class="text-primary underline hover:text-primary-dark ml-1">https://ec.europa.eu/consumers/odr</a>
            </li>
          </ul>
        </div>
      </section>

      <!-- 9. Forța Majoră, Legea Aplicabilă și GDPR -->
      <section class="border-t border-border pt-6 space-y-3">
        <h2 class="font-serif text-xl font-bold text-primary-dark">
          9. Forța Majoră, Protecția Datelor și Legea Aplicabilă
        </h2>
        <p>
          Niciuna dintre părțile contractante nu răspunde de neexecutarea la termen a obligațiilor dacă aceasta este cauzată de un eveniment de forță majoră sau de fenomene meteorologice extreme (îngheț puternic la sol, viscol, inundații) care fac imposibilă scoaterea sau transportul în siguranță al plantelor vii; în astfel de cazuri, expedierea se reprogramează de comun acord în prima zi cu temperaturi adecvate.
        </p>
        <p>
          Prelucrarea datelor cu caracter personal și utilizarea modulelor cookie sunt guvernate de
          <NuxtLink to="/politica-de-confidentialitate" class="text-primary underline hover:text-primary-dark font-semibold">Politica de Confidențialitate (GDPR) și Cookies</NuxtLink>.
          Prezentul contract este supus legii române.
        </p>
      </section>
    </div>
  </div>
</template>
