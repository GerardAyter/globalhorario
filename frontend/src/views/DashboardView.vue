<template>
  <div>
    <!-- ── Capçalera ────────────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">
          {{ greeting }}, {{ auth.user?.employee ? auth.user.employee.nom : auth.user?.name?.split(' ')[0] }}!
        </h2>
        <p class="text-sm text-gray-400 mt-0.5 capitalize">{{ today }}</p>
      </div>
    </div>

    <!-- ── SECCIÓ EMPLEAT ────────────────────────────────────────────────────── -->
    <template v-if="hasEmployee">

      <!-- Fila 1: widget (50%) + horari setmanal (50%) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

        <!-- Widget fitxatge -->
        <TimeTrackingWidget />

        <!-- Horari setmanal -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden flex flex-col">
          <div class="px-4 py-3 border-b flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900">Horari aquesta setmana</h3>
            <span class="text-xs text-gray-400">{{ weekRangeLabel }}</span>
          </div>

          <div v-if="trackingLoading" class="flex-1 flex items-center justify-center py-10">
            <svg class="animate-spin w-5 h-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
          </div>

          <div v-else-if="!todayShift" class="flex-1 flex flex-col items-center justify-center py-10 text-center px-4">
            <IconCalendarOff class="w-8 h-8 text-gray-200 mb-2" />
            <p class="text-sm text-gray-400">Sense torn assignat</p>
            <p class="text-xs text-gray-300 mt-1">Contacta amb l'administrador</p>
          </div>

          <div v-else class="divide-y divide-gray-50 flex-1">
            <div v-for="day in weekDays" :key="day.iso"
                 class="flex items-center gap-3 px-4 py-2.5 transition-colors"
                 :class="day.isToday ? 'bg-blue-50' : ''">

              <!-- Dia -->
              <div class="w-12 flex-shrink-0 text-center">
                <p class="text-[10px] text-gray-400 uppercase leading-none">{{ day.shortLabel }}</p>
                <p class="text-sm font-semibold mt-0.5 leading-none"
                   :class="day.isToday ? 'text-blue-600' : 'text-gray-700'">{{ day.dayNum }}</p>
              </div>

              <!-- Info del torn -->
              <div v-if="shiftOnDay(day)" class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full flex-shrink-0"
                        :style="{ backgroundColor: todayShift.color || '#94a3b8' }" />
                  <span class="text-xs font-medium text-gray-700 truncate">{{ todayShift.name }}</span>
                  <span v-if="day.isToday" class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 flex-shrink-0">Avui</span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5 pl-3.5">
                  {{ todayShift.start_time?.substring(0,5) }}
                  <template v-if="shiftEnd"> – {{ shiftEnd }}</template>
                  <template v-if="todayShift.break_duration">
                    <span class="mx-1 text-gray-200">·</span>☕ {{ todayShift.break_duration }} min
                  </template>
                </p>
              </div>
              <div v-else class="flex-1">
                <span class="text-xs text-gray-300">Lliure</span>
              </div>
            </div>
          </div>

          <div v-if="todayShift" class="border-t px-4 py-2.5">
            <router-link to="/time-entries"
                         class="text-xs text-blue-600 hover:underline flex items-center gap-1">
              Veure control horari <IconArrowRight class="w-3 h-3" />
            </router-link>
          </div>
        </div>
      </div>

      <!-- Fila 2: accesos ràpids -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">

        <!-- Calendari -->
        <router-link to="/absences"
                     class="bg-white border border-gray-200 hover:border-blue-300 rounded-xl p-4 transition-colors group block">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
              <IconUmbrella class="w-5 h-5 text-blue-500" />
            </div>
            <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors">Calendari</p>
          </div>
          <p class="text-xs text-gray-400 leading-relaxed">Consulta i sol·licita vacances, dies personals i absències.</p>
          <p class="text-xs text-blue-500 mt-3 flex items-center gap-1">
            Veure calendari <IconArrowRight class="w-3 h-3" />
          </p>
        </router-link>

        <!-- Propers festius i vacances -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
              <IconStar class="w-5 h-5 text-amber-500" />
            </div>
            <p class="text-sm font-medium text-gray-900">Properes dates</p>
          </div>

          <div v-if="upcomingItemsLoading" class="space-y-2">
            <div v-for="i in 3" :key="i" class="h-4 bg-gray-100 animate-pulse rounded w-full" />
          </div>
          <div v-else-if="upcomingItems.length === 0" class="text-xs text-gray-400">
            Sense festius ni vacances properes
          </div>
          <div v-else class="space-y-2">
            <div v-for="item in upcomingItems" :key="`${item.kind}-${item.date}-${item.name}`"
                 class="flex items-center gap-2">
              <!-- Indicador de tipus -->
              <span v-if="item.kind === 'holiday'"
                    class="w-2 h-2 rounded-full flex-shrink-0"
                    :style="{ backgroundColor: item.color || '#EF4444' }" />
              <span v-else
                    class="w-2 h-2 rounded-full flex-shrink-0"
                    :class="item.status === 'approved' ? 'bg-blue-400' : 'bg-amber-400'" />

              <!-- Nom -->
              <span class="text-xs text-gray-600 flex-1 min-w-0 truncate">{{ item.name }}</span>

              <!-- Data -->
              <span class="text-[11px] text-gray-400 flex-shrink-0">{{ item.dateLabel }}</span>
            </div>
          </div>

          <div v-if="!upcomingItemsLoading && upcomingItems.length > 0"
               class="mt-3 pt-2.5 border-t flex gap-3 text-[10px] text-gray-400">
            <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-400" />Festiu</span>
            <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-400" />Aprovat</span>
            <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-400" />Pendent</span>
          </div>
        </div>

        <!-- Nòmines -->
        <router-link to="/payrolls"
                     class="bg-white border border-gray-200 hover:border-blue-300 rounded-xl p-4 transition-colors group block">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0 group-hover:bg-green-100 transition-colors">
              <IconReceipt class="w-5 h-5 text-green-600" />
            </div>
            <p class="text-sm font-medium text-gray-900 group-hover:text-green-700 transition-colors">Les meves nòmines</p>
          </div>
          <p class="text-xs text-gray-400 leading-relaxed">Accedeix als teus rebuts de salari i documents laborals.</p>
          <p class="text-xs text-green-600 mt-3 flex items-center gap-1">
            Consultar <IconArrowRight class="w-3 h-3" />
          </p>
        </router-link>

      </div>
    </template>

    <!-- ── PANELLS DE GESTIÓ (HR+) ────────────────────────────────────────── -->
    <template v-if="isManagement">
      <div v-if="error" class="bg-red-50 text-red-700 rounded-lg p-3 mb-4 text-sm">Error carregant dades</div>

      <div class="grid grid-cols-4 gap-3 mb-4">
        <KpiCard v-for="k in kpis" :key="k.label" :label="k.label" :value="k.valueDisplay" :trend="k.trend" />
      </div>

      <div class="grid" style="grid-template-columns: 3fr 2fr; gap: 1rem;">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-medium text-sm">Últimes sol·licituds al calendari</h3>
            <router-link to="/absences" class="text-xs text-blue-700 hover:underline">veure calendari →</router-link>
          </div>
          <div v-if="loading" class="space-y-2">
            <div v-for="i in 3" :key="i" class="h-8 bg-gray-100 animate-pulse rounded" />
          </div>
          <div v-else>
            <div v-for="a in latestAbsences" :key="a.id" class="flex items-center justify-between py-2">
              <div>
                <div class="text-sm text-gray-900">{{ a.employee_name || a.employee?.name || '—' }}</div>
                <div class="text-xs text-gray-400">{{ a.department_name || a.employee?.department || '' }}</div>
              </div>
              <div class="flex items-center gap-2">
                <Badge :variant="(a.type && a.type.toLowerCase().includes('vac')) ? 'blue' : 'amber'">{{ a.type || 'vacances' }}</Badge>
                <Badge :variant="(a.status && a.status.toLowerCase().includes('aprov')) ? 'green' : 'amber'">{{ a.status || 'Pendent' }}</Badge>
              </div>
            </div>
            <p v-if="!latestAbsences?.length" class="text-sm text-gray-400">Sense absències recents</p>
          </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <h3 class="font-medium mb-3 text-sm">Alertes actives</h3>
          <div v-if="loading" class="space-y-2">
            <div v-for="i in 2" :key="i" class="h-8 bg-gray-100 animate-pulse rounded" />
          </div>
          <div v-else>
            <div v-if="!alerts?.length" class="text-sm text-gray-500">Sense alertes actives</div>
            <AlertItem v-for="al in alerts" :key="al.id" :level="al.level" :message="al.message" />
          </div>
        </div>
      </div>
    </template>

    <!-- Sense empleat ni gestió -->
    <div v-if="!hasEmployee && !isManagement"
         class="bg-white border border-gray-200 rounded-xl p-8 text-center text-sm text-gray-400">
      Benvingut/da. Contacta amb l'administrador per configurar el teu perfil d'empleat.
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useDashboard } from '../composables/useDashboard'
import { useTimeTracking } from '../composables/useTimeTracking'
import { useHolidays } from '../composables/useHolidays'
import TimeTrackingWidget from '../components/TimeTrackingWidget.vue'
import KpiCard from '../components/KpiCard.vue'
import Badge from '../components/Badge.vue'
import AlertItem from '../components/AlertItem.vue'
import api from '../services/api'
import {
  IconArrowRight, IconCalendarOff, IconUmbrella,
  IconStar, IconReceipt,
} from '@tabler/icons-vue'

