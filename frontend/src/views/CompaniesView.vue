<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">{{ $t('companies.title') }}</h2>
        <p class="text-sm text-gray-400 mt-0.5">{{ $t('companies.registered_count', { count: pagination.total }) }}</p>
      </div>
      <button @click="openCreate" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
        <IconPlus class="w-4 h-4" />{{ $t('companies.new_company') }}
      </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <!-- Skeleton -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4 px-5 py-4">
          <div class="w-10 h-10 rounded-lg bg-gray-100 animate-pulse flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-100 animate-pulse rounded w-40" />
            <div class="h-3 bg-gray-100 animate-pulse rounded w-24" />
          </div>
          <div class="flex gap-2">
            <div class="w-7 h-7 bg-gray-100 animate-pulse rounded" />
            <div class="w-7 h-7 bg-gray-100 animate-pulse rounded" />
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="flex flex-col items-center justify-center py-16 text-center">
        <IconAlertTriangle class="w-8 h-8 text-red-400 mb-2" />
        <p class="text-sm text-red-600">{{ error }}</p>
        <button @click="load()" class="mt-3 text-xs text-blue-600 hover:underline">{{ $t('common.retry') }}</button>
      </div>

      <!-- Buit -->
      <div v-else-if="companies.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
        <IconBuildingSkyscraper class="w-10 h-10 text-gray-300 mb-3" />
        <p class="text-sm text-gray-500">{{ $t('companies.empty') }}</p>
        <button @click="openCreate" class="mt-3 text-sm text-blue-600 hover:underline">{{ $t('companies.create_first') }}</button>
      </div>

      <!-- Llista -->
      <div v-else class="divide-y divide-gray-100">
        <div v-for="c in companies" :key="c.id" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
          <div class="w-8 text-xs text-gray-400 font-mono flex-shrink-0 text-right">#{{ c.id }}</div>
          <!-- Logo / Avatar -->
          <div class="flex-shrink-0">
            <img v-if="c.logo_url" :src="c.logo_url" :alt="c.name" class="w-10 h-10 rounded-lg object-cover border border-gray-200" />
            <div v-else class="w-10 h-10 rounded-lg flex items-center justify-center text-base font-semibold text-white"
                 :style="{ backgroundColor: avatarColor(c.name) }">
              {{ c.name[0]?.toUpperCase() }}
            </div>
          </div>

          <!-- Nom + nom legal -->
          <div class="w-44 min-w-0">
            <div class="font-medium text-gray-900 text-sm truncate">{{ c.name }}</div>
            <div v-if="c.nom_legal" class="text-xs text-gray-400 truncate">{{ c.nom_legal }}</div>
            <div v-if="c.tax_id" class="text-xs text-gray-400 truncate">{{ c.tax_id }}</div>
          </div>

          <!-- Empleats -->
          <div class="flex items-center gap-1.5 w-28 text-sm text-gray-600">
            <IconUsers class="w-4 h-4 text-gray-400 flex-shrink-0" />
            <span>{{ c.employees_count }} {{ c.employees_count === 1 ? $t('companies.employee') : $t('companies.employees') }}</span>
          </div>

          <!-- Mòduls actius de l'empresa -->
          <div class="flex-1 flex items-center flex-wrap gap-1.5 min-w-0">
            <template v-if="activeCompanyModules(c).length">
              <span v-for="mod in activeCompanyModules(c)" :key="mod.key"
                    :class="mod.cls" class="text-[10px] font-medium px-2 py-0.5 rounded-full">
                {{ mod.label }}
              </span>
            </template>
            <span v-else class="text-xs text-gray-400 italic">{{ $t('companies.no_modules') }}</span>
          </div>

          <!-- Botons -->
          <div class="flex items-center gap-1 flex-shrink-0">
            <button @click="viewTarget = c" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" :title="$t('companies.view_details')">
              <IconEye class="w-4 h-4" />
            </button>
            <button @click="openEdit(c)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" :title="$t('common.edit')">
              <IconEdit class="w-4 h-4" />
            </button>
            <button @click="askDelete(c)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" :title="$t('common.delete')">
              <IconTrash class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Paginació -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t bg-gray-50">
        <p class="text-xs text-gray-400">{{ $t('common.page_of', { current: pagination.current_page, total: pagination.last_page }) }}</p>
        <div class="flex gap-1">
          <button @click="load(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                  class="px-2.5 py-1 text-xs rounded border border-gray-200 disabled:opacity-40 hover:bg-white transition-colors">{{ $t('common.previous') }}</button>
          <button @click="load(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
                  class="px-2.5 py-1 text-xs rounded border border-gray-200 disabled:opacity-40 hover:bg-white transition-colors">{{ $t('common.next') }}</button>
        </div>
      </div>
    </div>

    <!-- ── Modal crear / editar ────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="closeModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
            <h3 class="font-medium text-gray-900">{{ modal.isEdit ? $t('companies.edit_company') : $t('companies.new_company') }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>

          <form @submit.prevent="submitModal" class="overflow-y-auto flex-1 px-6 py-5 space-y-6">

            <!-- Imatges -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('companies.section_images') }}</p>
              <div class="grid grid-cols-2 gap-6">
                <!-- Logo -->
                <div>
                  <p class="text-xs text-gray-500 mb-2">{{ $t('companies.logo') }}</p>
                  <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50">
                      <img v-if="logoPreview || form.logo_url" :src="logoPreview || form.logo_url" class="w-full h-full object-cover" />
                      <IconPhoto v-else class="w-5 h-5 text-gray-300" />
                    </div>
                    <div>
                      <label class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                        <IconUpload class="w-3.5 h-3.5" />{{ $t('companies.select_file') }}
                        <input type="file" accept="image/*" class="hidden" @change="onLogoChange" />
                      </label>
                      <p class="text-[10px] text-gray-400 mt-1">{{ $t('companies.logo_hint') }}</p>
                      <button v-if="logoPreview || form.logo_url" type="button" @click="clearLogo" class="text-[10px] text-red-500 hover:underline mt-0.5 block">{{ $t('common.delete') }}</button>
                    </div>
                  </div>
                </div>
                <!-- Favicon -->
                <div>
                  <p class="text-xs text-gray-500 mb-2">{{ $t('companies.favicon') }}</p>
                  <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50">
                      <img v-if="faviconPreview || form.favicon_url" :src="faviconPreview || form.favicon_url" class="w-full h-full object-contain p-2" />
                      <IconPhoto v-else class="w-5 h-5 text-gray-300" />
                    </div>
                    <div>
                      <label class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                        <IconUpload class="w-3.5 h-3.5" />{{ $t('companies.select_file') }}
                        <input type="file" accept="image/*" class="hidden" @change="onFaviconChange" />
                      </label>
                      <p class="text-[10px] text-gray-400 mt-1">{{ $t('companies.favicon_hint') }}</p>
                      <button v-if="faviconPreview || form.favicon_url" type="button" @click="clearFavicon" class="text-[10px] text-red-500 hover:underline mt-0.5 block">{{ $t('common.delete') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Identificació -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('companies.section_identification') }}</p>
              <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.name') }} <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" :placeholder="$t('companies.name_placeholder')"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="formErrors.name ? 'border-red-400' : 'border-gray-200'" />
                    <p v-if="formErrors.name" class="text-xs text-red-600 mt-1">{{ formErrors.name[0] }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.legal_name') }}</label>
                    <input v-model="form.nom_legal" type="text" :placeholder="$t('companies.legal_name_placeholder')"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="formErrors.nom_legal ? 'border-red-400' : 'border-gray-200'" />
                    <p v-if="formErrors.nom_legal" class="text-xs text-red-600 mt-1">{{ formErrors.nom_legal[0] }}</p>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.tax_id') }}</label>
                    <input v-model="form.tax_id" type="text" :placeholder="$t('companies.tax_id_placeholder')"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="formErrors.tax_id ? 'border-red-400' : 'border-gray-200'" />
                    <p v-if="formErrors.tax_id" class="text-xs text-red-600 mt-1">{{ formErrors.tax_id[0] }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.country') }}</label>
                    <input v-model="form.country" type="text" :placeholder="$t('companies.country_placeholder')"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.billing_address') }}</label>
                  <input v-model="form.adreca_facturacio" type="text" :placeholder="$t('companies.billing_address_placeholder')"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            </div>

            <!-- Contacte -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('companies.section_contact') }}</p>
              <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.contact_person') }}</label>
                    <input v-model="form.persona_contacte" type="text" :placeholder="$t('companies.contact_person_placeholder')"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.phone') }}</label>
                    <input v-model="form.telefon" type="tel" :placeholder="$t('companies.phone_placeholder')"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.contact_email') }}</label>
                  <input v-model="form.email_contacte" type="email" :placeholder="$t('companies.contact_email_placeholder')"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            </div>

            <!-- Mòduls -->
            <div v-if="availableModules.length">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('companies.section_modules') }}</p>
              <div class="space-y-2">
                <label v-for="mod in availableModules" :key="mod.key"
                       class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                  <input type="checkbox" :value="mod.key" v-model="form.modules"
                         class="w-4 h-4 text-blue-600 rounded border-gray-300" />
                  <div>
                    <p class="text-sm font-medium text-gray-800">{{ mod.label }}</p>
                    <p class="text-xs text-gray-400">{{ mod.desc }}</p>
                  </div>
                </label>
              </div>
            </div>

            <!-- Usuari administrador -->
            <div class="border rounded-xl overflow-hidden">
              <button type="button" @click="form.createAdmin = !form.createAdmin"
                      class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
                <div class="flex items-center gap-2">
                  <IconUserPlus class="w-4 h-4 text-gray-500" />
                  <span class="text-sm font-medium text-gray-700">{{ $t('companies.add_admin') }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-gray-400">{{ form.createAdmin ? $t('common.active') : $t('companies.optional') }}</span>
                  <div class="relative w-9 h-5 rounded-full transition-colors" :class="form.createAdmin ? 'bg-blue-600' : 'bg-gray-300'">
                    <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all" :class="form.createAdmin ? 'left-4' : 'left-0.5'" />
                  </div>
                </div>
              </button>
              <div v-if="form.createAdmin" class="px-4 py-4 space-y-3 border-t">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.name') }}</label>
                  <input v-model="form.admin_name" type="text" :placeholder="$t('companies.admin_name_placeholder')"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.email') }} <span class="text-red-500">*</span></label>
                    <input v-model="form.admin_email" type="email" :placeholder="$t('companies.admin_email_placeholder')"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="formErrors.admin_email ? 'border-red-400' : 'border-gray-200'" />
                    <p v-if="formErrors.admin_email" class="text-xs text-red-600 mt-1">{{ formErrors.admin_email[0] }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('companies.password') }} <span class="text-red-500">*</span></label>
                    <input v-model="form.admin_password" type="password" :placeholder="$t('companies.password_placeholder')"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="formErrors.admin_password ? 'border-red-400' : 'border-gray-200'" />
                    <p v-if="formErrors.admin_password" class="text-xs text-red-600 mt-1">{{ formErrors.admin_password[0] }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Error general -->
            <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ formError }}</div>

            <div class="flex items-center justify-end gap-2 pt-1 border-t">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                {{ $t('common.cancel') }}
              </button>
              <button type="submit" :disabled="saving"
                      class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
                <svg v-if="saving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ saving ? $t('common.saving') : (modal.isEdit ? $t('common.save') : $t('companies.create_company')) }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal veure detalls ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="viewTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="viewTarget = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
            <h3 class="font-medium text-gray-900">{{ $t('companies.details_title') }}</h3>
            <button @click="viewTarget = null" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>

          <div class="overflow-y-auto flex-1 px-6 py-5 space-y-4">
            <!-- Capçalera -->
            <div class="flex items-center gap-4">
              <img v-if="viewTarget.logo_url" :src="viewTarget.logo_url" :alt="viewTarget.name"
                   class="w-16 h-16 rounded-xl object-cover border border-gray-200 flex-shrink-0" />
              <div v-else class="w-16 h-16 rounded-xl flex items-center justify-center text-2xl font-bold text-white flex-shrink-0"
                   :style="{ backgroundColor: avatarColor(viewTarget.name) }">
                {{ viewTarget.name[0]?.toUpperCase() }}
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ viewTarget.name }}</p>
                <p v-if="viewTarget.nom_legal" class="text-sm text-gray-500">{{ viewTarget.nom_legal }}</p>
                <div class="flex items-center gap-1.5 mt-1 text-sm text-gray-500">
                  <IconUsers class="w-3.5 h-3.5" />
                  <span>{{ viewTarget.employees_count }} {{ viewTarget.employees_count === 1 ? $t('companies.employee') : $t('companies.employees') }}</span>
                </div>
              </div>
            </div>

            <!-- Identificació -->
            <div class="border-t pt-4 space-y-2.5">
              <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $t('companies.section_identification') }}</p>
              <div v-if="viewTarget.tax_id" class="flex gap-3 text-sm">
                <span class="w-36 text-gray-400 flex-shrink-0">{{ $t('companies.tax_id') }}</span>
                <span class="text-gray-800">{{ viewTarget.tax_id }}</span>
              </div>
              <div v-if="viewTarget.adreca_facturacio" class="flex gap-3 text-sm">
                <span class="w-36 text-gray-400 flex-shrink-0">{{ $t('companies.address') }}</span>
                <span class="text-gray-800">{{ viewTarget.adreca_facturacio }}</span>
              </div>
              <div v-if="viewTarget.country" class="flex gap-3 text-sm">
                <span class="w-36 text-gray-400 flex-shrink-0">{{ $t('companies.country') }}</span>
                <span class="text-gray-800">{{ viewTarget.country }}</span>
              </div>
            </div>

            <!-- Contacte -->
            <div class="border-t pt-4 space-y-2.5">
              <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $t('companies.section_contact') }}</p>
              <div v-if="viewTarget.persona_contacte" class="flex gap-3 text-sm">
                <span class="w-36 text-gray-400 flex-shrink-0">{{ $t('companies.person') }}</span>
                <span class="text-gray-800">{{ viewTarget.persona_contacte }}</span>
              </div>
              <div v-if="viewTarget.telefon" class="flex gap-3 text-sm">
                <span class="w-36 text-gray-400 flex-shrink-0">{{ $t('companies.phone') }}</span>
                <span class="text-gray-800">{{ viewTarget.telefon }}</span>
              </div>
              <div v-if="viewTarget.email_contacte" class="flex gap-3 text-sm">
                <span class="w-36 text-gray-400 flex-shrink-0">{{ $t('companies.email') }}</span>
                <a :href="`mailto:${viewTarget.email_contacte}`" class="text-blue-600 hover:underline">{{ viewTarget.email_contacte }}</a>
              </div>
            </div>

            <!-- Mòduls -->
            <div class="border-t pt-4">
              <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">{{ $t('companies.active_modules') }}</p>
              <div v-if="activeCompanyModules(viewTarget).length" class="flex flex-wrap gap-1.5">
                <span v-for="mod in activeCompanyModules(viewTarget)" :key="mod.key"
                      :class="mod.cls" class="text-[10px] font-medium px-2 py-0.5 rounded-full">
                  {{ mod.label }}
                </span>
              </div>
              <p v-else class="text-xs text-gray-400 italic">{{ $t('companies.no_modules_assigned') }}</p>
            </div>

            <!-- Favicon -->
            <div v-if="viewTarget.favicon_url" class="border-t pt-4">
              <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">{{ $t('companies.favicon') }}</p>
              <img :src="viewTarget.favicon_url" class="w-8 h-8 object-contain border border-gray-200 rounded p-1" />
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal confirmar eliminació ─────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="deleteTarget = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
          <div class="flex items-start gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
              <IconAlertTriangle class="w-5 h-5 text-red-600" />
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ $t('companies.delete_title') }}</p>
              <i18n-t keypath="companies.delete_confirm" tag="p" class="text-sm text-gray-500 mt-1">
                <template #name><strong class="text-gray-800">{{ deleteTarget.name }}</strong></template>
              </i18n-t>
            </div>
          </div>
          <div class="flex gap-2 justify-end">
            <button @click="deleteTarget = null" class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">{{ $t('common.cancel') }}</button>
            <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 transition-colors">
              {{ deleting ? $t('common.deleting') : $t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  IconPlus, IconEdit, IconTrash, IconX, IconEye, IconPhoto, IconUpload, IconUserPlus,
  IconBuildingSkyscraper, IconAlertTriangle, IconUsers,
} from '@tabler/icons-vue'
import { useCompanies } from '../composables/useCompanies'
import { useAuthStore } from '../stores/auth'

const { t } = useI18n()

const { companies, loading, saving, error, pagination, load, create, update, remove } = useCompanies()
const auth = useAuthStore()

// ── Mòduls ────────────────────────────────────────────────────────────────────
const ALL_MODULES = computed(() => [
  { key: 'time_tracking', label: t('companies.module_time_tracking'), desc: t('companies.module_time_tracking_desc'), cls: 'bg-blue-50 text-blue-700' },
  { key: 'documents',     label: t('companies.module_documents'),     desc: t('companies.module_documents_desc'),     cls: 'bg-purple-50 text-purple-700' },
  { key: 'calendar',      label: t('companies.module_calendar'),      desc: t('companies.module_calendar_desc'),      cls: 'bg-green-50 text-green-700' },
])

// Mòduls que el distribuidor té habilitats (limita el que l'empresa pot activar)
const availableModules = computed(() => {
  const flags = auth.user?.tenant?.plan_feature_flags || []
  const keys  = flags.map(f => f.feature)
  return ALL_MODULES.value.filter(m => keys.includes(m.key))
})

function activeCompanyModules(c) {
  const flags = (c.plan_flags || []).map(f => f.feature)
  return ALL_MODULES.value.filter(m => flags.includes(m.key))
}

// ── Avatar ────────────────────────────────────────────────────────────────────
const AVATAR_COLORS = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316']
function avatarColor(name) {
  let h = 0
  for (const c of name) h = (h * 31 + c.charCodeAt(0)) & 0xffffffff
  return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length]
}

