import { describe, it, beforeEach } from 'node:test'
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { createPinia, setActivePinia } from 'pinia'
import { StoreApiService, storeApi } from '~/services/storeApi'
import { useCartStore } from '~/stores/cart'
import { useProducts } from '~/composables/useProducts'
import type { Cart } from '~/types/cart'

// Provide Nuxt runtime globals & localStorage for isolated Storefront unit/integration tests
const storage = new Map<string, string>()
;(globalThis as any).localStorage = {
  getItem: (key: string) => storage.get(key) ?? null,
  setItem: (key: string, val: string) => storage.set(key, val),
  removeItem: (key: string) => storage.delete(key)
}
;(globalThis as any).useRuntimeConfig = () => ({
  public: {
    apiBase: 'http://localhost/wp-json/wc/store/v1'
  }
})

interface RecordedRequest {
  url: string
  options: Record<string, any>
}

describe('Storefront Order Placement & Cart Lifecycle', () => {
  let recordedRequests: RecordedRequest[] = []
  let mockCartState: Cart

  const createEmptyCart = (): Cart => ({
    items: [],
    items_count: 0,
    items_weight: 0,
    needs_payment: false,
    needs_shipping: false,
    totals: {
      total_items: '0',
      total_items_tax: '0',
      total_fees: '0',
      total_fees_tax: '0',
      total_discount: '0',
      total_discount_tax: '0',
      total_shipping: '0',
      total_shipping_tax: '0',
      total_price: '0',
      total_tax: '0',
      currency_code: 'RON',
      currency_symbol: 'lei',
      currency_minor_unit: 2,
      currency_decimal_separator: ',',
      currency_thousand_separator: '.',
      currency_prefix: '',
      currency_suffix: ' lei'
    }
  })

  beforeEach(() => {
    setActivePinia(createPinia())
    storage.clear()
    recordedRequests = []
    mockCartState = createEmptyCart()

    // Mock $fetch.raw used by StoreApiService
    ;(globalThis as any).$fetch = {
      raw: async (url: string, options: Record<string, any> = {}) => {
        recordedRequests.push({ url, options })

        const headers = new Map<string, string>([
          ['Nonce', 'test-store-nonce-123'],
          ['Cart-Token', 'test-cart-jwt-token-xyz']
        ])

        if (url.endsWith('/cart/add-item')) {
          const { id, quantity } = options.body
          const unitPriceMinor = 3500 // 35.00 RON
          const existing = mockCartState.items.find((i) => i.id === id)
          if (existing) {
            existing.quantity += quantity
            existing.totals.line_total = String(existing.quantity * unitPriceMinor)
          } else {
            mockCartState.items.push({
              key: `item_${id}`,
              id,
              quantity,
              name: 'Măr Florina — Pom Fructifer',
              short_description: 'Soi de iarnă rezistent',
              description: '',
              sku: 'MAR-FLO-01',
              images: [],
              prices: {
                price: String(unitPriceMinor),
                regular_price: String(unitPriceMinor),
                sale_price: String(unitPriceMinor),
                currency_code: 'RON',
                currency_symbol: 'lei',
                currency_minor_unit: 2,
                currency_prefix: '',
                currency_suffix: ' lei'
              },
              totals: {
                line_subtotal: String(quantity * unitPriceMinor),
                line_subtotal_tax: '0',
                line_total: String(quantity * unitPriceMinor),
                line_total_tax: '0',
                currency_code: 'RON',
                currency_symbol: 'lei',
                currency_minor_unit: 2,
                currency_prefix: '',
                currency_suffix: ' lei'
              }
            })
          }
          const totalCount = mockCartState.items.reduce((acc, item) => acc + item.quantity, 0)
          const totalMinor = mockCartState.items.reduce((acc, item) => acc + Number(item.totals.line_total), 0)
          const shippingMinor = totalCount > 0 ? 3000 : 0
          mockCartState.items_count = totalCount
          mockCartState.totals.total_items = String(totalMinor)
          mockCartState.totals.total_shipping = String(shippingMinor)
          mockCartState.totals.total_price = String(totalMinor + shippingMinor)
          return { headers, _data: structuredClone(mockCartState) }
        }

        if (url.endsWith('/cart/update-item')) {
          const { key, quantity } = options.body
          const unitPriceMinor = 3500
          const existing = mockCartState.items.find((i) => i.key === key)
          if (existing) {
            existing.quantity = quantity
            existing.totals.line_total = String(quantity * unitPriceMinor)
          }
          const totalCount = mockCartState.items.reduce((acc, item) => acc + item.quantity, 0)
          const totalMinor = mockCartState.items.reduce((acc, item) => acc + Number(item.totals.line_total), 0)
          const shippingMinor = totalCount > 0 ? 3000 : 0
          mockCartState.items_count = totalCount
          mockCartState.totals.total_items = String(totalMinor)
          mockCartState.totals.total_shipping = String(shippingMinor)
          mockCartState.totals.total_price = String(totalMinor + shippingMinor)
          return { headers, _data: structuredClone(mockCartState) }
        }

        if (url.endsWith('/cart/remove-item')) {
          const { key } = options.body
          mockCartState.items = mockCartState.items.filter((i) => i.key !== key)
          const totalCount = mockCartState.items.reduce((acc, item) => acc + item.quantity, 0)
          const totalMinor = mockCartState.items.reduce((acc, item) => acc + Number(item.totals.line_total), 0)
          const shippingMinor = totalCount > 0 ? 3000 : 0
          mockCartState.items_count = totalCount
          mockCartState.totals.total_items = String(totalMinor)
          mockCartState.totals.total_shipping = String(shippingMinor)
          mockCartState.totals.total_price = String(totalMinor + shippingMinor)
          return { headers, _data: structuredClone(mockCartState) }
        }

        if (url.endsWith('/checkout')) {
          const payload = options.body
          assert.equal(payload.payment_method, 'cod', 'Checkout uses COD payment method')
          assert.equal(payload.billing_address.country, 'RO', 'Checkout uses RO country code')
          // Server empties the cart after order creation
          mockCartState = createEmptyCart()
          return {
            headers,
            _data: {
              order_id: 1042,
              status: 'pending',
              payment_method: payload.payment_method,
              billing_address: payload.billing_address,
              shipping_address: payload.shipping_address,
              customer_note: payload.customer_note
            }
          }
        }

        if (url.includes('/products')) {
          return {
            headers,
            _data: [
              { id: 1, name: 'Produs În Stoc', is_in_stock: true },
              { id: 2, name: 'Produs Epuizat', is_in_stock: false }
            ]
          }
        }

        if (url.endsWith('/cart')) {
          return { headers, _data: structuredClone(mockCartState) }
        }

        throw new Error(`Unexpected request URL: ${url}`)
      }
    }
  })

  it('adds items to cart, updates quantities, and computes RON totals accurately', async () => {
    const cartStore = useCartStore()

    await cartStore.fetchCart()
    assert.equal(cartStore.itemCount, 0)
    assert.equal(cartStore.formattedTotal, '0,00 lei')

    // Add 3 units of product #77 (3 x 35.00 RON = 105.00 RON + 30.00 RON shipping = 135.00 RON)
    await cartStore.addItem(77, 3)
    assert.equal(cartStore.itemCount, 3)
    assert.equal(cartStore.getItemQuantity(77), 3)
    assert.equal(cartStore.subtotal, '105.00')
    assert.equal(cartStore.formattedShipping, '30,00 lei')
    assert.equal(cartStore.total, '135.00')
    assert.equal(cartStore.formattedSubtotal, '105,00 lei')
    assert.equal(cartStore.formattedTotal, '135,00 lei')
    assert.equal(cartStore.isOpen, true)

    // Update quantity to 5 units (5 x 35.00 RON = 175.00 RON + 30.00 RON shipping = 205.00 RON)
    await cartStore.updateItem('item_77', 5)
    assert.equal(cartStore.itemCount, 5)
    assert.equal(cartStore.formattedSubtotal, '175,00 lei')
    assert.equal(cartStore.formattedShipping, '30,00 lei')
    assert.equal(cartStore.formattedTotal, '205,00 lei')

    // Setting quantity to 0 removes the item
    await cartStore.updateItem('item_77', 0)
    assert.equal(cartStore.itemCount, 0)
    assert.equal(cartStore.formattedTotal, '0,00 lei')
  })

  it('completes the full checkout order placement flow and resets the cart', async () => {
    const cartStore = useCartStore()

    // 1. Customer adds 3 trees to the cart (105.00 RON + 30.00 RON shipping = 135.00 RON)
    await cartStore.addItem(77, 3)
    assert.equal(cartStore.itemCount, 3)
    assert.equal(cartStore.formattedSubtotal, '105,00 lei')
    assert.equal(cartStore.formattedShipping, '30,00 lei')
    assert.equal(cartStore.formattedTotal, '135,00 lei')

    // 2. Customer fills checkout form and submits order via storeApi.checkout
    const checkoutPayload = {
      billing_address: {
        first_name: 'Ion',
        last_name: 'Popescu',
        email: 'ion.popescu@example.com',
        phone: '0740111222',
        address_1: 'Strada Principală Nr. 10',
        city: 'Ciumbrud',
        state: 'AB',
        postcode: '515200',
        country: 'RO'
      },
      shipping_address: {
        first_name: 'Ion',
        last_name: 'Popescu',
        phone: '0740111222',
        address_1: 'Strada Principală Nr. 10',
        city: 'Ciumbrud',
        state: 'AB',
        postcode: '515200',
        country: 'RO'
      },
      customer_note: 'Rog apel înainte de livrare.',
      payment_method: 'cod'
    }

    const orderResponse = await storeApi.checkout(checkoutPayload)
    assert.equal(orderResponse.order_id, 1042)
    assert.equal(orderResponse.status, 'pending')

    // 3. Storefront clears local cart and re-fetches empty server cart
    cartStore.clearLocalCart()
    assert.equal(cartStore.itemCount, 0)
    assert.equal(cartStore.isOpen, false)

    await cartStore.fetchCart()
    assert.equal(cartStore.itemCount, 0)
    assert.equal(cartStore.formattedTotal, '0,00 lei')

    // Verify checkout POST endpoint was called with credentials: 'include' and session headers
    const checkoutReq = recordedRequests.find((r) => r.url.endsWith('/checkout'))
    assert.ok(checkoutReq, 'POST /checkout was called')
    assert.equal(checkoutReq.options.method, 'POST')
    assert.equal(checkoutReq.options.credentials, 'include')
    assert.equal(checkoutReq.options.headers.Nonce, 'test-store-nonce-123')
    assert.equal(checkoutReq.options.headers['Cart-Token'], 'test-cart-jwt-token-xyz')
  })

  it('filters out products that are out of stock for homepage seasonal recommendations', async () => {
    // 1. Fetch products using the seasonal recommendations query parameters
    const rawProducts = await storeApi.getProducts({ per_page: 9, stock_status: 'instock' })
    const productsReq = recordedRequests.find((r) => r.url.includes('/products'))
    assert.ok(productsReq, 'GET /products was called')
    assert.ok(productsReq.url.includes('stock_status=instock'), 'Request includes stock_status=instock')
    assert.ok(productsReq.url.includes('per_page=9'), 'Request includes per_page=9')

    // 2. Verify in-memory defensive filtering discards out of stock items
    const availableProducts = rawProducts.filter((p: any) => p.is_in_stock)
    assert.equal(availableProducts.length, 1)
    assert.equal(availableProducts[0].name, 'Produs În Stoc')
    assert.equal(availableProducts[0].is_in_stock, true)
  })

  it('passes rose colour, fragrance, and minor-unit price filters to Store API paged products query', async () => {
    const res = await storeApi.getProductsPaged({
      category: 'trandafiri',
      color: 'Roșu',
      fragrance: 'Intens',
      min_price: 2500,
      max_price: 2800,
      orderby: 'price',
      order: 'asc'
    })
    const req = recordedRequests.find((r) => r.url.includes('/products?'))
    assert.ok(req, 'GET /products paged request was called')
    assert.ok(req.url.includes('category=trandafiri'), 'Includes category=trandafiri')
    assert.ok(req.url.includes('color=Ro%C8%99u'), 'Includes encoded color=Roșu')
    assert.ok(req.url.includes('fragrance=Intens'), 'Includes fragrance=Intens')
    assert.ok(req.url.includes('min_price=2500'), 'Includes min_price=2500')
    assert.ok(req.url.includes('max_price=2800'), 'Includes max_price=2800')
    assert.equal(res.items.length, 2)
  })

  it('rejects adding more items than available in stock and surfaces decoded Romanian error message', async () => {
    const cartStore = useCartStore()

    // Override $fetch.raw to simulate a 1-stock product limit rejection from WooCommerce Store API
    const originalFetch = (globalThis as any).$fetch.raw
    ;(globalThis as any).$fetch.raw = async (url: string, options: Record<string, any> = {}) => {
      if (url.endsWith('/cart/add-item') && options.body?.quantity > 1) {
        const err: any = new Error('HTTP 400 Bad Request')
        err.data = {
          code: 'woocommerce_rest_product_not_enough_stock',
          message:
            'Nu poți să adaugi cantitatea &bdquo;Măr Florina — Pom Fructifer&rdquo; în coș deoarece stocul este insuficient (1 rămase).'
        }
        throw err
      }
      if (url.endsWith('/cart/update-item') && options.body?.quantity > 1) {
        const err: any = new Error('HTTP 400 Bad Request')
        err.data = {
          code: 'woocommerce_rest_invalid_quantity',
          message:
            'Cantitatea maximă de &quot;Măr Florina — Pom Fructifer&quot; permisă în coș este 1'
        }
        throw err
      }
      return originalFetch(url, options)
    }

    // 1. Try adding 2 items when only 1 is in stock
    await cartStore.addItem(77, 2)
    assert.equal(cartStore.itemCount, 0, 'Cart remains empty when add-to-cart exceeds stock')
    assert.equal(
      cartStore.error,
      'Nu poți să adaugi cantitatea „Măr Florina — Pom Fructifer” în coș deoarece stocul este insuficient (1 rămase).',
      'HTML entities in WooCommerce stock error message are decoded cleanly'
    )
    assert.equal(cartStore.isOpen, true, 'Cart drawer opens to show stock error message')

    // 2. Adding 1 item (within stock limit) succeeds and clears the error
    await cartStore.addItem(77, 1)
    assert.equal(cartStore.itemCount, 1)
    assert.equal(cartStore.error, null)

    // 3. Trying to increase cart item quantity to 2 fails and sets decoded error
    await assert.rejects(async () => {
      await cartStore.updateItem('item_77', 2)
    })
    assert.equal(cartStore.itemCount, 1, 'Item quantity stays at 1 after failed update')
    assert.equal(
      cartStore.error,
      'Cantitatea maximă de "Măr Florina — Pom Fructifer" permisă în coș este 1'
    )
  })

  it('orders in-stock products before out-of-stock products while preserving relative order', async () => {
    const { sortInStockFirst } = useProducts()
    const mixed = [
      { id: 227, name: 'Photinia Red Robin', is_in_stock: false },
      { id: 226, name: 'Cornus Alba Sibirica', is_in_stock: true },
      { id: 225, name: 'Lavandă Hidcote', is_in_stock: true },
      { id: 199, name: 'Trandafir Epuizat', is_in_stock: false },
      { id: 101, name: 'Măr Florina', is_in_stock: true }
    ]
    const sorted = sortInStockFirst(mixed)
    assert.deepEqual(
      sorted.map((p) => p.id),
      [226, 225, 101, 227, 199],
      'In-stock items come first in their original relative order, followed by out-of-stock items at the end'
    )
  })

  it('shows product card badges only for exceptions (never "În stoc"), normalizes 2 spec chips, and detects optional 2nd hover photo', () => {
    const {
      getProductExceptionBadges,
      getProductCardSpecs,
      getSecondaryProductImage
    } = useProducts()

    // 1. Standard in-stock product has ZERO badges (no "În stoc" noise)
    const standardInStock: any = {
      id: 201,
      name: 'Trandafir Pink Panther',
      slug: 'trandafir-pink-panther',
      is_in_stock: true,
      on_sale: false,
      low_stock_remaining: null,
      quantity_limit: 50,
      prices: { price: '2500', regular_price: '2500', sale_price: '2500', currency_minor_unit: 2 },
      images: [{ id: 1, src: '/img/pink-1.jpg', thumbnail: '/img/pink-1.jpg', alt: 'Pink 1' }],
      categories: [{ id: 10, name: 'Trandafiri Teahibrizi', slug: 'trandafiri-teahibrizi' }],
      tags: [],
      attributes: [
        { name: 'Culoare', terms: [{ name: 'Roz' }] },
        { name: 'Parfum', terms: [{ name: 'Moderat' }] },
        { name: 'Înălțime la maturitate', terms: [{ name: '90 - 120 cm' }] }
      ]
    }
    assert.deepEqual(getProductExceptionBadges(standardInStock), [], 'Normal in-stock product shows no badges')
    assert.equal(getSecondaryProductImage(standardInStock), null, 'Single-photo product has no hover secondary image')
    assert.deepEqual(getProductCardSpecs(standardInStock), [
      { label: 'Culoare', value: 'Roz' },
      { label: 'Parfum', value: 'Moderat' }
    ])

    // 2. Out-of-stock product shows "Stoc epuizat"
    const outOfStock: any = {
      ...standardInStock,
      id: 227,
      is_in_stock: false
    }
    assert.deepEqual(
      getProductExceptionBadges(outOfStock).map((b) => b.label),
      ['Stoc epuizat']
    )

    // 3. Low-stock + New + Sale product shows exception badges ("-20%", "Ultimele bucăți", "Nou")
    const exceptionProduct: any = {
      ...standardInStock,
      id: 112,
      on_sale: true,
      low_stock_remaining: 3,
      prices: { price: '2000', regular_price: '2500', sale_price: '2000', currency_minor_unit: 2 },
      tags: [{ id: 99, name: 'Nou', slug: 'nou' }],
      images: [
        { id: 1, src: '/img/pink-1.jpg', thumbnail: '/img/pink-1.jpg', alt: 'Pink 1' },
        { id: 2, src: '/img/pink-2.jpg', thumbnail: '/img/pink-2.jpg', alt: 'Pink 2' }
      ],
      attributes: []
    }
    assert.deepEqual(
      getProductExceptionBadges(exceptionProduct).map((b) => b.label),
      ['-20%', 'Ultimele bucăți', 'Nou']
    )
    assert.equal(
      getSecondaryProductImage(exceptionProduct),
      '/img/pink-2.jpg',
      'Product with 2 distinct photos returns second photo URL for hover'
    )
    assert.equal(
      getProductCardSpecs(exceptionProduct).length,
      2,
      'Product without custom attributes still gets 2 consistent fallback spec chips'
    )
  })

  it('orders homepage sections into Discover → Explore → Trust → Choose → Buy journey', () => {
    const homeSource = readFileSync(resolve(process.cwd(), 'pages/index.vue'), 'utf8')

    const orderedSectionAnchors = [
      'id="colectii-trandafiri"',
      'id="trandafiri-populari"',
      'id="incredere-garantie"',
      'id="alte-categorii"',
      'id="cum-livram"',
      'id="newsletter-calendar"'
    ]

    const indices = orderedSectionAnchors.map((anchor) => {
      const idx = homeSource.indexOf(anchor)
      assert.ok(idx > -1, `Homepage includes section ${anchor}`)
      return idx
    })

    for (let i = 1; i < indices.length; i++) {
      assert.ok(
        indices[i] > indices[i - 1],
        `Expected ${orderedSectionAnchors[i - 1]} to appear before ${orderedSectionAnchors[i]}`
      )
    }

    // Verify rose collections include colour, scent, and use entry points
    assert.ok(homeSource.includes("category=trandafiri&fragrance=Intens"), 'Includes rose scent collection link')
    assert.ok(homeSource.includes("category=trandafiri&color="), 'Includes rose colour swatch links')
    assert.ok(homeSource.includes("category=teahibrizi"), 'Includes rose use/habit collection link')
  })

  it('enforces symmetric button radii, aligned equal-height category cards, clean backgrounds, and balanced About page sections', () => {
    const buttonSource = readFileSync(resolve(process.cwd(), 'components/ui/AppButton.vue'), 'utf8')
    const homeSource = readFileSync(resolve(process.cwd(), 'pages/index.vue'), 'utf8')
    const aboutSource = readFileSync(resolve(process.cwd(), 'pages/despre-noi.vue'), 'utf8')
    const defaultLayoutSource = readFileSync(resolve(process.cwd(), 'layouts/default.vue'), 'utf8')
    const minimalLayoutSource = readFileSync(resolve(process.cwd(), 'layouts/minimal.vue'), 'utf8')

    // 1. Symmetric button corner radius
    assert.ok(!buttonSource.includes('rounded-tr-'), 'AppButton does not use asymmetric rounded-tr-*')
    assert.ok(!buttonSource.includes('rounded-bl-'), 'AppButton does not use asymmetric rounded-bl-*')
    assert.ok(!homeSource.includes('rounded-tr-'), 'Homepage buttons do not use asymmetric rounded-tr-*')
    assert.ok(!homeSource.includes('rounded-bl-'), 'Homepage buttons do not use asymmetric rounded-bl-*')

    // 2. No background watermark overlapping hero text or stain-like background shapes on content pages
    assert.ok(!homeSource.includes('text-white/15'), 'Hero text background watermark is removed')
    assert.ok(!defaultLayoutSource.includes('LogoMark'), 'Default layout removes stain-like fixed background watermark')
    assert.ok(!minimalLayoutSource.includes('LogoMark'), 'Minimal layout removes stain-like fixed background watermark')

    // 3. Equal-height aligned category cards without alternating asymmetric petal classes
    assert.ok(!homeSource.includes("petal-alt"), 'Category cards use symmetric rounded corners instead of alternating petal-alt')
    assert.ok(homeSource.includes('grid md:grid-cols-3 gap-6 sm:gap-8 items-stretch'), 'Category cards grid stretches cards to equal height')

    // 4. Balanced About page sections without empty sticky column voids or background watermarks
    assert.ok(!aboutSource.includes('LogoMark'), 'About page removes overlapping card watermark')
    assert.ok(!aboutSource.includes('lg:sticky'), 'About page timeline uses balanced grid instead of lopsided sticky column')
    assert.ok(aboutSource.includes('grid md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch'), 'About page timeline renders a balanced 3x2 equal-height grid')
  })
})
