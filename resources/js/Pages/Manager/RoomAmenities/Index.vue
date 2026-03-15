<template>
    <DashboardLayout title="Room Amenities" :user="user" :navigation="navigation">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Room Amenities</h1>
                    <p class="text-gray-600 mt-1">Manage amenities and features for room types</p>
                </div>
                <button @click="showModal = true" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Add Amenity
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="amenity in amenities" :key="amenity.id" 
                     class="border rounded-lg p-4 hover:shadow-lg transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg">{{ amenity.name }}</h3>
                            <p v-if="amenity.description" class="text-sm text-gray-600 mt-1">{{ amenity.description }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs px-2 py-1 rounded" :class="amenity.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                    {{ amenity.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="text-xs text-gray-500">{{ amenity.room_types_count }} room types</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="editAmenity(amenity)" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="deleteAmenity(amenity)" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" @click="closeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div @click.stop class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h2 class="text-xl font-bold mb-4 text-gray-900">{{ editingAmenity ? 'Edit' : 'Add' }} Amenity</h2>
                <form @submit.prevent="saveAmenity">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input v-model="form.name" type="text" required 
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon (optional)</label>
                            <input v-model="form.icon" type="text" 
                                   class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                   placeholder="e.g., wifi, tv, pool">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3"
                                      class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.is_active" type="checkbox" class="mr-2">
                            <label class="text-sm font-medium text-gray-700">Active</label>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                            Save
                        </button>
                        <button type="button" @click="closeModal" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300">
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

const props = defineProps({
    user: Object,
    amenities: Array,
})

const navigation = computed(() => getNavigationForRole('admin'))
const showModal = ref(false)
const editingAmenity = ref(null)
const form = ref({
    name: '',
    icon: '',
    description: '',
    is_active: true,
})

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
        router.put(`/admin/room-amenities/${editingAmenity.value.id}`, form.value, {
            onSuccess: () => closeModal()
        })
    } else {
        router.post('/admin/room-amenities', form.value, {
            onSuccess: () => closeModal()
        })
    }
}

const deleteAmenity = (amenity) => {
    if (confirm(`Delete "${amenity.name}"?`)) {
        router.delete(`/admin/room-amenities/${amenity.id}`)
    }
}
</script>
