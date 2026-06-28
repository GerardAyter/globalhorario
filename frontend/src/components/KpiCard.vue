<template>
  <div class="bg-white border border-gray-200 rounded-xl p-4">
    <div class="text-[11px] text-gray-400 mb-1">{{ label }}</div>
    <div class="text-2xl font-medium text-gray-900">{{ value }}</div>
    <div class="text-[10px] mt-1" :class="trendClass">{{ trendText }}</div>
  </div>
</template>

<script setup>
import { computed, toRefs } from 'vue'
const props = defineProps({ label: String, value: [String, Number], trend: { type: Number, default: 0 } })
const { label, value, trend } = toRefs(props)

const trendText = computed(() => (trend.value > 0 ? `↑ ${trend.value}` : trend.value < 0 ? `↓ ${Math.abs(trend.value)}` : '—'))
const trendClass = computed(() => (trend.value > 0 ? 'text-green-600' : trend.value < 0 ? 'text-red-600' : 'text-gray-500'))
</script>