// ── Imatges ───────────────────────────────────────────────────────────────────
const logoPreview    = ref('')
const logoBase64     = ref('')
const faviconPreview = ref('')
const faviconBase64  = ref('')

function readImageFile(file, onResult) {
  const reader = new FileReader()
  reader.onload = ev => onResult(ev.target.result)
  reader.readAsDataURL(file)
}
function onLogoChange(e) {
  const file = e.target.files?.[0]
  if (!file) return
  readImageFile(file, r => { logoPreview.value = r; logoBase64.value = r })
}
function clearLogo() { logoPreview.value = ''; logoBase64.value = ''; form.logo_url = '' }
function onFaviconChange(e) {
  const file = e.target.files?.[0]
  if (!file) return
  readImageFile(file, r => { faviconPreview.value = r; faviconBase64.value = r })
}
function clearFavicon() { faviconPreview.value = ''; faviconBase64.value = ''; form.favicon_url = '' }

// ── Formulari ─────────────────────────────────────────────────────────────────
const modal      = reactive({ open: false, isEdit: false, editId: null })
const formErrors = ref({})
const formError  = ref('')
const form = reactive({
  name: '', nom_legal: '', tax_id: '', adreca_facturacio: '',
  telefon: '', email_contacte: '', persona_contacte: '',
  country: '', timezone: '', collective_agreement: '',
  logo_url: '', favicon_url: '', modules: [],
  createAdmin: false, admin_name: '', admin_email: '', admin_password: '',
})

