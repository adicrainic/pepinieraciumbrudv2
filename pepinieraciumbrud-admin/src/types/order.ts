export interface OrderAddress {
  first_name: string
  last_name: string
  address_1: string
  city: string
  state: string
  postcode: string
  country: string
  email?: string
  phone?: string
}

export interface OrderLineItem {
  id: number
  name: string
  product_id: number
  quantity: number
  subtotal: string
  total: string
  price: number
}

export interface Order {
  id: number
  status: 'pending' | 'processing' | 'on-hold' | 'completed' | 'cancelled' | 'refunded' | 'failed'
  currency: string
  total: string
  date_created: string
  billing: OrderAddress
  shipping: OrderAddress
  payment_method_title: string
  customer_note?: string
  line_items: OrderLineItem[]
}

