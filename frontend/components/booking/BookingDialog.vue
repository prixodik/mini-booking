<template>
  <Dialog
    :visible="visible"
    modal
    header="Бронирование слота"
    :closable="!loading"
    :close-on-escape="!loading"
    :style="{ width: '26rem' }"
    :breakpoints="{ '575px': 'calc(100vw - 32px)' }"
    @show="focusName"
    @update:visible="emit('update:visible', $event)"
  >
    <p v-if="slotItem" class="booking-dialog__slot">{{ slotLabel }}</p>

    <form class="booking-dialog__form" novalidate @submit.prevent="submit">
      <div class="booking-dialog__field">
        <label for="customer-name">Имя</label>
        <InputText
          id="customer-name"
          v-model.trim="name"
          ref="nameField"
          class="booking-dialog__input"
          required
          autocomplete="name"
          :invalid="!!errors.name"
          :disabled="loading"
        />
        <small v-if="errors.name" class="booking-dialog__error">{{ errors.name }}</small>
      </div>

      <div class="booking-dialog__field">
        <label for="customer-email">Email</label>
        <InputText
          id="customer-email"
          v-model.trim="email"
          class="booking-dialog__input"
          type="email"
          required
          autocomplete="email"
          :invalid="!!errors.email"
          :disabled="loading"
        />
        <small v-if="errors.email" class="booking-dialog__error">{{ errors.email }}</small>
      </div>

      <div v-if="generalError" class="booking-dialog__alert" role="alert">{{ generalError }}</div>

      <div class="booking-dialog__actions">
        <Button
          type="button"
          class="booking-dialog__button"
          label="Отмена"
          severity="secondary"
          text
          :disabled="loading"
          @click="emit('update:visible', false)"
        />
        <Button
          type="submit"
          class="booking-dialog__button"
          :label="generalError ? 'Повторить' : 'Забронировать'"
          :loading="loading"
          :disabled="loading"
        />
      </div>
    </form>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import { dateKey, formatTime, formatDateLabel } from '~/utils/datetime'

const props = defineProps({
  visible: { type: Boolean, default: false },
  slotItem: { type: Object, default: null },
  timezone: { type: String, default: 'UTC' },
  loading: { type: Boolean, default: false },
  // Ошибки с сервера (422), ключи — поля API: customer_name, customer_email, slot_id
  serverErrors: { type: Object, default: () => ({}) },
  // Общая ошибка (сбой сети/сервера): форма остаётся открытой, можно повторить
  generalError: { type: String, default: '' },
})
const emit = defineEmits(['update:visible', 'submit'])

// Dialog после открытия фокусирует первый элемент с атрибутом autofocus, а если такого нет — кнопку закрытия.
// Vue не всегда оставляет autofocus атрибутом в DOM, поэтому при открытии выставляем его на поле «Имя» явно.
const nameField = ref(null)
function focusName() {
  nameField.value?.$el?.setAttribute('autofocus', '')
}

const name = ref('')
const email = ref('')
const clientErrors = ref({})

// Тот же формат проверяет сервер (backend/app/Http/Requests/StoreBookingRequest.php, EMAIL_FORMAT): при правке меняйте оба.
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const slotLabel = computed(() =>
  props.slotItem
    ? `${formatDateLabel(dateKey(props.slotItem.starts_at, props.timezone))}, ${formatTime(props.slotItem.starts_at, props.timezone)}–${formatTime(props.slotItem.ends_at, props.timezone)}`
    : '',
)

// Ошибки браузерной проверки и серверные (422) показываются под полями.
const errors = computed(() => ({
  name: clientErrors.value.name || props.serverErrors.customer_name?.[0] || '',
  email: clientErrors.value.email || props.serverErrors.customer_email?.[0] || '',
}))

watch(
  () => props.visible,
  (open) => {
    if (open) {
      name.value = ''
      email.value = ''
      clientErrors.value = {}
    }
  },
)

function submit() {
  const next = {}
  if (!name.value) next.name = 'Укажите имя.'
  if (!email.value) next.email = 'Укажите email.'
  else if (!EMAIL_RE.test(email.value)) next.email = 'Укажите корректный email.'
  clientErrors.value = next
  if (Object.keys(next).length) return
  emit('submit', { name: name.value, email: email.value })
}
</script>

<style scoped>
.booking-dialog__slot {
  margin: 0 0 var(--mb-space-4);
  font-weight: 700;
}
.booking-dialog__form {
  display: flex;
  flex-direction: column;
  gap: var(--mb-space-4);
}
.booking-dialog__field {
  display: flex;
  flex-direction: column;
  gap: var(--mb-space-2);
}
.booking-dialog__input {
  min-height: var(--mb-tap-min);
}
.booking-dialog__error {
  color: var(--mb-color-danger);
}
.booking-dialog__alert {
  padding: var(--mb-space-3);
  color: var(--mb-color-danger-fg);
  background: var(--mb-color-danger-bg);
  border-radius: var(--mb-radius-md);
}
.booking-dialog__actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--mb-space-2);
}
.booking-dialog__button {
  min-height: var(--mb-tap-min);
}
</style>
