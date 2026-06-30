import { ref } from 'vue'
import api from '../services/api'

export function useAbsences() {
  const myRequests    = ref([])
  const companyRequests = ref([])
  const balance       = ref(null)
  const absenceTypes  = ref([])
  const loading       = ref(false)
  const acting        = ref(false)
  const error         = ref('')

  async function loadMyRequests() {
    loading.value = true
    try {
      const res = await api.get('/v1/absence-requests/my')
      myRequests.value = res.data.data || []
    } catch { myRequests.value = [] } finally { loading.value = false }
  }

  async function loadBalance() {
    try {
      const res = await api.get('/v1/vacation-balances/my')
      balance.value = res.data.data || null
    } catch { balance.value = null }
  }

  async function loadAbsenceTypes() {
    try {
      const res = await api.get('/v1/absence-types')
      absenceTypes.value = res.data.data || []
    } catch { absenceTypes.value = [] }
  }

  async function createRequest(data) {
    acting.value = true
    error.value  = ''
    try {
      const res = await api.post('/v1/absence-requests', data)
      myRequests.value.unshift(res.data.data)
      await loadBalance()
      return { ok: true }
    } catch (e) {
      error.value = e?.response?.data?.message || 'Error creant la sol·licitud'
      return { ok: false, message: error.value }
    } finally { acting.value = false }
  }

  async function cancelRequest(id) {
    acting.value = true
    try {
      await api.delete(`/v1/absence-requests/${id}`)
      myRequests.value = myRequests.value.filter(r => r.id !== id)
      await loadBalance()
      return { ok: true }
    } catch (e) {
      return { ok: false, message: e?.response?.data?.message || 'Error' }
    } finally { acting.value = false }
  }

  // HR+
  async function loadCompanyRequests(status = '') {
    loading.value = true
    try {
      const url = status ? `/v1/absence-requests?status=${status}` : '/v1/absence-requests'
      const res = await api.get(url)
      companyRequests.value = res.data.data || []
    } catch { companyRequests.value = [] } finally { loading.value = false }
  }

  async function approveRequest(id, comment = '') {
    acting.value = true
    try {
      const res = await api.post(`/v1/absence-requests/${id}/approve`, { manager_comment: comment })
      _replaceInCompany(id, res.data.data)
      return { ok: true }
    } catch (e) {
      return { ok: false, message: e?.response?.data?.message || 'Error' }
    } finally { acting.value = false }
  }

  async function denyRequest(id, comment = '') {
    acting.value = true
    try {
      const res = await api.post(`/v1/absence-requests/${id}/deny`, { manager_comment: comment })
      _replaceInCompany(id, res.data.data)
      return { ok: true }
    } catch (e) {
      return { ok: false, message: e?.response?.data?.message || 'Error' }
    } finally { acting.value = false }
  }

  function _replaceInCompany(id, updated) {
    const idx = companyRequests.value.findIndex(r => r.id === id)
    if (idx !== -1) companyRequests.value[idx] = updated
  }

  return {
    myRequests, companyRequests, balance, absenceTypes,
    loading, acting, error,
    loadMyRequests, loadBalance, loadAbsenceTypes, loadCompanyRequests,
    createRequest, cancelRequest, approveRequest, denyRequest,
  }
}
