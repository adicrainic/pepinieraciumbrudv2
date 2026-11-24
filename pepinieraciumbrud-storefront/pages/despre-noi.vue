<script setup lang="ts">
import { useSeo } from '~/composables/useSeo'
import { useSiteSettings } from '~/composables/useSiteSettings'
import AppButton from '~/components/ui/AppButton.vue'

const { setPageSeo } = useSeo()
const { settings } = useSiteSettings()

setPageSeo({
  title: 'Despre Pepiniera Ciumbrud – Satul Trandafirilor, Istorie și Oameni',
  description: 'Descoperiți povestea Pepinierei Ciumbrud din Satul Trandafirilor (județul Alba): două generații de horticultori, altoire manuală în câmp și plante aclimatizate din 2000.'
})

const whatsappUrl = computed(() => {
  const raw = settings.value?.contact_phone || '+40 765 152 832'
  let digits = raw.replace(/\D/g, '')
  if (digits.startsWith('0') && digits.length === 10) {
    digits = '40' + digits.slice(1)
  }
  return `https://wa.me/${digits || '40765152832'}`
})

const timeline = [
  {
    year: 'Secolul XX',
    subtitle: 'Moștenirea localității Ciumbrud',
    title: 'Ciumbrud devine „Satul Trandafirilor” din Transilvania',
    description:
      'Datorită solului aluvionar de pe malul stâng al Mureșului și microclimatului ferit de curenți reci, familiile din Ciumbrud transformă altoirea trandafirilor și a pomilor fructiferi într-un meșteșug recunoscut în toată România.',
  },
  {
    year: '2000',
    subtitle: 'Primele parcele proprii',
    title: 'Înființarea Pepinierei de Familie în Ciumbrud',
    description:
      'Am pornit cu primele rânduri de portaltoi pentru pomi fructiferi și trandafiri teahibrizi, lucrate manual în familie. Fiecare altoi a fost legat cu grijă în câmp, punând bazele pepinierei noastre.',
  },
  {
    year: '2012',
    subtitle: 'Selecție & autorizare fitosanitară',
    title: 'Extinderea colecției de trandafiri nobili și arbuști',
    description:
      'Am introdus soiuri consacrate europene și românești — trandafiri urcători, floribunda, pomișor, meri, peri, cireși și arbuști de cultură — testate pentru rezistență la îngheț și boli sub control fitosanitar oficial.',
  },
  {
    year: '2020',
    subtitle: 'De la poarta pepinierei în toată țara',
    title: 'Ambalare în hidrogel și livrare rapidă la nivel național',
    description:
      'Am perfecționat metoda de împachetare a rădăcinilor în hidrogel și rumeguș umed, astfel încât puieții scoși proaspăt din câmp să își păstreze umiditatea optimă timp de 7–10 zile pe durata transportului.',
  },
  {
    year: 'Astăzi',
    subtitle: 'Două generații în slujba grădinii tale',
    title: 'Peste 100 de soiuri aclimatizate & Garanție scrisă de prindere',
    description:
      'Continuăm să cultivăm, să altoim și să expediem direct din câmpurile noastre din Ciumbrud, oferind fiecărui gospodar consultanță telefonică directă și garanție de prindere până la 30 iunie.',
  },
]

