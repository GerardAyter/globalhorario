<template>
  <div>
    <!-- Capçalera -->
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">{{ $t('time_tracking.admin_entries_title') }}</h2>
        <p class="text-sm text-gray-400 mt-0.5">{{ $t('time_tracking.admin_entries_subtitle') }}</p>
      </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white border border-gray-200 rounded-xl px-5 py-3 mb-4 flex flex-wrap items-center gap-3">
      <div class="flex items-center gap-2">
        <button @click="prevMonth"
                class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
          <IconChevronLeft class="w-3.5 h-3.5" />
        </button>
        <select v-model.number="selectedMonth"
                class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option v-for="(name, idx) in MONTHS" :key="idx" :value="idx + 1">{{ name }}</option>
        </select>
        <select v-model.number="selectedYear"
                class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
        </select>
        <button @click="nextMonth" :disabled="isCurrentMonth"
                class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
          <IconChevronRight class="w-3.5 h-3.5" :class="isCurrentMonth ? 'opacity-30' : ''" />
        </button>
      </div>

      <div class="h-5 border-l border-gray-200" />

      <select v-model.number="selectedEmployee"
              class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[180px]">
        <option :value="null">{{ $t('time_tracking.all_employees') }}</option>
        <option v-for="emp in employees" :key="emp.id" :value="emp.id">
          {{ emp.nom }} {{ emp.cognoms }}
        </option>
      </select>

      <span class="ml-auto text-xs text-gray-400">{{ $t('time_tracking.records_count', { n: entries.length }) }}</span>
    </div>

    <!-- Taula -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

      <!-- Skeleton -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 6" :key="i" class="px-5 py-3 flex items-center gap-4">
          <div class="w-24 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="w-32 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="w-16 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="w-16 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="flex-1 h-4 bg-gray-100 animate-pulse rounded" />
        </div>
      </div>

      <!-- Buit -->
      <div v-else-if="entries.length === 0"
           class="flex flex-col items-center justify-center py-16 text-center">
        <IconClockOff class="w-10 h-10 text-gray-300 mb-3" />
        <p class="text-sm text-gray-500">{{ $t('time_tracking.no_entries_month', { month: MONTHS[selectedMonth - 1], year: selectedYear }) }}</p>
      </div>

      <template v-else>
        <!-- Cap taula -->
        <div class="px-5 py-2 bg-gray-50 border-b grid grid-cols-[1.2fr_1.6fr_auto_auto_1fr_auto_auto] gap-4 text-[10px] font-medium text-gray-400 uppercase tracking-wider">
          <span>{{ $t('time_tracking.col_employee') }}</span>
          <span>{{ $t('time_tracking.col_date') }}</span>
          <span class="w-20 text-center">{{ $t('time_tracking.col_entry') }}</span>
          <span class="w-20 text-center">{{ $t('time_tracking.col_exit') }}</span>
          <span>{{ $t('time_tracking.col_breaks') }}</span>
          <span class="w-20 text-center">{{ $t('time_tracking.col_effective') }}</span>
          <span class="w-24 text-right">{{ $t('common.actions') }}</span>
        </div>

        <div v-for="e in entries" :key="e.id"
             class="px-5 py-3 border-b last:border-0 grid grid-cols-[1.2fr_1.6fr_auto_auto_1fr_auto_auto] gap-4 items-start hover:bg-gray-50/60 transition-colors">

          <!-- Empleat -->
          <div class="pt-0.5">
            <p class="text-sm font-medium text-gray-900 truncate">{{ e.employee?.nom }} {{ e.employee?.cognoms }}</p>
          </div>

          <!-- Data + estat -->
          <div>
            <p class="text-sm font-medium text-gray-800 capitalize">{{ formatDate(e.date) }}</p>
            <div class="flex flex-wrap items-center gap-1 mt-0.5">
              <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full" :class="statusBadge(e.work_status)">
                {{ statusLabel(e.work_status) }}
              </span>
              <span v-if="e.pending_request_type"
                    class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700">
                {{ $t('time_tracking.pending_badge') }}
              </span>
              <span v-if="e.pending_admin_request"
                    class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-purple-50 text-purple-700">
                {{ $t('time_tracking.pending_approval_badge') }}
              </span>
            </div>
          </div>

          <!-- Entrada -->
          <div class="w-20 text-center pt-0.5">
            <span class="flex items-center justify-center gap-1 text-sm text-gray-700">
              <IconLogin class="w-3.5 h-3.5 text-green-500 flex-shrink-0" />
              {{ formatTime(e.clock_in_at) }}
            </span>
          </div>

          <!-- Sortida -->
          <div class="w-20 text-center pt-0.5">
            <span v-if="e.clock_out_at" class="flex items-center justify-center gap-1 text-sm text-gray-700">
              <IconLogout class="w-3.5 h-3.5 text-red-400 flex-shrink-0" />
              {{ formatTime(e.clock_out_at) }}
            </span>
            <span v-else class="text-xs text-amber-600 font-medium">{{ $t('time_tracking.in_progress') }}</span>
          </div>

          <!-- Pauses -->
          <div class="pt-0.5">
            <div v-if="completedBreaks(e).length > 0" class="space-y-0.5">
              <div v-for="(b, bi) in completedBreaks(e)" :key="bi"
                   class="group flex items-center gap-1 text-xs text-gray-500">
                <IconCoffee class="w-3 h-3 text-gray-400 flex-shrink-0" />
                <span>{{ formatTime(b.break_start_at) }} → {{ formatTime(b.break_end_at) }}</span>
                <span class="text-gray-400">({{ b.duration_minutes }}min)</span>
                <span v-if="b.pending_request_type" class="text-[9px] px-1 rounded-full bg-amber-50 text-amber-700">{{ $t('time_tracking.pending_badge') }}</span>
                <span v-else-if="b.pending_admin_request" class="text-[9px] px-1 rounded-full bg-purple-50 text-purple-700">{{ $t('time_tracking.pending_approval_badge') }}</span>
                <div v-else class="hidden group-hover:flex items-center gap-0.5 ml-0.5">
                  <button @click.stop="openBreakEdit(e, b)"
                          class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-amber-600 hover:bg-amber-50">
                    <IconPencil class="w-2.5 h-2.5" />
                  </button>
                  <button @click.stop="openBreakDelete(e, b)"
                          class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-red-600 hover:bg-red-50">
                    <IconTrash class="w-2.5 h-2.5" />
                  </button>
                </div>
              </div>
            </div>
            <span v-else class="text-sm text-gray-300">—</span>
          </div>

          <!-- Efectiu -->
          <div class="w-20 text-center pt-0.5">
            <span v-if="e.effective_minutes != null" class="text-sm font-mono font-medium"
                  :class="e.effective_minutes < 420 ? 'text-amber-600' : 'text-gray-800'">
              {{ formatDuration(e.effective_minutes) }}
            </span>
            <span v-else class="text-sm text-gray-300">—</span>
          </div>

          <!-- Accions -->
          <div class="w-24 flex items-center justify-end gap-1 pt-0.5">
            <button v-if="!e.clock_out_at"
                    @click="doAdminClockOut(e)"
                    :disabled="clockingOutId === e.id"
                    :title="$t('time_tracking.finalize_shift')"
                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                    :class="clockingOutId === e.id ? 'text-gray-200 cursor-not-allowed' : 'text-gray-400 hover:bg-green-50 hover:text-green-600'">
              <IconLogout class="w-3.5 h-3.5" />
            </button>
            <button @click="openEdit(e)" :title="$t('time_tracking.request_edit')"
                    :disabled="!!(e.pending_request_type || e.pending_admin_request)"
                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                    :class="(e.pending_request_type || e.pending_admin_request) ? 'text-gray-200 cursor-not-allowed' : 'text-gray-400 hover:bg-amber-50 hover:text-amber-600'">
              <IconPencil class="w-3.5 h-3.5" />
            </button>
            <button @click="openDelete(e)" :title="$t('time_tracking.request_delete')"
                    :disabled="!!(e.pending_request_type || e.pending_admin_request)"
                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                    :class="(e.pending_request_type || e.pending_admin_request) ? 'text-gray-200 cursor-not-allowed' : 'text-gray-400 hover:bg-red-50 hover:text-red-600'">
              <IconTrash class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <!-- Peu -->
        <div class="border-t bg-gray-50 px-5 py-2.5 flex items-center justify-between text-xs text-gray-500">
          <span>{{ $t('time_tracking.journeys_count', { n: entries.length }) }}</span>
          <span>{{ $t('time_tracking.total_effective') }} <span class="font-semibold text-gray-700">{{ formatDuration(totalEffective) }}</span></span>
        </div>
      </template>
    </div>

    <!-- ── MODAL EDITAR FITXATGE ──────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="editModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="editModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-medium text-gray-900">{{ $t('time_tracking.request_edit_entry') }}</h3>
            <button @click="editModal = false" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>
          <div v-if="selected && !editSuccess" class="px-5 py-4 space-y-4">
            <p class="text-xs text-gray-500">
              Fitxatge de <strong>{{ selected.employee?.nom }} {{ selected.employee?.cognoms }}</strong>
              del <strong class="capitalize">{{ formatDateLong(selected.date) }}</strong>.
              {{ $t('time_tracking.employee_must_approve_edit') }}
            </p>
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
              <p>{{ $t('time_tracking.current_entry') }}: <span class="font-mono font-medium text-gray-700">{{ formatTime(selected.clock_in_at) }}</span></p>
              <p>{{ $t('time_tracking.current_exit') }}: <span class="font-mono font-medium text-gray-700">{{ selected.clock_out_at ? formatTime(selected.clock_out_at) : $t('time_tracking.not_registered') }}</span></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('time_tracking.new_entry_time') }}</label>
              <input v-model="editForm.clock_in_at" type="datetime-local"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div v-if="selected.clock_out_at">
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('time_tracking.new_exit_time') }}</label>
              <input v-model="editForm.clock_out_at" type="datetime-local"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.reason') }} <span class="text-red-500">*</span></label>
              <textarea v-model="editForm.reason" rows="3" maxlength="500"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div v-if="editError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ editError }}</div>
          </div>
          <div v-if="editSuccess" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center"><IconCheck class="w-6 h-6 text-green-600" /></div>
            <p class="text-sm font-medium text-gray-900">{{ $t('time_tracking.request_sent_to_employee') }}</p>
            <p class="text-xs text-gray-500">{{ $t('time_tracking.employee_will_be_notified_edit') }}</p>
          </div>
          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="editModal = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ editSuccess ? $t('common.close') : $t('common.cancel') }}
            </button>
            <button v-if="!editSuccess" @click="submitEdit" :disabled="editSaving || !editForm.reason.trim()"
                    class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2">
              <svg v-if="editSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
              {{ editSaving ? $t('common.sending') : $t('time_tracking.send_request') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL ELIMINAR FITXATGE ────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="deleteModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="deleteModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
          <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="font-medium text-gray-900">{{ $t('time_tracking.request_delete_entry') }}</h3>
            <button @click="deleteModal = false" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>
          <div v-if="selected && !deleteSuccess" class="px-5 py-4 space-y-3">
            <p class="text-sm text-gray-600">
              Sol·licites eliminar el fitxatge de
              <strong class="text-gray-900">{{ selected.employee?.nom }} {{ selected.employee?.cognoms }}</strong>
              del <strong class="capitalize">{{ formatDateLong(selected.date) }}</strong>.
              {{ $t('time_tracking.employee_must_approve_delete') }}
            </p>
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
              <p>{{ $t('time_tracking.clock_in') }}: <span class="font-mono font-medium text-gray-700">{{ formatTime(selected.clock_in_at) }}</span></p>
              <p v-if="selected.clock_out_at">{{ $t('time_tracking.clock_out') }}: <span class="font-mono font-medium text-gray-700">{{ formatTime(selected.clock_out_at) }}</span></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.reason') }} <span class="text-red-500">*</span></label>
              <textarea v-model="deleteReason" rows="3" maxlength="500"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-500" />
            </div>
            <div v-if="deleteError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ deleteError }}</div>
          </div>
          <div v-if="deleteSuccess" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center"><IconCheck class="w-6 h-6 text-green-600" /></div>
            <p class="text-sm font-medium text-gray-900">{{ $t('time_tracking.request_sent_to_employee') }}</p>
            <p class="text-xs text-gray-500">{{ $t('time_tracking.employee_will_be_notified_delete') }}</p>
          </div>
          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="deleteModal = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ deleteSuccess ? $t('common.close') : $t('common.cancel') }}
            </button>
            <button v-if="!deleteSuccess" @click="submitDelete" :disabled="deleteSaving || !deleteReason.trim()"
                    class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2">
              <svg v-if="deleteSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
              {{ deleteSaving ? $t('common.sending') : $t('time_tracking.send_request') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL EDITAR PAUSA ─────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="breakEditModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="breakEditModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-medium text-gray-900">{{ $t('time_tracking.request_edit_entry') }}</h3>
            <button @click="breakEditModal = false" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>
          <div v-if="selectedBreak && !breakEditSuccess" class="px-5 py-4 space-y-4">
            <p class="text-xs text-gray-500">
              Pausa de <strong>{{ selected?.employee?.nom }} {{ selected?.employee?.cognoms }}</strong>
              del <strong class="capitalize">{{ selected ? formatDateLong(selected.date) : '' }}</strong>.
              {{ $t('time_tracking.employee_must_approve_edit') }}
            </p>
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
              <p>{{ $t('edit_requests.break_start_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selectedBreak.break_start_at) }}</span></p>
              <p>{{ $t('edit_requests.break_end_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selectedBreak.break_end_at) }}</span></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('time_tracking.new_break_start') }}</label>
              <input v-model="breakEditForm.break_start_at" type="datetime-local"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('time_tracking.new_break_end') }}</label>
              <input v-model="breakEditForm.break_end_at" type="datetime-local"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.reason') }} <span class="text-red-500">*</span></label>
              <textarea v-model="breakEditForm.reason" rows="3" maxlength="500"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div v-if="breakEditError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ breakEditError }}</div>
          </div>
          <div v-if="breakEditSuccess" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center"><IconCheck class="w-6 h-6 text-green-600" /></div>
            <p class="text-sm font-medium text-gray-900">{{ $t('time_tracking.request_sent_to_employee') }}</p>
          </div>
          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="breakEditModal = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ breakEditSuccess ? $t('common.close') : $t('common.cancel') }}
            </button>
            <button v-if="!breakEditSuccess" @click="submitBreakEdit" :disabled="breakEditSaving || !breakEditForm.reason.trim()"
                    class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2">
              <svg v-if="breakEditSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
              {{ breakEditSaving ? $t('common.sending') : $t('time_tracking.send_request') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL ELIMINAR PAUSA ───────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="breakDeleteModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="breakDeleteModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
          <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="font-medium text-gray-900">{{ $t('time_tracking.request_delete_entry') }}</h3>
            <button @click="breakDeleteModal = false" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>
          <div v-if="selectedBreak && !breakDeleteSuccess" class="px-5 py-4 space-y-3">
            <p class="text-sm text-gray-600">
              Sol·licites eliminar la pausa de
              <strong class="text-gray-900">{{ selected?.employee?.nom }} {{ selected?.employee?.cognoms }}</strong>
              del <strong class="capitalize">{{ selected ? formatDateLong(selected.date) : '' }}</strong>.
              {{ $t('time_tracking.employee_must_approve_delete') }}
            </p>
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
              <p>{{ $t('edit_requests.break_start_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selectedBreak.break_start_at) }}</span></p>
              <p>{{ $t('edit_requests.break_end_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selectedBreak.break_end_at) }}</span></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.reason') }} <span class="text-red-500">*</span></label>
              <textarea v-model="breakDeleteReason" rows="3" maxlength="500"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-500" />
            </div>
            <div v-if="breakDeleteError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ breakDeleteError }}</div>
          </div>
          <div v-if="breakDeleteSuccess" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center"><IconCheck class="w-6 h-6 text-green-600" /></div>
            <p class="text-sm font-medium text-gray-900">{{ $t('time_tracking.request_sent_to_employee') }}</p>
          </div>
          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="breakDeleteModal = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ breakDeleteSuccess ? $t('common.close') : $t('common.cancel') }}
            </button>
            <button v-if="!breakDeleteSuccess" @click="submitBreakDelete" :disabled="breakDeleteSaving || !breakDeleteReason.trim()"
                    class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2">
              <svg v-if="breakDeleteSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
              {{ breakDeleteSaving ? $t('common.sending') : $t('time_tracking.send_request') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  IconChevronLeft, IconChevronRight,
  IconLogin, IconLogout, IconCoffee, IconClockOff,
  IconPencil, IconTrash, IconX, IconCheck,
} from '@tabler/icons-vue'
import api from '../services/api'

const { t, locale } = useI18n()

const dateLocale = computed(() => ({ ca: 'ca-ES', es: 'es-ES', en: 'en-GB' }[locale.value] || 'ca-ES'))

const MONTHS = computed(() => Array.from({ length: 12 }, (_, i) => t(`months.${i + 1}`)))

const now = new Date()
const selectedYear     = ref(now.getFullYear())
const selectedMonth    = ref(now.getMonth() + 1)
const selectedEmployee = ref(null)

const yearOptions = computed(() => {
  const y = now.getFullYear()
  return Array.from({ length: 4 }, (_, i) => y - i)
})
const isCurrentMonth = computed(() =>
  selectedYear.value === now.getFullYear() && selectedMonth.value === now.getMonth() + 1
)
function prevMonth() {
  if (selectedMonth.value === 1) { selectedMonth.value = 12; selectedYear.value-- }
  else selectedMonth.value--
}
function nextMonth() {
  if (isCurrentMonth.value) return
  if (selectedMonth.value === 12) { selectedMonth.value = 1; selectedYear.value++ }
  else selectedMonth.value++
}

// ── Empleats per al filtre ─────────────────────────────────────────────────────
const employees = ref([])
async function fetchEmployees() {
  try {
    const res = await api.get('/v1/employees')
    employees.value = (res.data.data || []).sort((a, b) =>
      (a.nom + a.cognoms).localeCompare(b.nom + b.cognoms)
    )
  } catch {}
}

// ── Entrades ───────────────────────────────────────────────────────────────────
const entries = ref([])
const loading = ref(false)

async function fetchEntries() {
  loading.value = true
  try {
    const res = await api.get('/v1/time-tracking/admin/entries-month', {
      params: {
        year: selectedYear.value,
        month: selectedMonth.value,
        ...(selectedEmployee.value ? { employee_id: selectedEmployee.value } : {}),
      }
    })
    entries.value = res.data.data || []
  } catch {
    entries.value = []
  } finally {
    loading.value = false
  }
}

watch([selectedYear, selectedMonth, selectedEmployee], fetchEntries)
onMounted(() => { fetchEmployees(); fetchEntries() })

const totalEffective = computed(() => entries.value.reduce((s, e) => s + (e.effective_minutes || 0), 0))
const completedBreaks = e => (e.breaks || []).filter(b => b.break_end_at)

// ── Shared ─────────────────────────────────────────────────────────────────────
const selected     = ref(null)
const selectedBreak = ref(null)

// ── Modal editar fitxatge ──────────────────────────────────────────────────────
const editModal   = ref(false)
const editSaving  = ref(false)
const editSuccess = ref(false)
const editError   = ref('')
const editForm    = ref({ clock_in_at: '', clock_out_at: '', reason: '' })

function openEdit(e) {
  selected.value  = e
  editSuccess.value = false; editError.value = ''
  editForm.value  = { clock_in_at: toDatetimeLocal(e.clock_in_at), clock_out_at: toDatetimeLocal(e.clock_out_at), reason: '' }
  editModal.value = true
}
async function submitEdit() {
  editError.value = ''; editSaving.value = true
  const payload = { reason: editForm.value.reason }
  if (editForm.value.clock_in_at)  payload.clock_in_at  = localToUtc(editForm.value.clock_in_at)
  if (editForm.value.clock_out_at) payload.clock_out_at = localToUtc(editForm.value.clock_out_at)
  try {
    await api.post(`/v1/time-tracking/admin/entries/${selected.value.id}/edit-request`, payload)
    editSuccess.value = true
    const e = entries.value.find(e => e.id === selected.value.id)
    if (e) e.pending_admin_request = { type: 'edit' }
  } catch (err) {
    editError.value = err?.response?.data?.message || t('common.error')
  } finally { editSaving.value = false }
}

// ── Modal eliminar fitxatge ────────────────────────────────────────────────────
const deleteModal   = ref(false)
const deleteSaving  = ref(false)
const deleteSuccess = ref(false)
const deleteError   = ref('')
const deleteReason  = ref('')

function openDelete(e) {
  selected.value = e
  deleteSuccess.value = false; deleteError.value = ''; deleteReason.value = ''
  deleteModal.value = true
}
async function submitDelete() {
  deleteError.value = ''; deleteSaving.value = true
  try {
    await api.post(`/v1/time-tracking/admin/entries/${selected.value.id}/delete-request`, { reason: deleteReason.value })
    deleteSuccess.value = true
    const e = entries.value.find(e => e.id === selected.value.id)
    if (e) e.pending_admin_request = { type: 'delete' }
  } catch (err) {
    deleteError.value = err?.response?.data?.message || t('common.error')
  } finally { deleteSaving.value = false }
}

// ── Modal editar pausa ─────────────────────────────────────────────────────────
const breakEditModal   = ref(false)
const breakEditSaving  = ref(false)
const breakEditSuccess = ref(false)
const breakEditError   = ref('')
const breakEditForm    = ref({ break_start_at: '', break_end_at: '', reason: '' })

function openBreakEdit(entry, brk) {
  selected.value = entry; selectedBreak.value = brk
  breakEditSuccess.value = false; breakEditError.value = ''
  breakEditForm.value = { break_start_at: toDatetimeLocal(brk.break_start_at), break_end_at: toDatetimeLocal(brk.break_end_at), reason: '' }
  breakEditModal.value = true
}
async function submitBreakEdit() {
  breakEditError.value = ''; breakEditSaving.value = true
  const payload = { reason: breakEditForm.value.reason }
  if (breakEditForm.value.break_start_at) payload.break_start_at = localToUtc(breakEditForm.value.break_start_at)
  if (breakEditForm.value.break_end_at)   payload.break_end_at   = localToUtc(breakEditForm.value.break_end_at)
  try {
    await api.post(`/v1/time-tracking/admin/breaks/${selectedBreak.value.id}/edit-request`, payload)
    breakEditSuccess.value = true
    const brk = entries.value.find(e => e.id === selected.value.id)?.breaks?.find(b => b.id === selectedBreak.value.id)
    if (brk) brk.pending_admin_request = { type: 'break_edit' }
  } catch (err) {
    breakEditError.value = err?.response?.data?.message || t('common.error')
  } finally { breakEditSaving.value = false }
}

// ── Modal eliminar pausa ───────────────────────────────────────────────────────
const breakDeleteModal   = ref(false)
const breakDeleteSaving  = ref(false)
const breakDeleteSuccess = ref(false)
const breakDeleteError   = ref('')
const breakDeleteReason  = ref('')

function openBreakDelete(entry, brk) {
  selected.value = entry; selectedBreak.value = brk
  breakDeleteSuccess.value = false; breakDeleteError.value = ''; breakDeleteReason.value = ''
  breakDeleteModal.value = true
}
const clockingOutId = ref(null)

async function doAdminClockOut(entry) {
  clockingOutId.value = entry.id
  try {
    await api.post(`/v1/time-tracking/admin/entries/${entry.id}/clock-out`)
    await fetchEntries()
  } catch (err) {
    console.error(err?.response?.data?.message || err)
  } finally {
    clockingOutId.value = null
  }
}

async function submitBreakDelete() {
  breakDeleteError.value = ''; breakDeleteSaving.value = true
  try {
    await api.post(`/v1/time-tracking/admin/breaks/${selectedBreak.value.id}/delete-request`, { reason: breakDeleteReason.value })
    breakDeleteSuccess.value = true
    const brk = entries.value.find(e => e.id === selected.value.id)?.breaks?.find(b => b.id === selectedBreak.value.id)
    if (brk) brk.pending_admin_request = { type: 'break_delete' }
  } catch (err) {
    breakDeleteError.value = err?.response?.data?.message || t('common.error')
  } finally { breakDeleteSaving.value = false }
}

// ── Formats ────────────────────────────────────────────────────────────────────
function formatTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleTimeString(dateLocale.value, { hour: '2-digit', minute: '2-digit' })
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString(dateLocale.value, { weekday: 'short', day: 'numeric', month: 'short' })
}
function formatDateLong(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString(dateLocale.value, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}
function formatDuration(minutes) {
  if (!minutes && minutes !== 0) return '—'
  const h = Math.floor(minutes / 60), m = minutes % 60
  return h > 0 ? `${h}h${m > 0 ? ' ' + m + 'min' : ''}` : `${m} min`
}
function toDatetimeLocal(iso) {
  if (!iso) return ''
  const d = new Date(iso), pad = n => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}
function localToUtc(s) {
  if (!s) return null
  return new Date(s).toISOString().slice(0, 16)
}
function statusBadge(s) {
  return { clocked_in: 'bg-green-50 text-green-700', on_break: 'bg-amber-50 text-amber-700', clocked_out: 'bg-gray-100 text-gray-500' }[s] || 'bg-gray-100 text-gray-500'
}
function statusLabel(s) {
  return { clocked_in: t('time_tracking.working'), on_break: t('time_tracking.on_break'), clocked_out: t('time_tracking.completed') }[s] || '—'
}
</script>
