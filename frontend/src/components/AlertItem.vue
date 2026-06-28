<template>
  <div :class="containerClass" class="flex items-start gap-2 mb-2">
    <div class="pt-0.5">{{ icon }}</div>
    <div class="text-[13px]">{{ message }}</div>
  </div>
</template>

<script setup>
import { computed, toRefs } from 'vue'
const props = defineProps({ level: { type: String, default: 'info' }, message: String })
const { level, message } = toRefs(props)

const icon = computed(() => (level.value === 'error' ? '⛔' : level.value === 'warning' ? '⚠️' : '✅'))
const containerClass = computed(() => (level.value === 'error' ? 'bg-red-50 text-red-800 rounded-lg p-2 text-[11px]' : level.value === 'warning' ? 'bg-amber-50 text-amber-800 rounded-lg p-2 text-[11px]' : 'bg-green-50 text-green-800 rounded-lg p-2 text-[11px]'))
</script>
