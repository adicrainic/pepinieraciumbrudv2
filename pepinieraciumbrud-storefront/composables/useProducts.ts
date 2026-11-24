import type { Product } from '~/types/product'
import { storeApi } from '~/services/storeApi'

export interface ProductExceptionBadge {
  key: 'out_of_stock' | 'sale' | 'last_pieces' | 'new'
  label: string
  variant: 'neutral' | 'danger' | 'warning' | 'success'
}

export interface ProductCardSpec {
  label: string
  value: string
}

export const useProducts = () => {
  const cleanProductText = (input: string | undefined | null): string => {
    if (!input) return ''
    return String(input)
      .replace(/<[^>]*>/g, ' ')
      .replace(/&bdquo;/gi, '„')
      .replace(/&rdquo;/gi, '”')
      .replace(/&ldquo;/gi, '“')
      .replace(/&rsquo;|&#8217;|&#039;|&apos;/gi, '’')
      .replace(/&lsquo;|&#8216;/gi, '‘')
      .replace(/&ndash;|&#8211;/gi, '–')
      .replace(/&mdash;|&#8212;/gi, '—')
      .replace(/&quot;|&#34;/gi, '"')
      .replace(/&nbsp;|&#160;/gi, ' ')
      .replace(/&amp;|&#038;|&#38;/gi, '&')
      .replace(/&#(\d+);/g, (_, code) => String.fromCharCode(Number(code)))
      .replace(/\s+/g, ' ')
      .trim()
  }

  const formatPrice = (prices: any): string => {
    if (!prices) return '0,00 lei'
    const priceStr = prices.price || prices.regular_price || '0'
    const minorUnit = prices.currency_minor_unit ?? 2
    const num = parseFloat(priceStr) / Math.pow(10, minorUnit)
    return `${num.toFixed(2).replace('.', ',')} lei`
  }

  const formatRegularPrice = (prices: any): string | null => {
    if (!prices || !prices.regular_price) return null
    const current = parseFloat(prices.price || '0')
    const regular = parseFloat(prices.regular_price || '0')
    if (regular <= current) return null
    const minorUnit = prices.currency_minor_unit ?? 2
    const num = regular / Math.pow(10, minorUnit)
    return `${num.toFixed(2).replace('.', ',')} lei`
  }

  const getDiscountPercent = (prices: any): number => {
    if (!prices || !prices.regular_price || !prices.price) return 0
    const current = parseFloat(prices.price || '0')
    const regular = parseFloat(prices.regular_price || '0')
    if (regular <= 0 || regular <= current) return 0
    return Math.round(((regular - current) / regular) * 100)
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
    if (categorySlugs.includes('arbusti-ornamentali') || categorySlugs.includes('arbusti') || /ornamental|tuia|ienup[aă]r|buxus|hortens|gard viu|lemn c[aâ]nesc|spiraea/.test(textToMatch)) {
      return '/plants/ornamental.svg'
    }

    return '/plants/default.svg'
  }

  const getSecondaryProductImage = (product: Product): string | null => {
    if (!product?.images || !Array.isArray(product.images) || product.images.length < 2) {
      return null
    }
    const primary = getProductImage(product)
    for (let i = 1; i < product.images.length; i++) {
      const candidate = product.images[i]?.src?.trim()
      if (candidate && candidate !== primary) {
        return candidate
      }
    }
    return null
  }

  const getProductExceptionBadges = (product: Product): ProductExceptionBadge[] => {
    const badges: ProductExceptionBadge[] = []
    if (!product) return badges

    if (!product.is_in_stock) {
      badges.push({
        key: 'out_of_stock',
        label: 'Stoc epuizat',
        variant: 'neutral'
      })
    }

    const discount = getDiscountPercent(product.prices)
    if (discount > 0) {
      badges.push({
        key: 'sale',
        label: `-${discount}%`,
        variant: 'danger'
      })
    } else if (product.on_sale) {
      badges.push({
        key: 'sale',
        label: 'Promoție',
        variant: 'danger'
      })
    }

    if (product.is_in_stock) {
      const lowStock =
        typeof product.low_stock_remaining === 'number' && product.low_stock_remaining > 0
          ? product.low_stock_remaining
          : null
      const maxLimitRaw = product.quantity_limit || (product as any)?.add_to_cart?.maximum
      const maxLimit =
        typeof maxLimitRaw === 'number' && maxLimitRaw > 0 && maxLimitRaw < 9999
          ? maxLimitRaw
          : null

      if ((lowStock !== null && lowStock <= 15) || (maxLimit !== null && maxLimit <= 5)) {
        badges.push({
          key: 'last_pieces',
          label: 'Ultimele bucăți',
          variant: 'warning'
        })
      }

      const hasNewFlag = Boolean((product as any).is_new)
      const hasNewTag =
        Array.isArray(product.tags) &&
        product.tags.some((t: any) =>
          /^(nou|new|noutati|soi-nou)$/i.test(String(t?.slug || t?.name || '').trim())
        )

      if (hasNewFlag || hasNewTag) {
        badges.push({
          key: 'new',
          label: 'Nou',
          variant: 'success'
        })
      }
    }

    return badges
  }

  const compactSpecLabel = (rawName: string): string => {
    const norm = cleanProductText(rawName).toLowerCase()
    if (norm.includes('coacere') || norm.includes('recoltare')) return 'Coacere'
    if (norm.includes('inflorire') || norm.includes('înflorire')) return 'Înflorire'
    if (norm.includes('inaltime') || norm.includes('înălțime')) return 'Înălțime'
    if (norm.includes('distanta') || norm.includes('distanță') || norm.includes('plantare')) return 'Plantare'
    if (norm.includes('portaltoi')) return 'Portaltoi'
    if (norm.includes('culoare')) return 'Culoare'
    if (norm.includes('parfum')) return 'Parfum'
    if (norm.includes('poleniz')) return 'Polenizare'
    return cleanProductText(rawName)
  }

  const compactSpecValue = (rawVal: string): string => {
    let val = cleanProductText(rawVal)
      .replace(/\s*\([^)]*\)/g, '')
      .trim()

    if (val.includes(',')) {
      val = val.split(',')[0].trim()
    }

    val = val
      .replace(/^Sfârșit de\s+/i, '')
      .replace(/\s*-\s*început de\s+/i, ' - ')
      .replace(/\bSeptembrie\b/gi, 'Sept.')
      .replace(/\bOctombrie\b/gi, 'Oct.')
      .replace(/\bNoiembrie\b/gi, 'Nov.')
      .replace(/\bAugust\b/gi, 'Aug.')
      .replace(/\bIulie\b/gi, 'Iul.')
      .replace(/\bIunie\b/gi, 'Iun.')
      .replace(/\bAprilie\b/gi, 'Apr.')
      .replace(/\bMartie\b/gi, 'Mar.')
      .replace(/\bmetri\b/gi, 'm')
      .replace(/\s+/g, ' ')
      .trim()

    if (val.length > 0) {
      val = val.charAt(0).toUpperCase() + val.slice(1)
    }
    return val
  }

  const getProductCardSpecs = (product: Product): ProductCardSpec[] => {
    const specs: ProductCardSpec[] = []

    if (product?.attributes && Array.isArray(product.attributes)) {
      for (const attr of product.attributes) {
        if (!attr?.name) continue
        const rawTerms = Array.isArray(attr.terms)
          ? attr.terms.map((t: any) => t?.name).filter(Boolean).join(', ')
          : Array.isArray(attr.options)
            ? attr.options.filter(Boolean).join(', ')
            : String(attr.value || '')
        const label = compactSpecLabel(attr.name)
        const value = compactSpecValue(rawTerms)
        if (label && value) {
          specs.push({ label, value })
        }
        if (specs.length === 2) break
      }
    }

    if (specs.length < 2) {
      const categorySlugs = (product?.categories || []).map(c => (c.slug || '').toLowerCase())
      const text = `${product?.name || ''} ${product?.slug || ''} ${categorySlugs.join(' ')}`.toLowerCase()

      let fallbacks: ProductCardSpec[] = [
        { label: 'Origine', value: 'Ciumbrud' },
        { label: 'Rădăcină', value: 'Hidrogel' }
      ]

      if (/trandafir|rose|teahibrizi|urcatori/.test(text)) {
        fallbacks = [
          { label: 'Înflorire', value: 'Mai - Oct.' },
          { label: 'Altoire', value: 'În oculaj' }
        ]
      } else if (/m[aă]r|meri|p[aă]r|peri|prun|cire[sș]|vi[sș]in|cais|piersic|nuc|pomi/.test(text)) {
        fallbacks = [
          { label: 'Tip', value: 'Pom altoit' },
          { label: 'Rădăcină', value: 'Hidrogel' }
        ]
      } else if (/zmeur|coac[aă]z|afin|mur|agri[sș]|aronia|goji|c[aă]tin|arbusti-fructiferi/.test(text)) {
        fallbacks = [
          { label: 'Recoltare', value: 'Iun. - Sept.' },
          { label: 'Vigoare', value: 'Aclimatizat' }
        ]
      } else if (/ornamental|hortens|lavand|magnoli|azale|liliac|hibiscus|buxus|spiraea|forsythia/.test(text)) {
        fallbacks = [
          { label: 'Rezistență', value: 'Aclimatizat' },
          { label: 'Rădăcină', value: 'Protejată' }
        ]
      }

      for (const fb of fallbacks) {
        if (specs.length >= 2) break
        if (!specs.some(s => s.label.toLowerCase() === fb.label.toLowerCase())) {
          specs.push(fb)
        }
      }
    }

    return specs.slice(0, 2)
  }

  const sortInStockFirst = <T extends { is_in_stock?: boolean }>(items: T[]): T[] => {
    if (!Array.isArray(items) || items.length <= 1) return items || []
    return [...items].sort((a, b) => {
      const aInStock = Boolean(a?.is_in_stock)
      const bInStock = Boolean(b?.is_in_stock)
      if (aInStock === bInStock) return 0
      return aInStock ? -1 : 1
    })
  }

  const fetchProducts = async (params: Record<string, any> = {}) => {
    const items = await storeApi.getProducts(params)
    return sortInStockFirst(items)
  }

  const fetchProductsPaged = async (params: Record<string, any> = {}) => {
    const res = await storeApi.getProductsPaged(params)
    return {
      ...res,
      items: sortInStockFirst(res.items)
    }
  }

  const fetchProductBySlug = async (slug: string) => {
    return await storeApi.getProductBySlug(slug)
  }

  const fetchCategories = async () => {
    return await storeApi.getCategories()
  }

  return {
    cleanProductText,
    formatPrice,
    formatRegularPrice,
    getDiscountPercent,
    getProductImage,
    getSecondaryProductImage,
    getProductExceptionBadges,
    getProductCardSpecs,
    sortInStockFirst,
    fetchProducts,
    fetchProductsPaged,
    fetchProductBySlug,
    fetchCategories
  }
}
