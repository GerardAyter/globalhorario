import { ref } from 'vue'
import api from '../services/api'

export function useDocuments() {
  const documents = ref([])
  const loading   = ref(false)
  const saving    = ref(false)
  const error     = ref(null)

  async function load(filters = {}) {
    loading.value = true
    error.value   = null
    try {
      const params = new URLSearchParams(filters).toString()
      const res = await api.get(`/v1/documents${params ? '?' + params : ''}`)
      documents.value = res.data.data || []
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error carregant documents'
    } finally {
      loading.value = false
    }
  }

  async function upload(formData) {
    saving.value = true
    try {
      const res = await api.post('/v1/documents', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return { ok: true, data: res.data.data }
    } catch (e) {
      return { ok: false, errors: e?.response?.data?.errors || {}, message: e?.response?.data?.message }
    } finally {
      saving.value = false
    }
  }

  async function remove(id) {
    await api.delete(`/v1/documents/${id}`)
  }

  async function download(doc) {
    const res = await api.get(`/v1/documents/${doc.id}/download`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', doc.file_name || 'document')
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  }

  return { documents, loading, saving, error, load, upload, remove, download }
}
