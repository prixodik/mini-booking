<template>
  <li class="slot-card" :class="`slot-card--${item.status}`">
    <div class="slot-card__header">
      <span class="slot-card__time">
        <i class="pi pi-clock slot-card__time-icon" aria-hidden="true" />
        {{ formatTime(item.starts_at, timezone) }}–{{ formatTime(item.ends_at, timezone) }}
      </span>
      <Tag class="slot-card__status" :value="status.label" :icon="`pi ${status.icon}`" :severity="status.severity" />
    </div>

    <div v-if="item.status !== 'past'" class="slot-card__footer">
      <Button
        v-if="item.status === 'available'"
        class="slot-card__action"
        label="Забронировать"
        :disabled="busy"
        @click="emit('book', item)"
      />
      <Button
        v-else
        class="slot-card__action"
        label="Отменить бронь"
        severity="danger"
        outlined
        :loading="cancelling"
        :disabled="busy"
        @click="emit('cancel', item)"
      />
    </div>
  </li>
</template>

<script setup>
import { computed } from 'vue'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import { formatTime } from '~/utils/datetime'

const props = defineProps({
  item: { type: Object, required: true }, // слот из GET /api/slots
  timezone: { type: String, required: true },
  busy: { type: Boolean, default: false }, // блокирует действия, пока выполняется запрос
  cancelling: { type: Boolean, default: false }, // спиннер на кнопке «Отменить бронь»: идёт отмена этой брони
})
const emit = defineEmits(['book', 'cancel'])

// Статус передаётся подписью и иконкой, цвет — дополнительный признак.
// Цвета Tag задаются токенами пресета (plugins/primevue.js).
const STATUS = {
  available: { label: 'Свободен', icon: 'pi-check-circle', severity: 'success' },
  booked: { label: 'Занят', icon: 'pi-lock', severity: 'warn' },
  past: { label: 'Прошёл', icon: 'pi-history', severity: 'secondary' },
}
const status = computed(() => STATUS[props.item.status])
</script>

<style scoped>
.slot-card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: var(--mb-space-4);
  padding: var(--mb-space-4);
  background: var(--mb-color-surface);
  border: 1px solid var(--mb-color-border);
  border-radius: var(--mb-radius-md);
  box-shadow: var(--mb-shadow-card);
}
.slot-card--past {
  background: var(--mb-color-bg);
  box-shadow: none;
}
.slot-card__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: var(--mb-space-2);
}
.slot-card__time {
  display: inline-flex;
  align-items: center;
  gap: var(--mb-space-2);
  font-size: 1.25rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}
.slot-card__time-icon {
  font-size: 1.125rem;
  color: var(--mb-color-text-muted);
}
.slot-card__status {
  flex-shrink: 0;
}
.slot-card__footer {
  margin-top: auto;
}
.slot-card__action {
  width: 100%;
  min-height: var(--mb-tap-min);
}
</style>
