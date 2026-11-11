export const orderStatusTranslations: Record<string, string> = {
  'pending': 'În așteptare (Pending)',
  'processing': 'În procesare',
  'on-hold': 'În așteptare plată',
  'completed': 'Finalizată (Livrată)',
  'cancelled': 'Anulată',
  'refunded': 'Rambursată',
  'failed': 'Eșuată'
}

export const getStatusLabel = (status: string): string => {
  return orderStatusTranslations[status] || status
}

export const getStatusBadge = (status: string): string => {
  switch (status) {
    case 'completed':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'processing':
      return 'bg-blue-50 text-blue-700 border-blue-200'
    case 'pending':
      return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'cancelled':
      return 'bg-rose-50 text-rose-700 border-rose-200'
    case 'on-hold':
      return 'bg-orange-50 text-orange-700 border-orange-200'
    case 'refunded':
      return 'bg-purple-50 text-purple-700 border-purple-200'
    case 'failed':
      return 'bg-red-50 text-red-700 border-red-200'
    default:
      return 'bg-gray-50 text-gray-700 border-gray-200'
  }
}
