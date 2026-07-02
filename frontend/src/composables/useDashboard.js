import { ref } from 'vue'
import api from '../services/api'

function isToday(dateStr) {
  if (!dateStr) return false
  const d = new Date(dateStr)
  const now = new Date()
  return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() && d.getDate() === now.getDate()
}

export function useDashboard() {
  const loading = ref(false)
  const error = ref(null)
  const metrics = ref({})
  const latestAbsences = ref([])
  const alerts = ref([])

  async function load() {
    loading.value = true
    error.value = null
    try {
      // Employees count
      const empRes = await api.get('/v1/employees?per_page=1').catch(() => ({ data: {} }))
      const employeesTotal = empRes?.data?.data?.total ?? empRes?.data?.total ?? 0

      // Time entries - fetch a reasonable page and compute today's clockings client-side
      const teRes = await api.get('/v1/time-entries?per_page=100').catch(() => ({ data: { data: [] } }))
      const teData = teRes?.data?.data?.data ?? teRes?.data?.data ?? []
      // Compta empleats únics que fitxen avui O que tenen un torn obert (de dies anteriors)
      const clockingSet = new Set()
      teData.forEach(t => {
        if (isToday(t.clock_in_at) || ['clocked_in', 'on_break'].includes(t.work_status)) {
          clockingSet.add(t.employee_id)
        }
      })
      const clockingToday = clockingSet.size

      // Absence requests - latest
      const absRes = await api.get('/v1/absence-requests?per_page=5').catch(() => ({ data: { data: [] } }))
      const absList = absRes?.data?.data?.data ?? absRes?.data?.data ?? absRes?.data ?? []

      // No alerts endpoint implemented yet — leave empty
      const alertList = []

      metrics.value = {
        active_employees: employeesTotal,
        clocking_today: clockingToday,
        absences_today: absList.filter(a => isToday(a.starts_at || a.requested_at || a.created_at)).length || 0,
        pending_requests: absList.filter(a => a.status && a.status.toLowerCase().includes('pend')).length || 0,
      }
      latestAbsences.value = absList
      alerts.value = alertList
    } catch (e) {
      error.value = e
    } finally {
      loading.value = false
    }
  }

  return { loading, error, metrics, latestAbsences, alerts, load }
}
