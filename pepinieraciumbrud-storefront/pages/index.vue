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
</script>

<template>
  <div>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-b from-primary-dark via-primary to-primary-dark text-white py-24 sm:py-32 overflow-hidden">
      <!-- Decorative background plant pattern overlay -->
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#74C69D_1px,transparent_1px)] [background-size:24px_24px]"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold tracking-wider uppercase bg-white/10 text-accent backdrop-blur-md border border-white/20 mb-6">
          🌱 Material Săditor Certificat & Aclimatizat
        </span>

        <h1 class="font-serif text-4xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-white mb-6 max-w-4xl mx-auto leading-tight text-balance">
          Pomi Fructiferi și Trandafiri din Inima Transilvaniei
        </h1>

        <p class="text-base sm:text-xl text-white/85 max-w-2xl mx-auto mb-10 leading-relaxed font-light">
          La Ciumbrud, cultivăm tradiția pomiculturii de generații. Oferim peste 150 de soiuri de meri, peri, pruni, cireși și trandafiri altoiți cu pasiune și grijă.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <AppButton
            variant="warm"
            size="lg"
            to="/produse"
            class="w-full sm:w-auto shadow-lg"
          >
            Explorează Catalogul de Plante
          </AppButton>
          <AppButton
            variant="outline"
            size="lg"
            to="/despre-noi"
            class="w-full sm:w-auto border-white text-white hover:bg-white hover:text-primary-dark"
          >
            Află Povestea Noastră
          </AppButton>
        </div>
      </div>
    </section>

    <!-- Trust Badges Strip -->
    <section class="border-b border-border bg-white py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
          <div class="p-3">
            <div class="text-2xl mb-1">🌿</div>
            <h4 class="font-bold text-sm text-text">Material Certificat</h4>
            <p class="text-xs text-text-muted">Garanție de prindere și autenticitate</p>
          </div>
          <div class="p-3">
            <div class="text-2xl mb-1">🚚</div>
            <h4 class="font-bold text-sm text-text">Livrare în Toată Țara</h4>
            <p class="text-xs text-text-muted">Ambalare specială cu rădăcini umede</p>
          </div>
          <div class="p-3">
            <div class="text-2xl mb-1">🍎</div>
            <h4 class="font-bold text-sm text-text">Soiuri Aclimatizate</h4>
            <p class="text-xs text-text-muted">Rezistente la ger și boli specifice</p>
          </div>
          <div class="p-3">
            <div class="text-2xl mb-1">💬</div>
            <h4 class="font-bold text-sm text-text">Consultanță Gratuită</h4>
            <p class="text-xs text-text-muted">Sfaturi utile pentru plantare și tăiere</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular Categories -->
    <section class="py-16 bg-surface-warm/40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
          <div>
            <span class="text-xs uppercase tracking-wider text-primary font-semibold block mb-1">
              Categorii Principale
            </span>
            <h2 class="font-serif text-3xl font-bold text-primary-dark">
              Ce doriți să plantați în grădina dumneavoastră?
            </h2>
          </div>
          <NuxtLink to="/produse" class="text-sm font-semibold text-primary hover:text-primary-dark mt-4 md:mt-0 flex items-center gap-1">
            Vezi toate categoriile
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </NuxtLink>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <NuxtLink
            to="/produse?category=pomi-fructiferi"
            class="group bg-white p-6 rounded-2xl border border-border hover:border-primary/40 shadow-sm hover:shadow-md transition-all text-center block"
          >
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-3xl mx-auto mb-4 group-hover:scale-110 transition-transform">
              🍏
            </div>
            <h3 class="font-serif text-lg font-bold text-text group-hover:text-primary transition-colors mb-1">
              Pomi Fructiferi
            </h3>
            <p class="text-xs text-text-muted">Meri, Peri, Pruni, Cireși, Vișini, Caiși, Piersici</p>
          </NuxtLink>

          <NuxtLink
            to="/produse?category=trandafiri"
            class="group bg-white p-6 rounded-2xl border border-border hover:border-primary/40 shadow-sm hover:shadow-md transition-all text-center block"
          >
            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-700 flex items-center justify-center text-3xl mx-auto mb-4 group-hover:scale-110 transition-transform">
              🌹
            </div>
            <h3 class="font-serif text-lg font-bold text-text group-hover:text-primary transition-colors mb-1">
              Trandafiri Nobili
            </h3>
            <p class="text-xs text-text-muted">Teahibrizi, Cățărători, Floribunda, Pomișor</p>
          </NuxtLink>

          <NuxtLink
            to="/produse?category=arbusti-fructiferi"
            class="group bg-white p-6 rounded-2xl border border-border hover:border-primary/40 shadow-sm hover:shadow-md transition-all text-center block"
          >
            <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center text-3xl mx-auto mb-4 group-hover:scale-110 transition-transform">
              🫐
            </div>
            <h3 class="font-serif text-lg font-bold text-text group-hover:text-primary transition-colors mb-1">
              Arbuști Fructiferi
            </h3>
            <p class="text-xs text-text-muted">Zmeur, Mur, Coacăz, Afin de cultură, Cătină</p>
          </NuxtLink>

          <NuxtLink
            to="/produse?category=vita-de-vie"
            class="group bg-white p-6 rounded-2xl border border-border hover:border-primary/40 shadow-sm hover:shadow-md transition-all text-center block"
          >
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center text-3xl mx-auto mb-4 group-hover:scale-110 transition-transform">
              🍇
            </div>
            <h3 class="font-serif text-lg font-bold text-text group-hover:text-primary transition-colors mb-1">
              Viță de Vie
            </h3>
            <p class="text-xs text-text-muted">Soiuri de masă și soiuri vinicole consacrate</p>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
          <div>
            <span class="text-xs uppercase tracking-wider text-primary font-semibold block mb-1">
              Disponibile Acum
            </span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-primary-dark">
              Produse Recomandate pentru Sezon
            </h2>
          </div>
          <AppButton variant="outline" to="/produse" class="mt-4 md:mt-0">
            Vezi Toate Produsele
          </AppButton>
        </div>

        <ProductGrid
          :products="products"
          :loading="productsStatus === 'pending'"
        />
      </div>
    </section>

    <!-- About Pepiniera Teaser -->
    <section class="py-20 bg-primary-dark text-white relative overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-6">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-accent/20 text-accent uppercase tracking-wider">
              Despre Ciumbrud
            </span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold leading-tight">
              Pepiniera Ciumbrud — Satul Rozelor și Tradiția Pomiculturii
            </h2>
            <p class="text-white/80 leading-relaxed text-sm sm:text-base">
              Localitatea Ciumbrud din județul Alba este renumită în toată țara pentru meșteșugul altoirii și cultivării plantelor. Pământul roditor și microclimatul special de pe valea Mureșului ne permit să obținem puieți viguroși, cu rădăcini bine dezvoltate.
            </p>
            <p class="text-white/80 leading-relaxed text-sm sm:text-base">
              Fiecare pom fructifer și fiecare butaș de trandafir este îngrijit individual, respectând toate normele fitosanitare europene.
            </p>
            <div class="pt-2">
              <AppButton variant="warm" to="/despre-noi">
                Citește Mai Mult Despre Noi
              </AppButton>
            </div>
          </div>

          <div class="relative">
            <div class="bg-white/10 backdrop-blur-md p-8 rounded-3xl border border-white/20 shadow-2xl space-y-6">
              <div class="border-l-4 border-accent pl-4">
                <span class="text-3xl font-serif font-bold text-accent">100%</span>
                <p class="text-xs uppercase tracking-wider text-white/70">Aclimatizate în România</p>
              </div>
              <div class="border-l-4 border-accent pl-4">
                <span class="text-3xl font-serif font-bold text-accent">+30 Ani</span>
                <p class="text-xs uppercase tracking-wider text-white/70">Experiență în altoire și selecție</p>
              </div>
              <div class="border-l-4 border-accent pl-4">
                <span class="text-3xl font-serif font-bold text-accent">Livrare Sigură</span>
                <p class="text-xs uppercase tracking-wider text-white/70">Pachete termoizolate pentru protecția rădăcinilor</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

