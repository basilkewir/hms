<template>
    <DashboardLayout title="Room Amenities" :user="user" :navigation="navigation">

        <!-- Header -->
        <div class="shadow rounded-lg p-6 mb-8 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">Room Amenities</h1>
                    <p class="mt-2" :style="{ color: themeColors.textSecondary }">Manage amenities and features for room types.</p>
                </div>
                <button @click="showModal = true"
                        class="px-4 py-2 rounded-md hover:opacity-90 transition-opacity flex items-center"
                        :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                    <PlusIcon class="h-4 w-4 mr-2 inline" />
                    Add Amenity
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <SparklesIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.primary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Total Amenities</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ amenities.length }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.success }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Active</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ activeCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg shadow p-6 border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <div class="flex items-center">
                    <XCircleIcon class="h-8 w-8 mr-4" :style="{ color: themeColors.textSecondary }" />
                    <div>
                        <p class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Inactive</p>
                        <p class="text-2xl font-bold" :style="{ color: themeColors.textPrimary }">{{ amenities.length - activeCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amenities Grid -->
        <div class="shadow rounded-lg overflow-hidden border" :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
            <div class="px-6 py-4 border-b" :style="{ borderColor: themeColors.border }">
                <h3 class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">All Amenities</h3>
            </div>

            <!-- Empty State -->
            <div v-if="amenities.length === 0" class="px-6 py-16 text-center">
                <SparklesIcon class="mx-auto h-12 w-12 mb-4" :style="{ color: themeColors.border }" />
                <p class="text-lg font-medium" :style="{ color: themeColors.textPrimary }">No amenities yet</p>
                <p class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">Click "Add Amenity" to create your first amenity.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
                <div v-for="amenity in amenities" :key="amenity.id"
                     class="rounded-lg p-4 border transition-colors"
                     :style="hoveredCard === amenity.id
                         ? { backgroundColor: themeColors.hover, borderColor: themeColors.border }
                         : { backgroundColor: themeColors.background, borderColor: themeColors.border }"
                     @mouseenter="hoveredCard = amenity.id"
                     @mouseleave="hoveredCard = null">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-base" :style="{ color: themeColors.textPrimary }">{{ amenity.name }}</h3>
                            <p v-if="amenity.description" class="text-sm mt-1" :style="{ color: themeColors.textSecondary }">{{ amenity.description }}</p>
                            <div class="mt-3 flex items-center gap-2">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                      :style="amenity.is_active
                                          ? { backgroundColor: themeColors.success, color: '#000' }
                                          : { backgroundColor: themeColors.border, color: themeColors.textSecondary }">
                                    {{ amenity.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="text-xs" :style="{ color: themeColors.textSecondary }">
                                    {{ amenity.room_types_count }} room type{{ amenity.room_types_count !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2 ml-3">
                            <button @click="editAmenity(amenity)"
                                    class="p-1.5 rounded transition-opacity hover:opacity-70"
                                    :style="{ color: themeColors.primary }">
                                <PencilIcon class="h-4 w-4" />
                            </button>
                            <button @click="deleteAmenity(amenity)"
                                    class="p-1.5 rounded transition-opacity hover:opacity-70"
                                    :style="{ color: themeColors.danger }">
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" @click="closeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div @click.stop class="rounded-lg p-6 max-w-md w-full mx-4 shadow-xl border"
                 :style="{ backgroundColor: themeColors.card, borderColor: themeColors.border }">
                <h2 class="text-xl font-bold mb-5" :style="{ color: themeColors.textPrimary }">
                    {{ editingAmenity ? 'Edit' : 'Add' }} Amenity
                </h2>
                <form @submit.prevent="saveAmenity">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Name</label>
                            <input v-model="form.name" type="text" required
                                   class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Icon (optional)</label>
                            <input v-model="form.icon" type="text"
                                   class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                   :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"
                                   placeholder="e.g., wifi, tv, pool" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: themeColors.textSecondary }">Description</label>
                            <textarea v-model="form.description" rows="3"
                                      class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                      :style="{ backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.textPrimary }"></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" id="is_active_manager" class="rounded" />
                            <label for="is_active_manager" class="text-sm font-medium" :style="{ color: themeColors.textSecondary }">Active</label>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                                class="flex-1 py-2 rounded-md font-medium hover:opacity-90 transition-opacity"
                                :style="{ backgroundColor: themeColors.primary, color: '#000' }">
                            Save
                        </button>
                        <button type="button" @click="closeModal"
                                class="flex-1 py-2 rounded-md font-medium border hover:opacity-80 transition-opacity"
                                :style="{ borderColor: themeColors.border, color: themeColors.textSecondary, backgroundColor: themeColors.background }">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { getNavigationForRole } from '@/Utils/navigation.js'
import { useTheme } from '@/Composables/useTheme'
import {
    PlusIcon,
    PencilIcon,
    TrashIcon,
    SparklesIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    amenities: Array,
})

const navigation = computed(() => getNavigationForRole('manager'))

const { currentTheme, loadTheme } = useTheme()
loadTheme()

const themeColors = computed(() => ({
    background: `var(--kotel-background)`,
    card: `var(--kotel-card)`,
    border: `var(--kotel-border)`,
    textPrimary: `var(--kotel-text-primary)`,
    textSecondary: `var(--kotel-text-secondary)`,
    primary: `var(--kotel-primary)`,
    secondary: `var(--kotel-secondary)`,
    success: `var(--kotel-success)`,
    warning: `var(--kotel-warning)`,
    danger: `var(--kotel-danger)`,
    hover: currentTheme.value.theme_mode === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.02)'
}))

const activeCount = computed(() => (props.amenities || []).filter(a => a.is_active).length)

const hoveredCard = ref(null)
const showModal = ref(false)
const editingAmenity = ref(null)
const form = ref({ name: '', icon: '', description: '', is_active: true })

const editAmenity = (amenity) => {
    editingAmenity.value = amenity
    form.value = { ...amenity }
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    editingAmenity.value = null
    form.value = { name: '', icon: '', description: '', is_active: true }
}

const saveAmenity = () => {
    if (editingAmenity.value) {
        router.put(`/manager/room-amenities/${editingAmenity.value.id}`, form.value, { onSuccess: () => closeModal() })
    } else {
        router.post('/manager/room-amenities', form.value, { onSuccess: () => closeModal() })
    }
}

const deleteAmenity = (amenity) => {
    if (confirm(`Delete "${amenity.name}"?`)) {
        router.delete(`/manager/room-amenities/${amenity.id}`)
    }
}
</script>