const teamMembers = [
  {
    role: 'Generația I · Fondatori & Meșteri Altoitori',
    name: 'Familia & Meșterii Altoitori din Ciumbrud',
    focus: 'Selecția portaltoilor, altoirea în oculaj și autenticitatea soiurilor',
    bio: 'Cu zeci de mii de puieți altoiți manual în fiecare vară, păstrează rigoarea meșteșugului tradițional din Ciumbrud: alegerea mugurelui sănătos, tăietura curată și îngrijirea rândurilor din lunca Mureșului.',
    badge: 'Meșteșugul altoirii',
  },
  {
    role: 'Generația a II-a · Horticultură & Consultanță Clienți',
    name: 'Coordonare Pepinieră & Protecția Plantelor',
    focus: 'Control fitosanitar, aclimatizare și sfaturi de plantare pentru clienți',
    bio: 'Urmărește sănătatea culturilor în câmp, certificarea fiecărui lot și răspunde direct la telefon sau pe WhatsApp tuturor grădinarilor care au nevoie de sfaturi despre tăieri, sol și scheme de plantare.',
    badge: 'Consultanță directă',
  },
  {
    role: 'Echipa din Câmp & Atelierul de Expediere',
    name: 'Oamenii care scot, etichetează și ambalează puieții',
    focus: 'Recoltare la comandă, hidratare în hidrogel și ambalare sigură',
    bio: 'În fiecare toamnă și primăvară, scot puieții cu rădăcina întreagă, aplică eticheta de soi pe fiecare plantă și învelesc rădăcinile în hidrogel și rumeguș umed înainte de predarea către curier.',
    badge: 'Rădăcini protejate',
  },
]

const craftSteps = [
  {
    number: '01',
    title: 'Portaltoi viguroși și altoire manuală în câmp',
    text: 'Nu vindem plante forțate în seră. Altoim manual în oculaj pe portaltoi adaptați solurilor din România, pentru o prindere sigură și o viață lungă a pomului sau trandafirului.',
  },
  {
    number: '02',
    title: 'Aclimatizare naturală pe Valea Mureșului',
    text: 'Puieții cresc în câmp deschis timp de două sezoane vegetative, trecând prin gerul iernii și soarele verii din Transilvania înainte de a ajunge în grădina dumneavoastră.',
  },
  {
    number: '03',
    title: 'Control fitosanitar și etichetare individuală',
    text: 'Fiecare lot este inspectat oficial și fiecare puiet pleacă din pepinieră cu etichetă durabilă care certifică soiul exact comandat.',
  },
  {
    number: '04',
    title: 'Scoatere la comandă și protecție cu hidrogel',
    text: 'În sezonul de repaus vegetativ, scoatem plantele din pământ la comandă și le protejăm rădăcinile cu hidrogel, rumeguș umed și folie protectoare.',
  },
]
</script>