function resetForm() {
  Object.assign(form, {
    name: '', nom_legal: '', tax_id: '', adreca_facturacio: '',
    telefon: '', email_contacte: '', persona_contacte: '',
    country: '', timezone: '', collective_agreement: '',
    logo_url: '', favicon_url: '', modules: [],
    createAdmin: false, admin_name: '', admin_email: '', admin_password: '',
  })
  logoPreview.value = ''; logoBase64.value = ''
  faviconPreview.value = ''; faviconBase64.value = ''
  formErrors.value = {}; formError.value = ''
}

function openCreate() {
  resetForm()
  Object.assign(modal, { open: true, isEdit: false, editId: null })
}

function openEdit(c) {
  resetForm()
  Object.assign(form, {
    name:               c.name,
    nom_legal:          c.nom_legal          || '',
    tax_id:             c.tax_id             || '',
    adreca_facturacio:  c.adreca_facturacio  || '',
    telefon:            c.telefon            || '',
    email_contacte:     c.email_contacte     || '',
    persona_contacte:   c.persona_contacte   || '',
    country:            c.country            || '',
    timezone:           c.timezone           || '',
    collective_agreement: c.collective_agreement || '',
    logo_url:           c.logo_url           || '',
    favicon_url:        c.favicon_url        || '',
    modules: (c.plan_flags || []).filter(f => ALL_MODULES.value.some(m => m.key === f.feature)).map(f => f.feature),
  })
  Object.assign(modal, { open: true, isEdit: true, editId: c.id })
}

