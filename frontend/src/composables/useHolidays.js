import { ref } from 'vue'
import api from '../services/api'

export function useHolidays() {
  const holidays = ref([])
  const loading  = ref(false)
  const acting   = ref(false)

  async function load(year) {
    loading.value = true
    try {
      const res = await api.get('/v1/holidays', { params: { year } })
      holidays.value = res.data.data || []
    } catch { holidays.value = [] } finally { loading.value = false }
  }

  async function create(data) {
    acting.value = true
    try {
      const res = await api.post('/v1/holidays', data)
      holidays.value.push(res.data.data)
      holidays.value.sort((a, b) => a.date.localeCompare(b.date))
      return { ok: true }
    } catch (e) {
      return { ok: false, message: e?.response?.data?.message || 'Error' }
    } finally { acting.value = false }
  }

  async function remove(id) {
    acting.value = true
    try {
      await api.delete(`/v1/holidays/${id}`)
      holidays.value = holidays.value.filter(h => h.id !== id)
      return { ok: true }
    } catch (e) {
      return { ok: false, message: e?.response?.data?.message || 'Error' }
    } finally { acting.value = false }
  }

  const TYPE_LABELS = {
    national: 'Nacional',
    local:    'Local',
    company:  'Empresa',
  }

  const TYPE_COLORS = {
    national: '#EF4444',
    local:    '#F97316',
    company:  '#8B5CF6',
  }

  function defaultColor(type) {
    return TYPE_COLORS[type] ?? '#EF4444'
  }

  // Comprova si un festiu aplica a una data concreta (gestiona recurrents)
  function appliesToDate(holiday, dateStr) {
    const hDate = holiday.date.slice(0, 10) // YYYY-MM-DD
    if (holiday.recurring) {
      return hDate.slice(5) === dateStr.slice(5) // MM-DD
    }
    return hDate === dateStr
  }

  function holidaysForDate(dateStr) {
    return holidays.value.filter(h => appliesToDate(h, dateStr))
  }

  return {
    holidays, loading, acting,
    load, create, remove,
    TYPE_LABELS, TYPE_COLORS, defaultColor, holidaysForDate,
  }
}
