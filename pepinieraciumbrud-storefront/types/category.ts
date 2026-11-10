export interface CategoryImage {
  id: number
  src: string
  thumbnail?: string
  alt: string
  name?: string
}

export interface Category {
  id: number
  name: string
  slug: string
  description: string
  parent: number
  count: number
  image: CategoryImage | null
  permalink?: string
}

