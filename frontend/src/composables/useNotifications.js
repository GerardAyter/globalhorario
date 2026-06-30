import { ref, computed } from 'vue'
import api from '../services/api'

export function useNotifications() {
  const notifications = ref([])
  const unreadCount   = computed(() => notifications.value.filter(n => !n.read_at).length)
  let   pollTimer     = null

  async function load() {
    try {
      const res = await api.get('/v1/notifications/my')
      const d   = res.data.data
      notifications.value = d.notifications || []
    } catch { /* silenci */ }
  }

  async function markRead(id, url) {
    const n = notifications.value.find(n => n.id === id)
    if (n && !n.read_at) {
      n.read_at = new Date().toISOString()
      api.post(`/v1/notifications/${id}/read`).catch(() => {})
    }
    return url || null
  }

  async function markAllRead() {
    notifications.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
    api.post('/v1/notifications/read-all').catch(() => {})
  }

  function startPolling(intervalMs = 30_000) {
    load()
    pollTimer = setInterval(load, intervalMs)
  }

  function stopPolling() {
    clearInterval(pollTimer)
    pollTimer = null
  }

  function timeAgo(iso) {
    if (!iso) return ''
    const diff = Math.floor((Date.now() - new Date(iso)) / 1000)
    if (diff < 60)   return 'ara mateix'
    if (diff < 3600) return `fa ${Math.floor(diff / 60)} min`
    if (diff < 86400) return `fa ${Math.floor(diff / 3600)} h`
    return `fa ${Math.floor(diff / 86400)} d`
  }

  const typeIcon = {
    absence_requested: '📅',
    absence_approved:  '✅',
    absence_denied:    '❌',
  }

  return {
    notifications, unreadCount,
    load, markRead, markAllRead,
    startPolling, stopPolling,
    timeAgo, typeIcon,
  }
}
