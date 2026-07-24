import { ref } from 'vue'
import api from '../services/api'

export function useTenantSettings() {
  const tenant  = ref(null)
  const loading = ref(false)
  const saving  = ref(false)
  const error   = ref(null)

  async function load() {
    loading.value = true
    error.value   = null
    try {
      const res = await api.get('/v1/tenants/my')
      tenant.value = res.data.data
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error carregant les dades del distribuïdor'
    } finally {
      loading.value = false
    }
  }

  async function save(payload) {
    saving.value = true
    try {
      const res = await api.put('/v1/tenants/my', payload)
      tenant.value = res.data.data
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  return { tenant, loading, saving, error, load, save }
}
