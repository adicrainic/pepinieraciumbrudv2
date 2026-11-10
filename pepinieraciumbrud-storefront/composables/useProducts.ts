import type { Product } from '~/types/product'
import { storeApi } from '~/services/storeApi'

export const useProducts = () => {
  const formatPrice = (prices: any): string => {
    if (!prices) return '0,00 lei'
    const priceStr = prices.price || prices.regular_price || '0'
    const minorUnit = prices.currency_minor_unit ?? 2
    const num = parseFloat(priceStr) / Math.pow(10, minorUnit)
    return `${num.toFixed(2).replace('.', ',')} lei`
  }

  const getProductImage = (product: Product): string => {
    if (product.images && product.images.length > 0 && product.images[0].src) {
      return product.images[0].src
    }

    const categorySlugs = (product.categories || []).map(c => (c.slug || '').toLowerCase())
    const textToMatch = `${product.name || ''} ${product.slug || ''} ${categorySlugs.join(' ')}`.toLowerCase()

    if (categorySlugs.includes('meri') || /m[aă]r|meri|apple/.test(textToMatch)) {
      return '/plants/apple.svg'
    }
    if (categorySlugs.includes('peri') || /p[aă]r|peri|pear/.test(textToMatch)) {
      return '/plants/pear.svg'
    }
    if (categorySlugs.includes('pruni') || /prun|plum/.test(textToMatch)) {
      return '/plants/plum.svg'
    }
    if (categorySlugs.includes('ciresi') || categorySlugs.includes('visini') || /cire[sș]|vi[sș]in|cherry/.test(textToMatch)) {
      return '/plants/cherry.svg'
    }
    if (categorySlugs.includes('caisi') || categorySlugs.includes('piersici') || /cais|piersic|nectarin|apricot|peach/.test(textToMatch)) {
      return '/plants/apricot.svg'
    }
    if (categorySlugs.includes('nuci') || /nuc|walnut/.test(textToMatch)) {
      return '/plants/walnut.svg'
    }
    if (categorySlugs.includes('trandafiri') || categorySlugs.includes('urcatori') || categorySlugs.includes('teahibrizi') || /trandafir|rose/.test(textToMatch)) {
      return '/plants/rose.svg'
    }
    if (categorySlugs.includes('arbusti-fructiferi') || categorySlugs.includes('afin') || categorySlugs.includes('zmeur') || categorySlugs.includes('coacaz') || /afin|zmeur|coac[aă]z|mur|berry|blueberry|raspberry/.test(textToMatch)) {
      return '/plants/berry.svg'
    }
    if (categorySlugs.includes('vita-de-vie') || categorySlugs.includes('soiuri-de-masa') || categorySlugs.includes('soiuri-de-vin') || /vi[tț][aă]|strugur|grape|vine/.test(textToMatch)) {
      return '/plants/grape.svg'
    }

    return '/plants/default.svg'
  }

  const fetchProducts = async (params: Record<string, any> = {}) => {
    return await storeApi.getProducts(params)
  }

  const fetchProductBySlug = async (slug: string) => {
    return await storeApi.getProductBySlug(slug)
  }

  const fetchCategories = async () => {
    return await storeApi.getCategories()
  }

  return {
    formatPrice,
    getProductImage,
    fetchProducts,
    fetchProductBySlug,
    fetchCategories
  }
}

