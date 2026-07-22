<template>
  <div>
    <!-- Capçalera -->
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">{{ $t('shifts.title') }}</h2>
        <p class="text-sm text-gray-400 mt-0.5">{{ $t('shifts.count', { n: shifts.length }) }}</p>
      </div>
      <div class="flex items-center gap-2">
        <!-- Toggle vista -->
        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
          <button @click="viewMode = 'list'"
                  class="px-3 py-1.5 text-sm flex items-center gap-1.5 transition-colors"
                  :class="viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'">
            <IconList class="w-4 h-4" />{{ $t('shifts.list') }}
          </button>
          <button @click="viewMode = 'calendar'"
                  class="px-3 py-1.5 text-sm flex items-center gap-1.5 transition-colors border-l border-gray-200"
                  :class="viewMode === 'calendar' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'">
            <IconCalendar class="w-4 h-4" />{{ $t('shifts.calendar') }}
          </button>
        </div>
        <button @click="openCreate" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
          <IconPlus class="w-4 h-4" />{{ $t('shifts.new') }}
        </button>
      </div>
    </div>

    <!-- ── VISTA LLISTA ─────────────────────────────────────────────────────── -->
    <div v-if="viewMode === 'list'" class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <!-- Skeleton -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4 px-5 py-4">
          <div class="w-3 h-3 rounded-full bg-gray-100 animate-pulse flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-100 animate-pulse rounded w-32" />
            <div class="h-3 bg-gray-100 animate-pulse rounded w-48" />
          </div>
          <div class="flex gap-2">
            <div class="w-7 h-7 bg-gray-100 animate-pulse rounded" />
            <div class="w-7 h-7 bg-gray-100 animate-pulse rounded" />
          </div>
        </div>
      </div>

      <div v-else-if="error" class="flex flex-col items-center justify-center py-16 text-center">
        <IconAlertTriangle class="w-8 h-8 text-red-400 mb-2" />
        <p class="text-sm text-red-600">{{ error }}</p>
        <button @click="load()" class="mt-3 text-xs text-blue-600 hover:underline">{{ $t('common.retry') }}</button>
      </div>

      <div v-else-if="shifts.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
        <IconClock class="w-10 h-10 text-gray-300 mb-3" />
        <p class="text-sm text-gray-500">{{ $t('shifts.empty') }}</p>
        <button @click="openCreate" class="mt-3 text-sm text-blue-600 hover:underline">{{ $t('shifts.create_first') }}</button>
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div v-for="s in shifts" :key="s.id" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
          <div class="w-3 h-3 rounded-full flex-shrink-0 border border-black/10" :style="{ backgroundColor: s.color || '#94a3b8' }" />

          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-medium text-gray-900 text-sm">{{ s.name }}</span>
              <span v-if="!s.active" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">{{ $t('shifts.inactive') }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1 flex-wrap">
              <div class="flex gap-0.5">
                <span v-for="d in 7" :key="d"
                      class="w-5 h-5 rounded text-[10px] font-medium flex items-center justify-center"
                      :class="(s.days_of_week || []).map(Number).includes(d) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-300'">
                  {{ DAY_LABELS[d] }}
                </span>
              </div>
              <span v-if="s.start_time" class="text-xs text-gray-500 flex items-center gap-1">
                <IconClock class="w-3 h-3" />{{ s.start_time.substring(0,5) }}
              </span>
              <span v-if="s.total_hours" class="text-xs text-gray-500">{{ formatHours(s.total_hours) }}</span>
              <span v-if="s.flexible_entry" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">{{ $t('shifts.flexible') }}</span>
              <span v-if="s.break_duration" class="text-xs text-gray-500 flex items-center gap-1">
                <IconCoffee class="w-3 h-3" />{{ s.break_duration }} min
              </span>
            </div>
          </div>

          <div class="flex items-center gap-1 flex-shrink-0">
            <button @click="openEdit(s)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" :title="$t('common.edit')">
              <IconEdit class="w-4 h-4" />
            </button>
            <button @click="askDelete(s)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" :title="$t('common.delete')">
              <IconTrash class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── VISTA CALENDARI ──────────────────────────────────────────────────── -->
    <div v-else-if="viewMode === 'calendar'" class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div v-if="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-6 h-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>
      </div>

      <template v-else>
        <!-- Capçalera dies (sticky) -->
        <div class="flex border-b border-gray-200 bg-gray-50 sticky top-0 z-10">
          <div class="w-14 flex-shrink-0 border-r border-gray-200" />
          <div v-for="d in 7" :key="d"
               class="flex-1 text-center py-2.5 text-xs font-medium border-r border-gray-200 last:border-r-0"
               :class="scheduledDays.has(d) ? 'text-gray-900' : 'text-gray-300'">
            {{ DAY_FULL_SHORT[d] }}
          </div>
        </div>

        <!-- Cos del calendari (scrollable) -->
        <div ref="calendarBodyRef" class="overflow-y-auto" style="max-height: 640px">
          <div class="flex" :style="{ height: TOTAL_H + 'px' }">

            <!-- Columna d'hores -->
            <div class="w-14 flex-shrink-0 relative border-r border-gray-200">
              <div v-for="h in 24" :key="h"
                   class="absolute right-2 text-[10px] text-gray-400 tabular-nums"
                   :style="{ top: (h * HOUR_H - 7) + 'px' }">
                {{ String(h).padStart(2, '0') }}:00
              </div>
            </div>

            <!-- Columnes dels dies -->
            <div v-for="d in 7" :key="d" class="flex-1 relative border-r border-gray-200 last:border-r-0">
              <!-- Línies d'hora -->
              <div v-for="h in 25" :key="h"
                   class="absolute left-0 right-0 pointer-events-none"
                   :class="h % 6 === 0 ? 'border-t border-gray-200' : 'border-t border-gray-100'"
                   :style="{ top: (h * HOUR_H) + 'px' }" />

              <!-- Torns del dia -->
              <template v-for="s in shifts" :key="s.id">
                <div v-if="shiftIsOnDay(s, d)"
                     class="absolute left-1 right-1 rounded-md overflow-hidden cursor-pointer group transition-opacity hover:opacity-90"
                     :style="shiftBlockStyle(s)"
                     @click="openEdit(s)">
                  <!-- Franja de color sòlid a l'esquerra -->
                  <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-md" :style="{ backgroundColor: s.color || '#3B82F6' }" />
                  <!-- Contingut -->
                  <div class="pl-2.5 pr-1.5 py-1 h-full flex flex-col justify-start overflow-hidden"
                       :style="{ backgroundColor: hexToRgba(s.color || '#3B82F6', 0.12) }">
                    <span class="text-[11px] font-semibold leading-tight truncate"
                          :style="{ color: s.color || '#3B82F6' }">{{ s.name }}</span>
                    <span v-if="shiftBlockHeight(s) >= 32" class="text-[10px] leading-tight opacity-70 truncate"
                          :style="{ color: s.color || '#3B82F6' }">
                      {{ s.start_time.substring(0,5) }}
                      <template v-if="s.total_hours"> · {{ formatHours(s.total_hours) }}</template>
                    </span>
                  </div>
                </div>
              </template>
            </div>

          </div>
        </div>

        <!-- Nota torns sense horari -->
        <div v-if="shiftsWithoutTime.length" class="border-t px-5 py-3 bg-amber-50">
          <p class="text-xs text-amber-700">
            <span class="font-medium">{{ $t('shifts.unassigned_note') }}</span>
            {{ shiftsWithoutTime.map(s => s.name).join(', ') }}
          </p>
        </div>
      </template>
    </div>

    <!-- ── Modal crear / editar ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="closeModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
            <h3 class="font-medium text-gray-900">{{ modal.isEdit ? $t('shifts.edit_title') : $t('shifts.new_title') }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>

          <form @submit.prevent="submitModal" class="overflow-y-auto flex-1 px-6 py-5 space-y-6">
            <!-- General -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('shifts.general') }}</p>
              <div class="space-y-3">
                <div class="grid grid-cols-[1fr_auto] gap-3 items-end">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.name_label') }}</label>
                    <input v-model="form.name" type="text" :placeholder="$t('shifts.name_placeholder')"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="formErrors.name ? 'border-red-400' : 'border-gray-200'" />
                    <p v-if="formErrors.name" class="text-xs text-red-600 mt-1">{{ formErrors.name[0] }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.color_label') }}</label>
                    <input v-model="form.color" type="color"
                           class="w-10 h-9 rounded-lg border border-gray-200 cursor-pointer p-0.5" />
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-2">{{ $t('shifts.weekdays_label') }}</label>
                  <div class="flex gap-1.5 flex-wrap">
                    <button v-for="(label, day) in DAY_FULL" :key="day" type="button"
                            @click="toggleDay(day)"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors"
                            :class="form.days_of_week.includes(Number(day))
                              ? 'bg-blue-600 border-blue-600 text-white'
                              : 'bg-white border-gray-200 text-gray-600 hover:border-blue-400'">
                      {{ label }}
                    </button>
                  </div>
                </div>

                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                  <div class="relative w-9 h-5 rounded-full transition-colors" :class="form.active ? 'bg-blue-600' : 'bg-gray-300'"
                       @click="form.active = !form.active">
                    <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all"
                         :class="form.active ? 'left-4' : 'left-0.5'" />
                  </div>
                  <span class="text-sm text-gray-700">{{ $t('shifts.active_label') }}</span>
                </label>
              </div>
            </div>

            <!-- Horari -->
            <div class="border-t pt-5">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('shifts.schedule') }}</p>
              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.start_label') }}</label>
                    <input v-model="form.start_time" type="time"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.duration_label') }}</label>
                    <div class="relative">
                      <input v-model.number="form.total_hours" type="number" min="0" max="24" step="0.5" placeholder="8"
                             class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                      <span class="absolute right-3 top-2 text-xs text-gray-400">h</span>
                    </div>
                  </div>
                </div>

                <div class="bg-amber-50 rounded-xl p-4 space-y-3">
                  <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <div class="relative w-9 h-5 rounded-full transition-colors" :class="form.flexible_entry ? 'bg-amber-500' : 'bg-gray-300'"
                         @click="form.flexible_entry = !form.flexible_entry">
                      <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all"
                           :class="form.flexible_entry ? 'left-4' : 'left-0.5'" />
                    </div>
                    <div>
                      <span class="text-sm font-medium text-gray-700">{{ $t('shifts.flexible') }}</span>
                      <p class="text-xs text-gray-400">{{ $t('shifts.flexible_desc') }}</p>
                    </div>
                  </label>
                  <div v-if="form.flexible_entry" class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.flexible_start_min') }}</label>
                      <input v-model="form.flex_entry_from" type="time"
                             class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                             :class="formErrors.flex_entry_from ? 'border-red-400' : 'border-gray-200'" />
                      <p v-if="formErrors.flex_entry_from" class="text-xs text-red-600 mt-1">{{ formErrors.flex_entry_from[0] }}</p>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.flexible_start_max') }}</label>
                      <input v-model="form.flex_entry_to" type="time"
                             class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                             :class="formErrors.flex_entry_to ? 'border-red-400' : 'border-gray-200'" />
                      <p v-if="formErrors.flex_entry_to" class="text-xs text-red-600 mt-1">{{ formErrors.flex_entry_to[0] }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pausa -->
            <div class="border-t pt-5">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('shifts.break') }}</p>
              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.break_duration_label') }}</label>
                  <div class="relative w-40">
                    <input v-model.number="form.break_duration" type="number" min="0" max="240" step="5" placeholder="0"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <span class="absolute right-3 top-2 text-xs text-gray-400">min</span>
                  </div>
                  <p class="text-xs text-gray-400 mt-1">{{ $t('shifts.break_hint') }}</p>
                </div>
                <div v-if="form.break_duration > 0" class="bg-gray-50 rounded-xl p-4 space-y-3">
                  <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <div class="relative w-9 h-5 rounded-full transition-colors" :class="form.break_window ? 'bg-blue-600' : 'bg-gray-300'"
                         @click="form.break_window = !form.break_window">
                      <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all"
                           :class="form.break_window ? 'left-4' : 'left-0.5'" />
                    </div>
                    <div>
                      <span class="text-sm font-medium text-gray-700">{{ $t('shifts.break_window_label') }}</span>
                      <p class="text-xs text-gray-400">{{ $t('shifts.break_window_desc') }}</p>
                    </div>
                  </label>
                  <div v-if="form.break_window" class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.break_from_label') }}</label>
                      <input v-model="form.break_from" type="time"
                             class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('shifts.break_to_label') }}</label>
                      <input v-model="form.break_to" type="time"
                             class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ formError }}</div>

            <div class="flex items-center justify-end gap-2 pt-1 border-t">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                {{ $t('common.cancel') }}
              </button>
              <button type="submit" :disabled="saving"
                      class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
                <svg v-if="saving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ saving ? $t('common.saving') : (modal.isEdit ? $t('common.save') : $t('shifts.create_btn')) }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal confirmar eliminació ─────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="deleteTarget = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
          <div class="flex items-start gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
              <IconAlertTriangle class="w-5 h-5 text-red-600" />
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ $t('shifts.delete_title') }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ $t('shifts.delete_desc', { name: deleteTarget.name }) }}
                {{ $t('shifts.delete_warning') }}
              </p>
            </div>
          </div>
          <div class="flex gap-2 justify-end">
            <button @click="deleteTarget = null" class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">{{ $t('common.cancel') }}</button>
            <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 transition-colors">
              {{ deleting ? $t('common.deleting') : $t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPlus, IconEdit, IconTrash, IconX, IconClock, IconCoffee, IconAlertTriangle, IconList, IconCalendar } from '@tabler/icons-vue'
import { useShifts } from '../composables/useShifts'

const { shifts, loading, saving, error, load, create, update, remove } = useShifts()
const { locale, t } = useI18n()

// ── Constants ────────────────────────────────────────────────────────────────
const HOUR_H  = 56                // px per hora
const TOTAL_H = 24 * HOUR_H      // 1344 px

const dateLocale = computed(() => ({ ca: 'ca-ES', es: 'es-ES', en: 'en-GB' }[locale.value] || 'ca-ES'))
const DAY_LABELS = computed(() => ({
  1: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'short' }).format(new Date(2024, 0, 1)),
  2: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'short' }).format(new Date(2024, 0, 2)),
  3: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'short' }).format(new Date(2024, 0, 3)),
  4: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'short' }).format(new Date(2024, 0, 4)),
  5: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'short' }).format(new Date(2024, 0, 5)),
  6: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'short' }).format(new Date(2024, 0, 6)),
  7: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'short' }).format(new Date(2024, 0, 7)),
}))
const DAY_FULL = computed(() => ({
  1: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'long' }).format(new Date(2024, 0, 1)),
  2: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'long' }).format(new Date(2024, 0, 2)),
  3: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'long' }).format(new Date(2024, 0, 3)),
  4: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'long' }).format(new Date(2024, 0, 4)),
  5: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'long' }).format(new Date(2024, 0, 5)),
  6: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'long' }).format(new Date(2024, 0, 6)),
  7: new Intl.DateTimeFormat(dateLocale.value, { weekday: 'long' }).format(new Date(2024, 0, 7)),
}))
const DAY_FULL_SHORT = DAY_LABELS

