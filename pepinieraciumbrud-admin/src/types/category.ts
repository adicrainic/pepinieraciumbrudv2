export interface Category {
  id: number
  name: string
  slug: string
  parent: number
  description: string
  count: number
  image: {
    id: number
    src: string
  } | null
}

