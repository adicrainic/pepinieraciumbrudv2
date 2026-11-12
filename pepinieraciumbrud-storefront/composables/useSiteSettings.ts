import type { SiteSettings } from '~/types/settings'

export const defaultSiteSettings: SiteSettings = {
  site_title: 'Pepiniera Ciumbrud',
  site_tagline: 'Pepiniera de familie din 2000',
  site_description: 'Producător autorizat de material săditor pomicol, trandafiri și plante ornamentale de cea mai bună calitate, aclimatizate pentru România.',
  logo_url: '/logo.jpg',
  contact_phone: '+40 700 000 000',
  contact_email: 'contact@pepinieraciumbrud.ro',
  contact_address: 'Sat Ciumbrud, Jud. Alba, România',
  schedule_weekdays: 'Luni – Vineri: 08:00 – 17:00',
  schedule_saturday: 'Sâmbătă: 08:00 – 13:00',
  schedule_sunday: 'Duminică: Închis',
  schedule_notes: 'Comenzile se pot plasa direct prin magazinul online la orice oră. Expedierea se face în ordinea primirii comenzilor.',
  footer_copyright: 'Pepiniera Ciumbrud. Toate drepturile rezervate.'
}

export const useSiteSettings = () => {
  const config = useRuntimeConfig()
  const wpBase = config.public.wpBase || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json'

  const settings = useState<SiteSettings>('site_settings', () => ({ ...defaultSiteSettings }))

  const fetchSettings = async () => {
    try {
      const data = await $fetch<Partial<SiteSettings>>(`${wpBase}/pcs/v1/settings`)
      if (data && typeof data === 'object') {
        settings.value = { ...defaultSiteSettings, ...data, logo_url: data.logo_url || defaultSiteSettings.logo_url }
      }
    } catch (err) {
      // Graceful fallback to default settings if endpoint fails or network is offline
    }
    return settings.value
  }

  return {
    settings,
    fetchSettings
  }
}