const auth = useAuthStore()
const { loading, error, metrics, latestAbsences, alerts, load } = useDashboard()
const { shift, loading: trackingLoading, load: loadTracking } = useTimeTracking()
const { holidays, load: loadHolidays } = useHolidays()

const ROLE_HIERARCHY = { user: 0, hr: 1, admin: 2, superadmin: 3, founder: 4 }
const hasEmployee  = computed(() => !!auth.user?.employee?.id)
const isManagement = computed(() => (ROLE_HIERARCHY[auth.user?.role] ?? -1) >= ROLE_HIERARCHY.hr)

// ── Salutació ──────────────────────────────────────────────────────────────────
const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Bon dia'
  if (h < 20) return 'Bona tarda'
  return 'Bona nit'
})

const today = computed(() =>
  new Date().toLocaleDateString('ca-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
)

// ── Horari setmanal ────────────────────────────────────────────────────────────
const DAY_SHORT = ['DL', 'DM', 'DC', 'DJ', 'DV', 'DS', 'DG']

const todayShift = computed(() => shift.value ?? null)

const shiftEnd = computed(() => {
  const s = todayShift.value
  if (!s?.start_time || !s?.total_hours) return null
  const [h, m] = s.start_time.split(':').map(Number)
  const totalMin = h * 60 + m + Math.round(s.total_hours * 60)
  return `${String(Math.floor(totalMin / 60) % 24).padStart(2, '0')}:${String(totalMin % 60).padStart(2, '0')}`
})

const weekDays = computed(() => {
  const today = new Date()
  const dow = today.getDay() // 0=Dium
  const monday = new Date(today)
  monday.setDate(today.getDate() - (dow === 0 ? 6 : dow - 1))
  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(monday)
    d.setDate(monday.getDate() + i)
    const isToday = d.toDateString() === today.toDateString()
    const isoDow = i + 1 // 1=DL … 7=DG
    return { iso: d.toISOString(), dayNum: d.getDate(), shortLabel: DAY_SHORT[i], isToday, isoDow }
  })
})

