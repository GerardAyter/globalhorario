import { ref } from 'vue'
import api from '../services/api'

export function useDepartments() {
  const departments = ref([])
  const loading     = ref(false)
  const saving      = ref(false)
  const error       = ref(null)
  const pagination  = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20 })

  async function load(page = 1) {
    loading.value = true
    error.value   = null
    try {
      const res = await api.get(`/v1/departments?page=${page}`)
      const d   = res.data.data
      if (Array.isArray(d)) {
        departments.value = d
      } else {
        departments.value = d.data
        pagination.value  = { current_page: d.current_page, last_page: d.last_page, total: d.total, per_page: d.per_page }
      }
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error carregant departaments'
    } finally {
      loading.value = false
    }
  }

  async function loadAll() {
    try {
      const res = await api.get('/v1/departments?all=1')
      return res.data.data || []
    } catch {
      return []
    }
  }

  async function create(payload) {
    saving.value = true
    try {
      const res = await api.post('/v1/departments', payload)
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  async function update(id, payload) {
    saving.value = true
    try {
      const res = await api.put(`/v1/departments/${id}`, payload)
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  async function remove(id) {
    await api.delete(`/v1/departments/${id}`)
  }

  return { departments, loading, saving, error, pagination, load, loadAll, create, update, remove }
}
