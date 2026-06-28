<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-base font-medium">Resum del dia</h2>
      <div class="text-sm text-gray-400">{{ today }}</div>
    </div>

    <div v-if="error" class="bg-red-50 text-red-700 rounded-lg p-3 mb-4">Error carregant dades</div>

    <div class="grid grid-cols-4 gap-3 mb-4">
      <KpiCard v-for="k in kpis" :key="k.label" :label="k.label" :value="k.valueDisplay" :trend="k.trend" />
    </div>

    <div class="grid" style="grid-template-columns: 3fr 2fr; gap: 1rem;">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-medium">Últimes absències sol·licitades</h3>
          <a href="#" class="text-sm text-blue-700">veure totes →</a>
        </div>
        <div v-if="loading" class="space-y-2">
          <div class="h-8 bg-gray-100 animate-pulse rounded"></div>
          <div class="h-8 bg-gray-100 animate-pulse rounded"></div>
          <div class="h-8 bg-gray-100 animate-pulse rounded"></div>
        </div>
        <div v-else>
          <div v-for="a in latestAbsences" :key="a.id" class="flex items-center justify-between py-2">
            <div>
              <div class="text-sm text-gray-900">{{ a.employee_name || a.employee?.name || '—' }}</div>
              <div class="text-xs text-gray-400">{{ a.department_name || a.employee?.department || '' }}</div>
            </div>
            <div class="flex items-center gap-2">
              <Badge :variant="(a.type && a.type.toLowerCase().includes('vac')) ? 'blue' : 'amber'">{{ a.type || 'vacances' }}</Badge>
              <Badge :variant="(a.status && a.status.toLowerCase().includes('aprov')) ? 'green' : (a.status && a.status.toLowerCase().includes('pend')) ? 'amber' : 'blue'">{{ a.status || 'Pendent' }}</Badge>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <h3 class="font-medium mb-3">Alertes actives</h3>
        <div v-if="loading" class="space-y-2">
          <div class="h-8 bg-gray-100 animate-pulse rounded"></div>
          <div class="h-8 bg-gray-100 animate-pulse rounded"></div>
        </div>
        <div v-else>
          <div v-if="alerts.length === 0" class="text-sm text-gray-500">No active alerts</div>
          <div v-else>
            <AlertItem v-for="al in alerts" :key="al.id" :level="al.level" :message="al.message" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useDashboard } from '../composables/useDashboard'
import KpiCard from '../components/KpiCard.vue'
import Badge from '../components/Badge.vue'
import AlertItem from '../components/AlertItem.vue'

const { loading, error, metrics, latestAbsences, alerts, load } = useDashboard()

const today = computed(() => new Date().toLocaleDateString('ca-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))

onMounted(async () => {
  await load()
})

const kpis = computed(() => {
  const m = metrics.value || {}
  return [
    { label: 'Empleats actius', value: m.active_employees || 0, valueDisplay: m.active_employees || 0, trend: m.employees_trend || 0 },
    { label: 'Fitxant avui', value: m.clocking_today || 0, valueDisplay: m.clocking_today || 0, trend: m.clocking_trend || 0 },
    { label: 'Absències', value: m.absences_today || 0, valueDisplay: m.absences_today || 0, trend: m.absences_trend || 0 },
    { label: 'Sol·licituds pendents', value: m.pending_requests || 0, valueDisplay: m.pending_requests || 0, trend: m.requests_trend || 0 },
  ].map(item => ({
    ...item,
    trendText: item.trend > 0 ? `↑ ${item.trend}` : item.trend < 0 ? `↓ ${Math.abs(item.trend)}` : '—',
    trendClass: item.trend > 0 ? 'text-green-600' : item.trend < 0 ? 'text-red-600' : 'text-gray-500',
  }))
})

function badgeClass(type) {
  if (!type) return 'bg-blue-50 text-blue-800 px-2 rounded'
  if (type.toLowerCase().includes('vac')) return 'bg-blue-50 text-blue-800 px-2 rounded'
  return 'bg-amber-50 text-amber-800 px-2 rounded'
}

function statusClass(status) {
  if (!status) return 'bg-amber-50 text-amber-800 px-2 rounded'
  const s = status.toLowerCase()
  if (s.includes('aprov') || s.includes('aprob')) return 'bg-green-50 text-green-800 px-2 rounded'
  if (s.includes('pendent')) return 'bg-amber-50 text-amber-800 px-2 rounded'
  return 'bg-gray-50 text-gray-800 px-2 rounded'
}

function alertClass(level) {
  if (level === 'error') return 'bg-red-50 text-red-800 rounded-lg p-2 text-[11px]'
  if (level === 'warning') return 'bg-amber-50 text-amber-800 rounded-lg p-2 text-[11px]'
  return 'bg-green-50 text-green-800 rounded-lg p-2 text-[11px]'
}

</script>
