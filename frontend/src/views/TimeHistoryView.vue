<template>
  <div>
    <!-- Capçalera -->
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-3">
        <button @click="$router.back()"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
          <IconArrowLeft class="w-4 h-4" />
        </button>
        <div>
          <h2 class="text-base font-medium text-gray-900">{{ $t('time_tracking.history_title') }}</h2>
          <p class="text-sm text-gray-400 mt-0.5">{{ $t('time_tracking.history_subtitle') }}</p>
        </div>
      </div>

      <!-- Selector mes/any -->
      <div class="flex items-center gap-2">
        <button @click="prevMonth"
                class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors">
          <IconChevronLeft class="w-4 h-4" />
        </button>
        <div class="flex items-center gap-2">
          <select v-model.number="selectedMonth"
                  class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option v-for="(name, idx) in MONTHS" :key="idx" :value="idx + 1">{{ name }}</option>
          </select>
          <select v-model.number="selectedYear"
                  class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
        <button @click="nextMonth" :disabled="isCurrentMonth"
                class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors">
          <IconChevronRight class="w-4 h-4" :class="isCurrentMonth ? 'opacity-30' : ''" />
        </button>
      </div>
    </div>

    <!-- Taula -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

      <!-- Skeleton -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 6" :key="i" class="px-5 py-3 flex items-center gap-4">
          <div class="w-28 h-4 bg-gray-100 animate-pulse rounded" />
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
        <!-- Capçalera taula -->
        <div class="px-5 py-2 bg-gray-50 border-b grid grid-cols-[1.4fr_auto_auto_1fr_auto_auto] gap-4 text-[10px] font-medium text-gray-400 uppercase tracking-wider">
          <span>{{ $t('time_tracking.col_date') }}</span>
          <span class="w-20 text-center">{{ $t('time_tracking.col_entry') }}</span>
          <span class="w-20 text-center">{{ $t('time_tracking.col_exit') }}</span>
          <span>{{ $t('time_tracking.col_breaks') }}</span>
          <span class="w-20 text-center">{{ $t('time_tracking.col_effective') }}</span>
          <span class="w-24 text-right">{{ $t('common.actions') }}</span>
        </div>

        <div v-for="e in entries" :key="e.id"
             class="px-5 py-3 border-b last:border-0 grid grid-cols-[1.4fr_auto_auto_1fr_auto_auto] gap-4 items-start hover:bg-gray-50/60 transition-colors">

          <!-- Data -->
          <div>
            <p class="text-sm font-medium text-gray-900 capitalize">{{ formatDate(e.date) }}</p>
            <div class="flex items-center gap-1.5 mt-0.5">
              <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full"
                    :class="statusBadge(e.work_status)">
                {{ statusLabel(e.work_status) }}
              </span>
              <span v-if="e.pending_request_type === 'edit'"
                    class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700">
                {{ $t('time_tracking.request_edit') }}
              </span>
              <span v-else-if="e.pending_request_type === 'delete'"
                    class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-red-50 text-red-700">
                {{ $t('time_tracking.request_delete') }}
              </span>
              <button v-if="e.pending_admin_request"
                      @click="openAdminReview(e)"
                      class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-purple-50 text-purple-700 hover:bg-purple-100 transition-colors">
                Admin vol {{ adminActionLabel(e.pending_admin_request.type) }} ›
              </button>
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
              <span class="relative">
                {{ formatClockOut(e.clock_out_at, e.clock_in_at) }}
                <sup v-if="dayDiff(e.clock_out_at, e.clock_in_at) > 0"
                     class="absolute -top-1.5 -right-3 text-[9px] font-bold text-blue-500">
                  +{{ dayDiff(e.clock_out_at, e.clock_in_at) }}
                </sup>
              </span>
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
                <span v-if="b.pending_request_type === 'break_edit'"
                      class="text-[9px] font-medium px-1 py-0.5 rounded-full bg-amber-50 text-amber-700">{{ $t('common.edit') }}</span>
                <span v-else-if="b.pending_request_type === 'break_delete'"
                      class="text-[9px] font-medium px-1 py-0.5 rounded-full bg-red-50 text-red-700">{{ $t('common.delete') }}</span>
                <button v-else-if="b.pending_admin_request"
                        @click.stop="openAdminBreakReview(e, b)"
                        class="text-[9px] font-medium px-1 py-0.5 rounded-full bg-purple-50 text-purple-700 hover:bg-purple-100">
                  {{ $t('edit_requests.review_btn') }}
                </button>
                <div v-else class="ml-0.5 hidden group-hover:flex items-center gap-0.5">
                  <button @click.stop="openBreakEdit(e, b)" title="Editar pausa"
                          class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                    <IconPencil class="w-2.5 h-2.5" />
                  </button>
                  <button @click.stop="openBreakDelete(e, b)" title="Eliminar pausa"
                          class="w-5 h-5 flex items-center justify-center rounded text-gray-300 hover:text-red-600 hover:bg-red-50 transition-colors">
                    <IconTrash class="w-2.5 h-2.5" />
                  </button>
                </div>
              </div>
            </div>
            <span v-else class="text-sm text-gray-300">—</span>
          </div>

          <!-- Efectiu -->
          <div class="w-20 text-center pt-0.5">
            <span v-if="e.effective_minutes != null"
                  class="text-sm font-mono font-medium"
                  :class="e.effective_minutes < 420 ? 'text-amber-600' : 'text-gray-800'">
              {{ formatDuration(e.effective_minutes) }}
            </span>
            <span v-else class="text-sm text-gray-300">—</span>
          </div>

          <!-- Accions -->
          <div class="w-24 flex items-center justify-end gap-1 pt-0.5">
            <button @click="openView(e)" title="Veure detalls"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors">
              <IconEye class="w-3.5 h-3.5" />
            </button>
            <button @click="openEdit(e)" title="Sol·licitar edició"
                    :disabled="!!(e.pending_request_type || e.pending_admin_request)"
                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                    :class="(e.pending_request_type || e.pending_admin_request)
                      ? 'text-gray-200 cursor-not-allowed'
                      : 'text-gray-400 hover:bg-amber-50 hover:text-amber-600'">
              <IconPencil class="w-3.5 h-3.5" />
            </button>
            <button @click="openDelete(e)" title="Sol·licitar eliminació"
                    :disabled="!!(e.pending_request_type || e.pending_admin_request)"
                    class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                    :class="(e.pending_request_type || e.pending_admin_request)
                      ? 'text-gray-200 cursor-not-allowed'
                      : 'text-gray-400 hover:bg-red-50 hover:text-red-600'">
              <IconTrash class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <!-- Peu -->
        <div class="border-t bg-gray-50 px-5 py-2.5 flex items-center justify-between text-xs text-gray-500">
          <span>{{ $t('time_tracking.journeys_count', { n: entries.length }) }}</span>
          <span>{{ $t('time_tracking.total_effective') }}: <span class="font-semibold text-gray-700">{{ formatDuration(totalEffective) }}</span></span>
        </div>
      </template>
    </div>

    <!-- ── MODAL VEURE ─────────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="viewModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="viewModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-medium text-gray-900">Detalls del fitxatge</h3>
            <button @click="viewModal = false" class="text-gray-400 hover:text-gray-600">
              <IconX class="w-5 h-5" />
            </button>
          </div>

          <div v-if="selected" class="px-5 py-4 space-y-4">
            <!-- Data i estat -->
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-semibold text-gray-900 capitalize">{{ formatDateLong(selected.date) }}</p>
              </div>
              <span class="text-xs font-medium px-2 py-1 rounded-full" :class="statusBadge(selected.work_status)">
                {{ statusLabel(selected.work_status) }}
              </span>
            </div>

            <!-- Horari -->
            <div class="bg-gray-50 rounded-xl p-3 space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 flex items-center gap-2">
                  <IconLogin class="w-4 h-4 text-green-500" /> {{ $t('time_tracking.clock_in') }}
                </span>
                <span class="font-mono font-medium text-gray-900">{{ formatTime(selected.clock_in_at) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 flex items-center gap-2">
                  <IconLogout class="w-4 h-4 text-red-400" /> {{ $t('time_tracking.clock_out') }}
                </span>
                <span class="font-mono font-medium" :class="selected.clock_out_at ? 'text-gray-900' : 'text-amber-600'">
                  <span v-if="selected.clock_out_at" class="relative">
                    {{ formatClockOut(selected.clock_out_at, selected.clock_in_at) }}
                    <sup v-if="dayDiff(selected.clock_out_at, selected.clock_in_at) > 0"
                         class="absolute -top-1.5 -right-3 text-[9px] font-bold text-blue-500">
                      +{{ dayDiff(selected.clock_out_at, selected.clock_in_at) }}
                    </sup>
                  </span>
                  <span v-else class="text-amber-600">{{ $t('time_tracking.in_progress') }}</span>
                </span>
              </div>
              <div class="border-t pt-2 flex items-center justify-between text-sm">
                <span class="text-gray-500">{{ $t('widget.effective_time') }}</span>
                <span class="font-semibold text-gray-900">{{ formatDuration(selected.effective_minutes) }}</span>
              </div>
            </div>

            <!-- Pauses -->
            <div v-if="completedBreaks(selected).length > 0">
              <p class="text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">{{ $t('time_tracking.breaks') }}</p>
              <div class="space-y-1.5">
                <div v-for="(b, bi) in completedBreaks(selected)" :key="bi"
                     class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-3 py-2">
                  <span class="flex items-center gap-2 text-gray-600">
                    <IconCoffee class="w-3.5 h-3.5 text-gray-400" />
                    {{ formatTime(b.break_start_at) }} → {{ formatTime(b.break_end_at) }}
                    <span v-if="b.pending_request_type === 'break_edit'"
                          class="text-[9px] font-medium px-1 py-0.5 rounded-full bg-amber-50 text-amber-700">{{ $t('time_tracking.request_edit') }}</span>
                    <span v-else-if="b.pending_request_type === 'break_delete'"
                          class="text-[9px] font-medium px-1 py-0.5 rounded-full bg-red-50 text-red-700">{{ $t('time_tracking.request_delete') }}</span>
                  </span>
                  <span class="text-gray-400 text-xs">{{ b.duration_minutes }} min</span>
                </div>
              </div>
              <div class="flex items-center justify-between text-xs text-gray-500 mt-2 px-1">
                <span>{{ $t('time_tracking.breaks') }} total</span>
                <span class="font-medium">{{ formatDuration(selected.total_break_minutes) }}</span>
              </div>
            </div>
            <div v-else class="text-sm text-gray-400">{{ $t('time_tracking.no_records') }}</div>

            <!-- Sol·licitud pendent -->
            <div v-if="selected.pending_request_type === 'edit'"
                 class="bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 flex items-start gap-2">
              <IconAlertTriangle class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" />
              <p class="text-xs text-amber-700">Hi ha una sol·licitud d'edició pendent de revisió per aquest fitxatge.</p>
            </div>
            <div v-else-if="selected.pending_request_type === 'delete'"
                 class="bg-red-50 border border-red-200 rounded-xl px-3 py-2.5 flex items-start gap-2">
              <IconAlertTriangle class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" />
              <p class="text-xs text-red-700">Hi ha una sol·licitud d'eliminació pendent de revisió per aquest fitxatge.</p>
            </div>
          </div>

          <div class="px-5 py-3 border-t flex justify-end">
            <button @click="viewModal = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ $t('common.close') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL EDITAR ────────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="editModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="editModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-medium text-gray-900">{{ $t('time_tracking.request_edit_title') }}</h3>
            <button @click="editModal = false" class="text-gray-400 hover:text-gray-600">
              <IconX class="w-5 h-5" />
            </button>
          </div>

          <div v-if="selected && !editSuccess" class="px-5 py-4 space-y-4">
            <p class="text-xs text-gray-500">
              Fitxatge del <strong class="capitalize">{{ formatDateLong(selected.date) }}</strong>.
              {{ $t('time_tracking.employee_must_approve_edit') }}
            </p>

            <!-- Valors actuals -->
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
              <p>{{ $t('time_tracking.current_entry') }}: <span class="font-mono font-medium text-gray-700">{{ formatTime(selected.clock_in_at) }}</span></p>
              <p>{{ $t('time_tracking.current_exit') }}: <span class="font-mono font-medium text-gray-700 relative inline-flex items-baseline gap-1">
                {{ selected.clock_out_at ? formatClockOut(selected.clock_out_at, selected.clock_in_at) : $t('time_tracking.not_registered') }}
                <sup v-if="dayDiff(selected.clock_out_at, selected.clock_in_at) > 0"
                     class="text-[9px] font-bold text-blue-500">
                  +{{ dayDiff(selected.clock_out_at, selected.clock_in_at) }}
                </sup>
              </span></p>
            </div>

            <!-- Nova entrada -->
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('time_tracking.new_entry_time') }}</label>
              <input v-model="editForm.clock_in_at" type="datetime-local"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <!-- Nova sortida (només si ja existeix) -->
            <div v-if="selected.clock_out_at">
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('time_tracking.new_exit_time') }} <span class="text-gray-400">(opcional)</span></label>
              <input v-model="editForm.clock_out_at" type="datetime-local"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <!-- Motiu -->
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.reason') }} <span class="text-red-500">*</span></label>
              <textarea v-model="editForm.reason" rows="3" maxlength="500" placeholder="Explica el motiu del canvi..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="editErrors.reason ? 'border-red-400' : ''" />
              <p v-if="editErrors.reason" class="text-xs text-red-600 mt-1">{{ editErrors.reason[0] }}</p>
            </div>

            <div v-if="editError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ editError }}</div>
          </div>

          <!-- Èxit -->
          <div v-if="editSuccess" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
              <IconCheck class="w-6 h-6 text-green-600" />
            </div>
            <p class="text-sm font-medium text-gray-900">{{ $t('time_tracking.request_sent') }}</p>
            <p class="text-xs text-gray-500">{{ $t('time_tracking.request_sent_body') }}</p>
          </div>

          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="editModal = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ editSuccess ? $t('common.close') : $t('common.cancel') }}
            </button>
            <button v-if="!editSuccess" @click="submitEdit" :disabled="editSaving"
                    class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
              <svg v-if="editSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
              {{ editSaving ? $t('common.sending') : $t('time_tracking.send_request') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL REVISAR SOL·LICITUD ADMIN ──────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="adminReviewModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="adminReviewModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-medium text-gray-900">{{ $t('edit_requests.title') }}</h3>
            <button @click="adminReviewModal = false" class="text-gray-400 hover:text-gray-600">
              <IconX class="w-5 h-5" />
            </button>
          </div>

          <div v-if="adminReviewReq && !adminReviewDone" class="px-5 py-4 space-y-4">
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-sm text-purple-800">
              <p class="font-medium mb-0.5">{{ adminReviewReq.requestedBy?.name ?? 'L\'administrador' }} {{ adminActionSentence(adminReviewReq.type) }}</p>
              <p class="text-xs text-purple-600 mt-1">{{ formatDateLong(adminReviewReq.original_data?.date) }}</p>
            </div>

            <!-- Detalls del canvi -->
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-600 space-y-1.5">
              <template v-if="adminReviewReq.type === 'edit'">
                <p v-if="adminReviewReq.requested_data?.clock_in_at">
                  {{ $t('edit_requests.entry_label') }}
                  <template v-if="isSameTime(adminReviewReq.original_data?.clock_in_at, adminReviewReq.requested_data?.clock_in_at)">
                    <span class="font-mono">{{ formatTime(adminReviewReq.original_data?.clock_in_at) }}</span>
                  </template>
                  <template v-else>
                    <span class="font-mono line-through text-gray-400">{{ fmtEdit(adminReviewReq.original_data?.clock_in_at, adminReviewReq.requested_data?.clock_in_at) }}</span>
                    → <span class="font-mono font-medium text-blue-700">{{ fmtEdit(adminReviewReq.requested_data?.clock_in_at, adminReviewReq.original_data?.clock_in_at) }}</span>
                  </template>
                </p>
                <p v-if="adminReviewReq.requested_data?.clock_out_at">
                  {{ $t('edit_requests.exit_label') }}
                  <template v-if="isSameTime(adminReviewReq.original_data?.clock_out_at, adminReviewReq.requested_data?.clock_out_at)">
                    <span class="font-mono">{{ formatTime(adminReviewReq.original_data?.clock_out_at) }}</span>
                  </template>
                  <template v-else>
                    <span class="font-mono line-through text-gray-400">{{ fmtEdit(adminReviewReq.original_data?.clock_out_at, adminReviewReq.requested_data?.clock_out_at) }}</span>
                    → <span class="font-mono font-medium text-blue-700">{{ fmtEdit(adminReviewReq.requested_data?.clock_out_at, adminReviewReq.original_data?.clock_out_at) }}</span>
                  </template>
                </p>
              </template>
              <template v-else-if="adminReviewReq.type === 'delete'">
                <p>{{ $t('edit_requests.entry_label') }} <span class="font-mono font-medium">{{ formatTime(adminReviewReq.original_data?.clock_in_at) }}</span></p>
                <p v-if="adminReviewReq.original_data?.clock_out_at">{{ $t('edit_requests.exit_label') }} <span class="font-mono font-medium">{{ formatTime(adminReviewReq.original_data?.clock_out_at) }}</span></p>
              </template>
              <template v-else-if="adminReviewReq.type === 'break_edit'">
                <p v-if="adminReviewReq.requested_data?.break_start_at">
                  {{ $t('time_tracking.break_start') }}:
                  <template v-if="isSameTime(adminReviewReq.original_data?.break_start_at, adminReviewReq.requested_data?.break_start_at)">
                    <span class="font-mono">{{ formatTime(adminReviewReq.original_data?.break_start_at) }}</span>
                  </template>
                  <template v-else>
                    <span class="font-mono line-through text-gray-400">{{ fmtEdit(adminReviewReq.original_data?.break_start_at, adminReviewReq.requested_data?.break_start_at) }}</span>
                    → <span class="font-mono font-medium text-blue-700">{{ fmtEdit(adminReviewReq.requested_data?.break_start_at, adminReviewReq.original_data?.break_start_at) }}</span>
                  </template>
                </p>
                <p v-if="adminReviewReq.requested_data?.break_end_at">
                  {{ $t('time_tracking.break_end') }}:
                  <template v-if="isSameTime(adminReviewReq.original_data?.break_end_at, adminReviewReq.requested_data?.break_end_at)">
                    <span class="font-mono">{{ formatTime(adminReviewReq.original_data?.break_end_at) }}</span>
                  </template>
                  <template v-else>
                    <span class="font-mono line-through text-gray-400">{{ fmtEdit(adminReviewReq.original_data?.break_end_at, adminReviewReq.requested_data?.break_end_at) }}</span>
                    → <span class="font-mono font-medium text-blue-700">{{ fmtEdit(adminReviewReq.requested_data?.break_end_at, adminReviewReq.original_data?.break_end_at) }}</span>
                  </template>
                </p>
              </template>
              <template v-else-if="adminReviewReq.type === 'break_delete'">
                <p>{{ $t('edit_requests.break_start_label') }} <span class="font-mono font-medium">{{ formatTime(adminReviewReq.original_data?.break_start_at) }}</span></p>
                <p>{{ $t('edit_requests.break_end_label') }} <span class="font-mono font-medium">{{ formatTime(adminReviewReq.original_data?.break_end_at) }}</span></p>
              </template>
            </div>

            <div class="text-xs text-gray-500 bg-gray-50 rounded-xl p-3">
              <span class="font-medium text-gray-600">{{ $t('common.reason') }}: </span>{{ adminReviewReq.reason }}
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.note') }} (opcional)</label>
              <textarea v-model="adminReviewNote" rows="2" maxlength="300"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Pots afegir un comentari..." />
            </div>
            <div v-if="adminReviewError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ adminReviewError }}</div>
          </div>

          <div v-if="adminReviewDone" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
              <IconCheck class="w-6 h-6 text-green-600" />
            </div>
            <p class="text-sm font-medium text-gray-900">Resposta enviada</p>
            <p class="text-xs text-gray-500">L'administrador rebrà la teva resposta.</p>
          </div>

          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="adminReviewModal = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ adminReviewDone ? $t('common.close') : $t('common.cancel') }}
            </button>
            <template v-if="!adminReviewDone">
              <button @click="submitAdminDeny" :disabled="adminReviewSaving"
                      class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60">
                {{ adminReviewSaving ? '...' : $t('common.deny') }}
              </button>
              <button @click="submitAdminApprove" :disabled="adminReviewSaving"
                      class="px-4 py-2 text-sm font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg disabled:opacity-60">
                {{ adminReviewSaving ? '...' : $t('common.accept') }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL EDITAR PAUSA ────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="breakEditModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="breakEditModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-medium text-gray-900">{{ $t('time_tracking.request_edit_title') }}</h3>
            <button @click="breakEditModal = false" class="text-gray-400 hover:text-gray-600">
              <IconX class="w-5 h-5" />
            </button>
          </div>

          <div v-if="selectedBreak && !breakEditSuccess" class="px-5 py-4 space-y-4">
            <p class="text-xs text-gray-500">
              Pausa del <strong class="capitalize">{{ selected ? formatDateLong(selected.date) : '' }}</strong>.
              {{ $t('time_tracking.employee_must_approve_edit') }}
            </p>
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
              <p>{{ $t('edit_requests.break_start_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selectedBreak.break_start_at) }}</span></p>
              <p>{{ $t('edit_requests.break_end_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selectedBreak.break_end_at) }}</span></p>
              <p>Durada: <span class="font-medium text-gray-700">{{ selectedBreak.duration_minutes }} min</span></p>
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
              <textarea v-model="breakEditForm.reason" rows="3" maxlength="500" placeholder="Explica el motiu del canvi..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="breakEditErrors.reason ? 'border-red-400' : ''" />
              <p v-if="breakEditErrors.reason" class="text-xs text-red-600 mt-1">{{ breakEditErrors.reason[0] }}</p>
            </div>
            <div v-if="breakEditError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ breakEditError }}</div>
          </div>

          <div v-if="breakEditSuccess" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
              <IconCheck class="w-6 h-6 text-green-600" />
            </div>
            <p class="text-sm font-medium text-gray-900">{{ $t('time_tracking.request_sent') }}</p>
            <p class="text-xs text-gray-500">{{ $t('time_tracking.request_sent_body') }}</p>
          </div>

          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="breakEditModal = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ breakEditSuccess ? $t('common.close') : $t('common.cancel') }}
            </button>
            <button v-if="!breakEditSuccess" @click="submitBreakEdit" :disabled="breakEditSaving || !breakEditForm.reason.trim()"
                    class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
              <svg v-if="breakEditSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
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
            <h3 class="font-medium text-gray-900">{{ $t('time_tracking.request_delete_title') }}</h3>
            <button @click="breakDeleteModal = false" class="text-gray-400 hover:text-gray-600">
              <IconX class="w-5 h-5" />
            </button>
          </div>

          <div v-if="selectedBreak && !breakDeleteSuccess" class="px-5 py-4 space-y-3">
            <p class="text-sm text-gray-600">
              Sol·licites eliminar la pausa del
              <strong class="capitalize text-gray-900">{{ selected ? formatDateLong(selected.date) : '' }}</strong>.
              {{ $t('time_tracking.employee_must_approve_delete') }}
            </p>
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
              <p>{{ $t('edit_requests.break_start_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selectedBreak.break_start_at) }}</span></p>
              <p>{{ $t('edit_requests.break_end_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selectedBreak.break_end_at) }}</span></p>
              <p>Durada: <span class="font-medium text-gray-700">{{ selectedBreak.duration_minutes }} min</span></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.reason') }} <span class="text-red-500">*</span></label>
              <textarea v-model="breakDeleteReason" rows="3" maxlength="500" placeholder="Explica per què vols eliminar aquesta pausa..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-500" />
            </div>
            <div v-if="breakDeleteError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ breakDeleteError }}</div>
          </div>

          <div v-if="breakDeleteSuccess" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
              <IconCheck class="w-6 h-6 text-green-600" />
            </div>
            <p class="text-sm font-medium text-gray-900">{{ $t('time_tracking.request_sent') }}</p>
            <p class="text-xs text-gray-500">{{ $t('time_tracking.request_sent_body') }}</p>
          </div>

          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="breakDeleteModal = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ breakDeleteSuccess ? $t('common.close') : $t('common.cancel') }}
            </button>
            <button v-if="!breakDeleteSuccess" @click="submitBreakDeleteRequest" :disabled="breakDeleteSaving || !breakDeleteReason.trim()"
                    class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
              <svg v-if="breakDeleteSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
              {{ breakDeleteSaving ? $t('common.sending') : $t('time_tracking.send_request') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL ELIMINAR ──────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="deleteModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
           @click.self="deleteModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm">
          <div class="px-5 py-4 border-b flex items-center justify-between">
            <h3 class="font-medium text-gray-900">{{ $t('time_tracking.request_delete_title') }}</h3>
            <button @click="deleteModal = false" class="text-gray-400 hover:text-gray-600">
              <IconX class="w-5 h-5" />
            </button>
          </div>

          <div v-if="selected && !deleteSuccess" class="px-5 py-4 space-y-3">
            <p class="text-sm text-gray-600">
              Sol·licites eliminar el fitxatge del
              <strong class="capitalize text-gray-900">{{ formatDateLong(selected.date) }}</strong>.
              {{ $t('time_tracking.employee_must_approve_delete') }}
            </p>
            <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 space-y-1">
              <p>{{ $t('edit_requests.entry_label') }} <span class="font-mono font-medium text-gray-700">{{ formatTime(selected.clock_in_at) }}</span></p>
              <p v-if="selected.clock_out_at">{{ $t('edit_requests.exit_label') }} <span class="font-mono font-medium text-gray-700 relative inline-flex items-baseline gap-1">
                {{ formatClockOut(selected.clock_out_at, selected.clock_in_at) }}
                <sup v-if="dayDiff(selected.clock_out_at, selected.clock_in_at) > 0"
                     class="text-[9px] font-bold text-blue-500">
                  +{{ dayDiff(selected.clock_out_at, selected.clock_in_at) }}
                </sup>
              </span></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('common.reason') }} <span class="text-red-500">*</span></label>
              <textarea v-model="deleteReason" rows="3" maxlength="500" placeholder="Explica per què vols eliminar aquest fitxatge..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-500" />
            </div>
            <div v-if="deleteError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ deleteError }}</div>
          </div>

          <!-- Èxit -->
          <div v-if="deleteSuccess" class="px-5 py-8 flex flex-col items-center text-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
              <IconCheck class="w-6 h-6 text-green-600" />
            </div>
            <p class="text-sm font-medium text-gray-900">{{ $t('time_tracking.request_sent') }}</p>
            <p class="text-xs text-gray-500">{{ $t('time_tracking.request_sent_body') }}</p>
          </div>

          <div class="px-5 py-3 border-t flex items-center justify-end gap-2">
            <button @click="deleteModal = false"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              {{ deleteSuccess ? $t('common.close') : $t('common.cancel') }}
            </button>
            <button v-if="!deleteSuccess" @click="submitDeleteRequest" :disabled="deleteSaving || !deleteReason.trim()"
                    class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
              <svg v-if="deleteSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
              {{ deleteSaving ? $t('common.sending') : $t('time_tracking.send_request') }}
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
  IconArrowLeft, IconChevronLeft, IconChevronRight,
  IconLogin, IconLogout, IconCoffee, IconClockOff,
  IconEye, IconPencil, IconTrash, IconX, IconCheck, IconAlertTriangle,
} from '@tabler/icons-vue'
import api from '../services/api'

const { t, locale } = useI18n()

const dateLocale = computed(() => ({ ca: 'ca-ES', es: 'es-ES', en: 'en-GB' }[locale.value] || 'ca-ES'))

const MONTHS = computed(() => Array.from({ length: 12 }, (_, i) => t(`months.${i + 1}`)))

const now           = new Date()
const selectedYear  = ref(now.getFullYear())
const selectedMonth = ref(now.getMonth() + 1)

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

// ── Dades ─────────────────────────────────────────────────────────────────────
const entries = ref([])
const loading = ref(false)

async function fetchEntries() {
  loading.value = true
  try {
    const res = await api.get('/v1/time-tracking/my-history-month', {
      params: { year: selectedYear.value, month: selectedMonth.value }
    })
    entries.value = res.data.data || []
  } catch {
    entries.value = []
  } finally {
    loading.value = false
  }
}

watch([selectedYear, selectedMonth], fetchEntries)
onMounted(fetchEntries)

// ── Totals ────────────────────────────────────────────────────────────────────
const totalEffective = computed(() => entries.value.reduce((s, e) => s + (e.effective_minutes || 0), 0))

// ── Helpers de dades ──────────────────────────────────────────────────────────
function completedBreaks(entry) {
  return (entry.breaks || []).filter(b => b.break_end_at)
}

// ── Modal VEURE ───────────────────────────────────────────────────────────────
const viewModal = ref(false)
const selected  = ref(null)

function openView(entry) {
  selected.value = entry
  viewModal.value = true
}

// ── Modal EDITAR ──────────────────────────────────────────────────────────────
const editModal   = ref(false)
const editSaving  = ref(false)
const editSuccess = ref(false)
const editError   = ref('')
const editErrors  = ref({})

const editForm = ref({ clock_in_at: '', clock_out_at: '', reason: '' })

function openEdit(entry) {
  selected.value     = entry
  editSuccess.value  = false
  editError.value    = ''
  editErrors.value   = {}
  editForm.value = {
    clock_in_at:  toDatetimeLocal(entry.clock_in_at),
    clock_out_at: toDatetimeLocal(entry.clock_out_at),
    reason:       '',
  }
  editModal.value = true
}

async function submitEdit() {
  editError.value  = ''
  editErrors.value = {}
  editSaving.value = true

  const payload = { reason: editForm.value.reason }
  if (editForm.value.clock_in_at)  payload.clock_in_at  = localToUtc(editForm.value.clock_in_at)
  if (editForm.value.clock_out_at) payload.clock_out_at = localToUtc(editForm.value.clock_out_at)

  try {
    await api.post(`/v1/time-tracking/entries/${selected.value.id}/edit-request`, payload)
    editSuccess.value = true
    // marca l'entrada com a pendent
    const idx = entries.value.findIndex(e => e.id === selected.value.id)
    if (idx !== -1) entries.value[idx].has_pending_edit_request = true
  } catch (err) {
    if (err?.response?.status === 422) {
      const data = err.response.data
      editErrors.value = data.errors || {}
      editError.value  = data.message || ''
    } else {
      editError.value = err?.response?.data?.message || t('common.error')
    }
  } finally {
    editSaving.value = false
  }
}

// ── Modal REVISIÓ SOL·LICITUD ADMIN ──────────────────────────────────────────
const adminReviewModal  = ref(false)
const adminReviewReq    = ref(null)
const adminReviewNote   = ref('')
const adminReviewSaving = ref(false)
const adminReviewDone   = ref(false)
const adminReviewError  = ref('')
let   adminReviewEntry  = null
let   adminReviewBreak  = null

async function openAdminReview(entry) {
  adminReviewEntry  = entry
  adminReviewBreak  = null
  adminReviewDone.value  = false
  adminReviewNote.value  = ''
  adminReviewError.value = ''
  adminReviewReq.value   = null
  adminReviewModal.value = true
  await loadAdminReq(entry.pending_admin_request.id)
}

async function openAdminBreakReview(entry, brk) {
  adminReviewEntry  = entry
  adminReviewBreak  = brk
  adminReviewDone.value  = false
  adminReviewNote.value  = ''
  adminReviewError.value = ''
  adminReviewReq.value   = null
  adminReviewModal.value = true
  await loadAdminReq(brk.pending_admin_request.id)
}

async function loadAdminReq(id) {
  try {
    const res = await api.get(`/v1/time-entry-edit-requests/my-incoming`)
    const all = res.data.data || []
    adminReviewReq.value = all.find(r => r.id === id) || all[0] || null
  } catch {}
}

async function submitAdminApprove() {
  await submitAdminDecision('approve')
}
async function submitAdminDeny() {
  await submitAdminDecision('deny')
}
async function submitAdminDecision(action) {
  if (!adminReviewReq.value) return
  adminReviewError.value  = ''
  adminReviewSaving.value = true
  try {
    await api.post(`/v1/time-entry-edit-requests/${adminReviewReq.value.id}/employee-${action}`, {
      note: adminReviewNote.value || null,
    })
    adminReviewDone.value = true
    // Elimina el pendent localment
    if (adminReviewBreak) {
      const brk = entries.value.find(e => e.id === adminReviewEntry.id)?.breaks?.find(b => b.id === adminReviewBreak.id)
      if (brk) brk.pending_admin_request = null
    } else {
      const e = entries.value.find(e => e.id === adminReviewEntry.id)
      if (e) e.pending_admin_request = null
    }
  } catch (err) {
    adminReviewError.value = err?.response?.data?.message || t('common.error')
  } finally {
    adminReviewSaving.value = false
  }
}

function adminActionLabel(type) {
  return {
    edit:         t('common.edit').toLowerCase(),
    delete:       t('common.delete').toLowerCase(),
    break_edit:   `${t('common.edit').toLowerCase()} ${t('time_tracking.break').toLowerCase()}`,
    break_delete: `${t('common.delete').toLowerCase()} ${t('time_tracking.break').toLowerCase()}`,
  }[type] || type
}
function adminActionSentence(type) {
  return {
    edit:         t('time_tracking.admin_wants_edit'),
    delete:       t('time_tracking.admin_wants_delete'),
    break_edit:   t('time_tracking.admin_wants_edit'),
    break_delete: t('time_tracking.admin_wants_delete'),
  }[type] || ''
}

// ── Modal EDITAR PAUSA ────────────────────────────────────────────────────────
const breakEditModal   = ref(false)
const breakEditSaving  = ref(false)
const breakEditSuccess = ref(false)
const breakEditError   = ref('')
const breakEditErrors  = ref({})
const selectedBreak    = ref(null)

const breakEditForm = ref({ break_start_at: '', break_end_at: '', reason: '' })

function openBreakEdit(entry, brk) {
  selected.value       = entry
  selectedBreak.value  = brk
  breakEditSuccess.value = false
  breakEditError.value   = ''
  breakEditErrors.value  = {}
  breakEditForm.value = {
    break_start_at: toDatetimeLocal(brk.break_start_at),
    break_end_at:   toDatetimeLocal(brk.break_end_at),
    reason:         '',
  }
  breakEditModal.value = true
}

async function submitBreakEdit() {
  breakEditError.value  = ''
  breakEditErrors.value = {}
  breakEditSaving.value = true

  const payload = { reason: breakEditForm.value.reason }
  if (breakEditForm.value.break_start_at) payload.break_start_at = localToUtc(breakEditForm.value.break_start_at)
  if (breakEditForm.value.break_end_at)   payload.break_end_at   = localToUtc(breakEditForm.value.break_end_at)

  try {
    await api.post(`/v1/time-tracking/breaks/${selectedBreak.value.id}/edit-request`, payload)
    breakEditSuccess.value = true
    // marca la pausa com a pendent
    const entry = entries.value.find(e => e.id === selected.value.id)
    const brk   = entry?.breaks?.find(b => b.id === selectedBreak.value.id)
    if (brk) brk.pending_request_type = 'break_edit'
  } catch (err) {
    if (err?.response?.status === 422) {
      const data = err.response.data
      breakEditErrors.value = data.errors || {}
      breakEditError.value  = data.message || ''
    } else {
      breakEditError.value = err?.response?.data?.message || t('common.error')
    }
  } finally {
    breakEditSaving.value = false
  }
}

// ── Modal ELIMINAR PAUSA ──────────────────────────────────────────────────────
const breakDeleteModal   = ref(false)
const breakDeleteSaving  = ref(false)
const breakDeleteSuccess = ref(false)
const breakDeleteError   = ref('')
const breakDeleteReason  = ref('')

function openBreakDelete(entry, brk) {
  selected.value          = entry
  selectedBreak.value     = brk
  breakDeleteSuccess.value = false
  breakDeleteError.value   = ''
  breakDeleteReason.value  = ''
  breakDeleteModal.value   = true
}

async function submitBreakDeleteRequest() {
  breakDeleteError.value  = ''
  breakDeleteSaving.value = true
  try {
    await api.post(`/v1/time-tracking/breaks/${selectedBreak.value.id}/delete-request`, {
      reason: breakDeleteReason.value,
    })
    breakDeleteSuccess.value = true
    const entry = entries.value.find(e => e.id === selected.value.id)
    const brk   = entry?.breaks?.find(b => b.id === selectedBreak.value.id)
    if (brk) brk.pending_request_type = 'break_delete'
  } catch (err) {
    breakDeleteError.value = err?.response?.data?.message || t('common.error')
  } finally {
    breakDeleteSaving.value = false
  }
}

// ── Modal ELIMINAR ────────────────────────────────────────────────────────────
const deleteModal   = ref(false)
const deleteSaving  = ref(false)
const deleteSuccess = ref(false)
const deleteError   = ref('')
const deleteReason  = ref('')

function openDelete(entry) {
  selected.value      = entry
  deleteSuccess.value = false
  deleteError.value   = ''
  deleteReason.value  = ''
  deleteModal.value   = true
}

async function submitDeleteRequest() {
  deleteError.value  = ''
  deleteSaving.value = true
  try {
    await api.post(`/v1/time-tracking/entries/${selected.value.id}/delete-request`, {
      reason: deleteReason.value,
    })
    deleteSuccess.value = true
    const idx = entries.value.findIndex(e => e.id === selected.value.id)
    if (idx !== -1) entries.value[idx].pending_request_type = 'delete'
  } catch (err) {
    deleteError.value = err?.response?.data?.message || t('common.error')
  } finally {
    deleteSaving.value = false
  }
}

// ── Formats ───────────────────────────────────────────────────────────────────
function formatTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleTimeString(dateLocale.value, { hour: '2-digit', minute: '2-digit' })
}

function formatClockOut(outIso, _inIso) {
  if (!outIso) return '—'
  const out = new Date(outIso)
  return out.toLocaleTimeString(dateLocale.value, { hour: '2-digit', minute: '2-digit' })
}

function isSameTime(iso1, iso2) {
  if (!iso1 || !iso2) return false
  return new Date(iso1).getTime() === new Date(iso2).getTime()
}
function fmtEdit(iso, peerIso) {
  if (!iso) return '—'
  const d = new Date(iso)
  const time = d.toLocaleTimeString(dateLocale.value, { hour: '2-digit', minute: '2-digit' })
  if (!peerIso) return time
  const p = new Date(peerIso)
  const sameDay = d.getFullYear() === p.getFullYear() && d.getMonth() === p.getMonth() && d.getDate() === p.getDate()
  if (sameDay) return time
  return d.toLocaleDateString(dateLocale.value, { weekday: 'short', day: 'numeric', month: 'short' }) + ' ' + time
}

function dayDiff(outIso, inIso) {
  if (!outIso || !inIso) return 0
  const out = new Date(outIso)
  const inn = new Date(inIso)
  const outDay = new Date(out.getFullYear(), out.getMonth(), out.getDate())
  const inDay  = new Date(inn.getFullYear(), inn.getMonth(), inn.getDate())
  return Math.round((outDay - inDay) / 86400000)
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
  const h = Math.floor(minutes / 60)
  const m = minutes % 60
  return h > 0 ? `${h}h${m > 0 ? ' ' + m + 'min' : ''}` : `${m} min`
}

function toDatetimeLocal(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  const pad = n => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

// Converteix el valor d'un input datetime-local (hora local del navegador)
// a un string ISO UTC (YYYY-MM-DDTHH:mm) per enviar al backend.
// new Date('2026-06-30T09:42') → interpreta com hora local → .toISOString() → UTC
function localToUtc(datetimeLocalStr) {
  if (!datetimeLocalStr) return null
  return new Date(datetimeLocalStr).toISOString().slice(0, 16)
}

function statusBadge(s) {
  return {
    clocked_in:  'bg-green-50 text-green-700',
    on_break:    'bg-amber-50 text-amber-700',
    clocked_out: 'bg-gray-100 text-gray-500',
  }[s] || 'bg-gray-100 text-gray-500'
}

function statusLabel(s) {
  return { clocked_in: t('time_tracking.working'), on_break: t('time_tracking.on_break'), clocked_out: t('time_tracking.completed') }[s] || '—'
}
</script>
