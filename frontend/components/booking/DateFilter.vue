<template>
  <div class="date-filter">
    <span id="date-filter-label" class="date-filter__label">Дата</span>
    <SelectButton
      :model-value="modelValue"
      :options="options"
      option-label="label"
      option-value="value"
      :allow-empty="false"
      :disabled="disabled"
      fluid
      aria-labelledby="date-filter-label"
      class="date-filter__buttons"
      @update:model-value="emit('update:modelValue', $event)"
    >
      <template #option="{ option }">
        <span class="date-filter__option">
          <span class="date-filter__weekday">{{ option.weekday }}</span>
          <span class="date-filter__day">{{ option.day }}</span>
        </span>
      </template>
    </SelectButton>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import SelectButton from 'primevue/selectbutton'
import { formatDateLabel, formatWeekday, formatDayOfMonth } from '~/utils/datetime'

const props = defineProps({
  modelValue: { type: String, default: null },
  dates: { type: Array, required: true }, // ['2026-01-05', ...]
  disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

// На кнопке две строки: день недели и число; label — полная подпись для экранных читалок и поиска.
const options = computed(() =>
  props.dates.map((value) => ({
    value,
    label: formatDateLabel(value),
    weekday: formatWeekday(value),
    day: formatDayOfMonth(value),
  })),
)
</script>

<style scoped>
.date-filter {
  display: flex;
  flex-direction: column;
  gap: var(--mb-space-2);
}
.date-filter__label {
  font-weight: 600;
}
.date-filter__option {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  line-height: 1.2;
}
.date-filter__weekday {
  font-size: 0.75rem;
}
.date-filter__day {
  font-size: 1rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}
</style>
