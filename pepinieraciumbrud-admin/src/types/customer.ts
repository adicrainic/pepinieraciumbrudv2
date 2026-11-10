export interface Customer {
  id: number
  email: string
  first_name: string
  last_name: string
  username: string
  orders_count: number
  total_spent: string
  date_created: string
  billing?: any
  shipping?: any
}

