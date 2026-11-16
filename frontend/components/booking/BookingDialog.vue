<template>
  <Dialog
    :visible="visible"
    modal
    header="Бронирование слота"
    :closable="!loading"
    :style="{ width: '26rem', maxWidth: '95vw' }"
    @update:visible="emit('update:visible', $event)"
  >
    <p v-if="slotItem" class="dialog__slot">{{ slotLabel }}</p>

    <form class="form" novalidate @submit.prevent="submit">
      <div class="form__field">
        <label for="customer-name">Имя</label>
        <InputText id="customer-name" v-model.trim="name" required autocomplete="name" :invalid="!!errors.name" :disabled="loading" />
        <small v-if="errors.name" class="form__error">{{ errors.name }}</small>
      </div>

      <div class="form__field">
        <label for="customer-email">Email</label>
        <InputText id="customer-email" v-model.trim="email" type="email" required autocomplete="email" :invalid="!!errors.email" :disabled="loading" />
        <small v-if="errors.email" class="form__error">{{ errors.email }}</small>
      </div>

      <Message v-if="generalError" severity="error" size="small" :closable="false">{{ generalError }}</Message>

      <div class="form__actions">
        <Button type="button" label="Отмена" severity="secondary" text :disabled="loading" @click="emit('update:visible', false)" />
        <Button type="submit" :label="generalError ? 'Повторить' : 'Забронировать'" :loading="loading" :disabled="loading" />
      </div>
    </form>
  </Dialog>
</template>

<script setup>
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Message from 'primevue/message'

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

const name = ref('')
const email = ref('')
const clientErrors = ref({})

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const slotLabel = computed(() =>
  props.slotItem
    ? `${dateKey(props.slotItem.starts_at, props.timezone)}, ${formatTime(props.slotItem.starts_at, props.timezone)}–${formatTime(props.slotItem.ends_at, props.timezone)}`
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
.dialog__slot {
  margin: 0 0 16px;
  font-weight: 600;
}
.form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form__error {
  color: #c0392b;
}
.form__actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
</style>