function closeModal() { modal.open = false }

async function submitModal() {
  formErrors.value = {}
  formError.value  = ''
  const payload = {
    name: form.name.trim(), nom_legal: form.nom_legal, tax_id: form.tax_id,
    adreca_facturacio: form.adreca_facturacio, telefon: form.telefon,
    email_contacte: form.email_contacte, persona_contacte: form.persona_contacte,
    country: form.country, timezone: form.timezone,
    collective_agreement: form.collective_agreement,
    modules: form.modules,
  }
  if (logoBase64.value)    payload.logo_base64    = logoBase64.value
  if (faviconBase64.value) payload.favicon_base64 = faviconBase64.value
  if (form.createAdmin) {
    payload.admin_name     = form.admin_name
    payload.admin_email    = form.admin_email
    payload.admin_password = form.admin_password
  }
  const result = modal.isEdit
    ? await update(modal.editId, payload)
    : await create(payload)
  if (result.ok) { closeModal(); load(pagination.value.current_page) }
  else { formErrors.value = result.errors || {}; formError.value = result.message || t('common.error_saving') }
}

// ── Veure / Eliminar ──────────────────────────────────────────────────────────
const viewTarget   = ref(null)
const deleteTarget = ref(null)
const deleting     = ref(false)

function askDelete(c) { deleteTarget.value = c }

async function confirmDelete() {
  deleting.value = true
  try {
    await remove(deleteTarget.value.id)
    deleteTarget.value = null
    load(pagination.value.current_page)
  } finally {
    deleting.value = false
  }
}

onMounted(() => load())
</script>
