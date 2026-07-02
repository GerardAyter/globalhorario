<template>
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <!-- Skeleton -->
    <div v-if="state === 'loading'" class="p-5">
      <div class="h-5 bg-gray-100 animate-pulse rounded w-48 mb-3" />
      <div class="h-10 bg-gray-100 animate-pulse rounded w-full" />
    </div>

    <!-- Sense empleat associat -->
    <div v-else-if="state === 'no_employee'" class="p-5 text-center text-sm text-gray-400 py-8">
      No tens un registre d'empleat associat. Contacta amb l'administrador.
    </div>

    <!-- ── IDLE: no ha fitxat ────────────────────────────────────────────────── -->
    <div v-else-if="state === 'idle'" class="p-5">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
          <IconClockOff class="w-5 h-5 text-gray-400" />
        </div>
        <div>
          <p class="font-medium text-gray-900">Bon dia, {{ employee?.nom }}!</p>
          <p class="text-sm text-gray-400">{{ todayFormatted }} · Encara no has fitxat</p>
        </div>
      </div>
      <div v-if="shift" class="mb-4 flex items-center gap-2 text-sm text-gray-500 bg-gray-50 rounded-lg px-3 py-2">
        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: shift.color || '#94a3b8' }" />
        <span>{{ shift.name }}</span>
        <span v-if="shift.start_time" class="text-gray-400">· Inici previst: {{ shift.start_time.substring(0,5) }}</span>
      </div>
      <button @click="doClockIn" :disabled="acting"
              class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white font-medium py-3 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm">
        <svg v-if="acting" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <IconLogin v-else class="w-4 h-4" />
        Fitxar entrada
      </button>
      <p v-if="error" class="text-xs text-red-600 mt-2 text-center">{{ error }}</p>
    </div>

    <!-- ── CLOCKED IN: treballant ────────────────────────────────────────────── -->
    <div v-else-if="state === 'clocked_in'" class="p-5">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse" />
          </div>
          <div>
            <p class="font-medium text-gray-900">Treballant</p>
            <p class="text-sm text-gray-400">Entrada: {{ formatTimeWithDay(entry.clock_in_at) }}</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-2xl font-mono font-bold text-gray-900">{{ elapsed }}</p>
          <p v-if="entry.estimated_end_at" class="text-xs text-gray-400">fi: {{ formatTime(entry.estimated_end_at) }}</p>
        </div>
      </div>

      <div v-if="shift" class="mb-4 flex items-center gap-2 text-xs text-gray-500 bg-gray-50 rounded-lg px-3 py-2">
        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: shift.color || '#94a3b8' }" />
        <span>{{ shift.name }}</span>
        <template v-if="shift.break_duration">
          <span class="text-gray-300">·</span>
          <IconCoffee class="w-3 h-3" /><span>Pausa: {{ shift.break_duration }} min</span>
          <template v-if="shift.break_from && shift.break_to">
            <span class="text-gray-300">·</span>
            <span>entre {{ shift.break_from.substring(0,5) }} i {{ shift.break_to.substring(0,5) }}</span>
          </template>
        </template>
      </div>

      <div v-if="entry.total_break_minutes > 0" class="mb-4 text-xs text-gray-500 flex items-center gap-1">
        <IconCoffee class="w-3 h-3" />
        Pauses acumulades: {{ formatDuration(entry.total_break_minutes) }}
      </div>

      <div class="flex gap-2">
        <button v-if="shift?.break_duration"
                @click="doBreakStart" :disabled="acting"
                class="flex-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-medium py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm disabled:opacity-60">
          <IconCoffee class="w-4 h-4" />Iniciar pausa
        </button>
        <button @click="doClockOut" :disabled="acting"
                class="flex-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-medium py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm disabled:opacity-60">
          <IconLogout class="w-4 h-4" />Finalitzar torn
        </button>
      </div>
      <p v-if="error" class="text-xs text-red-600 mt-2 text-center">{{ error }}</p>
    </div>

    <!-- ── ON BREAK: en pausa ────────────────────────────────────────────────── -->
    <div v-else-if="state === 'on_break'" class="p-5">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center flex-shrink-0">
            <IconCoffee class="w-5 h-5 text-amber-500" />
          </div>
          <div>
            <p class="font-medium text-gray-900">En pausa</p>
            <p class="text-sm text-gray-400">Des de {{ formatTime(entry.current_break?.break_start_at) }}</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-2xl font-mono font-bold text-amber-600">{{ elapsedBreak }}</p>
          <p v-if="breakEstimatedEnd" class="text-xs text-gray-400">fi pausa: {{ breakEstimatedEnd }}</p>
        </div>
      </div>

      <!-- Temps treballant (segueix corrent durant la pausa) -->
      <div class="mb-3 bg-gray-50 rounded-xl px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-500">
          <IconClock class="w-4 h-4" />
          <span>Treballant des de {{ formatTimeWithDay(entry.clock_in_at) }}</span>
        </div>
        <span class="font-mono font-semibold text-gray-800 text-sm">{{ elapsed }}</span>
      </div>

      <div v-if="shift?.break_duration" class="mb-4 flex items-center gap-2 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
        <IconAlertTriangle class="w-3.5 h-3.5 flex-shrink-0" />
        <span>La pausa hauria de durar {{ shift.break_duration }} minuts</span>
      </div>

      <div class="flex gap-2">
        <button @click="doBreakEnd" :disabled="acting"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm disabled:opacity-60">
          <svg v-if="acting" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <IconPlayerPlay v-else class="w-4 h-4" />
          Finalitzar pausa
        </button>
        <button @click="doClockOut" :disabled="acting"
                class="flex-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-medium py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm disabled:opacity-60">
          <IconLogout class="w-4 h-4" />Finalitzar torn
        </button>
      </div>
      <p v-if="error" class="text-xs text-red-600 mt-2 text-center">{{ error }}</p>
    </div>

    <!-- ── CLOCKED OUT: torn completat ──────────────────────────────────────── -->
    <div v-else-if="state === 'clocked_out'" class="p-5">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
            <IconCircleCheck class="w-5 h-5 text-blue-500" />
          </div>
          <div>
            <p class="font-medium text-gray-900">Torn completat</p>
            <p class="text-sm text-gray-400">{{ todayFormatted }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-3 gap-3 mb-3">
        <div class="bg-gray-50 rounded-xl p-3 text-center">
          <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Entrada</p>
          <p class="font-mono font-semibold text-gray-900">{{ formatTimeWithDay(entry.clock_in_at) }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-3 text-center">
          <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Sortida</p>
          <p class="font-mono font-semibold text-gray-900">{{ formatTime(entry.clock_out_at) }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-3 text-center">
          <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Pauses</p>
          <p class="font-mono font-semibold text-gray-900">{{ formatDuration(entry.total_break_minutes) }}</p>
        </div>
      </div>

      <div class="mb-3 bg-blue-50 rounded-xl p-3 text-center">
        <p class="text-[10px] text-blue-500 uppercase tracking-wider mb-1">Temps efectiu</p>
        <p class="font-mono font-bold text-blue-700 text-lg">{{ effectiveTime }}</p>
      </div>

      <button @click="doClockIn" :disabled="acting"
              class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white font-medium py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors text-sm">
        <svg v-if="acting" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <IconLogin v-else class="w-4 h-4" />
        Iniciar nou torn
      </button>
      <p v-if="error" class="text-xs text-red-600 mt-2 text-center">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import {
  IconClockOff, IconClock, IconLogin, IconLogout, IconCoffee,
  IconPlayerPlay, IconCircleCheck, IconAlertTriangle,
} from '@tabler/icons-vue'
import { useTimeTracking } from '../composables/useTimeTracking'

const emit = defineEmits(['action-done'])

const { state, entry, employee, shift, acting, error, load, clockIn, clockOut, breakStart, breakEnd } = useTimeTracking()

const now = ref(new Date())
let ticker = null

onMounted(() => {
  load()
  ticker = setInterval(() => { now.value = new Date() }, 1000)
})
onUnmounted(() => clearInterval(ticker))

// ── Helpers de temps ─────────────────────────────────────────────────────────
const todayFormatted = computed(() =>
  now.value.toLocaleDateString('ca-ES', { weekday: 'long', day: 'numeric', month: 'long' })
)

function formatTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' })
}

function formatTimeWithDay(iso) {
  if (!iso) return '—'
  const d = new Date(iso)
  const now = new Date()
  const isToday = d.getFullYear() === now.getFullYear() &&
                  d.getMonth()    === now.getMonth()    &&
                  d.getDate()     === now.getDate()
  const time = d.toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' })
  if (isToday) return time
  return d.toLocaleDateString('ca-ES', { weekday: 'short', day: 'numeric', month: 'short' }) + ' ' + time
}

function formatDuration(minutes) {
  if (!minutes) return '0 min'
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return h > 0 ? `${h}h ${m > 0 ? m + 'min' : ''}`.trim() : `${m} min`
}

function diffSeconds(fromIso, toDate) {
  return Math.max(0, Math.floor((toDate - new Date(fromIso)) / 1000))
}

function secondsToHMS(s) {
  const h = Math.floor(s / 3600)
  const m = Math.floor((s % 3600) / 60)
  const sec = s % 60
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
}

const elapsed = computed(() => {
  if (!entry.value?.clock_in_at) return '00:00:00'
  const worked = diffSeconds(entry.value.clock_in_at, now.value)
  return secondsToHMS(Math.max(0, worked))
})

const elapsedBreak = computed(() => {
  const start = entry.value?.current_break?.break_start_at
  if (!start) return '00:00:00'
  return secondsToHMS(diffSeconds(start, now.value))
})

const breakEstimatedEnd = computed(() => {
  const start = entry.value?.current_break?.break_start_at
  const dur   = shift.value?.break_duration
  if (!start || !dur) return null
  const end = new Date(new Date(start).getTime() + dur * 60000)
  return end.toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' })
})

const effectiveTime = computed(() => {
  const e = entry.value
  if (!e?.clock_in_at || !e?.clock_out_at) return '—'
  const totalMin = Math.round((new Date(e.clock_out_at) - new Date(e.clock_in_at)) / 60000)
  const shiftBreak = shift.value?.break_duration || 0
  const effectiveMin = totalMin - (e.total_break_minutes || 0) + shiftBreak
  return formatDuration(Math.max(0, effectiveMin))
})

// ── Accions ───────────────────────────────────────────────────────────────────
async function doClockIn()    { const r = await clockIn();    if (r.ok) emit('action-done') }
async function doClockOut()   { const r = await clockOut();   if (r.ok) emit('action-done') }
async function doBreakStart() { const r = await breakStart(); if (r.ok) emit('action-done') }
async function doBreakEnd()   { const r = await breakEnd();   if (r.ok) emit('action-done') }
</script>
