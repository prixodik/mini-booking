<template>
  <li class="slot-card" :class="`slot-card--${item.status}`">
    <div class="slot-card__header">
      <span class="slot-card__time">{{ formatTime(item.starts_at, timezone) }}–{{ formatTime(item.ends_at, timezone) }}</span>
      <span class="slot-card__status" :class="`slot-card__status--${item.status}`">
        <i class="pi" :class="status.icon" aria-hidden="true" />
        {{ status.label }}
      </span>
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
        :disabled="busy"
        @click="emit('cancel', item)"
      />
    </div>
  </li>
</template>

<script setup>
import { computed } from 'vue'
import Button from 'primevue/button'

const props = defineProps({
  item: { type: Object, required: true }, // слот из GET /api/slots
  timezone: { type: String, required: true },
  busy: { type: Boolean, default: false }, // блокирует действия, пока выполняется запрос
})
const emit = defineEmits(['book', 'cancel'])

// Статус передаётся подписью и иконкой, цвет — дополнительный признак.
const STATUS = {
  available: { label: 'Свободен', icon: 'pi-check-circle' },
  booked: { label: 'Занят', icon: 'pi-lock' },
  past: { label: 'Прошёл', icon: 'pi-clock' },
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
  font-size: 1.25rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}
.slot-card__status {
  display: inline-flex;
  align-items: center;
  gap: var(--mb-space-1);
  padding: var(--mb-space-1) var(--mb-space-2);
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: var(--mb-radius-sm);
}
.slot-card__status--available {
  color: var(--mb-color-available-fg);
  background: var(--mb-color-available-bg);
}
.slot-card__status--booked {
  color: var(--mb-color-booked-fg);
  background: var(--mb-color-booked-bg);
}
.slot-card__status--past {
  color: var(--mb-color-past-fg);
  background: var(--mb-color-past-bg);
}
.slot-card__footer {
  margin-top: auto;
}
.slot-card__action {
  width: 100%;
  min-height: var(--mb-tap-min);
}
</style>
