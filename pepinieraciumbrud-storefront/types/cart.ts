import type { ProductPrices, ProductImage } from './product'

export interface CartItemPrices {
  price: string
  regular_price: string
  sale_price: string
  price_range: any | null
  currency_code: string
  currency_symbol: string
  currency_minor_unit: number
  currency_decimal_separator: string
  currency_thousand_separator: string
  currency_prefix: string
  currency_suffix: string
  raw_prices: {
    precision: number
    price: string
    regular_price: string
    sale_price: string
  }
}

export interface CartTotals {
  total_items: string
  total_items_tax: string
  total_fees: string
  total_fees_tax: string
  total_discount: string
  total_discount_tax: string
  total_shipping: string
  total_shipping_tax: string
  total_price: string
  total_tax: string
  tax_lines: any[]
  currency_code: string
  currency_symbol: string
  currency_minor_unit: number
  currency_decimal_separator: string
  currency_thousand_separator: string
  currency_prefix: string
  currency_suffix: string
}

export interface CartItem {
  key: string
  id: number
  type: string
  quantity: number
  quantity_limits: {
    minimum: number
    maximum: number
    multiple_of: number
    editable: boolean
  }
  name: string
  short_description: string
  description: string
  sku: string
  low_stock_remaining: number | null
  backorders_allowed: boolean
  show_backorder_badge: boolean
  sold_individually: boolean
  permalink: string
  images: ProductImage[]
  variation: any[]
  item_data: any[]
  prices: CartItemPrices
  totals: {
    line_subtotal: string
    line_subtotal_tax: string
    line_total: string
    line_total_tax: string
    currency_code: string
    currency_symbol: string
    currency_minor_unit: number
    currency_decimal_separator: string
    currency_thousand_separator: string
    currency_prefix: string
    currency_suffix: string
  }
  catalog_visibility: string
  extensions: Record<string, any>
}

export interface Cart {
  items: CartItem[]
  coupons: any[]
  fees: any[]
  totals: CartTotals
  shipping_address: Record<string, any>
  billing_address: Record<string, any>
  needs_payment: boolean
  needs_shipping: boolean
  payment_requirements: string[]
  has_calculated_shipping: boolean
  shipping_rates: any[]
  items_count: number
  items_weight: number
  cross_sells: any[]
  errors: any[]
  payment_methods: string[]
  extensions: Record<string, any>
}

