import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '../services/api'
import { setLocale } from '../i18n'

export const useAuthStore = defineStore('auth', () => {
  const token     = ref(localStorage.getItem('token') || '')
  const tenant_id = ref(localStorage.getItem('tenant_id') || '')
  const user      = ref(JSON.parse(sessionStorage.getItem('user') || 'null'))

  const isAuthenticated = computed(() => !!token.value)

  const initials = computed(() => {
    if (!user.value?.name) return '??'
    const parts = user.value.name.trim().split(' ')
    return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || '??'
  })

  const companyName = computed(() => user.value?.employee?.company?.name || null)
  const tenant      = computed(() => user.value?.tenant || null)
  const whitelabel  = computed(() => user.value?.tenant?.whitelabel || null)

  function _persistUser(u) {
    user.value = u
    sessionStorage.setItem('user', JSON.stringify(u))
    setLocale(u?.locale)
  }

  async function login(credentials) {
    const remember = credentials._remember === undefined ? true : !!credentials._remember
    const res  = await api.post('/auth/login', { email: credentials.email, password: credentials.password })
    const data = res.data.data
    token.value     = data.token
    tenant_id.value = data.user.tenant_id || ''
    _persistUser(data.user)
    if (remember) {
      localStorage.setItem('token', token.value)
      if (tenant_id.value) localStorage.setItem('tenant_id', tenant_id.value)
    } else {
      sessionStorage.setItem('token', token.value)
      if (tenant_id.value) sessionStorage.setItem('tenant_id', tenant_id.value)
    }
    return res
  }

  function logout() {
    token.value     = ''
    user.value      = null
    tenant_id.value = ''
    sessionStorage.removeItem('user')
    localStorage.removeItem('token')
    localStorage.removeItem('tenant_id')
  }

  async function fetchMe() {
    const res = await api.get('/auth/me')
    _persistUser(res.data.data)
    return res.data.data
  }

  async function updateProfile(payload) {
    const res = await api.patch('/auth/profile', payload)
    _persistUser(res.data.data)
    return res.data.data
  }

  return { token, tenant_id, user, isAuthenticated, initials, companyName, tenant, whitelabel, login, logout, fetchMe, updateProfile }
})
