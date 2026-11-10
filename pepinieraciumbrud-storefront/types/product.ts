export interface ProductImage {
  id: number
  src: string
  thumbnail: string
  srcset?: string
  alt: string
  name?: string
}

export interface ProductCategoryRef {
  id: number
  name: string
  slug: string
  link?: string
}

export interface ProductPrices {
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
}

export interface Product {
  id: number
  name: string
  slug: string
  parent: number
  type: string
  variation: string
  permalink: string
  sku: string
  short_description: string
  description: string
  on_sale: boolean
  prices: ProductPrices
  price_html: string
  average_rating: string
  review_count: number
  images: ProductImage[]
  categories: ProductCategoryRef[]
  tags: any[]
  attributes: any[]
  variations: any[]
  has_options: boolean
  is_purchasable: boolean
  is_in_stock: boolean
  is_on_backorder: boolean
  low_stock_remaining: number | null
  sold_individually: boolean
  quantity_limit: number
}

