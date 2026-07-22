<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">{{ $t('tenants.title') }}</h2>
        <p class="text-sm text-gray-400 mt-0.5">{{ $t('tenants.count', { n: pagination.total }) }}</p>
      </div>
      <button @click="openCreate" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
        <IconPlus class="w-4 h-4" />{{ $t('tenants.new') }}
      </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4 px-5 py-4">
          <div class="w-10 h-10 rounded-lg bg-gray-100 animate-pulse flex-shrink-0" />
          <div class="flex-1 space-y-2"><div class="h-4 bg-gray-100 animate-pulse rounded w-40" /><div class="h-3 bg-gray-100 animate-pulse rounded w-24" /></div>
          <div class="flex gap-2"><div class="w-7 h-7 bg-gray-100 animate-pulse rounded" /><div class="w-7 h-7 bg-gray-100 animate-pulse rounded" /></div>
        </div>
      </div>
      <div v-else-if="error" class="flex flex-col items-center justify-center py-16 text-center">
        <IconAlertTriangle class="w-8 h-8 text-red-400 mb-2" />
        <p class="text-sm text-red-600">{{ error }}</p>
        <button @click="load()" class="mt-3 text-xs text-blue-600 hover:underline">{{ $t('common.retry') }}</button>
      </div>
      <div v-else-if="tenants.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
        <IconBuilding class="w-10 h-10 text-gray-300 mb-3" />
        <p class="text-sm text-gray-500">{{ $t('tenants.empty') }}</p>
        <button @click="openCreate" class="mt-3 text-sm text-blue-600 hover:underline">{{ $t('tenants.create_first') }}</button>
      </div>
      <div v-else class="divide-y divide-gray-100">
        <div v-for="t in tenants" :key="t.id" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
          <div class="w-8 text-xs text-gray-400 font-mono flex-shrink-0 text-right">#{{ t.id }}</div>
          <div class="flex-shrink-0">
            <img v-if="t.whitelabel && t.whitelabel.logo_url" :src="t.whitelabel.logo_url" :alt="t.nom_intern" class="w-10 h-10 rounded-lg object-cover border border-gray-200" />
            <div v-else class="w-10 h-10 rounded-lg flex items-center justify-center text-base font-semibold text-white flex-shrink-0" :style="{ backgroundColor: avatarColor(t.nom_intern) }">
              {{ t.nom_intern[0] ? t.nom_intern[0].toUpperCase() : '' }}
            </div>
          </div>
          <div class="w-44 min-w-0">
            <div class="font-medium text-gray-900 text-sm truncate">{{ t.nom_intern }}</div>
            <div v-if="t.nom_legal" class="text-xs text-gray-400 truncate">{{ t.nom_legal }}</div>
            <span :class="planClass(t.pla)" class="inline-block text-[10px] font-medium px-1.5 py-0.5 rounded-full mt-0.5">{{ t.pla }}</span>
          </div>
          <div class="flex items-center gap-1.5 w-28 text-sm text-gray-600">
            <IconBuildingSkyscraper class="w-4 h-4 text-gray-400 flex-shrink-0" />
            <span>{{ t.companies_count }} {{ t.companies_count === 1 ? $t('tenants.company_one') : $t('tenants.company_other') }}</span>
          </div>
          <div class="flex-1 flex items-center flex-wrap gap-1.5 min-w-0">
            <template v-if="activePlanModules(t).length">
              <span v-for="mod in activePlanModules(t)" :key="mod.key" :class="mod.cls" class="text-[10px] font-medium px-2 py-0.5 rounded-full">{{ mod.label }}</span>
            </template>
            <span v-else class="text-xs text-gray-400 italic">{{ $t('tenants.no_modules') }}</span>
          </div>
          <span :class="t.actiu ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'" class="text-[10px] font-medium px-2 py-0.5 rounded-full flex-shrink-0">
            {{ t.actiu ? $t('common.active') : $t('common.inactive') }}
          </span>
          <div class="flex items-center gap-1 flex-shrink-0">
            <button @click="viewTarget = t" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" :title="$t('tenants.view_details')"><IconEye class="w-4 h-4" /></button>
            <button @click="openEdit(t)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" :title="$t('common.edit')"><IconEdit class="w-4 h-4" /></button>
            <button @click="askDelete(t)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" :title="$t('common.delete')"><IconTrash class="w-4 h-4" /></button>
          </div>
        </div>
      </div>
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t bg-gray-50">
        <p class="text-xs text-gray-400">{{ $t('common.page_of', { current: pagination.current_page, total: pagination.last_page }) }}</p>
        <div class="flex gap-1">
          <button @click="load(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-2.5 py-1 text-xs rounded border border-gray-200 disabled:opacity-40 hover:bg-white transition-colors">{{ $t('common.previous') }}</button>
          <button @click="load(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-2.5 py-1 text-xs rounded border border-gray-200 disabled:opacity-40 hover:bg-white transition-colors">{{ $t('common.next') }}</button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="closeModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
            <h3 class="font-medium text-gray-900">{{ modal.isEdit ? $t('tenants.edit_title') : $t('tenants.new_title') }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>
          <form @submit.prevent="submitModal" class="overflow-y-auto flex-1 px-6 py-5 space-y-6">

            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('tenants.images_section') }}</p>
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <p class="text-xs text-gray-500 mb-2">{{ $t('tenants.logo') }}</p>
                  <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50">
                      <img v-if="logoPreview || form.logo_url" :src="logoPreview || form.logo_url" class="w-full h-full object-cover" />
                      <IconPhoto v-else class="w-5 h-5 text-gray-300" />
                    </div>
                    <div>
                      <label class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                        <IconUpload class="w-3.5 h-3.5" />{{ $t('tenants.select_file') }}
                        <input type="file" accept="image/*" class="hidden" @change="onLogoChange" />
                      </label>
                      <p class="text-[10px] text-gray-400 mt-1">{{ $t('tenants.logo_hint') }}</p>
                      <button v-if="logoPreview || form.logo_url" type="button" @click="clearLogo" class="text-[10px] text-red-500 hover:underline mt-0.5 block">{{ $t('common.delete') }}</button>
                    </div>
                  </div>
                </div>
                <div>
                  <p class="text-xs text-gray-500 mb-2">{{ $t('tenants.favicon') }}</p>
                  <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50">
                      <img v-if="faviconPreview || form.favicon_url" :src="faviconPreview || form.favicon_url" class="w-8 h-8 object-contain" />
                      <IconPhoto v-else class="w-5 h-5 text-gray-300" />
                    </div>
                    <div>
                      <label class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                        <IconUpload class="w-3.5 h-3.5" />{{ $t('tenants.select_file') }}
                        <input type="file" accept="image/*,.ico" class="hidden" @change="onFaviconChange" />
                      </label>
                      <p class="text-[10px] text-gray-400 mt-1">{{ $t('tenants.favicon_hint') }}</p>
                      <button v-if="faviconPreview || form.favicon_url" type="button" @click="clearFavicon" class="text-[10px] text-red-500 hover:underline mt-0.5 block">{{ $t('common.delete') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('tenants.identification_section') }}</p>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.internal_name') }}</label>
                  <input v-model="form.nom_intern" type="text" :placeholder="$t('tenants.internal_name_placeholder')"
                         class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                         :class="formErrors.nom_intern ? 'border-red-400' : 'border-gray-200'" />
                  <p v-if="formErrors.nom_intern" class="text-xs text-red-600 mt-1">{{ formErrors.nom_intern[0] }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.legal_name') }}</label>
                  <input v-model="form.nom_legal" type="text" :placeholder="$t('tenants.legal_name_placeholder')"
                         class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                         :class="formErrors.nom_legal ? 'border-red-400' : 'border-gray-200'" />
                  <p v-if="formErrors.nom_legal" class="text-xs text-red-600 mt-1">{{ formErrors.nom_legal[0] }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.tax_id') }}</label>
                  <input v-model="form.nif" type="text" :placeholder="$t('tenants.tax_id_placeholder')"
                         class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                         :class="formErrors.nif ? 'border-red-400' : 'border-gray-200'" />
                  <p v-if="formErrors.nif" class="text-xs text-red-600 mt-1">{{ formErrors.nif[0] }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.billing_address') }}</label>
                  <input v-model="form.adreca_facturacio" type="text" :placeholder="$t('tenants.billing_address_placeholder')" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            </div>

            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('tenants.contact_section') }}</p>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.contact_person') }}</label>
                  <input v-model="form.persona_contacte" type="text" :placeholder="$t('tenants.contact_person_placeholder')" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.phone') }}</label>
                  <input v-model="form.telefon" type="tel" :placeholder="$t('tenants.phone_placeholder')" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="col-span-2">
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.contact_email') }}</label>
                  <input v-model="form.email_contacte" type="email" :placeholder="$t('tenants.contact_email_placeholder')"
                         class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                         :class="formErrors.email_contacte ? 'border-red-400' : 'border-gray-200'" />
                  <p v-if="formErrors.email_contacte" class="text-xs text-red-600 mt-1">{{ formErrors.email_contacte[0] }}</p>
                </div>
              </div>
            </div>

            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('tenants.plan_section') }}</p>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.plan') }}</label>
                  <select v-model="form.pla" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="starter">Starter</option>
                    <option value="pro">Pro</option>
                    <option value="enterprise">Enterprise</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.max_employees') }}</label>
                  <input v-model.number="form.max_empleats" type="number" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  <p class="text-xs text-gray-400 mt-1">{{ $t('tenants.unlimited_hint') }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3 mt-4">
                <button type="button" @click="form.actiu = !form.actiu" :class="form.actiu ? 'bg-blue-600' : 'bg-gray-200'" class="relative w-10 h-5 rounded-full transition-colors focus:outline-none flex-shrink-0">
                  <span :class="form.actiu ? 'translate-x-5' : 'translate-x-0.5'" class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform block" />
                </button>
                <span class="text-sm text-gray-700 select-none">{{ $t('tenants.active_label') }}</span>
              </div>
            </div>

            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('tenants.modules_section') }}</p>
              <div class="grid grid-cols-3 gap-3">
                <label v-for="mod in MODULES" :key="mod.key"
                       class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors select-none"
                       :class="form.modules.includes(mod.key) ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                  <input type="checkbox" :value="mod.key" v-model="form.modules" class="mt-0.5 accent-blue-600 flex-shrink-0" />
                  <div>
                    <div class="text-sm font-medium text-gray-800">{{ $t(mod.labelKey) }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $t(mod.descKey) }}</div>
                  </div>
                </label>
              </div>
            </div>

            <div>
              <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('tenants.superadmin_section') }}</p>
                <button type="button" @click="form.createSuperadmin = !form.createSuperadmin"
                        :class="form.createSuperadmin ? 'bg-blue-600' : 'bg-gray-200'"
                        class="relative w-8 h-4 rounded-full transition-colors focus:outline-none flex-shrink-0">
                  <span :class="form.createSuperadmin ? 'translate-x-4' : 'translate-x-0.5'"
                        class="absolute top-0.5 w-3 h-3 bg-white rounded-full shadow transition-transform block" />
                </button>
              </div>
              <div v-if="form.createSuperadmin" class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.superadmin_name') }}</label>
                  <input v-model="form.superadmin_name" type="text" :placeholder="$t('tenants.superadmin_name_placeholder')"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.email') }}</label>
                  <input v-model="form.superadmin_email" type="email" :placeholder="$t('tenants.superadmin_email_placeholder')"
                         class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                         :class="formErrors.superadmin_email ? 'border-red-400' : 'border-gray-200'" />
                  <p v-if="formErrors.superadmin_email" class="text-xs text-red-600 mt-1">{{ formErrors.superadmin_email[0] }}</p>
                </div>
                <div class="col-span-2">
                  <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('tenants.password') }}</label>
                  <input v-model="form.superadmin_password" type="password" :placeholder="$t('tenants.password_placeholder')"
                         class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                         :class="formErrors.superadmin_password ? 'border-red-400' : 'border-gray-200'" />
                  <p v-if="formErrors.superadmin_password" class="text-xs text-red-600 mt-1">{{ formErrors.superadmin_password[0] }}</p>
                </div>
              </div>
              <p v-else class="text-xs text-gray-400">{{ $t('tenants.superadmin_hint') }}</p>
            </div>

            <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ formError }}</div>
          </form>
          <div class="flex items-center justify-end gap-2 px-6 py-4 border-t flex-shrink-0">
            <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">{{ $t('common.cancel') }}</button>
            <button @click="submitModal" :disabled="saving" class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
              <svg v-if="saving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
              {{ saving ? $t('common.saving') : (modal.isEdit ? $t('common.save') : $t('tenants.create')) }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal de detall ────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="viewTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="viewTarget = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
            <div class="flex items-center gap-3">
              <img v-if="viewTarget.whitelabel && viewTarget.whitelabel.logo_url"
                   :src="viewTarget.whitelabel.logo_url" class="w-8 h-8 rounded-lg object-cover border border-gray-200" />
              <div v-else class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-semibold text-white flex-shrink-0"
                   :style="{ backgroundColor: avatarColor(viewTarget.nom_intern) }">
                {{ viewTarget.nom_intern[0] ? viewTarget.nom_intern[0].toUpperCase() : '' }}
              </div>
              <div>
                <h3 class="font-medium text-gray-900 leading-none">{{ viewTarget.nom_intern }}</h3>
                <p v-if="viewTarget.nom_legal" class="text-xs text-gray-400 mt-0.5">{{ viewTarget.nom_legal }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button @click="openEdit(viewTarget); viewTarget = null"
                      class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                <IconEdit class="w-3.5 h-3.5" />{{ $t('common.edit') }}
              </button>
              <button @click="viewTarget = null" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
            </div>
          </div>

          <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">
            <!-- Badges pla + estat -->
            <div class="flex items-center gap-2">
              <span :class="planClass(viewTarget.pla)" class="text-xs font-medium px-2 py-0.5 rounded-full">{{ viewTarget.pla }}</span>
              <span :class="viewTarget.actiu ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'" class="text-xs font-medium px-2 py-0.5 rounded-full">{{ viewTarget.actiu ? $t('common.active') : $t('common.inactive') }}</span>
              <span class="text-xs text-gray-400">{{ viewTarget.companies_count }} {{ viewTarget.companies_count === 1 ? $t('tenants.company_one') : $t('tenants.company_other') }}</span>
              <span v-if="viewTarget.max_empleats" class="text-xs text-gray-400">· {{ $t('tenants.max_employees_short', { n: viewTarget.max_empleats }) }}</span>
            </div>

            <!-- Identificació -->
            <div>
              <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-2">{{ $t('tenants.identification_section') }}</p>
              <div class="grid grid-cols-2 gap-x-6 gap-y-2">
                <div v-if="viewTarget.nif">
                  <p class="text-[10px] text-gray-400">{{ $t('tenants.tax_id') }}</p>
                  <p class="text-sm text-gray-800">{{ viewTarget.nif }}</p>
                </div>
                <div v-if="viewTarget.adreca_facturacio">
                  <p class="text-[10px] text-gray-400">{{ $t('tenants.billing_address') }}</p>
                  <p class="text-sm text-gray-800">{{ viewTarget.adreca_facturacio }}</p>
                </div>
              </div>
            </div>

            <!-- Contacte -->
            <div v-if="viewTarget.persona_contacte || viewTarget.telefon || viewTarget.email_contacte">
              <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-2">{{ $t('tenants.contact_section') }}</p>
              <div class="grid grid-cols-2 gap-x-6 gap-y-2">
                <div v-if="viewTarget.persona_contacte">
                  <p class="text-[10px] text-gray-400">{{ $t('tenants.contact_person') }}</p>
                  <p class="text-sm text-gray-800">{{ viewTarget.persona_contacte }}</p>
                </div>
                <div v-if="viewTarget.telefon">
                  <p class="text-[10px] text-gray-400">{{ $t('tenants.phone') }}</p>
                  <p class="text-sm text-gray-800">{{ viewTarget.telefon }}</p>
                </div>
                <div v-if="viewTarget.email_contacte" class="col-span-2">
                  <p class="text-[10px] text-gray-400">{{ $t('tenants.contact_email') }}</p>
                  <a :href="'mailto:' + viewTarget.email_contacte" class="text-sm text-blue-600 hover:underline">{{ viewTarget.email_contacte }}</a>
                </div>
              </div>
            </div>

            <!-- Mòduls -->
            <div>
              <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-2">{{ $t('tenants.modules_section') }}</p>
              <div v-if="activePlanModules(viewTarget).length" class="flex flex-wrap gap-1.5">
                <span v-for="mod in activePlanModules(viewTarget)" :key="mod.key" :class="mod.cls"
                      class="text-xs font-medium px-2.5 py-1 rounded-full">{{ $t(mod.labelKey) }}</span>
              </div>
              <p v-else class="text-sm text-gray-400 italic">{{ $t('tenants.no_modules_assigned') }}</p>
            </div>

            <!-- Favicon -->
            <div v-if="viewTarget.whitelabel && viewTarget.whitelabel.favicon_url">
              <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-2">{{ $t('tenants.favicon') }}</p>
              <img :src="viewTarget.whitelabel.favicon_url" class="w-8 h-8 object-contain" />
            </div>

            <!-- Data alta -->
            <div v-if="viewTarget.data_alta">
              <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">{{ $t('tenants.joined_at') }}</p>
              <p class="text-sm text-gray-800">{{ new Date(viewTarget.data_alta).toLocaleDateString(dateLocale) }}</p>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="deleteTarget = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
          <div class="flex items-start gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0"><IconAlertTriangle class="w-5 h-5 text-red-600" /></div>
            <div>
              <p class="font-medium text-gray-900">{{ $t('tenants.delete_title') }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ $t('tenants.delete_desc', { name: deleteTarget.nom_intern }) }} {{ $t('tenants.delete_warning') }}</p>
            </div>
          </div>
          <div class="flex gap-2 justify-end">
            <button @click="deleteTarget = null" class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">{{ $t('common.cancel') }}</button>
            <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 transition-colors">{{ deleting ? $t('common.deleting') : $t('common.delete') }}</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPlus, IconEdit, IconTrash, IconX, IconEye, IconBuilding, IconBuildingSkyscraper, IconAlertTriangle, IconPhoto, IconUpload } from '@tabler/icons-vue'
import { useTenants } from '../composables/useTenants'

const { tenants, loading, saving, error, pagination, load, create, update, remove } = useTenants()
const { locale } = useI18n()

const dateLocale = computed(() => ({ ca: 'ca-ES', es: 'es-ES', en: 'en-GB' }[locale.value] || 'ca-ES'))
const MODULES = [
  { key: 'time_tracking', labelKey: 'tenants.module_time_tracking', descKey: 'tenants.module_time_tracking_desc' },
  { key: 'documents',     labelKey: 'tenants.module_documents', descKey: 'tenants.module_documents_desc' },
  { key: 'calendar',      labelKey: 'tenants.module_calendar', descKey: 'tenants.module_calendar_desc' },
]
const MODULE_KEYS = MODULES.map(m => m.key)
const MODULE_CLS  = { time_tracking: 'bg-blue-50 text-blue-700', documents: 'bg-purple-50 text-purple-700', calendar: 'bg-green-50 text-green-700' }

function activePlanModules(t) {
  return (t.plan_feature_flags || [])
    .filter(f => MODULE_KEYS.includes(f.feature))
    .map(f => ({ key: f.feature, labelKey: (MODULES.find(m => m.key === f.feature) || {}).labelKey || f.feature, cls: MODULE_CLS[f.feature] || 'bg-gray-100 text-gray-600' }))
}

const AVATAR_COLORS = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316']
function avatarColor(name) { let h = 0; for (const c of name) h = (h * 31 + c.charCodeAt(0)) & 0xffffffff; return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length] }
function planClass(pla) { if (pla === 'enterprise') return 'bg-blue-50 text-blue-700'; if (pla === 'pro') return 'bg-amber-50 text-amber-700'; return 'bg-gray-100 text-gray-600' }

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
  const file = e.target.files && e.target.files[0]
  if (!file) return
  readImageFile(file, r => { logoPreview.value = r; logoBase64.value = r })
}
function clearLogo() { logoPreview.value = ''; logoBase64.value = ''; form.logo_url = '' }
function onFaviconChange(e) {
  const file = e.target.files && e.target.files[0]
  if (!file) return
  readImageFile(file, r => { faviconPreview.value = r; faviconBase64.value = r })
}
function clearFavicon() { faviconPreview.value = ''; faviconBase64.value = ''; form.favicon_url = '' }

