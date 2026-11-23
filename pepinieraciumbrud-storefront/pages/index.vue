<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import { useSiteSettings } from '~/composables/useSiteSettings'
import ProductGrid from '~/components/product/ProductGrid.vue'
import AppButton from '~/components/ui/AppButton.vue'
import LogoMark from '~/components/ui/LogoMark.vue'

const { fetchProducts, fetchCategories } = useProducts()
const { setPageSeo } = useSeo()
const { settings } = useSiteSettings()

setPageSeo({
  title: settings.value?.site_tagline ? `${settings.value.site_title} – ${settings.value.site_tagline}` : 'Pepinieră Pomul și Trandafirul – Ciumbrud',
  description: settings.value?.site_description || 'Pepiniera Ciumbrud – Producător de pomi fructiferi certificați, trandafiri nobili, arbuști ornamentali și arbuști fructiferi aclimatizați.'
})

// SSR data fetching
const { data: productsData, status: productsStatus } = await useAsyncData(
  'home-products',
  () => fetchProducts({ per_page: 9, stock_status: 'instock' }),
  { getCachedData: () => undefined }
)

const { data: categoriesData } = await useAsyncData('home-categories', () =>
  fetchCategories()
)

const products = computed(() => (productsData.value || []).filter((p: any) => p.is_in_stock))
const categories = computed(() => categoriesData.value || [])
const stats = [
  { value: 'Din 2000', label: 'pepinieră de familie' },
  { value: '100+', label: 'soiuri cultivate în câmp' },
  { value: '100%', label: 'aclimatizate în România' },
]

const villageHighlights = [
  {
    title: 'Altoire manuală în oculaj',
    text: 'Fiecare trandafir și pom fructifer este altoit manual în câmpurile noastre din lunca Mureșului.',
  },
  {
    title: 'Călite 2 sezoane în câmp deschis',
    text: 'Plantele trec prin iernile din Transilvania înainte de livrare, dezvoltând rădăcini fibroase și viguroase.',
  },
  {
    title: 'Scoatere la comandă & hidrogel',
    text: 'Recoltăm proaspăt în ferestrele de plantare și protejăm rădăcinile în hidrogel și rumeguș umed.',
  },
]

const benefits = [
  { title: 'Material Certificat', text: 'Garanție de prindere și autenticitate' },
  { title: 'Livrare în Toată Țara', text: 'Ambalare specială cu rădăcini umede' },
  { title: 'Soiuri Aclimatizate', text: 'Rezistente la ger și boli specifice' },
  { title: 'Consultanță Gratuită', text: 'Sfaturi pentru plantare și tăiere' },
]

const preferredOrder = ['pomi-fructiferi', 'trandafiri', 'arbusti-fructiferi', 'arbusti-ornamentali']

const defaultCategoryMeta: Record<string, { img: string; text: string; title?: string }> = {
  'pomi-fructiferi': { img: '/plants/apple.svg', text: 'Meri, Peri, Pruni, Cireși, Vișini, Caiși, Piersici' },
  'trandafiri': { img: '/plants/rose.svg', text: 'Teahibrizi, Cățărători, Floribunda, Pomișor', title: 'Trandafiri Nobili' },
  'arbusti-fructiferi': { img: '/plants/berry.svg', text: 'Zmeur, Mur, Coacăz, Afin de cultură, Cătină' },
  'arbusti-ornamentali': { img: '/plants/ornamental.svg', text: 'Tuia, Buxus, Ienupăr, Hortensii, Gard viu' },
}

