export const orderStatusTranslations: Record<string, string> = {
  'pending': 'În așteptare (Nouă)',
  'processing': 'În procesare',
  'prepared': 'Procesată (Pregătită)',
  'shipped': 'Predată Curier',
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
    case 'shipped':
      return 'bg-cyan-50 text-cyan-700 border-cyan-200'
    case 'prepared':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200'
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

const statusNameMap: Record<string, string> = {
  'Pending payment': 'În așteptarea plății',
  'Pending': 'În așteptare (Nouă)',
  'Processing': 'În procesare',
  'Prepared': 'Procesată (Pregătită)',
  'Procesată (Pregătită)': 'Procesată (Pregătită)',
  'Shipped': 'Predată Curier',
  'Predată Curier': 'Predată Curier',
  'On hold': 'În așteptare plată',
  'On-hold': 'În așteptare plată',
  'Completed': 'Finalizată (Livrată)',
  'Finalizată': 'Finalizată (Livrată)',
  'Cancelled': 'Anulată',
  'Refunded': 'Rambursată',
  'Failed': 'Eșuată',
  'Checkout draft': 'Ciornă finalizare',
  'Plată în așteptare': 'În așteptare (Nouă)'
}

const emailNameMap: Record<string, string> = {
  'Processing order': 'Comandă Nouă',
  'Comandă în procesare': 'Comandă Nouă',
  'New order': 'Comandă Nouă',
  'Comandă nouă': 'Comandă Nouă',
  'Comandă Nouă': 'Comandă Nouă',
  'Comandă Noua': 'Comandă Nouă',
  'Completed order': 'Comandă finalizată',
  'Cancelled order': 'Comandă anulată',
  'Failed order': 'Comandă eșuată',
  'Customer on-hold order': 'Comandă în așteptare',
  'Customer note': 'Notă client',
  'Customer invoice': 'Factură client',
  'Reset password': 'Resetare parolă',
  'New account': 'Cont nou'
}

/**
 * Format and translate order notes into clean, friendly Romanian.
 */
export const formatOrderNote = (note: string): string => {
  if (!note) return ''

  let text = note

  // Order status changed from X to Y.
  text = text.replace(
    /(?:Order status changed from|Am schimbat starea comenzii din)\s+([^.]+?)\s+(?:to|în)\s+([^.]+?)\./gi,
    (_, from, to) => {
      const fromLabel = statusNameMap[from.trim()] || from.trim()
      const toLabel = statusNameMap[to.trim()] || to.trim()
      return `Starea comenzii a fost modificată din '${fromLabel}' în '${toLabel}'.`
    }
  )

  // Stock hold
  text = text.replace(
    /Stock hold of (\d+) minutes applied to:/gi,
    'Rezervare stoc timp de $1 minute aplicată pentru:'
  )

  // Stock levels reduced
  text = text.replace(
    /(?:Stock levels reduced|Nivelurile stocurilor s-au redus):\s*(.*)/gi,
    'Nivel stoc redus: $1'
  )

  // Stock levels increased
  text = text.replace(
    /(?:Stock levels increased|Nivelurile stocurilor au crescut):\s*(.*)/gi,
    'Nivel stoc restabilit: $1'
  )

  // Payment upon delivery
  text = text.replace(/Payment to be made upon delivery\./gi, 'Plată ramburs la livrare.')
  text = text.replace(/Payment to be made upon delivery/gi, 'Plată ramburs la livrare')
  text = text.replace(/Plata care se va face la livrare\./gi, 'Plată ramburs la livrare.')

  // Email sent
  text = text.replace(
    /(?:Email\s*"([^"]+)"\s*sent\.|Emailul\s*[„«]([^”»]+)[”»]\s*(?:este|a fost) trimis\.)/gi,
    (_, e1, e2) => {
      const raw = (e1 || e2 || '').trim()
      const emailLabel = emailNameMap[raw] || raw
      return `Emailul «${emailLabel}» a fost trimis.`
    }
  )

  // Email failed to send
  text = text.replace(
    /Email "([^"]+)" failed to send:\s*(.*)/gi,
    (_, rawEmail, reason) => {
      const emailLabel = emailNameMap[rawEmail.trim()] || rawEmail.trim()
      let cleanReason = (reason || '').trim()
      if (cleanReason.toLowerCase().includes('could not instantiate mail function')) {
        cleanReason = 'Funcția de mail nu a putut fi inițializată (SMTP neconfigurat la acel moment).'
      }
      return `Emailul «${emailLabel}» nu a putut fi trimis: ${cleanReason}`
    }
  )

  // Unpaid order cancelled
  text = text.replace(
    /Unpaid order cancelled - time limit reached\./gi,
    'Comanda neplătită a fost anulată automat (timp expirat).'
  )

  // Awaiting payments
  text = text.replace(/Awaiting cash on delivery payment/gi, 'În așteptarea plății ramburs la livrare')
  text = text.replace(/Awaiting BACS payment/gi, 'În așteptarea transferului bancar')
  text = text.replace(/Awaiting cheque payment/gi, 'În așteptarea plății prin cec')

  // Added by customer during checkout
  text = text.replace(/Added by customer during checkout:/gi, 'Adăugată de client la plasarea comenzii:')

  return text
}
