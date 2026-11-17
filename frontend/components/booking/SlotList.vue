<template>
  <ul class="slot-list" aria-label="Слоты">
    <SlotCard
      v-for="slot in slots"
      :key="slot.id"
      :item="slot"
      :timezone="timezone"
      :busy="busy"
      :cancelling="slot.id === cancellingSlotId"
      class="slot-list__item"
      @book="emit('book', $event)"
      @cancel="emit('cancel', $event)"
    />
  </ul>
</template>

<script setup>
import SlotCard from '~/components/booking/SlotCard.vue'

defineProps({
  slots: { type: Array, required: true },
  timezone: { type: String, required: true },
  busy: { type: Boolean, default: false }, // блокирует действия, пока выполняется запрос
  cancellingSlotId: { type: Number, default: null }, // слот, бронь которого сейчас отменяется
})
const emit = defineEmits(['book', 'cancel'])
</script>

<style scoped>
.slot-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--mb-space-4);
}

@media (min-width: 768px) {
  .slot-list {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (min-width: 1280px) {
  .slot-list {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style>
