<template>
  <div class="container-center">
    <div class="w-full max-w-md card">
      <div class="mb-4">
        <h2 class="text-lg font-medium">{{ $t('auth.login') }}</h2>
        <div class="small-muted">{{ $t('auth.subtitle') }}</div>
      </div>

      <form @submit.prevent="onSubmit" class="space-y-4">
        <div>
          <label class="form-label">{{ $t('auth.email') }}</label>
          <input v-model="email" type="email" required class="input" />
        </div>
        <div>
          <label class="form-label">{{ $t('auth.password') }}</label>
          <input v-model="password" type="password" required class="input" />
        </div>

        <div v-if="error" class="error-text">{{ error }}</div>

        <div class="flex items-center justify-between">
          <button :disabled="loading" type="submit" class="btn btn-primary">
            <span v-if="loading">{{ $t('common.loading') }}</span>
            <span v-else>{{ $t('auth.login') }}</span>
          </button>
          <a href="#" class="small-muted">He oblidat la contrasenya</a>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref(null)
const loading = ref(false)

async function onSubmit() {
  error.value = null
  try {
    loading.value = true
    await auth.login({ email: email.value, password: password.value })
    router.push({ path: '/dashboard' })
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>
