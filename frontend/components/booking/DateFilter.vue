<template>
  <SelectButton
    :model-value="modelValue"
    :options="options"
    option-label="label"
    option-value="value"
    :allow-empty="false"
    :disabled="disabled"
    aria-label="Фильтр по дате"
    class="date-filter"
    @update:model-value="emit('update:modelValue', $event)"
  />
</template>

<script setup>
import SelectButton from 'primevue/selectbutton'

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
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}
</style>
