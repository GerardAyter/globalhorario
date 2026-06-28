<template>
  <span :class="classes"><slot /></span>
</template>

<script setup>
import { computed, toRefs } from 'vue'
const props = defineProps({ variant: { type: String, default: 'blue' } })
const { variant } = toRefs(props)

const classes = computed(() => {
  if (variant.value === 'green') return 'bg-green-50 text-green-800 px-2 rounded'
  if (variant.value === 'amber') return 'bg-amber-50 text-amber-800 px-2 rounded'
  return 'bg-blue-50 text-blue-800 px-2 rounded'
})
</script>
