import { ref } from 'vue'
import api from '../services/api'

export function useCompanySettings() {
  const company = ref(null)
  const loading = ref(false)
  const saving  = ref(false)
  const error   = ref(null)

  async function load() {
    loading.value = true
    error.value   = null
    try {
      const res = await api.get('/v1/companies/my')
      company.value = res.data.data
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error carregant les dades de l\'empresa'
    } finally {
      loading.value = false
    }
  }

  async function save(payload) {
    saving.value = true
    try {
      const res = await api.put('/v1/companies/my', payload)
      company.value = res.data.data
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  return { company, loading, saving, error, load, save }
}
