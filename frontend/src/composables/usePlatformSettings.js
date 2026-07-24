import { ref } from 'vue'
import api from '../services/api'

export function usePlatformSettings() {
  const settings = ref(null)
  const loading  = ref(false)
  const saving   = ref(false)
  const error    = ref(null)

  async function load() {
    loading.value = true
    error.value   = null
    try {
      const res = await api.get('/v1/platform-settings')
      settings.value = res.data.data
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error carregant la configuració de la plataforma'
    } finally {
      loading.value = false
    }
  }

  async function save(payload) {
    saving.value = true
    try {
      const res = await api.put('/v1/platform-settings', payload)
      settings.value = res.data.data
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  return { settings, loading, saving, error, load, save }
}
