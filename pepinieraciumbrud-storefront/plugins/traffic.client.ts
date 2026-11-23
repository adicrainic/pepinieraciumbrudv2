import { watch } from 'vue'
import { useCookieConsent } from '~/composables/useCookieConsent'

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const wpBase = config.public.wpBase || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json'
  const router = useRouter()
  const { hasAnalyticsConsent } = useCookieConsent()

  let lastTrackedPath = ''
  let isInitialLoad = true

  const sendHit = (path: string, utmSource?: string) => {
    // Respect visitor cookie/analytics consent: only send traffic hits when analytics consent is granted
    if (!hasAnalyticsConsent.value) return
    if (!path || path === lastTrackedPath) return
    lastTrackedPath = path

    const referrer = isInitialLoad && typeof document !== 'undefined' ? document.referrer || '' : ''
    isInitialLoad = false

    setTimeout(() => {
      if (!hasAnalyticsConsent.value) return
      const title = typeof document !== 'undefined' ? document.title || '' : ''

      $fetch(`${wpBase}/pcs/v1/traffic/track`, {
        method: 'POST',
        body: {
          path,
          title,
          referrer,
          utm_source: utmSource || ''
        },
        keepalive: true
      }).catch(() => {
        // Silent fail so tracking never disrupts storefront UX
      })
    }, 150)
  }

  const trackCurrentRoute = () => {
    const currentRoute = router.currentRoute.value
    if (!currentRoute?.path) return
    const utm = typeof currentRoute.query?.utm_source === 'string' ? currentRoute.query.utm_source : ''
    sendHit(currentRoute.path, utm)
  }

  nuxtApp.hook('page:finish', () => {
    trackCurrentRoute()
  })

  // If the visitor grants analytics consent on the current page, record the initial pageview right away
  watch(hasAnalyticsConsent, (consented) => {
    if (consented) {
      trackCurrentRoute()
    }
  })
})
