<script setup lang="ts">
import { useProducts } from '~/composables/useProducts'
import { useSeo } from '~/composables/useSeo'
import ProductGrid from '~/components/product/ProductGrid.vue'
import AppButton from '~/components/ui/AppButton.vue'

const { fetchProducts, fetchCategories } = useProducts()
const { setPageSeo } = useSeo()

setPageSeo({
  title: 'Pepinieră Pomul și Trandafirul – Ciumbrud',
  description: 'Pepiniera Ciumbrud – Producător de pomi fructiferi certificați, trandafiri nobili, viță de vie și arbuști fructiferi aclimatizați.'
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

const categoryCards = [
  { to: '/produse?category=pomi-fructiferi', img: '/plants/apple.svg', title: 'Pomi Fructiferi', text: 'Meri, Peri, Pruni, Cireși, Vișini, Caiși, Piersici' },
  { to: '/produse?category=trandafiri', img: '/plants/rose.svg', title: 'Trandafiri Nobili', text: 'Teahibrizi, Cățărători, Floribunda, Pomișor' },
  { to: '/produse?category=arbusti-fructiferi', img: '/plants/berry.svg', title: 'Arbuști Fructiferi', text: 'Zmeur, Mur, Coacăz, Afin de cultură, Cătină' },
  { to: '/produse?category=vita-de-vie', img: '/plants/grape.svg', title: 'Viță de Vie', text: 'Soiuri de masă și soiuri vinicole' },
]

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
      <div class="absolute inset-0 bg-gradient-to-r from-primary-dark/90 via-primary-dark/60 to-primary-dark/10"></div>
      <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-primary-dark/60 to-transparent"></div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 flex items-center lg:min-h-[calc(100svh-17rem)]">
        <div class="max-w-2xl">
          <span class="inline-flex items-center gap-2 px-4 py-1.5 mb-5 rounded-full text-xs font-semibold tracking-widest uppercase bg-white/10 text-white backdrop-blur-md border border-white/25">
            <span class="w-1.5 h-1.5 rounded-full bg-accent"></span> Ciumbrud, Alba · Satul Rozelor
          </span>
          <h1 class="font-serif text-4xl sm:text-5xl font-bold tracking-tight leading-[1.1] text-white mb-4 text-balance drop-shadow-lg">
            Puieți care prind <span class="italic text-accent">rădăcini</span> și rodesc ani la rând
          </h1>
          <p class="text-base sm:text-lg text-white/90 max-w-xl mb-6 leading-relaxed">
            Pomi fructiferi, arbuști, viță de vie și trandafiri nobili — altoiți cu meșteșug în satul renumit în toată România pentru trandafiri.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 mb-8">
            <AppButton variant="warm" size="lg" to="/produse" class="sm:w-auto shadow-lg">Explorează Catalogul</AppButton>
            <AppButton variant="outline" size="lg" to="/produse?category=trandafiri" class="sm:w-auto border-white/80 text-white hover:bg-white hover:text-primary-dark">🌹 Colecția de Trandafiri</AppButton>
          </div>
          <dl class="grid grid-cols-3 gap-6 max-w-lg border-t border-white/25 pt-5">
            <div v-for="st in stats" :key="st.label">
              <dt class="font-serif text-3xl font-bold text-white">{{ st.value }}</dt>
              <dd class="text-xs text-white/75 mt-1">{{ st.label }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </section>

    <!-- Benefits band -->
    <section class="bg-primary text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="b in benefits" :key="b.title" class="flex items-start gap-3">
          <div class="shrink-0 w-11 h-11 rounded-full bg-white/10 flex items-center justify-center text-xl">{{ b.icon }}</div>
          <div>
            <h4 class="font-semibold text-sm">{{ b.title }}</h4>
            <p class="text-xs text-white/70 mt-0.5">{{ b.text }}</p>
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
          <NuxtLink to="/produse" class="text-sm font-semibold text-primary hover:text-primary-dark">Vezi toate produsele →</NuxtLink>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
          <NuxtLink v-for="c in categoryCards" :key="c.to" :to="c.to" class="group block">
            <div class="arch overflow-hidden aspect-[3/4] bg-surface-warm border border-border group-hover:shadow-petal transition-all duration-300">
              <img :src="c.img" :alt="c.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            </div>
            <h3 class="font-serif text-lg sm:text-xl font-bold text-text group-hover:text-primary transition-colors mt-4">{{ c.title }}</h3>
            <p class="text-xs text-text-muted mt-1">{{ c.text }}</p>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Featured products -->
    <section class="py-20 bg-surface-warm">
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
        <div class="relative overflow-hidden rounded-[2rem] bg-rose-50 border border-rose-100 grid md:grid-cols-2 items-center">
          <div class="p-8 sm:p-14 relative z-10">
            <span class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-rose-600 font-semibold mb-3">🌹 Specialitatea casei</span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-rose-700 mb-4 text-balance">Trandafiri nobili, altoiți la Ciumbrud</h2>
            <p class="text-text-muted leading-relaxed mb-8 max-w-md">Teahibrizi, cățărători, floribunda și pomișor — soiuri parfumate și rezistente, crescute în satul renumit pentru trandafiri.</p>
            <AppButton variant="warm" size="lg" to="/produse?category=trandafiri">Vezi Trandafirii</AppButton>
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
        <div class="text-center mb-12">
          <span class="section-eyebrow">Cum funcționează</span>
          <h2 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark">De la pepinieră în grădina ta</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div v-for="st in steps" :key="st.n" class="bg-white border border-border rounded-2xl p-8 shadow-soft">
            <div class="font-serif text-5xl font-bold text-primary-100 mb-4">{{ st.n }}</div>
            <h3 class="font-serif text-xl font-bold text-text mb-2">{{ st.title }}</h3>
            <p class="text-sm text-text-muted leading-relaxed">{{ st.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- About -->
    <section class="py-20 bg-primary-dark text-white relative overflow-hidden">
      <div class="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-primary-light/20 blur-3xl"></div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 grid lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
          <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-accent uppercase tracking-wider">Despre Ciumbrud</span>
          <h2 class="font-serif text-3xl sm:text-4xl font-bold leading-tight text-white">Pepiniera Ciumbrud — Satul Rozelor și Tradiția Pomiculturii</h2>
          <p class="text-white/80 leading-relaxed">Localitatea Ciumbrud din județul Alba este renumită în toată țara pentru meșteșugul altoirii și cultivării plantelor. Pământul roditor și microclimatul special de pe valea Mureșului ne permit să obținem puieți viguroși, cu rădăcini bine dezvoltate.</p>
          <p class="text-white/80 leading-relaxed">Fiecare pom fructifer și fiecare butaș de trandafir este îngrijit individual, respectând toate normele fitosanitare europene.</p>
          <AppButton variant="warm" to="/despre-noi">Citește Mai Mult Despre Noi</AppButton>
        </div>
        <div class="bg-white/10 backdrop-blur-md p-8 rounded-3xl border border-white/20 space-y-6">
          <div class="border-l-4 border-accent pl-4"><span class="text-3xl font-serif font-bold text-accent">100%</span><p class="text-xs uppercase tracking-wider text-white/70">Aclimatizate în România</p></div>
          <div class="border-l-4 border-accent pl-4"><span class="text-3xl font-serif font-bold text-accent">+30 Ani</span><p class="text-xs uppercase tracking-wider text-white/70">Experiență în altoire și selecție</p></div>
          <div class="border-l-4 border-accent pl-4"><span class="text-3xl font-serif font-bold text-accent">Livrare Sigură</span><p class="text-xs uppercase tracking-wider text-white/70">Pachete termoizolate pentru protecția rădăcinilor</p></div>
        </div>
      </div>
    </section>
  </div>
</template>
