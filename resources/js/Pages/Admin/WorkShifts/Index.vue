<template>
  <DashboardLayout :user="user" :navigation="navigation">
    <div class="p-6 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-kotel-yellow">Work Shifts</h1>
          <p class="text-kotel-sky-blue/70 text-sm mt-1">Define and manage employee shift templates</p>
        </div>
        <button @click="openCreate" class="flex items-center gap-2 bg-kotel-yellow text-kotel-black font-semibold px-4 py-2 rounded-lg hover:bg-kotel-yellow/90 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          New Shift
        </button>
      </div>

      <!-- Flash message -->
      <div v-if="$page.props.flash?.success" class="bg-emerald-900/40 border border-emerald-400/40 text-emerald-300 rounded-lg px-4 py-3 text-sm">
        {{ $page.props.flash.success }}
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-kotel-dark border border-kotel-yellow/20 rounded-xl p-4">
          <p class="text-kotel-sky-blue/70 text-xs uppercase tracking-wide">Total Shifts</p>
          <p class="text-3xl font-bold text-white mt-1">{{ shiftStats.total_shifts }}</p>
        </div>
        <div class="bg-kotel-dark border border-emerald-400/20 rounded-xl p-4">
          <p class="text-kotel-sky-blue/70 text-xs uppercase tracking-wide">Active</p>
          <p class="text-3xl font-bold text-emerald-400 mt-1">{{ shiftStats.active_shifts }}</p>
        </div>
        <div class="bg-kotel-dark border border-gray-400/20 rounded-xl p-4">
          <p class="text-kotel-sky-blue/70 text-xs uppercase tracking-wide">Inactive</p>
          <p class="text-3xl font-bold text-gray-400 mt-1">{{ shiftStats.completed_shifts }}</p>
        </div>
        <div class="bg-kotel-dark border border-kotel-sky-blue/20 rounded-xl p-4">
          <p class="text-kotel-sky-blue/70 text-xs uppercase tracking-wide">Created Today</p>
          <p class="text-3xl font-bold text-kotel-sky-blue mt-1">{{ shiftStats.today_shifts }}</p>
        </div>
      </div>

      <!-- Shifts Table -->
      <div class="bg-kotel-dark border border-kotel-yellow/20 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-kotel-yellow/20 flex items-center justify-between">
          <h2 class="text-white font-semibold">All Shifts</h2>
          <input v-model="search" placeholder="Search shifts…"
                 class="bg-kotel-black/50 border border-kotel-yellow/20 rounded-lg px-3 py-1.5 text-white text-sm placeholder-kotel-sky-blue/40 focus:outline-none focus:border-kotel-yellow/50 w-52"/>
        </div>

        <div v-if="filtered.length === 0" class="py-16 text-center text-kotel-sky-blue/50">
          No shifts found.
        </div>

        <table v-else class="w-full text-sm">
          <thead>
            <tr class="border-b border-kotel-yellow/10 text-kotel-sky-blue/70 text-xs uppercase">
              <th class="px-5 py-3 text-left">Shift Name</th>
              <th class="px-5 py-3 text-left">Start</th>
              <th class="px-5 py-3 text-left">End</th>
              <th class="px-5 py-3 text-left">Hours</th>
              <th class="px-5 py-3 text-left">Break</th>
              <th class="px-5 py-3 text-left">Type</th>
              <th class="px-5 py-3 text-left">Staff</th>
              <th class="px-5 py-3 text-left">Status</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="shift in filtered" :key="shift.id"
                class="border-b border-kotel-yellow/10 hover:bg-kotel-yellow/5 transition">
              <td class="px-5 py-3 text-white font-medium">{{ shift.shift_name }}</td>
              <td class="px-5 py-3 text-kotel-sky-blue">{{ fmtTime(shift.start_time) }}</td>
              <td class="px-5 py-3 text-kotel-sky-blue">{{ fmtTime(shift.end_time) }}</td>
              <td class="px-5 py-3 text-white">{{ shift.hours ?? '—' }}h</td>
              <td class="px-5 py-3 text-white">{{ shift.break_minutes ?? 0 }}min</td>
              <td class="px-5 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                      :class="shift.is_overnight ? 'bg-indigo-900/60 text-indigo-300 border border-indigo-400/40' : 'bg-kotel-yellow/10 text-kotel-yellow border border-kotel-yellow/30'">
                  {{ shift.is_overnight ? '🌙 Night' : '☀️ Day' }}
                </span>
              </td>
              <td class="px-5 py-3 text-white">{{ shift.employees_count }}</td>
              <td class="px-5 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                      :class="shift.is_active ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-400/40' : 'bg-gray-800 text-gray-400 border border-gray-600/40'">
                  {{ shift.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-5 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openAssign(shift)" title="Assign Staff"
                          class="p-1.5 rounded-lg hover:bg-kotel-yellow/20 text-kotel-sky-blue hover:text-kotel-yellow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                  </button>
                  <button @click="openEdit(shift)" title="Edit"
                          class="p-1.5 rounded-lg hover:bg-kotel-yellow/20 text-kotel-sky-blue hover:text-kotel-yellow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </button>
                  <button @click="confirmDelete(shift)" title="Delete"
                          class="p-1.5 rounded-lg hover:bg-red-900/40 text-kotel-sky-blue hover:text-red-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Create / Edit Modal -->
      <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @click.self="showForm = false">
        <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-xl shadow-2xl w-full max-w-lg">
          <div class="px-6 py-4 border-b border-kotel-yellow/20 flex items-center justify-between">
            <h3 class="text-kotel-yellow font-semibold text-lg">{{ editing ? 'Edit Shift' : 'New Shift' }}</h3>
            <button @click="showForm = false" class="text-white/60 hover:text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitForm" class="p-6 space-y-4">
            <div>
              <label class="block text-kotel-sky-blue/80 text-sm mb-1">Shift Name *</label>
              <input v-model="form.name" required placeholder="e.g. Morning Shift"
                     class="w-full bg-kotel-black/60 border border-kotel-yellow/20 rounded-lg px-3 py-2 text-white placeholder-kotel-sky-blue/30 focus:outline-none focus:border-kotel-yellow/50"/>
              <p v-if="errors.name" class="text-red-400 text-xs mt-1">{{ errors.name }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-kotel-sky-blue/80 text-sm mb-1">Start Time *</label>
                <input v-model="form.start_time" type="time" required
                       class="w-full bg-kotel-black/60 border border-kotel-yellow/20 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-kotel-yellow/50"/>
                <p v-if="errors.start_time" class="text-red-400 text-xs mt-1">{{ errors.start_time }}</p>
              </div>
              <div>
                <label class="block text-kotel-sky-blue/80 text-sm mb-1">End Time *</label>
                <input v-model="form.end_time" type="time" required
                       class="w-full bg-kotel-black/60 border border-kotel-yellow/20 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-kotel-yellow/50"/>
                <p v-if="errors.end_time" class="text-red-400 text-xs mt-1">{{ errors.end_time }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-kotel-sky-blue/80 text-sm mb-1">Total Hours</label>
                <input v-model.number="form.hours" type="number" step="0.5" min="0" max="24" placeholder="8"
                       class="w-full bg-kotel-black/60 border border-kotel-yellow/20 rounded-lg px-3 py-2 text-white placeholder-kotel-sky-blue/30 focus:outline-none focus:border-kotel-yellow/50"/>
              </div>
              <div>
                <label class="block text-kotel-sky-blue/80 text-sm mb-1">Break (minutes)</label>
                <input v-model.number="form.break_minutes" type="number" min="0" max="120" placeholder="30"
                       class="w-full bg-kotel-black/60 border border-kotel-yellow/20 rounded-lg px-3 py-2 text-white placeholder-kotel-sky-blue/30 focus:outline-none focus:border-kotel-yellow/50"/>
              </div>
            </div>
            <div class="flex items-center gap-6">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_overnight" type="checkbox" class="rounded border-kotel-yellow/30 bg-kotel-black/50"/>
                <span class="text-kotel-sky-blue/80 text-sm">Overnight shift</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_active" type="checkbox" class="rounded border-kotel-yellow/30 bg-kotel-black/50"/>
                <span class="text-kotel-sky-blue/80 text-sm">Active</span>
              </label>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="submit" :disabled="processing"
                      class="flex-1 bg-kotel-yellow text-kotel-black font-semibold py-2 rounded-lg hover:bg-kotel-yellow/90 transition disabled:opacity-50">
                {{ processing ? 'Saving…' : (editing ? 'Update Shift' : 'Create Shift') }}
              </button>
              <button type="button" @click="showForm = false"
                      class="px-5 py-2 border border-kotel-yellow/30 text-kotel-sky-blue rounded-lg hover:bg-kotel-yellow/10 transition">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Assign Staff Modal -->
      <div v-if="showAssign" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @click.self="showAssign = false">
        <div class="bg-kotel-dark border border-kotel-yellow/30 rounded-xl shadow-2xl w-full max-w-lg">
          <div class="px-6 py-4 border-b border-kotel-yellow/20 flex items-center justify-between">
            <h3 class="text-kotel-yellow font-semibold text-lg">Assign Staff — {{ assignTarget?.shift_name }}</h3>
            <button @click="showAssign = false" class="text-white/60 hover:text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Assigned employees list -->
          <div v-if="assignTarget?.employees?.length" class="px-6 pt-4">
            <p class="text-kotel-sky-blue/70 text-xs uppercase tracking-wide mb-2">Currently Assigned</p>
            <div class="space-y-1 mb-4">
              <div v-for="emp in assignTarget.employees" :key="emp.id"
                   class="flex items-center justify-between bg-kotel-black/40 rounded-lg px-3 py-2">
                <span class="text-white text-sm">{{ emp.user?.name ?? '—' }}</span>
                <button @click="unassign(emp.id)"
                        class="text-red-400 hover:text-red-300 text-xs border border-red-400/30 px-2 py-0.5 rounded transition">
                  Remove
                </button>
              </div>
            </div>
          </div>

          <form @submit.prevent="submitAssign" class="p-6 space-y-4">
            <div>
              <label class="block text-kotel-sky-blue/80 text-sm mb-1">Employee *</label>
              <select v-model="assignForm.user_id" required
                      class="w-full bg-kotel-black/60 border border-kotel-yellow/20 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-kotel-yellow/50">
                <option value="">Select employee…</option>
                <option v-for="u in staffUsers" :key="u.id" :value="u.id">
                  {{ u.first_name }} {{ u.last_name }} ({{ u.email }})
                </option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-kotel-sky-blue/80 text-sm mb-1">Effective From *</label>
                <input v-model="assignForm.effective_date" type="date" required
                       class="w-full bg-kotel-black/60 border border-kotel-yellow/20 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-kotel-yellow/50"/>
              </div>
              <div>
                <label class="block text-kotel-sky-blue/80 text-sm mb-1">Until (optional)</label>
                <input v-model="assignForm.end_date" type="date"
                       class="w-full bg-kotel-black/60 border border-kotel-yellow/20 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-kotel-yellow/50"/>
              </div>
            </div>
            <div>
              <label class="block text-kotel-sky-blue/80 text-sm mb-2">Days of Week *</label>
              <div class="flex flex-wrap gap-2">
                <label v-for="day in days" :key="day.value"
                       class="flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border text-sm transition"
                       :class="assignForm.days_of_week.includes(day.value)
                         ? 'bg-kotel-yellow/20 border-kotel-yellow/60 text-kotel-yellow'
                         : 'bg-kotel-black/40 border-kotel-yellow/20 text-kotel-sky-blue/80 hover:border-kotel-yellow/40'">
                  <input type="checkbox" :value="day.value" v-model="assignForm.days_of_week" class="sr-only"/>
                  {{ day.label }}
                </label>
              </div>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="submit" :disabled="processing"
                      class="flex-1 bg-kotel-yellow text-kotel-black font-semibold py-2 rounded-lg hover:bg-kotel-yellow/90 transition disabled:opacity-50">
                {{ processing ? 'Assigning…' : 'Assign Employee' }}
              </button>
              <button type="button" @click="showAssign = false"
                      class="px-5 py-2 border border-kotel-yellow/30 text-kotel-sky-blue rounded-lg hover:bg-kotel-yellow/10 transition">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete Confirm -->
      <div v-if="deleteTarget" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @click.self="deleteTarget = null">
        <div class="bg-kotel-dark border border-red-400/30 rounded-xl shadow-2xl w-full max-w-sm p-6 text-center">
          <svg class="w-10 h-10 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <h3 class="text-white font-semibold text-lg mb-1">Delete Shift?</h3>
          <p class="text-kotel-sky-blue/70 text-sm mb-5">
            "<strong class="text-white">{{ deleteTarget.shift_name }}</strong>" will be permanently deleted.
          </p>
          <div class="flex gap-3">
            <button @click="doDelete" :disabled="processing"
                    class="flex-1 bg-red-600 text-white font-semibold py-2 rounded-lg hover:bg-red-500 transition disabled:opacity-50">
              {{ processing ? 'Deleting…' : 'Delete' }}
            </button>
            <button @click="deleteTarget = null"
                    class="flex-1 border border-kotel-yellow/30 text-kotel-sky-blue py-2 rounded-lg hover:bg-kotel-yellow/10 transition">
              Cancel
            </button>
          </div>
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
  user:        Object,
  navigation:  Array,
  workShifts:  Array,
  shiftStats:  Object,
  staffUsers:  Array,
})

const search      = ref('')
const showForm    = ref(false)
const showAssign  = ref(false)
const editing     = ref(null)
const deleteTarget = ref(null)
const assignTarget = ref(null)
const processing  = ref(false)

const days = [
  { label: 'Mon', value: 1 },
  { label: 'Tue', value: 2 },
  { label: 'Wed', value: 3 },
  { label: 'Thu', value: 4 },
  { label: 'Fri', value: 5 },
  { label: 'Sat', value: 6 },
  { label: 'Sun', value: 0 },
]

const emptyForm = () => ({ name: '', start_time: '', end_time: '', hours: '', break_minutes: 30, is_overnight: false, is_active: true })
const form = ref(emptyForm())
const errors = ref({})

const emptyAssign = () => ({ user_id: '', effective_date: '', end_date: '', days_of_week: [] })
const assignForm = ref(emptyAssign())

const filtered = computed(() => {
  if (!search.value) return props.workShifts ?? []
  const q = search.value.toLowerCase()
  return (props.workShifts ?? []).filter(s => s.shift_name?.toLowerCase().includes(q))
})

function fmtTime(t) {
  if (!t) return '—'
  try {
    const [h, m] = t.split('T').pop().split(':')
    const d = new Date()
    d.setHours(+h, +m)
    return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
  } catch { return t }
}

function openCreate() {
  editing.value = null
  form.value = emptyForm()
  errors.value = {}
  showForm.value = true
}

function openEdit(shift) {
  editing.value = shift
  form.value = {
    name:          shift.shift_name,
    start_time:    shift.start_time ? shift.start_time.split('T').pop().substring(0, 5) : '',
    end_time:      shift.end_time   ? shift.end_time.split('T').pop().substring(0, 5)   : '',
    hours:         shift.hours ?? '',
    break_minutes: shift.break_minutes ?? 30,
    is_overnight:  !!shift.is_overnight,
    is_active:     !!shift.is_active,
  }
  errors.value = {}
  showForm.value = true
}

function submitForm() {
  processing.value = true
  errors.value = {}
  const routeName = editing.value ? route('admin.work-shifts.update', editing.value.id) : route('admin.work-shifts.store')
  const method    = editing.value ? 'put' : 'post'
  router[method](routeName, form.value, {
    onSuccess: () => { showForm.value = false; processing.value = false },
    onError:   (e) => { errors.value = e; processing.value = false },
  })
}

function confirmDelete(shift) { deleteTarget.value = shift }

function doDelete() {
  processing.value = true
  router.delete(route('admin.work-shifts.destroy', deleteTarget.value.id), {
    onSuccess: () => { deleteTarget.value = null; processing.value = false },
    onFinish:  () => { processing.value = false },
  })
}

function openAssign(shift) {
  assignTarget.value = shift
  assignForm.value = emptyAssign()
  showAssign.value = true
}

function submitAssign() {
  processing.value = true
  router.post(route('admin.work-shifts.assign', assignTarget.value.id), assignForm.value, {
    onSuccess: () => { showAssign.value = false; processing.value = false },
    onError:   () => { processing.value = false },
  })
}

function unassign(employeeShiftId) {
  if (!confirm('Remove this employee from the shift?')) return
  router.delete(route('admin.work-shifts.unassign', { workShift: assignTarget.value.id, employeeShift: employeeShiftId }), {
    onSuccess: () => { showAssign.value = false },
  })
}
</script>
