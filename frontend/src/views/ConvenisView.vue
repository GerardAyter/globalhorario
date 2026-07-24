<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <div class="w-1/2 sm:w-auto">
        <h2 class="text-base font-medium text-gray-900">{{ $t('convenis.title') }}</h2>
        <p class="text-sm text-gray-400 mt-0.5">{{ $t('convenis.subtitle') }}</p>
      </div>
      <button @click="openCreate"
              class="w-1/2 sm:w-auto flex items-center justify-center sm:justify-start gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
        <IconPlus class="w-4 h-4" />{{ $t('convenis.new') }}
      </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <!-- Skeleton -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 3" :key="i" class="px-5 py-4">
          <div class="h-4 bg-gray-100 animate-pulse rounded w-40 mb-2" />
          <div class="h-3 bg-gray-100 animate-pulse rounded w-64" />
        </div>
      </div>

      <!-- Buit -->
      <div v-else-if="convenis.length === 0"
           class="flex flex-col items-center justify-center py-16 text-center">
        <IconFileDescription class="w-10 h-10 text-gray-300 mb-3" />
        <p class="text-sm text-gray-500">{{ $t('convenis.empty') }}</p>
        <button @click="openCreate" class="mt-3 text-sm text-blue-600 hover:underline">
          {{ $t('convenis.create_first') }}
        </button>
      </div>

      <!-- Llista -->
      <div v-else class="divide-y divide-gray-100">
        <div v-for="c in convenis" :key="c.id"
             class="px-5 py-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-start gap-4">
            <!-- Icona -->
            <div class="hidden sm:flex w-10 h-10 rounded-xl bg-indigo-50 items-center justify-center flex-shrink-0 mt-0.5">
              <IconFileDescription class="w-5 h-5 text-indigo-600" />
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-2">
                <p class="font-medium text-gray-900 text-sm">{{ c.name }}</p>
                <span class="text-[10px] bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">
                  {{ employeeCountLabel(c.employees_count) }}
                </span>
              </div>
              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-6 gap-y-1.5">
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $t('convenis.col_weekly') }}</p>
                  <p class="text-sm font-semibold text-gray-800">
                    {{ formatH(c.weekly_hours) }}
                  </p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $t('convenis.col_weekly_overtime') }}</p>
                  <p class="text-sm font-semibold" :class="c.weekly_overtime_max ? 'text-gray-800' : 'text-gray-300'">
                    {{ c.weekly_overtime_max ? formatH(c.weekly_overtime_max) : '—' }}
                  </p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $t('convenis.col_monthly_overtime') }}</p>
                  <p class="text-sm font-semibold" :class="c.monthly_overtime_max ? 'text-gray-800' : 'text-gray-300'">
                    {{ c.monthly_overtime_max ? formatH(c.monthly_overtime_max) : '—' }}
                  </p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $t('convenis.col_annual_overtime') }}</p>
                  <p class="text-sm font-semibold" :class="c.annual_overtime_max ? 'text-gray-800' : 'text-gray-300'">
                    {{ c.annual_overtime_max ? formatH(c.annual_overtime_max) : '—' }}
                  </p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $t('convenis.col_vacation') }}</p>
                  <p class="text-sm font-semibold text-gray-800">{{ c.vacation_days }} {{ dayLabel(c.vacation_days) }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $t('convenis.col_personal') }}</p>
                  <p class="text-sm font-semibold text-gray-800">{{ c.personal_days }} {{ dayLabel(c.personal_days) }}</p>
                </div>
              </div>
            </div>

            <!-- Botons -->
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="openEdit(c)"
                      class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                      :title="$t('common.edit')">
                <IconEdit class="w-4 h-4" />
              </button>
              <button @click="askDelete(c)"
                      class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                      :title="$t('common.delete')">
                <IconTrash class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal crear/editar -->
    <Teleport to="body">
      <div v-if="showModal"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
           @click.self="showModal = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
          <!-- Capçalera -->
          <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">
              {{ editing ? $t('convenis.edit_title') : $t('convenis.new_title') }}
            </h3>
            <button @click="showModal = false"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
              <IconX class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="doSave" class="px-6 py-5 space-y-5">
            <!-- Nom -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">{{ $t('convenis.name_label') }}</label>
              <input v-model="form.name" type="text" :placeholder="$t('convenis.name_placeholder')"
                     class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            <!-- Hores diàries + pausa -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">{{ $t('convenis.weekly_hours_label') }}</label>
                <div class="flex items-center gap-2">
                  <input v-model.number="form.weekly_hours" type="number" min="1" max="168" step="0.5"
                         class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                  <span class="text-sm text-gray-400 flex-shrink-0">{{ $t('convenis.weekly_hours_unit') }}</span>
                </div>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">{{ $t('convenis.break_label') }}</label>
                <div class="flex items-center gap-2">
                  <input v-model.number="form.break_minutes" type="number" min="0" max="240" step="5"
                         placeholder="0"
                         class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                  <span class="text-sm text-gray-400 flex-shrink-0">{{ $t('common.minutes_short') }}</span>
                </div>
              </div>
            </div>

            <!-- Hores extra màximes -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-2">{{ $t('convenis.overtime_max_label') }}</label>
              <div class="grid grid-cols-3 gap-3">
                <div>
                  <label class="block text-[11px] text-gray-400 mb-1">{{ $t('convenis.overtime_weekly') }}</label>
                  <div class="flex items-center gap-1.5">
                    <input v-model.number="form.weekly_overtime_max" type="number" min="0" step="0.5"
                           placeholder="—"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $t('common.hours_short') }}</span>
                  </div>
                </div>
                <div>
                  <label class="block text-[11px] text-gray-400 mb-1">{{ $t('convenis.overtime_monthly') }}</label>
                  <div class="flex items-center gap-1.5">
                    <input v-model.number="form.monthly_overtime_max" type="number" min="0" step="0.5"
                           placeholder="—"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $t('common.hours_short') }}</span>
                  </div>
                </div>
                <div>
                  <label class="block text-[11px] text-gray-400 mb-1">{{ $t('convenis.overtime_annual') }}</label>
                  <div class="flex items-center gap-1.5">
                    <input v-model.number="form.annual_overtime_max" type="number" min="0" step="0.5"
                           placeholder="—"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $t('common.hours_short') }}</span>
                  </div>
                </div>
              </div>
              <p class="text-[11px] text-gray-400 mt-1.5">{{ $t('convenis.overtime_hint') }}</p>
            </div>

            <!-- Dies de descans -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-2">{{ $t('convenis.rest_days_label') }}</label>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-[11px] text-gray-400 mb-1">{{ $t('convenis.vacation_label') }}</label>
                  <div class="flex items-center gap-1.5">
                    <input v-model.number="form.vacation_days" type="number" min="0"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $t('common.days_other') }}</span>
                  </div>
                </div>
                <div>
                  <label class="block text-[11px] text-gray-400 mb-1">{{ $t('convenis.personal_days_label') }}</label>
                  <div class="flex items-center gap-1.5">
                    <input v-model.number="form.personal_days" type="number" min="0"
                           class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $t('common.days_other') }}</span>
                  </div>
                </div>
              </div>
            </div>

            <p v-if="formError" class="text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ formError }}</p>

            <div class="flex gap-3 pt-1">
              <button type="button" @click="showModal = false"
                      class="flex-1 border border-gray-200 text-gray-700 text-sm font-medium py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                {{ $t('common.cancel') }}
              </button>
              <button type="submit" :disabled="acting"
                      class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium py-2.5 rounded-xl transition-colors">
                {{ acting ? $t('common.saving') : (editing ? $t('common.save') : $t('convenis.create_btn')) }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal confirmar eliminació -->
      <div v-if="deleteTarget"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
           @click.self="deleteTarget = null">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <h3 class="font-semibold text-gray-900 mb-1">{{ $t('convenis.delete_title') }}</h3>
          <p class="text-sm text-gray-500 mb-5">
            {{ $t('convenis.delete_desc', { name: deleteTarget.name }) }}
          </p>
          <p v-if="deleteError" class="text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2 mb-4">{{ deleteError }}</p>
          <div class="flex gap-3">
            <button @click="deleteTarget = null"
                    class="flex-1 border border-gray-200 text-gray-700 text-sm font-medium py-2 rounded-xl hover:bg-gray-50">
              {{ $t('common.cancel') }}
            </button>
            <button @click="doDelete" :disabled="acting"
                    class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-sm font-medium py-2 rounded-xl">
              {{ acting ? $t('common.deleting') : $t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPlus, IconEdit, IconTrash, IconX, IconFileDescription } from '@tabler/icons-vue'
import api from '../services/api'

// ── Dades ─────────────────────────────────────────────────────────────────────
const convenis  = ref([])
const loading   = ref(false)
const acting    = ref(false)
const { t } = useI18n()

// ── Modal crear/editar ────────────────────────────────────────────────────────
const showModal = ref(false)
const editing   = ref(null)
const formError = ref('')

const emptyForm = () => ({
  name:                 '',
  weekly_hours:         40,
  break_minutes:        0,
  weekly_overtime_max:  null,
  monthly_overtime_max: null,
  annual_overtime_max:  null,
  vacation_days:        23,
  personal_days:        6,
})
const form = ref(emptyForm())

// ── Modal eliminar ────────────────────────────────────────────────────────────
const deleteTarget = ref(null)
const deleteError  = ref('')

// ── Format ────────────────────────────────────────────────────────────────────
function formatH(val) {
  const n = parseFloat(val)
  if (isNaN(n)) return '—'
  if (n % 1 === 0) return `${n}h`
  return `${n}h`
}

function employeeCountLabel(count) {
  return t(count === 1 ? 'convenis.employee_count' : 'convenis.employee_count_plural', { n: count })
}

function dayLabel(count) {
  return t(count === 1 ? 'common.days_one' : 'common.days_other')
}

// ── Càrrega ───────────────────────────────────────────────────────────────────
async function load() {
  loading.value = true
  try {
    const res = await api.get('/v1/convenis')
    convenis.value = res.data.data || []
  } catch {
    convenis.value = []
  } finally {
    loading.value = false
  }
}

// ── Crear ─────────────────────────────────────────────────────────────────────
function openCreate() {
  editing.value   = null
  form.value      = emptyForm()
  formError.value = ''
  showModal.value = true
}

// ── Editar ────────────────────────────────────────────────────────────────────
function openEdit(c) {
  editing.value = c
  form.value = {
    name:                 c.name,
    weekly_hours:         parseFloat(c.weekly_hours),
    break_minutes:        c.break_minutes ?? 0,
    weekly_overtime_max:  c.weekly_overtime_max  ? parseFloat(c.weekly_overtime_max)  : null,
    monthly_overtime_max: c.monthly_overtime_max ? parseFloat(c.monthly_overtime_max) : null,
    annual_overtime_max:  c.annual_overtime_max  ? parseFloat(c.annual_overtime_max)  : null,
    vacation_days:        c.vacation_days,
    personal_days:        c.personal_days,
  }
  formError.value = ''
  showModal.value = true
}

// ── Desar ─────────────────────────────────────────────────────────────────────
async function doSave() {
  if (!form.value.name.trim()) { formError.value = t('convenis.name_required'); return }
  if (!form.value.weekly_hours) { formError.value = t('convenis.hours_required'); return }
  formError.value = ''
  acting.value = true
  try {
    const payload = {
      ...form.value,
      weekly_overtime_max:  form.value.weekly_overtime_max  || null,
      monthly_overtime_max: form.value.monthly_overtime_max || null,
      annual_overtime_max:  form.value.annual_overtime_max  || null,
    }
    if (editing.value) {
      const res = await api.put(`/v1/convenis/${editing.value.id}`, payload)
      const idx = convenis.value.findIndex(c => c.id === editing.value.id)
      if (idx !== -1) convenis.value[idx] = res.data.data
    } else {
      const res = await api.post('/v1/convenis', payload)
      convenis.value.push(res.data.data)
      convenis.value.sort((a, b) => a.name.localeCompare(b.name))
    }
    showModal.value = false
  } catch (e) {
    formError.value = e?.response?.data?.message || t('convenis.error_save')
  } finally {
    acting.value = false
  }
}

// ── Eliminar ──────────────────────────────────────────────────────────────────
function askDelete(c) {
  deleteTarget.value = c
  deleteError.value  = ''
}

async function doDelete() {
  acting.value = true
  try {
    await api.delete(`/v1/convenis/${deleteTarget.value.id}`)
    convenis.value = convenis.value.filter(c => c.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch (e) {
    deleteError.value = e?.response?.data?.message || t('convenis.error_delete')
  } finally {
    acting.value = false
  }
}

onMounted(load)
</script>
