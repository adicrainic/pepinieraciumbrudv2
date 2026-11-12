<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import ProductGrid from '~/components/product/ProductGrid.vue'
import AppButton from '~/components/ui/AppButton.vue'

const { fetchProducts, fetchCategories } = useProducts()
const { setPageSeo } = useSeo()

setPageSeo({
  title: 'Pepinieră Pomul și Trandafirul – Ciumbrud',
  description: 'Pepiniera Ciumbrud – Producător de pomi fructiferi certificați, trandafiri nobili, arbuști ornamentali și arbuști fructiferi aclimatizați.'
})

// SSR data fetching
const { data: productsData, status: productsStatus } = await useAsyncData('home-products', () =>
  fetchProducts({ per_page: 8 })
)

const { data: categoriesData } = await useAsyncData('home-categories', () =>
  fetchCategories()
)

const products = computed(() => productsData.value || [])
const categories = computed(() => categoriesData.value || [])
const stats = [
  { value: '30+', label: 'ani de experiență' },
  { value: '150+', label: 'soiuri disponibile' },
  { value: '100%', label: 'aclimatizate în România' },
]

const benefits = [
  { icon: '🌿', title: 'Material Certificat', text: 'Garanție de prindere și autenticitate' },
  { icon: '📦', title: 'Livrare în Toată Țara', text: 'Ambalare specială cu rădăcini umede' },
  { icon: '❄️', title: 'Soiuri Aclimatizate', text: 'Rezistente la ger și boli specifice' },
  { icon: '💬', title: 'Consultanță Gratuită', text: 'Sfaturi pentru plantare și tăiere' },
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
    <!-- Hero -->
    <section class="group relative overflow-hidden bg-primary-dark">
      <img
        src="/images/hero_garden.jpg"
        alt="Grădina de trandafiri din Ciumbrud"
        class="absolute inset-0 w-full h-full object-cover transition-transform duration-[2500ms] ease-out group-hover:scale-105"
        fetchpriority="high"
      />
      <!-- Fresh, luminous botanical overlay -->
      <div class="absolute inset-0 bg-gradient-to-r from-primary-dark/90 via-primary-dark/60 to-primary-dark/25"></div>
      <div class="absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t from-primary-dark/85 via-primary-dark/40 to-transparent"></div>
      <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-primary-light/20 blur-3xl pointer-events-none"></div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 flex items-center lg:min-h-[calc(100svh-17rem)]">
        <div class="max-w-2xl lg:max-w-3xl">
          <span class="inline-flex items-center gap-2 px-4 py-1.5 mb-5 rounded-full text-xs font-semibold tracking-widest uppercase bg-white/15 text-white backdrop-blur-md border border-white/30 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span> Ciumbrud, Alba · Satul Rozelor
          </span>
          <h1 class="font-serif text-3xl sm:text-5xl lg:text-6xl font-bold tracking-normal leading-[1.25] text-white mb-6 [text-shadow:0_2px_12px_rgba(0,0,0,0.35)]">
            Puieți care prind <span class="italic text-accent font-semibold inline-block px-1">rădăcini</span><br class="hidden sm:inline" /> și rodesc ani la rând
          </h1>
          <p class="text-base sm:text-lg text-white/95 max-w-xl mb-7 leading-relaxed font-normal">
            Pomi fructiferi, arbuști ornamentali, arbuști fructiferi și trandafiri nobili — cultivați cu meșteșug în satul renumit în toată România pentru trandafiri.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 mb-10">
            <AppButton variant="warm" size="lg" to="/produse" class="sm:w-auto shadow-lg hover:shadow-xl transition-all">Explorează Catalogul</AppButton>
            <AppButton variant="outline" size="lg" to="/produse?category=trandafiri" class="sm:w-auto border-white/80 text-white hover:bg-white hover:text-primary-dark backdrop-blur-sm">🌹 Colecția de Trandafiri</AppButton>
          </div>
          <dl class="grid grid-cols-3 gap-6 max-w-lg border-t border-white/20 pt-6">
            <div v-for="st in stats" :key="st.label">
              <dt class="font-serif text-3xl sm:text-4xl font-bold text-white">{{ st.value }}</dt>
              <dd class="text-xs text-white/80 mt-1 font-medium">{{ st.label }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </section>

    <!-- Benefits band -->
    <section class="bg-gradient-to-r from-primary-dark via-[#247545] to-primary-dark text-white border-y border-white/10 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-7 grid grid-cols-2 lg:grid-cols-4 gap-5">
        <div v-for="b in benefits" :key="b.title" class="flex items-start gap-3.5 p-3 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10 transition-transform hover:-translate-y-0.5">
          <div class="shrink-0 w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center text-xl shadow-inner">{{ b.icon }}</div>
          <div>
            <h4 class="font-semibold text-sm text-white">{{ b.title }}</h4>
            <p class="text-xs text-white/80 mt-0.5 leading-snug">{{ b.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="py-20 bg-background">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
          <div>
            <span class="section-eyebrow">Categorii</span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark max-w-xl text-balance">Ce doriți să plantați în grădina dumneavoastră?</h2>
          </div>
          <NuxtLink to="/produse" class="text-sm font-semibold text-primary hover:text-primary-dark inline-flex items-center gap-1 group">
            <span>Vezi toate produsele</span>
            <span class="group-hover:translate-x-1 transition-transform">→</span>
          </NuxtLink>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
          <NuxtLink v-for="c in categoryCards" :key="c.to" :to="c.to" class="group block">
            <div class="arch overflow-hidden aspect-[3/4] bg-surface-warm border border-border group-hover:border-primary/50 group-hover:shadow-petal transition-all duration-300 relative">
              <img :src="c.img" :alt="c.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              <div class="absolute inset-0 bg-gradient-to-t from-primary-dark/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>
            <h3 class="font-serif text-lg sm:text-xl font-bold text-text group-hover:text-primary transition-colors mt-4">{{ c.title }}</h3>
            <p class="text-xs text-text-muted mt-1 leading-snug">{{ c.text }}</p>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Featured products -->
    <section class="py-20 bg-surface-warm/70 border-y border-border/60">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
          <div>
            <span class="section-eyebrow">Disponibile Acum</span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark">Produse Recomandate pentru Sezon</h2>
          </div>
          <AppButton variant="outline" to="/produse" class="mt-4 md:mt-0">Vezi Toate Produsele</AppButton>
        </div>
        <ProductGrid :products="products" :loading="productsStatus === 'pending'" />
      </div>
    </section>

    <!-- Roses spotlight -->
    <section class="py-20 bg-background">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-rose-50 via-rose-50/80 to-pink-50 border border-rose-200/80 shadow-soft grid md:grid-cols-2 items-center">
          <div class="p-8 sm:p-14 relative z-10">
            <span class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-rose-600 font-semibold mb-3">🌹 Specialitatea casei</span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-rose-700 mb-4 text-balance">Trandafiri nobili, altoiți la Ciumbrud</h2>
            <p class="text-text-muted leading-relaxed mb-8 max-w-md">Teahibrizi, cățărători, floribunda și pomișor — soiuri parfumate și rezistente, crescute în satul renumit pentru trandafiri.</p>
            <AppButton variant="warm" size="lg" to="/produse?category=trandafiri" class="shadow-md hover:shadow-lg">Vezi Trandafirii</AppButton>
          </div>
          <div class="relative h-72 md:h-full min-h-[18rem]">
            <img src="/plants/rose.svg" alt="Trandafiri" class="absolute inset-0 w-full h-full object-cover md:rounded-l-[8rem]" />
          </div>
        </div>
      </div>
    </section>

    <!-- Steps -->
    <section class="pb-20 bg-background">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <span class="section-eyebrow">Cum funcționează</span>
          <h2 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark">De la pepinieră în grădina ta</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div v-for="st in steps" :key="st.n" class="bg-white border border-border rounded-2xl p-8 shadow-soft hover:border-primary/40 hover:shadow-md transition-all hover:-translate-y-1">
            <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary font-bold flex items-center justify-center text-lg mb-5 border border-primary-100 font-serif">{{ st.n }}</div>
            <h3 class="font-serif text-xl font-bold text-text mb-2">{{ st.title }}</h3>
            <p class="text-sm text-text-muted leading-relaxed">{{ st.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- About -->
    <section class="py-20 bg-gradient-to-br from-primary-dark via-primary-dark to-[#134026] text-white relative overflow-hidden">
      <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-primary-light/25 blur-3xl pointer-events-none"></div>
      <div class="absolute -left-24 -bottom-24 w-96 h-96 rounded-full bg-primary/20 blur-3xl pointer-events-none"></div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 grid lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
          <span class="inline-block px-3.5 py-1 rounded-full text-xs font-semibold bg-white/15 text-accent-light uppercase tracking-wider border border-white/20 backdrop-blur-xs">Despre Ciumbrud</span>
          <h2 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight text-white">Pepiniera Ciumbrud — Satul Rozelor și Tradiția Pomiculturii</h2>
          <p class="text-white/85 leading-relaxed">Localitatea Ciumbrud din județul Alba este renumită în toată țara pentru meșteșugul altoirii și cultivării plantelor. Pământul roditor și microclimatul special de pe valea Mureșului ne permit să obținem puieți viguroși, cu rădăcini bine dezvoltate.</p>
          <p class="text-white/85 leading-relaxed">Fiecare pom fructifer și fiecare butaș de trandafir este îngrijit individual, respectând toate normele fitosanitare europene.</p>
          <AppButton variant="warm" to="/despre-noi" class="shadow-lg hover:shadow-xl">Citește Mai Mult Despre Noi</AppButton>
        </div>
        <div class="bg-white/10 backdrop-blur-md p-8 sm:p-10 rounded-3xl border border-white/20 space-y-6 shadow-2xl">
          <div class="border-l-4 border-accent-light pl-5"><span class="text-3xl sm:text-4xl font-serif font-bold text-accent-light">100%</span><p class="text-xs uppercase tracking-wider text-white/80 mt-1">Aclimatizate în România</p></div>
          <div class="border-l-4 border-accent-light pl-5"><span class="text-3xl sm:text-4xl font-serif font-bold text-accent-light">+30 Ani</span><p class="text-xs uppercase tracking-wider text-white/80 mt-1">Experiență în altoire și selecție</p></div>
          <div class="border-l-4 border-accent-light pl-5"><span class="text-3xl sm:text-4xl font-serif font-bold text-accent-light">Livrare Sigură</span><p class="text-xs uppercase tracking-wider text-white/80 mt-1">Pachete termoizolate pentru protecția rădăcinilor</p></div>
        </div>
      </div>
    </section>
  </div>
</template>
