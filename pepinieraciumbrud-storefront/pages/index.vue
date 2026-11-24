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

// SSR data fetching — Best-selling & featured roses first
const { data: roseProductsData, status: roseProductsStatus } = await useAsyncData(
  'home-rose-products',
  () => fetchProducts({ category: 'trandafiri', per_page: 6, stock_status: 'instock' })
)

const { data: productsData, status: productsStatus } = await useAsyncData(
  'home-products',
  () => fetchProducts({ per_page: 6, stock_status: 'instock' })
)

const { data: categoriesData } = await useAsyncData('home-categories', () =>
  fetchCategories()
)

const bestSellingRoses = computed(() => {
  const roses = (roseProductsData.value || []).filter((p: any) => p.is_in_stock)
  if (roses.length > 0) return roses.slice(0, 6)
  return (productsData.value || []).filter((p: any) => p.is_in_stock).slice(0, 6)
})
const bestSellingLoading = computed(() =>
  roseProductsStatus.value === 'pending' && productsStatus.value === 'pending'
)
const categories = computed(() => categoriesData.value || [])

// Rose Collections: Colour / Scent / Use
const roseCollections = [
  {
    id: 'use',
    badge: 'După port & utilizare',
    title: 'Trandafiri Teahibrizi & Urcători',
    subtitle: 'Pentru ronduri elegante, pergole, arcade și flori tăiate cu tijă lungă.',
    image: '/images/teahibrizi.webp',
    primaryTo: '/produse?category=trandafiri',
    primaryLabel: 'Toți trandafirii (24+ soiuri)',
    pills: [
      { label: 'Teahibrizi (Tufă)', to: '/produse?category=teahibrizi' },
      { label: 'Urcători / Cățărători', to: '/produse?category=urcatori' },
      { label: 'Pentru flori tăiate', to: '/produse?category=teahibrizi' },
    ],
  },
  {
    id: 'scent',
    badge: 'După parfum',
    title: 'Trandafiri Intens Parfumați',
    subtitle: 'Soiuri nobile premiate pentru buchetul lor olfactiv bogat, de la note clasice de damasc la accente fructate.',
    image: '/images/parfumati.webp',
    primaryTo: '/produse?category=trandafiri&fragrance=Intens',
    primaryLabel: 'Explorează după parfum',
    pills: [
      { label: 'Parfum intens', to: '/produse?category=trandafiri&fragrance=Intens' },
      { label: 'Parfum moderat', to: '/produse?category=trandafiri&fragrance=Moderat' },
      { label: 'Parfum discret', to: '/produse?category=trandafiri&fragrance=Discret' },
    ],
  },
  {
    id: 'colour',
    badge: 'După culoare',
    title: 'Paleta Cromatică a Rozelor',
    subtitle: 'Alege nuanța care îți luminează grădina — de la roșu catifelat și galben solar până la soiuri bicolore rare.',
    image: '/images/colorati.webp',
    primaryTo: '/produse?category=trandafiri',
    primaryLabel: 'Alege culoarea preferată',
    swatches: [
      { label: 'Roșu', color: '#be123c', to: '/produse?category=trandafiri&color=Ro%C8%99u' },
      { label: 'Roz', color: '#f472b6', to: '/produse?category=trandafiri&color=Roz' },
      { label: 'Galben', color: '#facc15', to: '/produse?category=trandafiri&color=Galben' },
      { label: 'Alb', color: '#ffffff', border: true, to: '/produse?category=trandafiri&color=Alb' },
      { label: 'Portocaliu', color: '#fb923c', to: '/produse?category=trandafiri&color=Portocaliu' },
      { label: 'Mov / Lila', color: '#c084fc', to: '/produse?category=trandafiri&color=Mov%20%2F%20Lila' },
      { label: 'Bicolor', color: 'linear-gradient(135deg, #be123c 50%, #fde047 50%)', to: '/produse?category=trandafiri&color=Bicolor' },
    ],
  },
]

