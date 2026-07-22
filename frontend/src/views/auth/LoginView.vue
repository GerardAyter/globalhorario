<template>
  <div class="min-h-screen flex">
    <!-- Left column -->
    <div class="hidden md:flex w-1/2 bg-primary text-primary-contrast items-center justify-center p-8 flex-col">
      <div class="text-2xl font-medium">{{ $t('auth_brand.name') }}</div>
      <div class="text-primary-200 text-sm mt-2">{{ $t('auth_brand.tagline') }}</div>

      <div class="mt-12 space-y-4 text-primary-contrast text-sm">
        <div class="flex items-start gap-3">
          <IconClock class="text-primary-300" width="18" height="18" />
          <div>{{ $t('auth_brand.feature_time_tracking') }}</div>
        </div>
        <div class="flex items-start gap-3">
          <IconCalendarOff class="text-primary-300" width="18" height="18" />
          <div>{{ $t('auth_brand.feature_calendar') }}</div>
        </div>
        <div class="flex items-start gap-3">
          <IconUsers class="text-primary-300" width="18" height="18" />
          <div>{{ $t('auth_brand.feature_multitenant') }}</div>
        </div>
      </div>

      <div class="mt-auto text-primary-400 text-xs">{{ $t('auth_brand.copyright') }}</div>
    </div>

    <!-- Right column -->
    <div class="flex-1 bg-white flex items-center justify-center p-6">
      <div class="max-w-sm w-full mx-auto px-6">
        <div>
          <h1 class="text-2xl font-medium text-gray-900">{{ $t('auth.welcome') }}</h1>
          <div class="text-sm text-gray-400 mt-1">{{ $t('auth.access_account') }}</div>
        </div>

        <form class="mt-8 flex flex-col gap-4" @submit.prevent="handleSubmit">
          <div>
            <label class="text-xs font-medium text-gray-600 mb-1 block">{{ $t('auth.email') }}</label>
            <input v-model="email" :placeholder="$t('auth.email_placeholder')" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <div v-if="errors.email" class="text-xs text-red-600 mt-1">{{ errors.email }}</div>
          </div>

          <div class="relative">
            <label class="text-xs font-medium text-gray-600 mb-1 block">{{ $t('auth.password') }}</label>
            <input :type="showPassword ? 'text' : 'password'" v-model="password" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <button type="button" class="absolute right-3 top-9 text-gray-400" @click="showPassword = !showPassword">
              <IconEye v-if="!showPassword" />
              <IconEyeOff v-else />
            </button>
            <div v-if="errors.password" class="text-xs text-red-600 mt-1">{{ errors.password }}</div>
          </div>

          <div class="flex justify-between items-center">
            <label class="flex items-center gap-2 text-xs text-gray-500">
              <input type="checkbox" v-model="remember" />
              <span>{{ $t('auth.remember_me') }}</span>
            </label>
            <a class="text-xs link-primary hover:underline" href="#">{{ $t('auth.forgot_password') }}</a>
          </div>

          <button :disabled="loading" type="submit" class="w-full btn-primary btn font-medium text-sm py-2.5 rounded-lg disabled:opacity-75 flex items-center justify-center gap-2">
            <svg v-if="loading" class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" width="16" height="16">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span v-if="loading">{{ $t('auth.verifying') }}</span>
            <span v-else>{{ $t('auth.login') }}</span>
          </button>

            <div v-if="errorMsg" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3 flex items-center gap-2">
            <IconAlertCircle />
            <div>{{ errorMsg }}</div>
          </div>

          <div class="text-sm text-gray-600 mt-2">
            {{ $t('auth.no_account') }} <a class="text-blue-600" href="#">{{ $t('auth_brand.contact_us') }}</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../../stores/auth'
import { IconClock, IconCalendarOff, IconUsers, IconEye, IconEyeOff, IconAlertCircle } from '@tabler/icons-vue'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMsg = ref('')
const errors = ref({ email: '', password: '' })
const remember = ref(false)

function validateEmail(e) {
  const re = /[^\s@]+@[^\s@]+\.[^\s@]+/
  return re.test(e)
}

async function handleSubmit() {
  errors.value = { email: '', password: '' }
  errorMsg.value = ''

  if (!email.value) {
    errors.value.email = t('auth.email_required')
    return
  }
  if (!validateEmail(email.value)) {
    errors.value.email = t('auth.email_invalid')
    return
  }
  if (!password.value) {
    errors.value.password = t('auth.password_required')
    return
  }

  loading.value = true
  try {
    // pass remember via temporary _remember property (auth.login reads it)
    await auth.login({ email: email.value, password: password.value, _remember: remember.value })
    router.push({ path: '/dashboard' })
  } catch (e) {
    if (e?.response?.status === 422) {
      const srvErrors = e.response.data.errors || {}
      errors.value.email = srvErrors.email ? srvErrors.email.join(' ') : ''
      errors.value.password = srvErrors.password ? srvErrors.password.join(' ') : ''
    } else {
      errorMsg.value = e?.response?.data?.message || t('auth.invalid_credentials')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (auth.isAuthenticated) {
    router.push({ path: '/dashboard' })
  }
})
</script>
