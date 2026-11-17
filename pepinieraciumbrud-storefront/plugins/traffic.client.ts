export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const wpBase = config.public.wpBase || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json'
  const router = useRouter()

  let lastTrackedPath = ''
  let isInitialLoad = true

  const sendHit = (path: string, utmSource?: string) => {
    if (!path || path === lastTrackedPath) return
    lastTrackedPath = path

    const referrer = isInitialLoad && typeof document !== 'undefined' ? document.referrer || '' : ''
    isInitialLoad = false

    setTimeout(() => {
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

  nuxtApp.hook('page:finish', () => {
    const currentRoute = router.currentRoute.value
    const utm = typeof currentRoute.query?.utm_source === 'string' ? currentRoute.query.utm_source : ''
    sendHit(currentRoute.path, utm)
  })
})