const stats = computed(() => {
  const totalCount = (categories.value || [])
    .filter((c: any) => (!c.parent || c.parent === 0) && c.slug !== 'uncategorized')
    .reduce((sum: number, c: any) => sum + (Number(c.count) || 0), 0)
  const roseCat = (categories.value || []).find((c: any) => c.slug === 'trandafiri')
  const roseCount = Number(roseCat?.count) || 24
  return [
    { value: 'Din 2000', label: 'pepinieră de familie în Ciumbrud' },
    { value: `${roseCount} soiuri`, label: 'trandafiri nobili altoiți manual' },
    { value: `${totalCount || 98} soiuri`, label: 'în catalogul pepinierei' },
    { value: '100%', label: 'aclimatizate & garanție prindere' },
  ]
})

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
  { title: 'Material Certificat', text: 'Garanție de prindere și autenticitate fitosanitară' },
  { title: 'Livrare în Toată Țara', text: 'Ambalare specială cu rădăcini în hidrogel și rumeguș umed' },
  { title: 'Soiuri Aclimatizate', text: 'Călite în câmp deschis, rezistente la ger și boli' },
  { title: 'Garanție 100% Prindere', text: 'Înlocuire gratuită sau rambursare pentru orice puiet' },
]

const preferredOtherOrder = ['pomi-fructiferi', 'arbusti-fructiferi', 'arbusti-ornamentali']

const defaultCategoryMeta: Record<string, { img: string; text: string; title?: string; sublinks?: { label: string; to: string }[] }> = {
  'pomi-fructiferi': {
    img: '/plants/apple.svg',
    title: 'Pomi Fructiferi Altoiți',
    text: 'Meri, Peri, Pruni, Cireși, Vișini, Caiși, Piersici și Nuci altoiți pe portaltoi viguroși.',
    sublinks: [
      { label: 'Meri', to: '/produse?category=meri' },
      { label: 'Peri', to: '/produse?category=peri' },
      { label: 'Cireși', to: '/produse?category=ciresi' },
      { label: 'Pruni', to: '/produse?category=pruni' },
      { label: 'Caiși & Piersici', to: '/produse?category=caisi' },
    ],
  },
  'arbusti-fructiferi': {
    img: '/plants/berry.svg',
    title: 'Arbuști Fructiferi',
    text: 'Zmeur, Mur, Coacăz, Afin de cultură și Cătină — soiuri productive pe rod din primul an.',
    sublinks: [
      { label: 'Zmeur', to: '/produse?category=zmeur' },
      { label: 'Coacăz', to: '/produse?category=coacaz' },
      { label: 'Mur & Afin', to: '/produse?category=arbusti-fructiferi' },
      { label: 'Toți arbuștii fructiferi', to: '/produse?category=arbusti-fructiferi' },
    ],
  },
  'arbusti-ornamentali': {
    img: '/plants/ornamental.svg',
    title: 'Arbuști Ornamentali',
    text: 'Hortensii, Magnolii, Liliac, Lavandă, Buxus și plante pentru gard viu mereu verde.',
    sublinks: [
      { label: 'Hortensii & Magnolii', to: '/produse?category=arbusti-ornamentali' },
      { label: 'Gard viu & Buxus', to: '/produse?category=arbusti-ornamentali' },
      { label: 'Vezi colecția ornamentală', to: '/produse?category=arbusti-ornamentali' },
    ],
  },
  'trandafiri': {
    img: '/plants/rose.svg',
    text: 'Teahibrizi, Cățărători, Floribunda, Pomișor',
    title: 'Trandafiri Nobili',
  },
}

