import { ref } from 'vue'
import api from '../services/api'

export function useEmployees() {
  const employees  = ref([])
  const loading    = ref(false)
  const saving     = ref(false)
  const error      = ref(null)
  const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20 })

  async function load(page = 1) {
    loading.value = true
    error.value   = null
    try {
      const res = await api.get(`/v1/employees?page=${page}`)
      const d   = res.data.data
      employees.value  = d.data
      pagination.value = { current_page: d.current_page, last_page: d.last_page, total: d.total, per_page: d.per_page }
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error carregant empleats'
    } finally {
      loading.value = false
    }
  }

  async function create(payload) {
    saving.value = true
    try {
      const res = await api.post('/v1/employees', payload)
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
      const res = await api.put(`/v1/employees/${id}`, payload)
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  async function remove(id) {
    await api.delete(`/v1/employees/${id}`)
  }

  async function sendInvitation(id) {
    try {
      await api.post(`/v1/employees/${id}/send-invitation`)
      return { ok: true }
    } catch (e) {
      return { ok: false, message: e?.response?.data?.message || 'Error enviant el correu' }
    }
  }

  return { employees, loading, saving, error, pagination, load, create, update, remove, sendInvitation }
}
