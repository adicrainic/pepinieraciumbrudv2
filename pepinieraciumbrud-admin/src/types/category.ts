export interface CategoryImage {
  id?: number
  src: string
  name?: string
  title?: string
  alt?: string
}

export interface Category {
  id: number
  name: string
  slug: string
  parent: number
  description: string
  count: number
  image: CategoryImage | null
}