const otherCategoryCards = computed(() => {
  const cats = [...(categories.value || [])].filter(
    (c: any) => (!c.parent || c.parent === 0) && c.slug !== 'uncategorized' && c.slug !== 'trandafiri'
  )

  if (cats.length > 0) {
    cats.sort((a: any, b: any) => {
      const idxA = preferredOtherOrder.indexOf(a.slug)
      const idxB = preferredOtherOrder.indexOf(b.slug)
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
        slug: c.slug,
        count: c.count || 0,
        to: `/produse?category=${c.slug}`,
        img: c.image?.src || meta.img,
        title: meta.title || c.name,
        text: rawDesc || meta.text,
        sublinks: meta.sublinks || []
      }
    })
  }

  return preferredOtherOrder.map((slug) => {
    const meta = defaultCategoryMeta[slug]
    return {
      slug,
      count: 0,
      to: `/produse?category=${slug}`,
      img: meta.img,
      title: meta.title || slug,
      text: meta.text,
      sublinks: meta.sublinks || []
    }
  })
})

const steps = [
  {
    n: '01',
    badge: 'Comandă & Rezervare',
    title: 'Alegi soiurile potrivite',
    text: 'Selectezi trandafirii și pomii doriti online sau telefonic. Primești confirmare rapidă și sfaturi de asociere a soiurilor.'
  },
  {
    n: '02',
    badge: 'Scoatere din câmp & Hidrogel',
    title: 'Pregătim plantele proaspete',
    text: 'Recoltăm puieții la comandă, mocirlim rădăcinile în hidrogel și îi ambalăm în rumeguș umed și folie protectoare etichetată.'
  },
  {
    n: '03',
    badge: 'Curier 24–48h & Garanție',
    title: 'Primești și plantezi în siguranță',
    text: 'Coletul ajunge prin curier rapid oriunde în România, însoțit de ghidul de plantare și garanția de prindere 100%.'
  },
]

// Seasonal planting newsletter & updates form state
const newsletterEmail = ref('')
const newsletterSubmitted = ref(false)
const newsletterError = ref('')

const handleNewsletterSubmit = () => {
  newsletterError.value = ''
  const email = newsletterEmail.value.trim()
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    newsletterError.value = 'Te rugăm să introduci o adresă de email validă.'
    return
  }
  newsletterSubmitted.value = true
  newsletterEmail.value = ''
}
</script>