// ── Vista ────────────────────────────────────────────────────────────────────
const viewMode       = ref('list')
const calendarBodyRef = ref(null)

// Dies que tenen almenys un torn
const scheduledDays = computed(() => {
  const s = new Set()
  shifts.value.forEach(sh => (sh.days_of_week || []).forEach(d => s.add(Number(d))))
  return s
})

// Torns que no es poden mostrar al calendari (sense start_time o total_hours)
const shiftsWithoutTime = computed(() =>
  shifts.value.filter(s => !s.start_time || !s.total_hours)
)

// Auto-scroll a la primera hora de torn quan s'obre el calendari
watch(viewMode, async (mode) => {
  if (mode !== 'calendar') return
  await nextTick()
  if (!calendarBodyRef.value) return
  const earliest = shifts.value
    .filter(s => s.start_time)
    .map(s => {
      const [h, m] = s.start_time.split(':').map(Number)
      return h + m / 60
    })
    .reduce((a, b) => Math.min(a, b), 8)
  calendarBodyRef.value.scrollTop = Math.max(0, (earliest - 1)) * HOUR_H
})

// ── Helpers calendari ─────────────────────────────────────────────────────────
function hexToRgba(hex, alpha) {
  const clean = (hex || '#3B82F6').replace('#', '')
  const r = parseInt(clean.substring(0, 2), 16)
  const g = parseInt(clean.substring(2, 4), 16)
  const b = parseInt(clean.substring(4, 6), 16)
  return `rgba(${r},${g},${b},${alpha})`
}

