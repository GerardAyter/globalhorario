import { ref } from 'vue'
import api from '../services/api'

function toApiTime(t) {
  if (!t) return null
  return t.length === 5 ? t : t.substring(0, 5)
}

function preparePayload(form) {
  return {
    name:            form.name.trim(),
    color:           form.color || null,
    days_of_week:    form.days_of_week.length ? form.days_of_week : null,
    start_time:      toApiTime(form.start_time) || null,
    total_hours:     form.total_hours != null && form.total_hours !== '' ? Number(form.total_hours) : null,
    active:          form.active,
    flexible_entry:  form.flexible_entry,
    flex_entry_from: form.flexible_entry ? toApiTime(form.flex_entry_from) : null,
    flex_entry_to:   form.flexible_entry ? toApiTime(form.flex_entry_to)   : null,
    break_duration:  form.break_duration != null && form.break_duration !== '' ? Number(form.break_duration) : null,
    break_from:      form.break_duration ? toApiTime(form.break_from) : null,
    break_to:        form.break_duration ? toApiTime(form.break_to)   : null,
  }
}

export function useShifts() {
  const shifts  = ref([])
  const loading = ref(false)
  const saving  = ref(false)
  const error   = ref(null)

  async function load() {
    loading.value = true
    error.value   = null
    try {
      const res  = await api.get('/v1/shifts')
      shifts.value = res.data.data
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error carregant els torns'
    } finally {
      loading.value = false
    }
  }

  async function create(form) {
    saving.value = true
    try {
      const res = await api.post('/v1/shifts', preparePayload(form))
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  async function update(id, form) {
    saving.value = true
    try {
      const res = await api.put(`/v1/shifts/${id}`, preparePayload(form))
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  async function remove(id) {
    await api.delete(`/v1/shifts/${id}`)
  }

  async function loadAll() {
    try {
      const res = await api.get('/v1/shifts')
      return res.data.data || []
    } catch {
      return []
    }
  }

  return { shifts, loading, saving, error, load, create, update, remove, loadAll }
}
