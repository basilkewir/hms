<template>
    <DashboardLayout title="Locations" :user="user" :navigation="navigation">
        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-6"
             :style="{ backgroundColor: themeColors.card }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1" :style="{ color: themeColors.textPrimary }">Locations</h1>
                    <p class="text-sm" :style="{ color: themeColors.textSecondary }">
                        Manage hotel locations used for purchase deliveries and stock tracking.
                    </p>
                </div>
                <button @click="showAddModal = true"
                    class="px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 text-white transition-colors"
                    :style="{ backgroundColor: themeColors.primary }">
                    <PlusIcon class="h-4 w-4" />
                    Add Location
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="shadow rounded-lg overflow-hidden" :style="{ backgroundColor: themeColors.card }">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr :style="{ backgroundColor: themeColors.background }">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :style="{ color: themeColors.textSecondary }">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!locations || locations.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center">
                                <MapPinIcon class="mx-auto h-12 w-12 mb-3" :style="{ color: themeColors.textSecondary }" />
                                <p class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">No locations yet</p>
                                <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Add your first location to get started.</p>
                            </td>
                        </tr>
                        <tr v-for="loc in locations" :key="loc.id"
                            class="border-t transition-colors"
                            :style="{ borderColor: themeColors.border }"
                            @mouseenter="$event.currentTarget.style.backgroundColor = themeColors.hover"
                            @mouseleave="$event.currentTarget.style.backgroundColor = 'transparent'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium" :style="{ color: themeColors.textPrimary }">{{ loc.name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                      :class="typeClass(loc.type)">
                                    {{ loc.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm" :style="{ color: themeColors.textSecondary }">{{ loc.description || '—' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="loc.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ loc.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-3">
                                    <button @click="editLocation(loc)"
                                            class="transition-colors"
                                            :style="{ color: themeColors.primary }">Edit</button>
                                    <button @click="deleteLocation(loc)"
                                            class="transition-colors"
                                            :style="{ color: themeColors.danger }">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <DialogModal :show="showAddModal || showEditModal" @close="closeModal" max-width="lg">
            <template #title>
                <span :style="{ color: themeColors.textPrimary }">
                    {{ editingLocation ? 'Edit Location' : 'Add New Location' }}
                </span>
            </template>
            <template #content>
                <div class="space-y-4" :style="{ backgroundColor: themeColors.card }">
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Name *</label>
                        <input v-model="form.name" type="text"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="e.g. Main Kitchen" />
                        <p v-if="errors.name" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Type *</label>
                        <select v-model="form.type"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }">
                            <option value="">Select type</option>
                            <option v-for="t in locationTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                        <p v-if="errors.type" class="mt-1 text-xs" :style="{ color: themeColors.danger }">{{ errors.type }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description</label>
                        <textarea v-model="form.description" rows="2"
                            class="w-full px-3 py-2 rounded-lg border focus:outline-none"
                            :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary, borderWidth: '1px', borderStyle: 'solid' }"
                            placeholder="Optional description"></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <input v-model="form.is_active" type="checkbox" id="loc-active"
                            class="rounded cursor-pointer" :style="{ accentColor: themeColors.primary }" />
                        <label for="loc-active" class="text-sm cursor-pointer" :style="{ color: themeColors.textSecondary }">Active</label>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <button @click="closeModal" type="button"
                        class="px-4 py-2 rounded-lg border text-sm font-medium"
                        :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: 'transparent' }">
                        Cancel
                    </button>
                    <button @click="saveLocation" type="button" :disabled="processing"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-60"
                        :style="{ backgroundColor: editingLocation ? themeColors.success : themeColors.primary }">
                        {{ processing ? 'Saving...' : (editingLocation ? 'Update Location' : 'Create Location') }}
                    </button>
                </div>
            </template>
        </DialogModal>
    </DashboardLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import { PlusIcon, MapPinIcon } from '@heroicons/vue/24/outline'
import { notify } from '@/Composables/useNotification.js'
import { useTheme } from '@/Composables/useTheme.js'

const { loadTheme } = useTheme()
loadTheme()

const themeColors = computed(() => ({
    background:    'var(--kotel-background)',
    card:          'var(--kotel-card)',
    border:        'var(--kotel-border)',
    textPrimary:   'var(--kotel-text-primary)',
    textSecondary: 'var(--kotel-text-secondary)',
    textTertiary:  'var(--kotel-text-tertiary)',
    primary:       'var(--kotel-primary)',
    primaryHover:  'var(--kotel-primary-hover)',
    success:       'var(--kotel-success)',
    danger:        'var(--kotel-danger)',
    hover:         'rgba(255,255,255,0.05)',
}))

const props = defineProps({
    user:       Object,
    navigation: Array,
    locations:  { type: Array, default: () => [] },
})

const locationTypes = [
    { value: 'warehouse',  label: 'Warehouse'  },
    { value: 'kitchen',    label: 'Kitchen'    },
    { value: 'restaurant', label: 'Restaurant' },
    { value: 'bar',        label: 'Bar'        },
    { value: 'frontdesk',  label: 'Front Desk' },
    { value: 'other',      label: 'Other'      },
]

const typeClass = (type) => {
    const map = {
        warehouse:  'bg-blue-100 text-blue-800',
        kitchen:    'bg-orange-100 text-orange-800',
        restaurant: 'bg-yellow-100 text-yellow-800',
        bar:        'bg-purple-100 text-purple-800',
        frontdesk:  'bg-teal-100 text-teal-800',
        other:      'bg-gray-100 text-gray-800',
    }
    return map[type] || 'bg-gray-100 text-gray-800'
}

const showAddModal  = ref(false)
const showEditModal = ref(false)
const editingLocation = ref(null)
const processing = ref(false)
const errors = ref({})

const form = reactive({
    name:        '',
    type:        '',
    description: '',
    is_active:   true,
})

const editLocation = (loc) => {
    editingLocation.value = loc
    form.name        = loc.name
    form.type        = loc.type
    form.description = loc.description || ''
    form.is_active   = loc.is_active
    showEditModal.value = true
}

const deleteLocation = (loc) => {
    if (!confirm(`Delete "${loc.name}"? This cannot be undone.`)) return
    router.delete(route('admin.locations.destroy', loc.id), {
        preserveScroll: true,
        onSuccess: () => notify.success('Location deleted.'),
        onError:   () => notify.error('Failed to delete location.'),
    })
}

const saveLocation = () => {
    if (!form.name || !form.type) {
        errors.value = {
            name: !form.name ? 'Name is required.' : '',
            type: !form.type ? 'Type is required.' : '',
        }
        return
    }
    processing.value = true
    errors.value = {}

    if (editingLocation.value) {
        router.put(route('admin.locations.update', editingLocation.value.id), { ...form }, {
            preserveScroll: true,
            onSuccess: () => { notify.success('Location updated.'); closeModal() },
            onError:   (err) => { errors.value = err; notify.error('Failed to update.') },
            onFinish:  () => { processing.value = false },
        })
    } else {
        router.post(route('admin.locations.store'), { ...form }, {
            preserveScroll: true,
            onSuccess: () => { notify.success('Location created.'); closeModal() },
            onError:   (err) => { errors.value = err; notify.error('Failed to create.') },
            onFinish:  () => { processing.value = false },
        })
    }
}

const closeModal = () => {
    showAddModal.value    = false
    showEditModal.value   = false
    editingLocation.value = null
    form.name        = ''
    form.type        = ''
    form.description = ''
    form.is_active   = true
    errors.value     = {}
}
</script>