function shiftIsOnDay(s, day) {
  return !!(s.start_time && s.total_hours && (s.days_of_week || []).map(Number).includes(day))
}

function shiftStartDecimal(s) {
  const parts = s.start_time.split(':').map(Number)
  return parts[0] + parts[1] / 60
}

function shiftBlockHeight(s) {
  return Math.max(s.total_hours * HOUR_H, 22)
}

function shiftBlockStyle(s) {
  return {
    top:    shiftStartDecimal(s) * HOUR_H + 'px',
    height: shiftBlockHeight(s) + 'px',
  }
}

// ── Helpers generals ─────────────────────────────────────────────────────────
function formatHours(h) {
  if (!h) return ''
  const hrs = Math.floor(h)
  const min = Math.round((h - hrs) * 60)
  return min === 0 ? `${hrs}h` : `${hrs}h ${min}min`
}

// ── Formulari ────────────────────────────────────────────────────────────────
const modal      = reactive({ open: false, isEdit: false, editId: null })
const formErrors = ref({})
const formError  = ref('')

const form = reactive({
  name: '', color: '#3B82F6', days_of_week: [], start_time: '',
  total_hours: null, active: true,
  flexible_entry: false, flex_entry_from: '', flex_entry_to: '',
  break_duration: null, break_window: false, break_from: '', break_to: '',
})

