import type { Product } from '~/types/product'

const stripHtml = (html?: string): string => {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, ' ').replace(/&[a-z0-9#]+;/gi, ' ').replace(/\s+/g, ' ').trim()
}

export const useSeo = () => {
  const setPageSeo = (options: {
    title: string
    description?: string
    image?: string
    url?: string
    type?: string
  }) => {
    const siteTitle = 'Pepiniera Ciumbrud'
    const cleanTitle = stripHtml(options.title)
    const fullTitle = `${cleanTitle} | ${siteTitle}`
    const rawDesc = stripHtml(options.description)
    const desc = rawDesc || 'Pepiniera Ciumbrud – pomi fructiferi certificați, trandafiri și plante ornamentale aclimatizate.'

    useSeoMeta({
      title: fullTitle,
      ogTitle: fullTitle,
      description: desc,
      ogDescription: desc,
      ogImage: options.image || '/og-image.jpg',
      ogType: (options.type as any) || 'website',
      twitterCard: 'summary_large_image',
      twitterTitle: fullTitle,
      twitterDescription: desc,
      twitterImage: options.image || '/og-image.jpg'
    })
  }

  const setProductJsonLd = (product: Product) => {
    const price = product.prices ? (parseFloat(product.prices.price) / 100).toFixed(2) : '0.00'
    const cleanName = stripHtml(product.name)
    const cleanDesc = stripHtml(product.short_description || product.description)

    const jsonLd = {
      '@context': 'https://schema.org',
      '@type': 'Product',
      name: cleanName,
      description: cleanDesc,
      image: product.images?.map(img => img.src) || [],
      offers: {
        '@type': 'Offer',
        priceCurrency: 'RON',
        price: price,
        availability: product.is_in_stock
          ? 'https://schema.org/InStock'
          : 'https://schema.org/OutOfStock',
        seller: {
          '@type': 'Organization',
          name: 'Pepiniera Ciumbrud'
        }
      }
    }

    useHead({
      script: [
        {
          type: 'application/ld+json',
          children: JSON.stringify(jsonLd)
        }
      ]
    })
  }

  return {
    setPageSeo,
    setProductJsonLd
  }
}