const modal      = reactive({ open: false, isEdit: false, editId: null })
const formErrors = ref({})
const formError  = ref('')
const form = reactive({
  nom_intern: '', nom_legal: '', nif: '', adreca_facturacio: '', telefon: '',
  email_contacte: '', persona_contacte: '', pla: 'starter', max_empleats: 0, actiu: true,
  logo_url: '', favicon_url: '', modules: [],
  createSuperadmin: false, superadmin_name: '', superadmin_email: '', superadmin_password: '',
})

function resetForm() {
  Object.assign(form, {
    nom_intern: '', nom_legal: '', nif: '', adreca_facturacio: '', telefon: '',
    email_contacte: '', persona_contacte: '', pla: 'starter', max_empleats: 0, actiu: true,
    logo_url: '', favicon_url: '', modules: [],
    createSuperadmin: false, superadmin_name: '', superadmin_email: '', superadmin_password: '',
  })
  logoPreview.value = ''; logoBase64.value = ''
  faviconPreview.value = ''; faviconBase64.value = ''
  formErrors.value = {}; formError.value = ''
}
function openCreate() { resetForm(); Object.assign(modal, { open: true, isEdit: false, editId: null }) }
function openEdit(t) {
  resetForm()
  Object.assign(form, {
    nom_intern: t.nom_intern, nom_legal: t.nom_legal || '', nif: t.nif || '',
    adreca_facturacio: t.adreca_facturacio || '', telefon: t.telefon || '',
    email_contacte: t.email_contacte || '', persona_contacte: t.persona_contacte || '',
    pla: t.pla, max_empleats: t.max_empleats || 0, actiu: !!t.actiu,
    logo_url:    (t.whitelabel && t.whitelabel.logo_url)    || '',
    favicon_url: (t.whitelabel && t.whitelabel.favicon_url) || '',
    modules: (t.plan_feature_flags || []).filter(f => MODULE_KEYS.includes(f.feature)).map(f => f.feature),
  })
  Object.assign(modal, { open: true, isEdit: true, editId: t.id })
}
function closeModal() { modal.open = false }