<template>
  <div>
    <!-- Hero (Fullwidth panoramic photo) -->
    <section class="group relative overflow-hidden bg-neutral-900 min-h-[38rem] lg:min-h-[44rem] flex items-center justify-center">
      <!-- Full-width panoramic background photo - NOT cropped -->
      <img
        src="/images/hero_garden.jpg"
        alt="Grădina de trandafiri din Ciumbrud"
        class="absolute inset-0 w-full h-full object-cover object-center transition-transform duration-[2500ms] ease-out group-hover:scale-105"
        fetchpriority="high"
      />

      <!-- Dark gradient overlay to keep text and subtext crisp over the busy garden photo -->
      <div class="absolute inset-0 bg-gradient-to-b from-black/75 via-black/60 to-black/85"></div>
      <div class="absolute inset-0 bg-radial from-black/45 via-black/30 to-black/75 pointer-events-none"></div>

      <!-- Hero Content Centered in the Middle -->
      <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center flex flex-col items-center">
        <!-- Center Emblem: White Reversed S + Rose -->
        <div class="animate-hero-item hero-delay-1 mb-6 flex flex-col items-center">
          <div class="w-16 h-24 sm:w-20 sm:h-28 relative flex items-center justify-center">
            <LogoMark class="w-full h-full text-white drop-shadow-[0_2px_12px_rgba(0,0,0,0.4)]" />
          </div>
          <span class="inline-flex items-center gap-2 px-4 py-1.5 mt-2 rounded-full text-xs sm:text-sm font-medium tracking-wide uppercase bg-black/45 text-white backdrop-blur-md border border-white/25 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span> {{ settings.site_tagline || 'Ciumbrud, Alba · Satul Rozelor · Din 2000' }}
          </span>
        </div>

        <!-- Main Title: Rose-specific headline -->
        <h1 class="animate-hero-item hero-delay-2 font-serif text-4xl sm:text-6xl lg:text-7xl font-medium leading-[1.06] text-white mb-6 [text-shadow:0_2px_20px_rgba(0,0,0,0.65)] max-w-3xl text-balance">
          Trandafiri nobili din <em class="text-rose-200 font-medium not-italic">Ciumbrud</em>,<br class="hidden sm:inline" /> altoiți manual să înflorească ani la rând
        </h1>

        <!-- Subtitle with dark gradient backdrop for high contrast on busy photo -->
        <div class="animate-hero-item hero-delay-3 max-w-2xl mb-10 px-6 py-4 rounded-2xl bg-gradient-to-b from-black/60 via-black/70 to-black/60 backdrop-blur-sm border border-white/15 shadow-lg">
          <p class="text-base sm:text-lg text-white leading-relaxed font-normal [text-shadow:0_1px_10px_rgba(0,0,0,0.6)]">
            Trandafiri teahibrizi, urcători și parfumați, alături de pomi și arbuști fructiferi certificați — cultivați în câmp deschis în Satul Rozelor și livrați cu rădăcina protejată în hidrogel.
          </p>
        </div>

        <!-- CTA Buttons -->
        <div class="animate-hero-item hero-delay-4 flex flex-col sm:flex-row gap-4 justify-center">
          <AppButton variant="primary" size="lg" to="/produse?category=trandafiri" class="shadow-xl hover:shadow-2xl">
            Colecția de Trandafiri
          </AppButton>
          <NuxtLink
            to="/produse"
            class="inline-flex items-center justify-center px-8 py-4 text-sm sm:text-base uppercase tracking-wider rounded-2xl font-medium border border-white/60 bg-black/40 text-white backdrop-blur-md hover:bg-white hover:text-primary-dark transition-all duration-200 shadow-lg min-h-[48px]"
          >
            Explorează Tot Catalogul
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Credibility & Catalog Stats Bar (Moved out of Hero) -->
    <section id="statistici-pepiniera" aria-label="Cifrele Pepinierei Ciumbrud" class="bg-surface-warm border-b border-border/80 relative z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
          <div
            v-for="(st, i) in stats"
            :key="st.label"
            class="flex flex-col items-center lg:items-start text-center lg:text-left"
            :class="i > 0 ? 'lg:border-l lg:border-border/80 lg:pl-8' : ''"
          >
            <dt class="font-serif text-2xl sm:text-3xl font-semibold text-primary-dark leading-none">
              {{ st.value }}
            </dt>
            <dd class="text-xs sm:text-sm text-text-muted mt-1.5 font-medium">
              {{ st.label }}
            </dd>
          </div>
        </dl>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 2. EXPLORE — Rose Collections (Colour / Scent / Use)                      -->
    <!-- ========================================================================= -->
    <section id="colectii-trandafiri" class="py-20 sm:py-24 bg-white relative overflow-hidden border-b border-border/60">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 sm:mb-14 gap-4">
          <div>
            <span class="section-eyebrow">Emblema Ciumbrudului · Colecții de Trandafiri</span>
            <h2 class="section-title max-w-2xl">
              Descoperă trandafirii după port, parfum și culoare
            </h2>
            <p class="text-text-muted text-sm sm:text-base mt-3 max-w-2xl font-light leading-relaxed">
              Cultivați în Satul Rozelor din Ciumbrud, trandafirii noștri sunt altoiți manual în oculaj și aclimatizați în câmp deschis pentru înflorire bogată din mai până la primul îngheț.
            </p>
          </div>
          <NuxtLink
            to="/produse?category=trandafiri"
            class="link-underline text-xs sm:text-sm uppercase tracking-wide font-semibold text-primary inline-flex items-center gap-2 pb-1 self-start md:self-auto min-h-[44px]"
          >
            Vezi toți trandafirii <span>→</span>
          </NuxtLink>
        </div>

        <!-- 3 Rose Collection Pillars: Use, Scent, Colour -->
        <div class="grid lg:grid-cols-3 gap-6 sm:gap-8 items-stretch">
          <article
            v-for="(col, idx) in roseCollections"
            :key="col.id"
            class="group bg-surface-card rounded-3xl border border-border/90 hover:border-primary/50 shadow-sm hover:shadow-soft transition-all duration-300 flex flex-col h-full overflow-hidden"
          >
            <!-- Card Visual Header -->
            <NuxtLink :to="col.primaryTo" class="relative aspect-[16/10] overflow-hidden bg-neutral-900 block shrink-0">
              <img
                :src="col.image"
                :alt="col.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                loading="lazy"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
              <div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs uppercase tracking-wider font-semibold bg-white/90 text-primary-dark shadow-sm">
                  <span class="font-serif italic text-primary">0{{ idx + 1 }}</span>
                  <span>{{ col.badge }}</span>
                </span>
              </div>
              <div class="absolute bottom-4 left-5 right-5 text-white">
                <h3 class="font-serif text-2xl sm:text-3xl font-medium leading-tight text-white group-hover:text-rose-200 transition-colors">
                  {{ col.title }}
                </h3>
              </div>
            </NuxtLink>

            <!-- Card Body: Description + Interactive Filter Pills / Swatches -->
            <div class="p-5 sm:p-6 flex-1 flex flex-col">
              <p class="text-sm text-text-muted leading-relaxed mb-5">
                {{ col.subtitle }}
              </p>

              <!-- Quick filter pills for Use & Scent -->
              <div v-if="col.pills" class="flex flex-wrap gap-2 mb-5">
                <NuxtLink
                  v-for="pill in col.pills"
                  :key="pill.label"
                  :to="pill.to"
                  class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-border hover:border-primary hover:text-primary transition-colors min-h-[38px]"
                >
                  <span>🌹</span>
                  <span>{{ pill.label }}</span>
                </NuxtLink>
              </div>

              <!-- Interactive Colour Swatches for Colour Collection -->
              <div v-if="col.swatches" class="flex flex-wrap gap-2 mb-5">
                <NuxtLink
                  v-for="sw in col.swatches"
                  :key="sw.label"
                  :to="sw.to"
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white border border-border hover:border-primary hover:text-primary transition-colors min-h-[36px]"
                >
                  <span
                    class="w-3.5 h-3.5 rounded-full shrink-0 shadow-inner"
                    :class="sw.border ? 'border border-neutral-300' : ''"
                    :style="{ background: sw.color }"
                  ></span>
                  <span>{{ sw.label }}</span>
                </NuxtLink>
              </div>

              <!-- Card Footer CTA -->
              <div class="mt-auto pt-3.5 border-t border-border/70 flex items-center justify-between">
                <NuxtLink
                  :to="col.primaryTo"
                  class="text-xs sm:text-sm uppercase tracking-wide font-semibold text-primary hover:text-primary-dark inline-flex items-center gap-2 min-h-[36px]"
                >
                  <span>{{ col.primaryLabel }}</span>
                  <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                </NuxtLink>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. EXPLORE / CHOOSE — Best-Selling Roses                                  -->
    <!-- ========================================================================= -->
    <section id="trandafiri-populari" class="py-20 sm:py-24 bg-surface-warm relative overflow-hidden">
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 sm:mb-14 gap-4">
          <div>
            <span class="section-eyebrow">Cei Mai Căutați Trandafiri</span>
            <h2 class="section-title">Soiuri nobile de trandafiri disponibile acum</h2>
            <p class="text-text-muted text-sm sm:text-base mt-2 max-w-2xl font-light">
              Selecție din cele mai apreciate soiuri teahibride și urcătoare cultivate în pepiniera noastră, gata de plantare.
            </p>
          </div>
          <NuxtLink
            to="/produse?category=trandafiri"
            class="link-underline text-xs sm:text-sm uppercase tracking-wide font-semibold text-primary inline-flex items-center gap-2 pb-1 self-start md:self-auto min-h-[44px]"
          >
            Vezi toată colecția de trandafiri <span>→</span>
          </NuxtLink>
        </div>

        <ProductGrid :products="bestSellingRoses" :loading="bestSellingLoading" />

        <div class="mt-12 sm:mt-14 flex flex-col sm:flex-row items-center justify-center gap-4">
          <AppButton variant="primary" to="/produse?category=trandafiri">
            Explorează Toți Trandafirii
          </AppButton>
          <AppButton variant="outline" to="/produse">
            Vezi Întregul Catalog
          </AppButton>
        </div>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 4. TRUST — Benefits Band + Satul Trandafirilor Heritage & Guarantee       -->
    <!-- ========================================================================= -->
    <section id="incredere-garantie" class="bg-white border-y border-primary/15">
      <!-- 4 Pillars Trust Band -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 lg:grid-cols-4 border-b border-border/60">
        <div
          v-for="(b, i) in benefits"
          :key="b.title"
          class="py-7 px-4 sm:px-5 flex items-start gap-3.5 sm:gap-4"
          :class="i > 0 ? 'lg:border-l border-primary/15' : ''"
        >
          <span class="font-serif italic text-accent text-2xl leading-none">0{{ i + 1 }}</span>
          <div>
            <h3 class="font-sans text-xs sm:text-sm uppercase tracking-wide font-semibold text-primary-dark">
              {{ b.title }}
            </h3>
            <p class="text-xs sm:text-sm text-text-muted mt-1.5 leading-snug">{{ b.text }}</p>
          </div>
        </div>
      </div>

      <!-- Satul Trandafirilor (Visual Heritage & Proof Section) -->
      <div class="py-20 sm:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-14 items-center">
          <!-- Left Column: Story, Heritage & Proof -->
          <div class="lg:col-span-6 space-y-6">
            <span class="section-eyebrow">De Ce Pepiniera Ciumbrud · Tradiție &amp; Garanție</span>
            <h2 class="section-title">
              Rădăcini în satul trandafirilor și garanție de prindere 100%
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
                class="bg-surface-warm/70 rounded-2xl p-4 border border-border/80 shadow-sm"
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
              <NuxtLink
                to="/garantie"
                class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-primary text-white hover:bg-primary-dark text-xs sm:text-sm uppercase tracking-wider font-semibold shadow-sm transition-colors min-h-[44px]"
              >
                <span>🛡️ Garanția de prindere 100%</span>
                <span>→</span>
              </NuxtLink>
              <AppButton variant="outline" to="/despre-noi">
                Descoperă povestea și oamenii
              </AppButton>
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
                  <span class="inline-block text-xs sm:text-sm uppercase tracking-wide text-rose-200 font-semibold mb-1">
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
                  <span class="text-xs uppercase tracking-wide text-rose-200 font-semibold block">
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
                  <span class="text-xs uppercase tracking-wide text-rose-200 font-semibold block">
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

    <!-- ========================================================================= -->
    <!-- 5. CHOOSE — Other Categories (Fruit Trees, Berry Bushes, Ornamentals)     -->
    <!-- ========================================================================= -->
    <section id="alte-categorii" class="py-20 sm:py-24 bg-surface-warm relative overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 sm:mb-14 gap-4">
          <div>
            <span class="section-eyebrow">Completează Grădina și Livada</span>
            <h2 class="section-title max-w-2xl">
              Pomi fructiferi, arbuști de cultură și plante ornamentale
            </h2>
            <p class="text-text-muted text-sm sm:text-base mt-2 max-w-2xl font-light">
              Pe lângă trandafirii nobili, cultivăm în câmpurile din Ciumbrud zeci de soiuri de pomi fructiferi altoiți și arbuști aclimatizați.
            </p>
          </div>
          <NuxtLink
            to="/produse"
            class="link-underline text-xs sm:text-sm uppercase tracking-wide font-semibold text-primary inline-flex items-center gap-2 pb-1 self-start md:self-auto min-h-[44px]"
          >
            Vezi toate categoriile <span>→</span>
          </NuxtLink>
        </div>

        <div class="grid md:grid-cols-3 gap-6 sm:gap-8 items-stretch">
          <div
            v-for="(c, i) in otherCategoryCards"
            :key="c.to"
            class="group relative bg-white rounded-3xl border border-border/80 hover:border-primary/60 shadow-sm hover:shadow-soft transition-all duration-300 flex flex-col h-full p-5 sm:p-6"
          >
            <NuxtLink
              :to="c.to"
              class="overflow-hidden aspect-[4/3] bg-surface-warm relative mb-5 rounded-2xl transition-all duration-300 block shrink-0"
            >
              <img
                :src="c.img"
                :alt="c.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                loading="lazy"
              />
              <span
                v-if="c.count"
                class="absolute bottom-3 right-3 px-3 py-1 rounded-full text-xs font-semibold bg-black/65 text-white backdrop-blur-sm"
              >
                {{ c.count }} soiuri
              </span>
            </NuxtLink>

            <div class="flex items-baseline gap-2.5 mb-2.5">
              <span class="font-serif italic text-accent text-lg flex-shrink-0">0{{ i + 1 }}</span>
              <NuxtLink :to="c.to" class="font-serif text-2xl sm:text-3xl font-medium text-text group-hover:text-primary transition-colors duration-300 flex items-center justify-between w-full">
                <span>{{ c.title }}</span>
                <span class="text-accent text-base inline-block transition-transform duration-300 group-hover:translate-x-1">→</span>
              </NuxtLink>
            </div>

            <p class="text-sm text-text-muted leading-relaxed mb-4">
              {{ c.text }}
            </p>

            <!-- Subcategory chips -->
            <div v-if="c.sublinks && c.sublinks.length" class="mb-5 flex flex-wrap gap-1.5">
              <NuxtLink
                v-for="sub in c.sublinks"
                :key="sub.to + sub.label"
                :to="sub.to"
                class="px-2.5 py-1 rounded-lg text-xs font-medium bg-surface-warm text-text hover:bg-primary hover:text-white transition-colors"
              >
                {{ sub.label }}
              </NuxtLink>
            </div>

            <NuxtLink
              :to="c.to"
              class="mt-auto pt-3.5 border-t border-border/60 flex items-center justify-between text-xs sm:text-sm uppercase tracking-wide font-semibold text-primary group-hover:text-accent transition-colors min-h-[36px]"
            >
              <span>Explorează soiurile</span>
              <span class="inline-block transition-transform duration-300 group-hover:translate-x-1">→</span>
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 6. BUY — How Delivery Works (Steps + Packaging & Shipping Reassurance)    -->
    <!-- ========================================================================= -->
    <section id="cum-livram" class="py-20 sm:py-24 bg-white border-t border-border/60">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 sm:mb-16 max-w-2xl mx-auto">
          <span class="section-eyebrow">Cum Funcționează Livrarea</span>
          <h2 class="section-title">De la pepinieră direct în grădina ta</h2>
          <p class="text-text-muted text-sm sm:text-base mt-3 font-light">
            Expediem în toată România în ferestrele optime de plantare, cu rădăcinile protejate profesional pentru prindere sigură.
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 md:gap-0">
          <div
            v-for="(st, i) in steps"
            :key="st.n"
            class="relative md:px-10"
            :class="i > 0 ? 'md:border-l border-primary/15' : ''"
          >
            <div class="flex items-center justify-between mb-4">
              <span class="font-serif text-6xl sm:text-7xl font-light text-transparent leading-none block" style="-webkit-text-stroke: 1px #B21111">{{ st.n }}</span>
              <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-surface-warm text-primary-dark border border-border">
                {{ st.badge }}
              </span>
            </div>
            <div class="w-10 h-0.5 bg-accent mb-4"></div>
            <h3 class="font-serif text-2xl font-medium text-text mb-2.5">{{ st.title }}</h3>
            <p class="text-sm text-text-muted leading-relaxed font-light">{{ st.text }}</p>
          </div>
        </div>

        <!-- Delivery & Payment Info Callout Bar -->
        <div class="mt-14 p-6 sm:p-8 rounded-3xl bg-surface-warm border border-border flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
          <div class="space-y-1.5 max-w-2xl">
            <h3 class="font-serif text-2xl font-medium text-primary-dark">
              Transport sigur prin curier rapid &amp; plată ramburs la livrare
            </h3>
            <p class="text-sm text-text-muted leading-relaxed">
              Plătești în siguranță la primirea coletului. Fiecare comandă include instrucțiuni tipărite de mocirlire, plantare și tăiere.
            </p>
          </div>
          <div class="flex flex-wrap items-center gap-3 shrink-0">
            <AppButton variant="primary" to="/produse">
              Comandă Puieți Acum
            </AppButton>
            <NuxtLink
              to="/livrare-si-plata"
              class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-border bg-white hover:border-primary text-xs sm:text-sm uppercase tracking-wide font-semibold text-text hover:text-primary transition-colors min-h-[44px]"
            >
              <span>Detalii livrare &amp; plată</span>
              <span>→</span>
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 7. NEWSLETTER & PLANTING CALENDAR — Pre-Footer Conversion & Retention     -->
    <!-- ========================================================================= -->
    <section id="newsletter-calendar" class="py-16 sm:py-20 bg-surface-warm border-t border-border/80 relative overflow-hidden">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-border shadow-soft p-6 sm:p-10 lg:p-12">
          <div class="grid lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7 space-y-3">
              <span class="section-eyebrow">Jurnalul Grădinarului · Newsletter</span>
              <h2 class="font-serif text-3xl sm:text-4xl font-medium text-primary-dark leading-tight">
                Primește calendarul lucrărilor de sezon și noutățile din pepinieră
              </h2>
              <p class="text-sm sm:text-base text-text-muted font-light leading-relaxed">
                Abonează-te gratuit pentru a afla când deschidem campaniile de plantare de toamnă și primăvară, ce soiuri noi de trandafiri intră în stoc și sfaturi practice de îngrijire.
              </p>
            </div>

            <div class="lg:col-span-5">
              <div
                v-if="newsletterSubmitted"
                class="p-5 rounded-2xl bg-leaf-50 border border-leaf-300 text-leaf-900 space-y-1.5"
                role="status"
              >
                <div class="font-serif text-xl font-semibold flex items-center gap-2">
                  <span>🌿</span>
                  <span>Mulțumim pentru abonare!</span>
                </div>
                <p class="text-xs sm:text-sm text-leaf-700 leading-relaxed">
                  Te-am înregistrat în lista Pepinierei Ciumbrud. Vei primi sfaturi de plantare și alerte de stoc la începutul fiecărui sezon.
                </p>
              </div>

              <form v-else @submit.prevent="handleNewsletterSubmit" class="space-y-3" novalidate>
                <label for="home-newsletter-email" class="sr-only">Adresa ta de email</label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                  <input
                    id="home-newsletter-email"
                    v-model="newsletterEmail"
                    type="email"
                    required
                    placeholder="Adresa ta de email..."
                    class="flex-1 px-4 py-3.5 rounded-xl border border-border bg-surface-warm/60 text-sm text-text placeholder:text-text-light focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 min-h-[46px]"
                  />
                  <button
                    type="submit"
                    class="px-6 py-3.5 rounded-xl bg-primary hover:bg-primary-dark text-white text-xs sm:text-sm uppercase tracking-wider font-semibold transition-colors shadow-sm shrink-0 min-h-[46px]"
                  >
                    Abonează-mă
                  </button>
                </div>
                <p v-if="newsletterError" class="text-xs text-primary font-medium">
                  {{ newsletterError }}
                </p>
                <p class="text-xs text-text-light leading-snug">
                  Fără spam — trimitem doar 2–3 mesaje utile pe sezon. Te poți dezabona oricând.
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
