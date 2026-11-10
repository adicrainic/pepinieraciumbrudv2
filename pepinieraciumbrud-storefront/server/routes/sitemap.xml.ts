export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const siteUrl = config.public.siteUrl || 'http://localhost:3000'
  const apiBase = config.public.apiBase || 'http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-json/wc/store/v1'

  // Static public routes
  const staticRoutes = [
    { loc: '/', changefreq: 'daily', priority: 1.0 },
    { loc: '/produse', changefreq: 'daily', priority: 0.9 },
    { loc: '/despre-noi', changefreq: 'monthly', priority: 0.6 },
    { loc: '/contact', changefreq: 'monthly', priority: 0.6 },
    { loc: '/livrare-si-plata', changefreq: 'monthly', priority: 0.5 },
    { loc: '/termeni-si-conditii', changefreq: 'yearly', priority: 0.3 },
    { loc: '/politica-de-confidentialitate', changefreq: 'yearly', priority: 0.3 }
  ]

  // Dynamic product routes
  let productRoutes: { loc: string; changefreq: string; priority: number }[] = []
  try {
    const products: any = await $fetch(`${apiBase}/products?per_page=100`)
    if (Array.isArray(products)) {
      productRoutes = products.map((p: any) => ({
        loc: `/produse/${p.slug}`,
        changefreq: 'weekly',
        priority: 0.8
      }))
    }
  } catch (e) {
    console.warn('[Sitemap] Failed to fetch products for sitemap:', e)
  }

  const allRoutes = [...staticRoutes, ...productRoutes]

  const xmlEntries = allRoutes
    .map(
      (r) => `  <url>
    <loc>${siteUrl}${r.loc}</loc>
    <changefreq>${r.changefreq}</changefreq>
    <priority>${r.priority}</priority>
  </url>`
    )
    .join('\n')

  const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${xmlEntries}
</urlset>`

  setHeader(event, 'Content-Type', 'application/xml')
  return sitemap
})

