export type CookieConsentChoice = 'all' | 'necessary' | null

export const COOKIE_CONSENT_KEY = 'pcs_cookie_consent'

export const useCookieConsent = () => {
  const consentCookie = useCookie<CookieConsentChoice>(COOKIE_CONSENT_KEY, {
    maxAge: 60 * 60 * 24 * 180, // 180 days
    sameSite: 'lax',
    path: '/',
    default: () => null
  })

  const isPreferencesOpen = useState<boolean>('pcs_cookie_preferences_open', () => false)

  // Synchronize with localStorage on client if cookie was cleared or vice versa
  if (import.meta.client) {
    try {
      const stored = window.localStorage.getItem(COOKIE_CONSENT_KEY) as CookieConsentChoice
      if (!consentCookie.value && (stored === 'all' || stored === 'necessary')) {
        consentCookie.value = stored
      } else if (consentCookie.value && stored !== consentCookie.value) {
        window.localStorage.setItem(COOKIE_CONSENT_KEY, consentCookie.value)
      }
    } catch {
      // Ignore storage access errors in private browsing
    }
  }

  const hasDecided = computed(() => consentCookie.value === 'all' || consentCookie.value === 'necessary')
  const hasAnalyticsConsent = computed(() => consentCookie.value === 'all')
  const shouldShowBanner = computed(() => !hasDecided.value || isPreferencesOpen.value)

  const setConsent = (choice: 'all' | 'necessary') => {
    consentCookie.value = choice
    isPreferencesOpen.value = false
    if (import.meta.client) {
      try {
        window.localStorage.setItem(COOKIE_CONSENT_KEY, choice)
      } catch {
        // Ignore storage errors
      }
    }
  }

  const acceptAll = () => setConsent('all')
  const acceptNecessaryOnly = () => setConsent('necessary')
  const openPreferences = () => {
    isPreferencesOpen.value = true
  }
  const closePreferences = () => {
    if (hasDecided.value) {
      isPreferencesOpen.value = false
    }
  }

  return {
    consent: consentCookie,
    hasDecided,
    hasAnalyticsConsent,
    shouldShowBanner,
    isPreferencesOpen,
    acceptAll,
    acceptNecessaryOnly,
    openPreferences,
    closePreferences
  }
}