const categoryCards = computed(() => {
  const cats = [...(categories.value || [])].filter(
    (c: any) => (!c.parent || c.parent === 0) && c.slug !== 'uncategorized'
  )

  if (cats.length > 0) {
    cats.sort((a: any, b: any) => {
      const idxA = preferredOrder.indexOf(a.slug)
      const idxB = preferredOrder.indexOf(b.slug)
      if (idxA !== -1 && idxB !== -1) return idxA - idxB
      if (idxA !== -1) return -1
      if (idxB !== -1) return 1
      return (a.name || '').localeCompare(b.name || '')
    })

    return cats.map((c: any) => {
      const meta = defaultCategoryMeta[c.slug] || {
        img: '/plants/default.svg',
        text: c.description || ''
      }
      const rawDesc = c.description ? c.description.replace(/<[^>]*>/g, '').trim() : ''
      return {
        id: c.id,
        to: `/produse?category=${c.slug}`,
        img: c.image?.src || meta.img,
        title: meta.title || c.name,
        text: rawDesc || meta.text
      }
    })
  }

  return [
    { to: '/produse?category=pomi-fructiferi', img: '/plants/apple.svg', title: 'Pomi Fructiferi', text: 'Meri, Peri, Pruni, Cireși, Vișini, Caiși, Piersici' },
    { to: '/produse?category=trandafiri', img: '/plants/rose.svg', title: 'Trandafiri Nobili', text: 'Teahibrizi, Cățărători, Floribunda, Pomișor' },
    { to: '/produse?category=arbusti-fructiferi', img: '/plants/berry.svg', title: 'Arbuști Fructiferi', text: 'Zmeur, Mur, Coacăz, Afin de cultură, Cătină' },
    { to: '/produse?category=arbusti-ornamentali', img: '/plants/ornamental.svg', title: 'Arbuști Ornamentali', text: 'Tuia, Buxus, Ienupăr, Hortensii, Gard viu' },
  ]
})

const steps = [
  { n: '01', title: 'Alegi soiurile', text: 'Răsfoiește catalogul și alege puieții potriviți pentru solul și clima grădinii tale.' },
  { n: '02', title: 'Pregătim comanda', text: 'Fiecare puiet este selectat, etichetat și ambalat cu rădăcinile protejate.' },
  { n: '03', title: 'Primești și plantezi', text: 'Livrăm în toată țara, iar tu primești sfaturi de plantare și îngrijire.' },
]
</script>

