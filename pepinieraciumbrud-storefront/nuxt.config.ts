// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: false },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json/wc/store/v1',
      wpBase: process.env.NUXT_PUBLIC_WP_BASE || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json',
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'http://localhost:3000',
    },
  },

  app: {
    head: {
      htmlAttrs: { lang: 'ro' },
      title: 'Pepiniera Ciumbrud – Pomi Fructiferi, Trandafiri și Plante Ornamentale',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
          name: 'description',
          content: 'Pepiniera Ciumbrud oferă o gamă variată de pomi fructiferi certificați, trandafiri și plante ornamentale de cea mai înaltă calitate, aclimatizate în România.',
        },
        { property: 'og:site_name', content: 'Pepiniera Ciumbrud' },
        { property: 'og:locale', content: 'ro_RO' },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap',
        },
      ],
    },
  },

  css: ['~/assets/css/main.css'],
})