function resetForm() {
  Object.assign(form, {
    name: '', color: '#3B82F6', days_of_week: [], start_time: '',
    total_hours: null, active: true,
    flexible_entry: false, flex_entry_from: '', flex_entry_to: '',
    break_duration: null, break_window: false, break_from: '', break_to: '',
  })
  formErrors.value = {}
  formError.value  = ''
}

function toggleDay(day) {
  const d   = Number(day)
  const idx = form.days_of_week.indexOf(d)
  if (idx === -1) form.days_of_week.push(d)
  else            form.days_of_week.splice(idx, 1)
}

function openCreate() {
  resetForm()
  Object.assign(modal, { open: true, isEdit: false, editId: null })
}

function openEdit(s) {
  resetForm()
  Object.assign(form, {
    name:            s.name            || '',
    color:           s.color           || '#3B82F6',
    days_of_week:    s.days_of_week    ? s.days_of_week.map(Number) : [],
    start_time:      s.start_time      ? s.start_time.substring(0, 5) : '',
    total_hours:     s.total_hours     ?? null,
    active:          s.active          ?? true,
    flexible_entry:  s.flexible_entry  ?? false,
    flex_entry_from: s.flex_entry_from ? s.flex_entry_from.substring(0, 5) : '',
    flex_entry_to:   s.flex_entry_to   ? s.flex_entry_to.substring(0, 5)   : '',
    break_duration:  s.break_duration  ?? null,
    break_window:    !!(s.break_from || s.break_to),
    break_from:      s.break_from      ? s.break_from.substring(0, 5) : '',
    break_to:        s.break_to        ? s.break_to.substring(0, 5)   : '',
  })
  Object.assign(modal, { open: true, isEdit: true, editId: s.id })
}

function closeModal() { modal.open = false }

async function submitModal() {
  formErrors.value = {}
  formError.value  = ''
  const result = modal.isEdit ? await update(modal.editId, form) : await create(form)
  if (result.ok) { closeModal(); load() }
  else { formErrors.value = result.errors || {}; formError.value = result.message || t('common.error_saving') }
}

// ── Eliminar ─────────────────────────────────────────────────────────────────
const deleteTarget = ref(null)
const deleting     = ref(false)

function askDelete(s) { deleteTarget.value = s }

async function confirmDelete() {
  deleting.value = true
  try {
    await remove(deleteTarget.value.id)
    deleteTarget.value = null
    load()
  } finally {
    deleting.value = false
  }
}

onMounted(() => load())
</script>