const weekRangeLabel = computed(() => {
  const days = weekDays.value
  const first = new Date(days[0].iso)
  const last  = new Date(days[6].iso)
  return `${first.getDate()} – ${last.getDate()} ${last.toLocaleDateString('ca-ES', { month: 'short' })}`
})

function shiftOnDay(day) {
  if (!todayShift.value?.days_of_week?.length) return false
  return todayShift.value.days_of_week.map(Number).includes(day.isoDow)
}

// ── Properes dates (festius + vacances pròpies) ───────────────────────────────
const myAbsences          = ref([])
const upcomingItemsLoading = ref(false)

function localDateStr(d) {
  return d.getFullYear() + '-' +
    String(d.getMonth() + 1).padStart(2, '0') + '-' +
    String(d.getDate()).padStart(2, '0')
}

async function loadMyAbsences() {
  try {
    const res = await api.get('/v1/absence-requests/my')
    myAbsences.value = res.data.data || []
  } catch { myAbsences.value = [] }
}

const upcomingItems = computed(() => {
  const now = new Date()
  now.setHours(0, 0, 0, 0)
  const todayStr   = localDateStr(now)
  const horizon    = new Date(now)
  horizon.setDate(now.getDate() + 180)
  const horizonStr = localDateStr(horizon)
  const curYear    = now.getFullYear()
  const items      = []

  // ── Festius de l'empresa ──
  for (const h of holidays.value) {
    const mmdd       = String(h.date).slice(5, 10)
    const candidates = h.recurring
      ? [`${curYear}-${mmdd}`, `${curYear + 1}-${mmdd}`]
      : [String(h.date).slice(0, 10)]
    for (const d of candidates) {
      if (d >= todayStr && d <= horizonStr) {
        items.push({ date: d, name: h.name, kind: 'holiday', color: h.color })
        break
      }
    }
  }

  // ── Vacances / absències pròpies ──
  for (const req of myAbsences.value) {
    if (req.status === 'cancelled') continue
    const endStr = String(req.end_date).slice(0, 10)
    if (endStr < todayStr) continue
    const startStr = String(req.start_date).slice(0, 10)
    items.push({
      date:    startStr,
      endDate: endStr,
      name:    req.type?.name || 'Absència',
      kind:    'vacation',
      status:  req.status,
      days:    req.working_days,
    })
  }

  return items
    .sort((a, b) => a.date.localeCompare(b.date))
    .slice(0, 5)
    .map(item => {
      const fmt = d => new Date(d + 'T00:00:00').toLocaleDateString('ca-ES', { day: 'numeric', month: 'short' })
      const dateLabel = item.endDate && item.endDate !== item.date
        ? `${fmt(item.date)} – ${fmt(item.endDate)}`
        : fmt(item.date)
      return { ...item, dateLabel }
    })
})

// ── KPIs gestió ───────────────────────────────────────────────────────────────
const kpis = computed(() => {
  const m = metrics.value || {}
  return [
    { label: 'Empleats actius',       valueDisplay: m.active_employees || 0, trend: m.employees_trend || 0 },
    { label: 'Fitxant avui',          valueDisplay: m.clocking_today   || 0, trend: m.clocking_trend  || 0 },
    { label: 'Al calendari avui',     valueDisplay: m.absences_today   || 0, trend: m.absences_trend  || 0 },
    { label: 'Sol·licituds pendents', valueDisplay: m.pending_requests || 0, trend: m.requests_trend  || 0 },
  ]
})

onMounted(async () => {
  upcomingItemsLoading.value = true
  const year = new Date().getFullYear()
  const promises = [loadHolidays(year)]
  if (hasEmployee.value) {
    loadTracking()
    promises.push(loadMyAbsences())
  }
  await Promise.all(promises)
  upcomingItemsLoading.value = false

  if (isManagement.value) load()
})
</script>
