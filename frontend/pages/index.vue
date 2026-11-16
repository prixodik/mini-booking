<template>
  <section class="page">
    <div class="page__panel">
      <BookingDateFilter v-model="selectedDate" :dates="dates" :disabled="loading" />
    </div>

    <div v-if="loadError" class="page__alert" role="alert">
      <span>{{ loadError }}</span>
      <Button class="page__retry" label="Повторить" icon="pi pi-refresh" :loading="loading" @click="load()" />
    </div>

    <div v-if="loading && !slots.length" class="page__loading" role="status">
      <ProgressSpinner style="width: 40px; height: 40px" stroke-width="5" />
      <span>Загружаем расписание…</span>
    </div>

    <BookingSlotList
      v-else-if="slotsOfDay.length"
      :slots="slotsOfDay"
      :timezone="timezone"
      :busy="submitting"
      @book="openBooking"
      @cancel="askCancel"
    />
    <p v-else-if="!loadError" class="page__empty">На выбранную дату слотов нет.</p>

    <p v-if="slots.length" class="page__tz">Время указано по часовому поясу расписания: {{ timezone }}.</p>

    <BookingDialog
      v-model:visible="dialogVisible"
      :slot-item="activeSlot"
      :timezone="timezone"
      :loading="submitting"
      :server-errors="serverErrors"
      :general-error="generalError"
      @submit="submitBooking"
    />
  </section>
</template>

<script setup>
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const toast = useToast()
const confirm = useConfirm()

const { slots, timezone, dates, selectedDate, slotsOfDay, loading, submitting, loadError, load, book, cancel } = useBookingSlots()

const dialogVisible = ref(false)
const activeSlot = ref(null)
const serverErrors = ref({})
const generalError = ref('')

onMounted(() => load())

function openBooking(slot) {
  activeSlot.value = slot
  serverErrors.value = {}
  generalError.value = ''
  dialogVisible.value = true
}

async function submitBooking(form) {
  serverErrors.value = {}
  generalError.value = ''
  const result = await book(activeSlot.value, form)

  if (result.status === 'ok') {
    dialogVisible.value = false
    toast.add({ severity: 'success', summary: 'Слот забронирован', life: 4000 })
  } else if (result.status === 'validation') {
    serverErrors.value = result.errors
    if (result.errors.slot_id) generalError.value = result.errors.slot_id[0]
  } else if (result.status === 'conflict') {
    dialogVisible.value = false
    toast.add({ severity: 'warn', summary: 'Не удалось забронировать', detail: result.message, life: 6000 })
  } else if (result.status === 'error') {
    generalError.value = result.message // форма остаётся открытой, кнопка превращается в «Повторить»
  }
}

function askCancel(slot) {
  confirm.require({
    header: 'Отмена брони',
    message: 'Отменить бронь этого слота?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Отменить бронь',
    rejectLabel: 'Не отменять',
    acceptProps: { severity: 'danger' },
    rejectProps: { severity: 'secondary', text: true },
    accept: () => cancelBooking(slot),
  })
}

async function cancelBooking(slot) {
  const result = await cancel(slot)

  if (result.status === 'ok') {
    toast.add({ severity: 'success', summary: 'Бронь отменена', life: 4000 })
  } else if (result.status === 'notfound') {
    toast.add({ severity: 'warn', summary: 'Отмена не выполнена', detail: result.message, life: 6000 })
  } else if (result.status === 'error') {
    confirm.require({
      header: 'Не удалось отменить бронь',
      message: result.message,
      icon: 'pi pi-times-circle',
      acceptLabel: 'Повторить',
      rejectLabel: 'Закрыть',
      accept: () => cancelBooking(slot),
    })
  }
}
</script>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: var(--mb-space-4);
}
.page__panel {
  padding: var(--mb-space-4);
  background: var(--mb-color-surface);
  border: 1px solid var(--mb-color-border);
  border-radius: var(--mb-radius-md);
  box-shadow: var(--mb-shadow-card);
}
.page__alert {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--mb-space-3);
  padding: var(--mb-space-3) var(--mb-space-4);
  color: var(--mb-color-danger-fg);
  background: var(--mb-color-danger-bg);
  border-radius: var(--mb-radius-md);
}
.page__retry {
  min-height: var(--mb-tap-min);
}
.page__loading {
  display: flex;
  align-items: center;
  gap: var(--mb-space-3);
  padding: var(--mb-space-5) 0;
}
.page__empty,
.page__tz {
  margin: 0;
  color: var(--mb-color-text-muted);
}
</style>
