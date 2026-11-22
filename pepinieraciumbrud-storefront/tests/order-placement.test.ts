import { describe, it, beforeEach } from 'node:test'
import assert from 'node:assert/strict'
import { createPinia, setActivePinia } from 'pinia'
import { StoreApiService, storeApi } from '~/services/storeApi'
import { useCartStore } from '~/stores/cart'
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
})
