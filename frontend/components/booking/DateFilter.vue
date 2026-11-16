<template>
  <div class="date-filter" role="radiogroup" aria-label="Фильтр по дате">
    <button
      v-for="option in options"
      :key="option.value"
      type="button"
      role="radio"
      class="date-filter__item"
      :class="{ 'date-filter__item--active': option.value === modelValue }"
      :aria-checked="option.value === modelValue"
      :disabled="disabled"
      @click="emit('update:modelValue', option.value)"
    >
      {{ option.label }}
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: String, default: null },
  dates: { type: Array, required: true }, // ['2026-01-05', ...]
  disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

const options = computed(() => props.dates.map((value) => ({ value, label: formatDateLabel(value) })))
</script>

<style scoped>
.date-filter {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(64px, 1fr));
  gap: var(--mb-space-2);
}
.date-filter__item {
  min-height: var(--mb-tap-min);
  padding: var(--mb-space-1);
  font: inherit;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--mb-color-text);
  background: var(--mb-color-surface);
  border: 1px solid var(--mb-color-border-input);
  border-radius: var(--mb-radius-md);
  cursor: pointer;
}
.date-filter__item:hover:not(:disabled) {
  background: var(--mb-color-accent-soft);
}
.date-filter__item--active,
.date-filter__item--active:hover:not(:disabled) {
  color: var(--mb-color-on-accent);
  background: var(--mb-color-accent);
  border-color: var(--mb-color-accent);
}
.date-filter__item:disabled {
  cursor: default;
  opacity: 0.6;
}
</style>
