export interface ProductImage {
  id?: number
  src: string
  name?: string
  alt?: string
}

export interface ProductCategoryRef {
  id: number
  name: string
  slug?: string
}

export interface Product {
  id: number
  name: string
  slug: string
  status: 'publish' | 'draft' | 'pending' | 'private'
  type?: string
  description: string
  short_description: string
  sku: string
  price: string
  regular_price: string
  sale_price: string
  stock_status: 'instock' | 'outofstock' | 'onbackorder'
  stock_quantity: number | null
  manage_stock: boolean
  low_stock_amount?: number | null
  categories: ProductCategoryRef[]
  images: ProductImage[]
  featured?: boolean
  date_created?: string
}

export interface InventoryItem {
  id: number
  name: string
  sku: string
  stock_status: 'instock' | 'outofstock' | 'onbackorder'
  stock_quantity: number | null
  manage_stock: boolean
  low_stock_amount: number | null
  effective_threshold: number
  low_stock: boolean
  price: string
  permalink?: string
  categories?: ProductCategoryRef[]
  image?: string | null
}

