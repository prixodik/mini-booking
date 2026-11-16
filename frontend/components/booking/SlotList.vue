<template>
  <ul class="slots" aria-label="Слоты">
    <li v-for="slot in slots" :key="slot.id" class="slot" :class="`slot--${slot.status}`">
      <div class="slot__time">{{ formatTime(slot.starts_at, timezone) }}–{{ formatTime(slot.ends_at, timezone) }}</div>
      <Tag :value="STATUS[slot.status].label" :severity="STATUS[slot.status].severity" />
      <Button
        v-if="slot.status === 'available'"
        label="Забронировать"
        size="small"
        :disabled="busy"
        @click="emit('book', slot)"
      />
      <Button
        v-else-if="slot.status === 'booked'"
        label="Отменить бронь"
        size="small"
        severity="danger"
        outlined
        :disabled="busy"
        @click="emit('cancel', slot)"
      />
    </li>
  </ul>
</template>

<script setup>
import Tag from 'primevue/tag'
import Button from 'primevue/button'

defineProps({
  slots: { type: Array, required: true },
  timezone: { type: String, required: true },
  busy: { type: Boolean, default: false }, // блокирует действия, пока выполняется запрос
})
const emit = defineEmits(['book', 'cancel'])

const STATUS = {
  available: { label: 'Свободен', severity: 'success' },
  booked: { label: 'Занят', severity: 'danger' },
  past: { label: 'Прошёл', severity: 'secondary' },
}
</script>

<style scoped>
.slots {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 12px;
}
.slot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  flex-wrap: wrap;
  padding: 12px 16px;
  background: #fff;
  border: 1px solid #e4e7eb;
  border-radius: 8px;
}
.slot--past {
  opacity: 0.6;
}
.slot__time {
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}
</style>