<template>
  <div class="pb-20">
    <!-- Top Hero / Two-Column Visual Introduction -->
    <section class="pt-10 pb-16 sm:py-20 bg-surface-warm border-b border-border/70 relative overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="text-xs text-text-muted mb-8 flex items-center space-x-2">
          <NuxtLink to="/" class="hover:text-primary transition-colors">Acasă</NuxtLink>
          <span>/</span>
          <span class="text-text font-medium">Despre Pepiniera Ciumbrud</span>
        </nav>

        <div class="grid lg:grid-cols-12 gap-10 lg:gap-12 items-stretch">
          <!-- Column 1: Heritage Narrative -->
          <div class="lg:col-span-6 flex flex-col justify-between space-y-6">
            <div class="space-y-6">
              <span class="section-eyebrow">Satul Trandafirilor · Din 2000</span>
              <h1 class="font-serif text-4xl sm:text-5xl lg:text-[3.4rem] font-medium text-primary-dark leading-[1.08] text-balance">
                Povestea Pepinierei Ciumbrud: pământul, trandafirii și oamenii care îi cultivă
              </h1>
              <p class="text-base sm:text-lg text-text leading-relaxed font-light">
                Localitatea <strong>Ciumbrud</strong>, așezată pe malul stâng al râului Mureș în județul Alba, este cunoscută în toată România drept <em>„Satul Trandafirilor”</em>. Aici, pe lunca mănoasă a Mureșului, altoirea trandafirilor și a pomilor fructiferi nu este o simplă afacere, ci o moștenire transmisă din tată în fiu de peste o sută de ani.
              </p>
              <p class="text-sm sm:text-base text-text-muted leading-relaxed">
                Din anul <strong>2000</strong>, familia noastră duce mai departe această tradiție în propria pepinieră. Cultivăm peste 100 de soiuri de trandafiri nobili, pomi fructiferi, arbuști fructiferi și plante ornamentale — toate crescute în câmp deschis, aclimatizate în România și livrate direct din pepinieră, fără intermediari.
              </p>
            </div>

            <div class="space-y-6 pt-2">
              <!-- Key Credentials Bar -->
              <div class="grid grid-cols-3 gap-4 pt-4 border-t border-border">
                <div>
                  <div class="font-serif text-2xl sm:text-3xl font-bold text-primary">Din 2000</div>
                  <p class="text-xs text-text-muted mt-0.5">Pepinieră de familie în Ciumbrud</p>
                </div>
                <div>
                  <div class="font-serif text-2xl sm:text-3xl font-bold text-primary">100%</div>
                  <p class="text-xs text-text-muted mt-0.5">Altoite manual &amp; aclimatizate</p>
                </div>
                <div>
                  <div class="font-serif text-2xl sm:text-3xl font-bold text-primary">
                    {{ settings.company_authorization || 'AB 249100' }}
                  </div>
                  <p class="text-xs text-text-muted mt-0.5">Autorizație fitosanitară</p>
                </div>
              </div>

              <!-- CTAs -->
              <div class="flex flex-wrap items-center gap-4">
                <AppButton to="/produse" variant="primary" size="lg">
                  Explorează Plantele din Pepinieră
                </AppButton>
                <AppButton to="/garantie" variant="outline" size="lg">
                  Vezi Garanția de Prindere
                </AppButton>
              </div>
            </div>
          </div>

          <!-- Column 2: Layered Editorial Imagery -->
          <div class="lg:col-span-6 flex flex-col justify-between gap-5">
            <figure class="relative rounded-3xl overflow-hidden border border-border shadow-soft bg-neutral-900 flex-1 min-h-[16rem]">
              <img
                src="/images/hero_garden.jpg"
                alt="Răsărit peste câmpurile de trandafiri din Ciumbrud, județul Alba"
                class="absolute inset-0 w-full h-full object-cover"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/15 to-transparent"></div>
              <figcaption class="absolute bottom-0 inset-x-0 p-5 sm:p-6 text-white flex items-end justify-between gap-4">
                <div>
                  <span class="text-xs sm:text-sm uppercase tracking-wide text-rose-200 font-semibold block mb-1">
                    Lunca Mureșului · Județul Alba
                  </span>
                  <p class="font-serif text-xl sm:text-2xl font-medium">
                    Câmpurile de trandafiri din Ciumbrud la răsărit
                  </p>
                </div>
                <span class="px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/25 text-xs font-medium shrink-0">
                  Producător direct
                </span>
              </figcaption>
            </figure>

            <!-- Grower Quote Card + Inset Photo -->
            <div class="grid sm:grid-cols-12 gap-4 items-stretch">
              <div class="sm:col-span-7 bg-white rounded-2xl border border-border p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                <p class="font-serif italic text-lg sm:text-xl text-primary-dark leading-snug">
                  „Un puiet bun se cunoaște după rădăcină și după punctul de altoire. Nu trimitem spre niciun client o plantă pe care nu am planta-o în propria noastră livadă.”
                </p>
                <div class="mt-4 pt-3 border-t border-border/60 flex items-center justify-between text-xs text-text-muted">
                  <span class="font-semibold text-text">Familia Pepinierei Ciumbrud</span>
                  <span>Ciumbrud, jud. Alba</span>
                </div>
              </div>

              <figure class="sm:col-span-5 relative rounded-2xl overflow-hidden border border-border shadow-sm bg-neutral-900 min-h-[11rem]">
                <img
                  src="/images/ciumbrud_grafting.jpg"
                  alt="Altoirea manuală a trandafirilor și pomilor în Ciumbrud"
                  class="absolute inset-0 w-full h-full object-cover"
                  loading="lazy"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>
                <figcaption class="absolute bottom-3 inset-x-3 text-white text-xs font-medium leading-tight">
                  Altoire manuală în oculaj, în câmp deschis
                </figcaption>
              </figure>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Two-Column Section: How We Grow & Pack (Craftsmanship & Imagery) -->
    <section class="py-16 sm:py-24 bg-white border-b border-border/60">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-12 gap-10 lg:gap-12 items-stretch">
          <!-- Left Column: Visual Process Gallery -->
          <div class="lg:col-span-5 flex flex-col justify-between gap-5 order-2 lg:order-1">
            <figure class="rounded-3xl overflow-hidden border border-border shadow-sm bg-surface-warm flex-1 flex flex-col">
              <div class="relative flex-1 min-h-[12rem] overflow-hidden bg-neutral-900">
                <img
                  src="/images/ciumbrud_grafting.jpg"
                  alt="Detaliu cu altoirea manuală în câmp la Pepiniera Ciumbrud"
                  class="absolute inset-0 w-full h-full object-cover"
                  loading="lazy"
                />
              </div>
              <figcaption class="p-4 sm:p-5 bg-surface-warm text-xs text-text-muted leading-relaxed">
                <strong class="text-primary-dark font-semibold block text-sm font-serif mb-0.5">
                  1. Altoire și formare în câmp (2 ani ciclu de cultură)
                </strong>
                Fiecare soi este altoit manual pe portaltoi viguroși și crescut în solul aluvionar din Ciumbrud.
              </figcaption>
            </figure>

            <figure class="rounded-3xl overflow-hidden border border-border shadow-sm bg-surface-warm flex-1 flex flex-col">
              <div class="relative flex-1 min-h-[12rem] overflow-hidden bg-neutral-900">
                <img
                  src="/images/ciumbrud_packaging_roots.jpg"
                  alt="Ambalarea rădăcinilor în hidrogel și rumeguș umed la Pepiniera Ciumbrud"
                  class="absolute inset-0 w-full h-full object-cover"
                  loading="lazy"
                />
              </div>
              <figcaption class="p-4 sm:p-5 bg-surface-warm text-xs text-text-muted leading-relaxed">
                <strong class="text-primary-dark font-semibold block text-sm font-serif mb-0.5">
                  2. Rădăcini hidratate în hidrogel &amp; etichetare oficială
                </strong>
                La expediere, rădăcinile sunt tratate cu hidrogel și rumeguș umed, păstrându-și prospețimea până la plantare.
              </figcaption>
            </figure>
          </div>

          <!-- Right Column: The 4 Pillars of Ciumbrud Craftsmanship -->
          <div class="lg:col-span-7 flex flex-col justify-between gap-8 order-1 lg:order-2">
            <div>
              <span class="section-eyebrow">Meșteșugul Nostru</span>
              <h2 class="section-title">
                De ce prind rădăcini puieții și trandafirii din Ciumbrud?
              </h2>
              <p class="mt-4 text-text-muted leading-relaxed font-light">
                Diferența dintre o plantă de import depozitată luni de zile în camere frigorifice și un puiet scos proaspăt din pepiniera noastră se vede în primul sezon de primăvară.
              </p>
            </div>

            <div class="grid sm:grid-cols-2 gap-6 flex-1 items-stretch">
              <div
                v-for="step in craftSteps"
                :key="step.number"
                class="bg-surface-warm/70 rounded-2xl p-6 border border-border/80 flex flex-col justify-between h-full"
              >
                <div>
                  <span class="font-serif italic text-2xl text-accent block mb-2">{{ step.number }}</span>
                  <h3 class="font-serif text-xl font-semibold text-primary-dark mb-2">
                    {{ step.title }}
                  </h3>
                  <p class="text-sm text-text-muted leading-relaxed">
                    {{ step.text }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Timeline Section: Balanced 3x2 Heritage & Milestones Grid -->
    <section class="py-16 sm:py-24 bg-surface-warm border-b border-border/60">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-12 sm:mb-14">
          <span class="section-eyebrow">Cronologie &amp; Tradiție</span>
          <h2 class="section-title">
            De la tradiția satului la pepiniera de astăzi
          </h2>
          <p class="mt-4 text-sm sm:text-base text-text-muted leading-relaxed">
            Istoria noastră împletește tradiția de peste un secol a satului Ciumbrud cu rigoarea unei pepiniere de familie fondate în anul 2000.
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
          <article
            v-for="item in timeline"
            :key="item.year"
            class="bg-white rounded-3xl border border-border p-6 sm:p-7 shadow-sm hover:border-primary/40 transition-colors flex flex-col justify-between h-full"
          >
            <div>
              <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <span class="inline-flex items-center px-3.5 py-1 rounded-full bg-primary/10 text-primary font-serif font-bold text-base sm:text-lg">
                  {{ item.year }}
                </span>
                <span class="text-xs uppercase tracking-wide text-text-muted font-medium">
                  {{ item.subtitle }}
                </span>
              </div>
              <h3 class="font-serif text-xl sm:text-2xl font-semibold text-primary-dark mb-2.5">
                {{ item.title }}
              </h3>
              <p class="text-sm text-text-muted leading-relaxed">
                {{ item.description }}
              </p>
            </div>
          </article>

          <!-- 6th Card: Direct Visit & National Delivery Card -->
          <div class="bg-primary text-white rounded-3xl p-6 sm:p-7 shadow-sm flex flex-col justify-between h-full">
            <div class="space-y-3">
              <span class="inline-flex items-center px-3.5 py-1 rounded-full bg-white/15 text-rose-100 text-xs uppercase tracking-wider font-semibold">
                Cartea noastră de vizită
              </span>
              <h3 class="font-serif text-2xl font-medium leading-snug text-white">
                Fiecare colet pleacă direct din Ciumbrud, Str. Principală, jud. Alba.
              </h3>
              <p class="text-xs sm:text-sm text-white/85 leading-relaxed">
                Ne puteți vizita direct la pepinieră sau puteți comanda online cu livrare prin curier rapid în 24–48h în ferestrele optime de plantare.
              </p>
            </div>
            <div class="pt-5 mt-5 border-t border-white/20 flex items-center justify-between">
              <NuxtLink
                to="/contact"
                class="text-xs sm:text-sm uppercase tracking-wider font-semibold text-white hover:text-rose-200 inline-flex items-center gap-2"
              >
                <span>Vezi locația și programul</span>
                <span>→</span>
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- People / Team Section: Balanced Two-Column Intro + 3-Column Team Grid -->
    <section class="py-16 sm:py-24 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 sm:space-y-12">
        <!-- Top Row: Story + Direct Contact on Left, Team Photo on Right -->
        <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-stretch">
          <div class="lg:col-span-6 flex flex-col justify-between gap-6">
            <div>
              <span class="section-eyebrow">Oamenii Pepinierei</span>
              <h2 class="section-title">
                Cine îngrijește plantele pe care le primiți acasă
              </h2>
              <p class="mt-4 text-text-muted leading-relaxed font-light">
                În spatele fiecărui colet se află munca manuală a familiei și a echipei noastre din Ciumbrud — oameni care cunosc fiecare parcelă, fiecare soi de trandafir și fiecare portaltoi.
              </p>
            </div>

            <!-- Direct Grower Advice Callout -->
            <div class="bg-surface-warm rounded-3xl border border-border p-6 sm:p-7 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
              <div class="space-y-1.5">
                <span class="text-xs sm:text-sm uppercase tracking-wide text-primary font-bold block">
                  Consultanță horticolă gratuită
                </span>
                <h3 class="font-serif text-2xl font-semibold text-primary-dark">
                  Vorbiți direct cu noi înainte sau după plantare
                </h3>
                <p class="text-xs sm:text-sm text-text-muted leading-relaxed">
                  Vă ajutăm să alegeți polenizatorii potriviți, distanțele de plantare și tăierile de formare.
                </p>
              </div>
              <div class="flex flex-col gap-2.5 shrink-0">
                <a
                  :href="`tel:${(settings.contact_phone || '+40 765 152 832').replace(/\s+/g, '')}`"
                  class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary text-white text-xs sm:text-sm uppercase tracking-wide font-semibold hover:bg-primary-dark transition-colors shadow-sm min-h-[44px]"
                >
                  <span>📞 {{ settings.contact_phone || '+40 765 152 832' }}</span>
                </a>
                <a
                  :href="whatsappUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-border text-text hover:border-primary text-xs sm:text-sm uppercase tracking-wide font-semibold transition-colors min-h-[44px]"
                >
                  <span>💬 Scrie-ne pe WhatsApp</span>
                </a>
              </div>
            </div>
          </div>

          <!-- Right Column: Family & Team Photo -->
          <div class="lg:col-span-6 flex flex-col">
            <figure class="rounded-3xl overflow-hidden border border-border shadow-soft bg-neutral-900 h-full flex flex-col">
              <div class="relative flex-1 min-h-[18rem] overflow-hidden">
                <img
                  src="/images/ciumbrud_family_team.jpg"
                  alt="Familia și echipa de horticultori a Pepinierei Ciumbrud"
                  class="absolute inset-0 w-full h-full object-cover object-[center_88%]"
                  loading="lazy"
                />
              </div>
              <figcaption class="p-5 sm:p-6 bg-surface-warm border-t border-border">
                <div class="flex items-center justify-between gap-3 mb-1">
                  <span class="font-serif text-xl font-semibold text-primary-dark">
                    Echipa Pepinierei Ciumbrud
                  </span>
                  <span class="text-xs sm:text-sm font-semibold uppercase tracking-wide text-leaf-700 bg-leaf-50 px-3 py-1 rounded-full border border-leaf-100">
                    Cultivatori direcți
                  </span>
                </div>
                <p class="text-xs sm:text-sm text-text-muted leading-relaxed">
                  În câmpurile noastre din Ciumbrud, la verificarea înfloririi și autenticității soiurilor de trandafiri.
                </p>
              </figcaption>
            </figure>
          </div>
        </div>

        <!-- Middle Row: 3 Equal-Height Team Member Cards -->
        <div class="grid md:grid-cols-3 gap-6 items-stretch">
          <div
            v-for="person in teamMembers"
            :key="person.name"
            class="bg-surface-warm/60 rounded-3xl border border-border p-6 sm:p-7 shadow-sm flex flex-col justify-between h-full"
          >
            <div class="space-y-3">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-xs uppercase tracking-wide text-primary font-bold">
                  {{ person.role }}
                </span>
                <span class="text-xs font-medium px-3 py-1 rounded-full bg-white border border-border text-text-muted">
                  {{ person.badge }}
                </span>
              </div>
              <h3 class="font-serif text-2xl font-semibold text-primary-dark">
                {{ person.name }}
              </h3>
              <p class="text-xs font-semibold text-leaf-700">
                Responsabilitate: {{ person.focus }}
              </p>
              <p class="text-sm text-text-muted leading-relaxed">
                {{ person.bio }}
              </p>
            </div>
          </div>
        </div>

        <!-- Bottom Row: Full-Width Final CTA Card -->
        <div class="bg-surface-warm/50 rounded-3xl border-2 border-primary/20 p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <h3 class="font-serif text-xl sm:text-2xl font-semibold text-primary-dark">
              Pregătiți să plantați în grădina dumneavoastră?
            </h3>
            <p class="text-xs sm:text-sm text-text-muted mt-1">
              Toate comenzile beneficiază de garanție de prindere și ghid de plantare inclus.
            </p>
          </div>
          <AppButton to="/produse" variant="primary" class="shrink-0">
            Vezi Catalogul
          </AppButton>
        </div>
      </div>
    </section>
  </div>
</template>
