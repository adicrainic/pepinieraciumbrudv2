import { describe, it, beforeEach } from 'node:test'
import assert from 'node:assert/strict'
import { api } from '@/services/api'
import { ordersService } from '@/services/ordersService'
import { formatOrderNote, getStatusLabel, getStatusBadge } from '@/utils/order'
import type { Order } from '@/types/order'

// Mock localStorage for JWT token interceptor
const storage = new Map<string, string>()
;(globalThis as any).localStorage = {
  getItem: (key: string) => storage.get(key) ?? null,
  setItem: (key: string, val: string) => storage.set(key, val),
  removeItem: (key: string) => storage.delete(key)
}
;(globalThis as any).window = {
  location: { pathname: '/comenzi/1042', href: '' }
}

describe('Admin Order Lifecycle Management (pending → completed)', () => {
  let mockOrder: Order
  let mockNotes: Array<{ id: number; note: string; date_created: string }>
  let lastAuthHeader: string | undefined

  beforeEach(() => {
    storage.clear()
    storage.set('pcs_admin_token', 'mock-admin-jwt-token-2026')
    lastAuthHeader = undefined

    mockOrder = {
      id: 1042,
      number: '1042',
      status: 'pending',
      date_created: '2026-11-15T10:00:00',
      total: '105.00',
      currency: 'RON',
      payment_method: 'cod',
      payment_method_title: 'Plată Ramburs la Curier',
      customer_note: 'Rog apel înainte de livrare.',
      billing: {
        first_name: 'Ion',
        last_name: 'Popescu',
        email: 'ion.popescu@example.com',
        phone: '0740111222',
        address_1: 'Strada Principală Nr. 10',
        city: 'Ciumbrud',
        state: 'AB',
        postcode: '515200',
        country: 'RO'
      },
      shipping: {
        first_name: 'Ion',
        last_name: 'Popescu',
        address_1: 'Strada Principală Nr. 10',
        city: 'Ciumbrud',
        state: 'AB',
        postcode: '515200',
        country: 'RO'
      },
      line_items: [
        {
          id: 1,
          name: 'Măr Florina — Pom Fructifer',
          product_id: 77,
          quantity: 3,
          price: 35,
          total: '105.00'
        }
      ]
    }

    mockNotes = [
      {
        id: 1,
        note: 'Plată la livrare (Ramburs). Comandă primită în așteptare.',
        date_created: '2026-11-15T10:00:01'
      }
    ]

    // Custom Axios adapter to test full request/response + interceptors without external HTTP
    api.defaults.adapter = async (config) => {
      lastAuthHeader = config.headers?.Authorization as string | undefined
      const url = config.url || ''
      const method = (config.method || 'get').toLowerCase()

      if (method === 'get' && url === '/admin/orders') {
        const filterStatus = config.params?.status
        const list = !filterStatus || filterStatus === mockOrder.status ? [structuredClone(mockOrder)] : []
        return { data: list, status: 200, statusText: 'OK', headers: {}, config }
      }

      if (method === 'get' && url === `/admin/orders/${mockOrder.id}`) {
        return { data: structuredClone(mockOrder), status: 200, statusText: 'OK', headers: {}, config }
      }

      if (method === 'put' && url === `/admin/orders/${mockOrder.id}`) {
        const body = typeof config.data === 'string' ? JSON.parse(config.data) : config.data
        const oldStatus = mockOrder.status
        mockOrder.status = body.status
        mockNotes.unshift({
          id: mockNotes.length + 1,
          note: `Order status changed from ${oldStatus} to ${body.status}.`,
          date_created: new Date().toISOString()
        })
        return { data: structuredClone(mockOrder), status: 200, statusText: 'OK', headers: {}, config }
      }

      if (method === 'get' && url === `/admin/orders/${mockOrder.id}/notes`) {
        return { data: structuredClone(mockNotes), status: 200, statusText: 'OK', headers: {}, config }
      }

      if (method === 'post' && url === `/admin/orders/${mockOrder.id}/notes`) {
        const body = typeof config.data === 'string' ? JSON.parse(config.data) : config.data
        const newNote = {
          id: mockNotes.length + 1,
          note: body.note,
          date_created: new Date().toISOString()
        }
        mockNotes.unshift(newNote)
        return { data: newNote, status: 201, statusText: 'Created', headers: {}, config }
      }

      throw new Error(`Unhandled request: ${method.toUpperCase()} ${url}`)
    }
  })

  it('attaches JWT Bearer token and advances order through all lifecycle statuses until completed', async () => {
    // 1. Fetch newly placed order (initial status: pending)
    const initial = await ordersService.get(1042)
    assert.equal(lastAuthHeader, 'Bearer mock-admin-jwt-token-2026')
    assert.equal(initial.status, 'pending')
    assert.equal(getStatusLabel(initial.status), 'În așteptare (Nouă)')

    // 2. Transition pending -> processing
    const stepProcessing = await ordersService.updateStatus(1042, 'processing')
    assert.equal(stepProcessing.status, 'processing')
    assert.equal(getStatusLabel(stepProcessing.status), 'În procesare')

    // 3. Transition processing -> prepared
    const stepPrepared = await ordersService.updateStatus(1042, 'prepared')
    assert.equal(stepPrepared.status, 'prepared')
    assert.equal(getStatusLabel(stepPrepared.status), 'Procesată (Pregătită)')

    // 4. Add internal packing note during preparation
    await ordersService.addNote(1042, 'Plantele au fost etichetate și pregătite pentru curier.')
    const notesAfterPrep = await ordersService.getNotes(1042)
    assert.equal(notesAfterPrep[0].note, 'Plantele au fost etichetate și pregătite pentru curier.')

    // 5. Transition prepared -> shipped
    const stepShipped = await ordersService.updateStatus(1042, 'shipped')
    assert.equal(stepShipped.status, 'shipped')
    assert.equal(getStatusLabel(stepShipped.status), 'Predată Curier')

    // 6. Transition shipped -> completed
    const stepCompleted = await ordersService.updateStatus(1042, 'completed')
    assert.equal(stepCompleted.status, 'completed')
    assert.equal(getStatusLabel(stepCompleted.status), 'Finalizată (Livrată)')

    // 7. Verify listing by completed status returns the completed order
    const completedOrders = await ordersService.list({ status: 'completed' })
    assert.equal(completedOrders.length, 1)
    assert.equal(completedOrders[0].id, 1042)
    assert.equal(completedOrders[0].status, 'completed')
  })

  it('formats order status labels, badges, and Romanian order notes across the lifecycle', () => {
    assert.equal(getStatusLabel('pending'), 'În așteptare (Nouă)')
    assert.equal(getStatusLabel('processing'), 'În procesare')
    assert.equal(getStatusLabel('prepared'), 'Procesată (Pregătită)')
    assert.equal(getStatusLabel('shipped'), 'Predată Curier')
    assert.equal(getStatusLabel('completed'), 'Finalizată (Livrată)')
    assert.equal(getStatusLabel('cancelled'), 'Anulată')

    assert.ok(getStatusBadge('completed').includes('emerald'))
    assert.ok(getStatusBadge('shipped').includes('cyan'))
    assert.ok(getStatusBadge('prepared').includes('indigo'))

    assert.equal(
      formatOrderNote('Order status changed from Pending to Processing.'),
      "Starea comenzii a fost modificată din 'În așteptare (Nouă)' în 'În procesare'."
    )
    assert.equal(
      formatOrderNote('Order status changed from Processing to Prepared.'),
      "Starea comenzii a fost modificată din 'În procesare' în 'Procesată (Pregătită)'."
    )
    assert.equal(
      formatOrderNote('Order status changed from Prepared to Shipped.'),
      "Starea comenzii a fost modificată din 'Procesată (Pregătită)' în 'Predată Curier'."
    )
    assert.equal(
      formatOrderNote('Order status changed from Shipped to Completed.'),
      "Starea comenzii a fost modificată din 'Predată Curier' în 'Finalizată (Livrată)'."
    )
    assert.equal(
      formatOrderNote('Stock levels reduced: Măr Florina (#77) 50→47'),
      'Nivel stoc redus: Măr Florina (#77) 50→47'
    )
  })
})
