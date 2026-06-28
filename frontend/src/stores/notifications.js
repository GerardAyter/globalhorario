import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '../services/api'

export const useNotificationsStore = defineStore('notifications', () => {
  const items = ref([])
  const unreadCount = ref(0)

  async function fetch() {
    try {
      const res = await api.get('/v1/notifications')
      items.value = res.data.data || res.data || []
      unreadCount.value = items.value.filter(i => !i.read).length
    } catch (e) {
      throw e
    }
  }

  async function markRead(id) {
    try {
      await api.patch(`/v1/notifications/${id}/read`)
      const it = items.value.find(i => i.id === id)
      if (it) it.read = true
      unreadCount.value = items.value.filter(i => !i.read).length
    } catch (e) {
      throw e
    }
  }

  return { items, unreadCount, fetch, markRead }
})