async function submitModal() {
  formErrors.value = {}; formError.value = ''
  const payload = {
    nom_intern: form.nom_intern.trim(), nom_legal: form.nom_legal, nif: form.nif,
    adreca_facturacio: form.adreca_facturacio, telefon: form.telefon,
    email_contacte: form.email_contacte, persona_contacte: form.persona_contacte,
    pla: form.pla, max_empleats: form.max_empleats, actiu: form.actiu, modules: form.modules,
  }
  if (logoBase64.value)    payload.logo_base64    = logoBase64.value
  if (faviconBase64.value) payload.favicon_base64 = faviconBase64.value
  if (form.createSuperadmin) {
    payload.superadmin_name     = form.superadmin_name
    payload.superadmin_email    = form.superadmin_email
    payload.superadmin_password = form.superadmin_password
  }
  const result = modal.isEdit ? await update(modal.editId, payload) : await create(payload)
  if (result.ok) { closeModal(); load(pagination.current_page) }
  else { formErrors.value = result.errors || {}; formError.value = result.message || 'Error en desar.' }
}

const viewTarget   = ref(null)
const deleteTarget = ref(null)
const deleting     = ref(false)
function askDelete(t) { deleteTarget.value = t }
async function confirmDelete() {
  deleting.value = true
  try { await remove(deleteTarget.value.id); deleteTarget.value = null; load(pagination.current_page) }
  finally { deleting.value = false }
}

onMounted(() => load())
</script>
