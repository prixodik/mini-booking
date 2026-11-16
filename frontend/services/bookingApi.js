import { request } from './httpClient'

/** GET /api/slots → { data: Slot[], meta: { timezone, server_time } } */
export function getSlots() {
  return request('/slots')
}

/** POST /api/bookings → { data: { id } } */
export function createBooking({ slotId, name, email }) {
  return request('/bookings', {
    method: 'POST',
    body: { slot_id: slotId, customer_name: name, customer_email: email },
  })
}

/** DELETE /api/bookings/{id} → 204 */
export function cancelBooking(bookingId) {
  return request(`/bookings/${bookingId}`, { method: 'DELETE' })
}
