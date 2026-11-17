import { ref, computed } from 'vue'
import { getSlots, createBooking, cancelBooking } from '~/services/bookingApi'
import { dateKey } from '~/utils/datetime'

/**
 * Состояние страницы бронирования: загрузка расписания, фильтр по дате, создание и отмена броней.
 * Состояние — обычная реактивность Vue. Действия book/cancel возвращают результат
 * { status: 'ok' | 'validation' | 'conflict' | 'notfound' | 'error' | 'busy', message, errors },
 * а показ уведомлений остаётся за страницей.
 */
export function useBookingSlots() {
  const slots = ref([])
  const timezone = ref('UTC')
  const selectedDate = ref(null)
  const loading = ref(false) // загрузка списка
  const submitting = ref(false) // выполняется создание или отмена
  const cancellingSlotId = ref(null) // слот, бронь которого сейчас отменяется
  const loadError = ref(null) // сообщение, если список не загрузился

  const dates = computed(() => [...new Set(slots.value.map((s) => dateKey(s.starts_at, timezone.value)))].sort())

  const slotsOfDay = computed(() =>
    slots.value.filter((s) => dateKey(s.starts_at, timezone.value) === selectedDate.value),
  )

  let loadSeq = 0 // номер последней запущенной загрузки; применяется только её результат

  /** Загружает расписание целиком; фильтр по дате работает на клиенте. */
  async function load({ silent = false } = {}) {
    const seq = ++loadSeq
    if (!silent) loading.value = true
    try {
      const response = await getSlots()
      if (seq !== loadSeq) return false // пришёл ответ на устаревший запрос
      loadError.value = null // сообщение об ошибке убираем только после успешного ответа
      slots.value = response.data
      timezone.value = response.meta.timezone
      if (!dates.value.includes(selectedDate.value)) {
        const today = dateKey(response.meta.server_time, response.meta.timezone)
        selectedDate.value = dates.value.includes(today) ? today : (dates.value[0] ?? null)
      }
      return true
    } catch (error) {
      if (seq === loadSeq) loadError.value = error.message
      return false
    } finally {
      if (seq === loadSeq) loading.value = false
    }
  }

  async function run(action) {
    if (submitting.value) return { status: 'busy' } // защита от повторной отправки
    submitting.value = true
    try {
      await action()
      await load({ silent: true })
      return { status: 'ok' }
    } catch (error) {
      if (error.isValidation && error.errors?.slot_id) {
        // Слота больше нет: ошибка не в полях формы, а в устаревшем списке — ведём себя как при 409.
        await load({ silent: true })
        return { status: 'conflict', message: error.errors.slot_id[0] }
      }
      if (error.isValidation) {
        return { status: 'validation', message: error.message, errors: error.errors ?? {} }
      }
      if (error.isConflict || error.isNotFound) {
        // Слот занят/прошёл или бронь уже отменена: показываем причину и обновляем список.
        await load({ silent: true })
        return { status: error.isConflict ? 'conflict' : 'notfound', message: error.message }
      }
      // Таймаут: запрос мог выполниться на сервере, поэтому показываем актуальный список до повтора.
      if (error.isTimeout) await load({ silent: true })
      return { status: 'error', message: error.message }
    } finally {
      submitting.value = false
    }
  }

  const book = (slot, { name, email }) => run(() => createBooking({ slotId: slot.id, name, email }))

  /** Отменяет бронь слота; пока идёт запрос, cancellingSlotId указывает на этот слот (спиннер на его кнопке). */
  async function cancel(slot) {
    if (submitting.value) return { status: 'busy' }
    cancellingSlotId.value = slot.id
    try {
      return await run(() => cancelBooking(slot.booking_id))
    } finally {
      cancellingSlotId.value = null
    }
  }

  return {
    slots, timezone, dates, selectedDate, slotsOfDay, loading, submitting, cancellingSlotId, loadError,
    load, book, cancel,
  }
}
