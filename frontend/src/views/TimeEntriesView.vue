<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">Control horari</h2>
        <p class="text-sm text-gray-400 mt-0.5">Registre de les teves hores</p>
      </div>
    </div>

    <!-- Widget de fitxatge personal -->
    <div class="mb-5">
      <TimeTrackingWidget @action-done="onActionDone" />
    </div>

    <!-- ── Torn del dia ───────────────────────────────────────────────────── -->
    <div v-if="shift" class="mb-5 bg-white border border-gray-200 rounded-xl px-5 py-4">
      <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-2.5">
        Torn assignat{{ shiftAppliesToday ? ' avui' : '' }}
        <span v-if="!shiftAppliesToday" class="ml-2 text-amber-600 normal-case font-normal">— no aplicable avui</span>
      </p>
      <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
        <!-- Nom -->
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 border border-black/10"
                :style="{ backgroundColor: shift.color || '#94a3b8' }" />
          <span class="font-medium text-gray-900 text-sm">{{ shift.name }}</span>
        </div>

        <!-- Horari d'entrada / sortida -->
        <div v-if="shift.start_time" class="flex items-center gap-1.5 text-sm text-gray-600">
          <IconClock class="w-3.5 h-3.5 text-gray-400" />
          <span class="font-mono">{{ shift.start_time.substring(0, 5) }}</span>
          <template v-if="shiftEndTime">
            <span class="text-gray-300">→</span>
            <span class="font-mono">{{ shiftEndTime }}</span>
          </template>
          <span v-if="shift.total_hours" class="text-xs text-gray-400">({{ shift.total_hours }}h)</span>
        </div>

        <!-- Entrada flexible -->
        <div v-if="shift.flexible_entry && shift.flex_entry_from && shift.flex_entry_to"
             class="flex items-center gap-1.5 text-sm text-amber-700 bg-amber-50 rounded-lg px-2.5 py-1">
          <IconArrowsHorizontal class="w-3.5 h-3.5" />
          <span>Entrada: {{ shift.flex_entry_from.substring(0,5) }} – {{ shift.flex_entry_to.substring(0,5) }}</span>
        </div>

        <!-- Pausa -->
        <div v-if="shift.break_duration" class="flex items-center gap-1.5 text-sm text-gray-500">
          <IconCoffee class="w-3.5 h-3.5 text-gray-400" />
          <span>Pausa {{ shift.break_duration }} min</span>
          <span v-if="shift.break_from && shift.break_to" class="text-xs text-gray-400">
            ({{ shift.break_from.substring(0,5) }}–{{ shift.break_to.substring(0,5) }})
          </span>
        </div>

        <!-- Dies de la setmana -->
        <div v-if="shift.days_of_week?.length" class="flex gap-0.5 ml-auto">
          <span v-for="d in 7" :key="d"
                class="w-5 h-5 rounded text-[9px] font-medium flex items-center justify-center"
                :class="[
                  shift.days_of_week.map(Number).includes(d) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-300',
                  d === todayDow && shift.days_of_week.map(Number).includes(d) ? 'ring-2 ring-blue-400 ring-offset-1' : ''
                ]">
            {{ ['DL','DM','DC','DJ','DV','DS','DG'][d-1] }}
          </span>
        </div>
      </div>
    </div>

    <!-- ── Historial personal ──────────────────────────────────────────────── -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
      <div class="px-5 py-3 border-b flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-700">El meu historial</h3>
        <div class="flex items-center gap-2">
          <select v-model.number="days"
                  class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option :value="7">Última setmana</option>
            <option :value="30">Últim mes</option>
            <option :value="90">Últims 3 mesos</option>
          </select>
          <button @click="router.push({ name: 'time-entries-history' })"
                  class="flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 border border-blue-200 hover:border-blue-400 bg-blue-50 hover:bg-blue-100 rounded-lg px-2.5 py-1 transition-colors">
            <IconHistory class="w-3.5 h-3.5" />
            Veure tot
          </button>
        </div>
      </div>

      <div v-if="historyLoading" class="divide-y divide-gray-100">
        <div v-for="i in 4" :key="i" class="px-5 py-3 flex items-center gap-4">
          <div class="w-20 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="flex-1 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="w-16 h-4 bg-gray-100 animate-pulse rounded" />
        </div>
      </div>

      <div v-else-if="history.length === 0" class="py-10 text-center text-sm text-gray-400">
        Sense registres en el període seleccionat
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div v-for="e in history" :key="e.id"
             class="px-5 py-3 flex items-center gap-4 hover:bg-gray-50 transition-colors">
          <div class="w-32 flex-shrink-0">
            <p class="text-sm font-medium text-gray-900 capitalize">{{ formatDate(e.date) }}</p>
          </div>
          <div class="flex items-center gap-3 flex-1 text-sm text-gray-600">
            <span class="flex items-center gap-1">
              <IconLogin class="w-3.5 h-3.5 text-green-500" />{{ formatTime(e.clock_in_at) }}
            </span>
            <span class="text-gray-300">→</span>
            <span class="flex items-center gap-1">
              <IconLogout class="w-3.5 h-3.5 text-red-400" />{{ e.clock_out_at ? formatTime(e.clock_out_at) : '—' }}
            </span>
            <span v-if="e.total_break_minutes > 0" class="flex items-center gap-1 text-xs text-gray-400">
              <IconCoffee class="w-3 h-3" />{{ formatDuration(e.total_break_minutes) }}
            </span>
          </div>
          <div class="w-20 text-right flex-shrink-0">
            <span v-if="e.effective_minutes != null"
                  class="text-sm font-mono font-medium"
                  :class="e.effective_minutes < 420 ? 'text-amber-600' : 'text-gray-900'">
              {{ formatDuration(e.effective_minutes) }}
            </span>
            <span v-else class="text-xs text-gray-300">en curs</span>
          </div>
          <div class="w-20 text-right flex-shrink-0">
            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full" :class="statusBadge(e.work_status)">
              {{ statusLabel(e.work_status) }}
            </span>
          </div>
        </div>
      </div>

      <div v-if="history.length > 0" class="border-t bg-gray-50 px-5 py-3 flex items-center justify-between text-xs text-gray-500">
        <span>{{ history.length }} jornades</span>
        <span>Total efectiu: <span class="font-semibold text-gray-700">{{ formatDuration(totalEffectiveMinutes) }}</span></span>
      </div>
    </div>

    <!-- ── Registre empresa (HR+) ─────────────────────────────────────────── -->
    <div v-if="isHrPlus" class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-5 py-3 border-b flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <h3 class="text-sm font-medium text-gray-700">Empresa — fitxatges</h3>
          <span v-if="isToday" class="flex items-center gap-1 text-[10px] font-medium px-2 py-0.5 rounded-full bg-green-50 text-green-700">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse" />En temps real
          </span>
        </div>
        <div class="flex items-center gap-2">
          <input v-model="companyDate" type="date"
                 class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <button @click="fetchCompanyEntries"
                  class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 hover:bg-gray-50 transition-colors text-gray-500">
            <IconRefresh class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

      <!-- Skeleton -->
      <div v-if="companyLoading" class="divide-y divide-gray-100">
        <div v-for="i in 4" :key="i" class="px-5 py-3 flex items-center gap-4">
          <div class="w-8 h-8 bg-gray-100 animate-pulse rounded-full flex-shrink-0" />
          <div class="flex-1 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="w-32 h-4 bg-gray-100 animate-pulse rounded" />
        </div>
      </div>

      <template v-else>
        <!-- Empleats amb fitxatge -->
        <div v-if="companyEntries.length === 0 && companyNotClocked.length === 0"
             class="py-10 text-center text-sm text-gray-400">
          Sense fitxatges per a la data seleccionada
        </div>

        <div v-else class="divide-y divide-gray-100">
          <div v-for="e in companyEntries" :key="e.id"
               class="px-5 py-3 flex items-center gap-4 hover:bg-gray-50 transition-colors">

            <!-- Avatar empleat -->
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-white flex-shrink-0"
                 :style="{ backgroundColor: avatarColor(e.employee?.nom + e.employee?.cognoms) }">
              {{ (e.employee?.nom?.[0] || '').toUpperCase() }}{{ (e.employee?.cognoms?.[0] || '').toUpperCase() }}
            </div>

            <!-- Nom + Departament -->
            <div class="w-44 flex-shrink-0 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ e.employee?.nom }} {{ e.employee?.cognoms }}
              </p>
              <p v-if="e.employee?.department" class="text-xs text-gray-400 truncate">
                {{ e.employee.department.name }}
              </p>
            </div>

            <!-- Hores -->
            <div class="flex items-center gap-3 flex-1 text-sm text-gray-600">
              <span class="flex items-center gap-1">
                <IconLogin class="w-3.5 h-3.5 text-green-500" />{{ formatTime(e.clock_in_at) }}
              </span>
              <span class="text-gray-300">→</span>
              <span class="flex items-center gap-1">
                <IconLogout class="w-3.5 h-3.5 text-red-400" />{{ e.clock_out_at ? formatTime(e.clock_out_at) : '—' }}
              </span>
              <span v-if="e.total_break_minutes > 0" class="flex items-center gap-1 text-xs text-gray-400">
                <IconCoffee class="w-3 h-3" />{{ formatDuration(e.total_break_minutes) }}
              </span>
            </div>

            <!-- Temps efectiu -->
            <div class="w-20 text-right flex-shrink-0">
              <span v-if="e.effective_minutes != null" class="text-sm font-mono font-medium text-gray-900">
                {{ formatDuration(e.effective_minutes) }}
              </span>
              <span v-else class="text-xs text-gray-300">en curs</span>
            </div>

            <!-- Estat -->
            <div class="w-24 text-right flex-shrink-0">
              <span class="text-[10px] font-medium px-2 py-0.5 rounded-full" :class="statusBadge(e.work_status)">
                <span v-if="e.work_status === 'clocked_in'" class="inline-block w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse mr-1" />
                <span v-if="e.work_status === 'on_break'" class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse mr-1" />
                {{ statusLabel(e.work_status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Empleats sense fitxatge (avui) -->
        <div v-if="isToday && companyNotClocked.length > 0"
             class="border-t px-5 py-3 bg-amber-50">
          <p class="text-xs font-medium text-amber-700 mb-2">
            <IconAlertTriangle class="w-3.5 h-3.5 inline mr-1" />
            Sense fitxar avui ({{ companyNotClocked.length }})
          </p>
          <div class="flex flex-wrap gap-2">
            <span v-for="emp in companyNotClocked" :key="emp.id"
                  class="inline-flex items-center gap-1 text-xs bg-white border border-amber-200 text-amber-800 px-2 py-0.5 rounded-full">
              {{ emp.nom }} {{ emp.cognoms }}
              <span v-if="emp.department" class="text-amber-400">· {{ emp.department.name }}</span>
            </span>
          </div>
        </div>

        <!-- Totals -->
        <div v-if="companyEntries.length > 0"
             class="border-t bg-gray-50 px-5 py-3 flex items-center justify-between text-xs text-gray-500">
          <span>{{ companyEntries.length }} fitxatges</span>
          <div class="flex items-center gap-4">
            <span>Actius: <span class="font-semibold text-green-700">{{ companyEntries.filter(e => e.work_status === 'clocked_in').length }}</span></span>
            <span>En pausa: <span class="font-semibold text-amber-700">{{ companyEntries.filter(e => e.work_status === 'on_break').length }}</span></span>
            <span>Completats: <span class="font-semibold text-gray-700">{{ companyEntries.filter(e => e.work_status === 'clocked_out').length }}</span></span>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { IconLogin, IconLogout, IconCoffee, IconClock, IconArrowsHorizontal, IconRefresh, IconAlertTriangle, IconHistory } from '@tabler/icons-vue'
import { useAuthStore } from '../stores/auth'
import TimeTrackingWidget from '../components/TimeTrackingWidget.vue'
import { useTimeTracking } from '../composables/useTimeTracking'

const router = useRouter()

const auth = useAuthStore()
const { shift, load: loadToday, loadHistory, loadCompanyEntries } = useTimeTracking()

// Dies ISO (1=Dilluns … 7=Diumenge)
const todayDow = (() => { const d = new Date().getDay(); return d === 0 ? 7 : d })()

const shiftAppliesToday = computed(() => {
  if (!shift.value?.days_of_week?.length) return true
  return shift.value.days_of_week.map(Number).includes(todayDow)
})

const shiftEndTime = computed(() => {
  const s = shift.value
  if (!s?.start_time || !s?.total_hours) return null
  const [h, m] = s.start_time.split(':').map(Number)
  const totalMin = h * 60 + m + Math.round(s.total_hours * 60)
  return `${String(Math.floor(totalMin / 60) % 24).padStart(2, '0')}:${String(totalMin % 60).padStart(2, '0')}`
})

const ROLE_HIERARCHY = { user: 0, hr: 1, admin: 2, superadmin: 3, founder: 4 }
const isHrPlus = computed(() => (ROLE_HIERARCHY[auth.user?.role] ?? -1) >= ROLE_HIERARCHY.hr)

// ── Historial personal ────────────────────────────────────────────────────────
const days           = ref(30)
const history        = ref([])
const historyLoading = ref(false)

async function fetchHistory() {
  historyLoading.value = true
  history.value = await loadHistory(days.value)
  historyLoading.value = false
}

watch(days, fetchHistory)

// ── Registre empresa ──────────────────────────────────────────────────────────
const todayStr        = new Date().toISOString().substring(0, 10)
const companyDate     = ref(todayStr)
const companyEntries  = ref([])
const companyNotClocked = ref([])
const companyLoading  = ref(false)
let   refreshTimer    = null

const isToday = computed(() => companyDate.value === todayStr)

async function fetchCompanyEntries() {
  if (!isHrPlus.value) return
  companyLoading.value = true
  const data = await loadCompanyEntries(companyDate.value)
  companyEntries.value    = data.entries    || []
  companyNotClocked.value = data.not_clocked || []
  companyLoading.value = false
}

watch(companyDate, fetchCompanyEntries)

function onActionDone() {
  fetchHistory()
  if (isToday.value) fetchCompanyEntries()
}

onMounted(() => {
  loadToday()
  fetchHistory()
  if (isHrPlus.value) {
    fetchCompanyEntries()
    // Auto-refresh cada 60 s quan veiem avui
    refreshTimer = setInterval(() => {
      if (isToday.value) fetchCompanyEntries()
    }, 60_000)
  }
})

onUnmounted(() => clearInterval(refreshTimer))

// ── Helpers ───────────────────────────────────────────────────────────────────
const totalEffectiveMinutes = computed(() =>
  history.value.reduce((sum, e) => sum + (e.effective_minutes || 0), 0)
)

function formatTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' })
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ca-ES', { weekday: 'short', day: 'numeric', month: 'short' })
}

function formatDuration(minutes) {
  if (!minutes && minutes !== 0) return '—'
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return h > 0 ? `${h}h${m > 0 ? ' ' + m + 'min' : ''}` : `${m} min`
}

function statusBadge(s) {
  return {
    clocked_in:  'bg-green-50 text-green-700',
    on_break:    'bg-amber-50 text-amber-700',
    clocked_out: 'bg-gray-100 text-gray-500',
  }[s] || 'bg-gray-100 text-gray-500'
}

function statusLabel(s) {
  return { clocked_in: 'Treballant', on_break: 'En pausa', clocked_out: 'Completat' }[s] || '—'
}

const AVATAR_COLORS = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316']
function avatarColor(name) {
  let h = 0
  for (const c of (name || '')) h = (h * 31 + c.charCodeAt(0)) & 0xffffffff
  return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length]
}
</script>