<template>
  <div>
    <!-- Shared clip-path: the reversed-S edge from the logo -->
    <svg width="0" height="0" class="absolute" aria-hidden="true">
      <defs>
        <clipPath id="s-clip" clipPathUnits="objectBoundingBox">
          <path d="M0.06,0 A0.12,0.25 0 0 1 0.06,0.5 A0.12,0.25 0 0 0 0.06,1 L1,1 L1,0 Z" />
        </clipPath>
      </defs>
    </svg>

    <!-- Hero (Fullwidth panoramic photo with white S shape in the middle) -->
    <section class="group relative overflow-hidden bg-neutral-900 min-h-[42rem] lg:min-h-[48rem] flex items-center justify-center">
      <!-- Full-width panoramic background photo - NOT cropped -->
      <img
        src="/images/hero_garden.jpg"
        alt="Grădina de trandafiri din Ciumbrud"
        class="absolute inset-0 w-full h-full object-cover object-center transition-transform duration-[2500ms] ease-out group-hover:scale-105"
        fetchpriority="high"
      />

      <!-- Subtle cinematic gradient overlay to let sunrise and roses shine while keeping text crisp -->
      <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/30 to-black/75"></div>
      <div class="absolute inset-0 bg-radial from-transparent via-transparent to-black/40 pointer-events-none"></div>

      <!-- The Grand White S Shape in the Middle (full page height & wider) -->
      <div class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 h-full w-auto flex items-center justify-center">
        <LogoMark :rose="false" class="h-full w-auto scale-x-125 sm:scale-x-135 text-white/15 drop-shadow-[0_2px_20px_rgba(0,0,0,0.5)]" />
      </div>

      <!-- Hero Content Centered in the Middle -->
      <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center flex flex-col items-center">
        <!-- Center Emblem: White Reversed S + Rose -->
        <div class="animate-hero-item hero-delay-1 mb-6 flex flex-col items-center">
          <div class="w-20 h-28 sm:w-24 sm:h-36 relative flex items-center justify-center">
            <LogoMark class="w-full h-full text-white drop-shadow-[0_2px_12px_rgba(0,0,0,0.4)]" />
          </div>
          <span class="inline-flex items-center gap-2 px-4 py-1.5 mt-2 rounded-full text-xs sm:text-sm font-medium tracking-[0.22em] uppercase bg-white/10 text-white backdrop-blur-md border border-white/25 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span> {{ settings.site_tagline || 'Ciumbrud, Alba · Satul Rozelor · Din 2000' }}
          </span>
        </div>

        <!-- Main Title -->
        <h1 class="animate-hero-item hero-delay-2 font-serif text-4xl sm:text-6xl lg:text-7xl font-medium leading-[1.05] text-white mb-6 [text-shadow:0_2px_16px_rgba(0,0,0,0.45)] max-w-3xl text-balance">
          Puieți care prind <em class="text-rose-200 font-medium not-italic">rădăcini</em>,<br class="hidden sm:inline" /> și rodesc ani la rând
        </h1>

        <!-- Subtitle -->
        <p class="animate-hero-item hero-delay-3 text-base sm:text-lg text-white/90 max-w-2xl mb-10 leading-relaxed font-light [text-shadow:0_1px_8px_rgba(0,0,0,0.35)]">
          Pomi fructiferi, arbuști ornamentali, arbuști fructiferi și trandafiri nobili — cultivați cu meșteșug în satul renumit în toată România pentru trandafiri.
        </p>

        <!-- CTA Buttons -->
        <div class="animate-hero-item hero-delay-4 flex flex-col sm:flex-row gap-4 mb-14 justify-center">
          <AppButton variant="primary" size="lg" to="/produse" class="shadow-xl hover:shadow-2xl">Explorează Catalogul</AppButton>
          <NuxtLink
            to="/produse?category=trandafiri"
            class="inline-flex items-center justify-center px-8 py-4 text-sm uppercase tracking-[0.2em] rounded-tr-[1.25rem] rounded-bl-[1.25rem] font-medium border border-white/60 bg-white/10 text-white backdrop-blur-md hover:bg-white hover:text-primary-dark transition-all duration-200 shadow-lg"
          >
            Colecția de Trandafiri
          </NuxtLink>
        </div>

        <!-- Stats Bar Centered at Bottom of Hero -->
        <dl class="animate-hero-item hero-delay-5 grid grid-cols-3 gap-6 sm:gap-12 max-w-2xl w-full border-t border-white/20 pt-8">
          <div v-for="(st, i) in stats" :key="st.label" :class="i > 0 ? 'border-l border-white/15' : ''">
            <dt class="font-serif text-3xl sm:text-4xl font-medium text-white leading-none [text-shadow:0_2px_10px_rgba(0,0,0,0.3)]">{{ st.value }}</dt>
            <dd class="text-xs sm:text-sm uppercase tracking-[0.16em] text-white/80 mt-2 font-medium">{{ st.label }}</dd>
          </div>
        </dl>
      </div>
    </section>

    <!-- Benefits band -->
    <section class="bg-white border-y border-primary/15">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 lg:grid-cols-4">
        <div v-for="(b, i) in benefits" :key="b.title" class="py-7 px-5 flex items-start gap-4" :class="i > 0 ? 'lg:border-l border-primary/15' : ''">
          <span class="font-serif italic text-accent text-2xl leading-none">0{{ i + 1 }}</span>
          <div>
            <h4 class="font-sans text-xs sm:text-sm uppercase tracking-[0.16em] font-medium text-primary-dark">{{ b.title }}</h4>
            <p class="text-sm text-text-muted mt-1.5 leading-snug">{{ b.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="py-24 bg-white relative overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-4">
          <div>
            <span class="section-eyebrow">Categorii</span>
            <h2 class="section-title max-w-xl">Ce doriți să plantați în grădina dumneavoastră?</h2>
          </div>
          <NuxtLink to="/produse" class="link-underline text-xs sm:text-sm uppercase tracking-[0.2em] font-medium text-primary inline-flex items-center gap-2 pb-1 self-start md:self-auto">
            Vezi toate produsele <span>→</span>
          </NuxtLink>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-5 gap-y-12 sm:gap-x-8">
          <NuxtLink
            v-for="(c, i) in categoryCards"
            :key="c.to"
            :to="c.to"
            class="group relative bg-white rounded-2xl border border-border/80 hover:border-primary/60 shadow-sm hover:shadow-soft transition-all duration-300 flex flex-col h-full p-4 sm:p-5 cursor-pointer"
            :class="i % 2 === 1 ? 'lg:mt-12' : ''"
          >
            <div
              class="overflow-hidden aspect-[3/4] bg-surface-warm relative mb-4 transition-all duration-300"
              :class="i % 2 === 0 ? 'petal' : 'petal-alt'"
            >
              <img :src="c.img" :alt="c.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
            </div>
            <div class="flex items-baseline gap-2.5">
              <span class="font-serif italic text-accent text-lg flex-shrink-0 transition-transform duration-300 group-hover:scale-110">0{{ i + 1 }}</span>
              <h3 class="font-serif text-xl sm:text-2xl font-medium text-text group-hover:text-primary transition-colors duration-300 flex items-center justify-between w-full">
                <span>{{ c.title }}</span>
                <span class="text-accent text-base inline-block transition-transform duration-300 group-hover:translate-x-1">→</span>
              </h3>
            </div>
            <p class="text-sm text-text-muted mt-2 leading-relaxed flex-1 pl-6">{{ c.text }}</p>
            <div class="mt-4 pt-3 pl-6 border-t border-border/60 flex items-center justify-between text-xs uppercase tracking-[0.18em] font-semibold text-primary group-hover:text-accent transition-colors">
              <span>Vezi soiurile</span>
              <span class="inline-block transition-transform duration-300 group-hover:translate-x-1">→</span>
            </div>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Featured products -->
    <section class="py-24 bg-surface-warm relative overflow-hidden">
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <span class="section-eyebrow">Disponibile Acum</span>
          <h2 class="section-title">Produse recomandate pentru sezon</h2>
        </div>
        <ProductGrid :products="products" :loading="productsStatus === 'pending'" />
        <div class="mt-14 flex justify-center">
          <AppButton variant="outline" to="/produse">Vezi toate produsele</AppButton>
        </div>
      </div>
    </section>

    
    <!-- Steps -->
    <section class="py-24 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <span class="section-eyebrow">Cum funcționează</span>
          <h2 class="section-title">De la pepinieră în grădina ta</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-10 md:gap-0">
          <div v-for="(st, i) in steps" :key="st.n" class="relative md:px-10" :class="i > 0 ? 'md:border-l border-primary/15' : ''">
            <span class="font-serif text-7xl font-light text-transparent leading-none block mb-5" style="-webkit-text-stroke: 1px #B21111">{{ st.n }}</span>
            <div class="w-10 h-0.5 bg-accent mb-5"></div>
            <h3 class="font-serif text-2xl font-medium text-text mb-3">{{ st.title }}</h3>
            <p class="text-sm text-text-muted leading-relaxed font-light">{{ st.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Satul Trandafirilor (Visual Heritage Section) -->
    <section class="py-24 bg-surface-warm relative overflow-hidden border-t border-border/60">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-14 items-center">
          <!-- Left Column: Story, Heritage & Proof -->
          <div class="lg:col-span-6 space-y-6">
            <span class="section-eyebrow">Satul Trandafirilor · Ciumbrud, Alba</span>
            <h2 class="section-title">
              Rădăcini în satul trandafirilor și tradiția pomiculturii din Valea Mureșului
            </h2>
            <p class="text-text-muted leading-relaxed font-light text-base">
              Pe malul stâng al Mureșului, în localitatea <strong>Ciumbrud</strong>, meșteșugul altoirii trandafirilor și al pomilor fructiferi se transmite din generație în generație de peste un secol. Solul aluvionar fertil și microclimatul blând al văii oferă condiții ideale pentru puieți cu rădăcini bogate și lemn bine copt.
            </p>
            <p class="text-text-muted leading-relaxed font-light text-base">
              Înființată în anul <strong>2000</strong> ca pepinieră de familie, <strong>Pepiniera Ciumbrud</strong> cultivă în câmp propriu soiuri nobile de trandafiri și pomi certificați fitosanitar
              <span v-if="settings.company_authorization" class="text-primary-dark font-medium">(Aut. {{ settings.company_authorization }})</span>, îngrijiți manual de la altoire până la ambalarea în hidrogel pentru grădina ta.
            </p>

            <!-- Craftsmanship highlights -->
            <div class="grid sm:grid-cols-3 gap-4 pt-2">
              <div
                v-for="(item, idx) in villageHighlights"
                :key="item.title"
                class="bg-white/90 rounded-2xl p-4 border border-border/80 shadow-sm"
              >
                <span class="font-serif italic text-accent text-lg block mb-1">0{{ idx + 1 }}</span>
                <h3 class="font-serif text-lg font-semibold text-primary-dark leading-snug mb-1.5">
                  {{ item.title }}
                </h3>
                <p class="text-xs text-text-muted leading-relaxed">
                  {{ item.text }}
                </p>
              </div>
            </div>

            <!-- Action links -->
            <div class="pt-3 flex flex-wrap items-center gap-4">
              <AppButton variant="primary" to="/despre-noi">
                Descoperă povestea și oamenii
              </AppButton>
              <NuxtLink
                to="/garantie"
                class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-primary/25 bg-white/80 hover:bg-white text-xs uppercase tracking-[0.16em] font-semibold text-primary-dark hover:text-primary transition-colors"
              >
                <span>🛡️ Garanția de prindere 100%</span>
                <span>→</span>
              </NuxtLink>
            </div>
          </div>

          <!-- Right Column: Real Photos Collage of Ciumbrud Fields, People & Grafting -->
          <div class="lg:col-span-6 space-y-4 sm:space-y-5">
            <!-- Primary Feature Photo: Family / Team in the Ciumbrud Rose Fields -->
            <figure class="group relative rounded-3xl overflow-hidden border border-border shadow-soft bg-neutral-900 aspect-[4/3]">
              <img
                src="/images/ciumbrud_family_team.jpg"
                alt="Familia și echipa Pepinierei Ciumbrud în câmpul de trandafiri"
                class="w-full h-full object-cover object-[center_88%] group-hover:scale-105 transition-transform duration-700"
                loading="lazy"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></div>
              <figcaption class="absolute bottom-0 inset-x-0 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3 text-white">
                <div>
                  <span class="inline-block text-[11px] uppercase tracking-[0.2em] text-rose-200 font-semibold mb-1">
                    Oamenii din spatele pepinierei
                  </span>
                  <p class="font-serif text-xl sm:text-2xl font-medium leading-tight">
                    Două generații de horticultori în câmpurile din Ciumbrud
                  </p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/25 text-xs font-medium shrink-0 self-start sm:self-auto">
                  <span class="w-1.5 h-1.5 rounded-full bg-rose-300"></span>
                  Din 2000
                </span>
              </figcaption>
            </figure>

            <!-- Secondary 2-Photo Row: Hand Grafting & Root Packaging -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
              <figure class="group relative rounded-2xl overflow-hidden border border-border shadow-sm bg-neutral-900 aspect-[4/3]">
                <img
                  src="/images/ciumbrud_grafting.jpg"
                  alt="Altoire manuală în oculaj în pepiniera din Ciumbrud"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                  loading="lazy"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                <figcaption class="absolute bottom-0 inset-x-0 p-4 text-white">
                  <span class="text-[10px] uppercase tracking-[0.18em] text-rose-200 font-semibold block">
                    Meșteșug local
                  </span>
                  <p class="font-serif text-base sm:text-lg font-medium leading-snug">
                    Altoire manuală și selecție în câmp
                  </p>
                </figcaption>
              </figure>

              <figure class="group relative rounded-2xl overflow-hidden border border-border shadow-sm bg-neutral-900 aspect-[4/3]">
                <img
                  src="/images/ciumbrud_packaging_roots.jpg"
                  alt="Pregătirea rădăcinilor în hidrogel și rumeguș umed cu etichetă fitosanitară"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                  loading="lazy"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                <figcaption class="absolute bottom-0 inset-x-0 p-4 text-white">
                  <span class="text-[10px] uppercase tracking-[0.18em] text-rose-200 font-semibold block">
                    De la noi, acasă la tine
                  </span>
                  <p class="font-serif text-base sm:text-lg font-medium leading-snug">
                    Rădăcini în hidrogel &amp; soi etichetat
                  </p>
                </figcaption>
              </figure>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
