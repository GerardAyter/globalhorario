import { ref } from 'vue'
import api from '../services/api'

export function useTimeTracking() {
  const state    = ref('loading') // loading | no_employee | idle | clocked_in | on_break | clocked_out
  const entry    = ref(null)
  const employee = ref(null)
  const shift    = ref(null)
  const loading  = ref(false)
  const acting   = ref(false)
  const error    = ref('')

  async function load() {
    loading.value = true
    error.value   = ''
    try {
      const res   = await api.get('/v1/time-tracking/today')
      const data  = res.data.data
      state.value    = data.state
      entry.value    = data.entry
      employee.value = data.employee
      shift.value    = data.shift
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error carregant el control horari'
    } finally {
      loading.value = false
    }
  }

  async function clockIn()    { return _post('/v1/time-tracking/clock-in') }
  async function clockOut()   { return _post('/v1/time-tracking/clock-out') }
  async function breakStart() { return _post('/v1/time-tracking/break-start') }
  async function breakEnd()   { return _post('/v1/time-tracking/break-end') }

  async function _post(url) {
    acting.value = true
    error.value  = ''
    try {
      const res  = await api.post(url)
      const data = res.data.data
      state.value    = data.state
      entry.value    = data.entry
      employee.value = data.employee
      shift.value    = data.shift
      return { ok: true }
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error'
      return { ok: false, message: error.value }
    } finally {
      acting.value = false
    }
  }

  async function loadHistory(days = 30) {
    try {
      const res = await api.get(`/v1/time-tracking/my-history?days=${days}`)
      return res.data.data || []
    } catch {
      return []
    }
  }

  async function loadCompanyEntries(date) {
    try {
      const res = await api.get(`/v1/time-tracking/company-entries?date=${date}`)
      return res.data.data || { entries: [], not_clocked: [] }
    } catch {
      return { entries: [], not_clocked: [] }
    }
  }

  return {
    state, entry, employee, shift,
    loading, acting, error,
    load, clockIn, clockOut, breakStart, breakEnd, loadHistory, loadCompanyEntries,
  }
}
